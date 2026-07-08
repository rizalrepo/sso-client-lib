<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Rizalrepo\SsoClient\SSOClient;

class SSOController extends Controller
{
    private ?SSOClient $client = null;

    private function sso(): SSOClient
    {
        return $this->client ??= new SSOClient([
            'serverUrl'    => Config::get('sso.serverUrl'),
            'clientId'     => Config::get('sso.clientId'),
            'clientSecret' => Config::get('sso.clientSecret'),
            'callbackUrl'  => Config::get('sso.callbackUrl'),
        ]);
    }

    private function token(): string
    {
        return session()->get('access_token');
    }

    private function ok(int $status): bool
    {
        return $status >= 200 && $status < 300;
    }

    public function ssoPage()
    {
        return redirect(Config::get('sso.serverUrl'));
    }

    public function getLogin(Request $request)
    {
        $sso = $this->sso();
        $state = $sso->generateState();
        $request->session()->put('state', $state);

        if ($request->has('role_id')) {
            $request->session()->put('selected_role_id', $request->query('role_id'));
        }

        return redirect($sso->getAuthorizeUrl($state, $request->query('role_id')));
    }

    public function getCallback(Request $request)
    {
        $state = $request->session()->pull('state');

        if (empty($state) || $state !== $request->state) {
            return redirect()->route('sso.login')->withErrors([
                'message' => 'Invalid state parameter. Please try logging in again.',
            ]);
        }

        try {
            $tokenData = $this->sso()->exchangeCodeForToken($request->code);
        } catch (\RuntimeException $e) {
            Log::error('[SSO] Token exchange failed', ['error' => $e->getMessage()]);
            $request->session()->forget(['access_token', 'refresh_token', 'token_type', 'expires_in']);

            return redirect()->route('sso.login')->withErrors([
                'message' => 'SSO authentication failed. Please try again.',
            ]);
        }

        $request->session()->forget(['access_token', 'refresh_token', 'token_type', 'expires_in']);
        $request->session()->put($tokenData);

        return redirect()->route('sso.connect');
    }

    public function connectUser(Request $request)
    {
        $sso = $this->sso();
        $accessToken = $this->token();
        $userArray = $sso->getUser($accessToken);

        $request->session()->put([
            'countAccess' => count($userArray['oauth_client_users'] ?? []),
            'avatar' => $userArray['avatar_url'] ?? $sso->ssoUrl('assets/images/dashboard/profile.png'),
            'access_token' => $accessToken,
            'last_sso_profile_refresh' => time(),
        ]);

        $selectedRoleId = $request->session()->pull('selected_role_id') ?? $request->query('role_id');
        $oauthClientRoleId = $sso->resolveClientRoleId($userArray, $selectedRoleId);

        $user = User::where('username', $userArray['username'])->first();

        if (!$user) {
            $user = new User;
            $user->name = $userArray['name'];
            $user->username = $userArray['username'];
            $user->phone = $userArray['phone'];
            $user->prodi = $userArray['prodi'] ?: null;
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->oauth_client_role_id = $oauthClientRoleId;
            $user->save();
        } else {
            $user->update([
                'name' => $userArray['name'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'] ?: $user->prodi,
                'oauth_client_role_id' => $oauthClientRoleId,
            ]);
        }

        Auth::login($user);

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();
        return redirect($this->sso()->ssoUrl('sso/logout'));
    }

    public function portal()
    {
        Auth::logout();
        return redirect($this->sso()->ssoUrl('portal'));
    }

    public function editPassword()
    {
        return redirect($this->sso()->ssoUrl('edit-password'));
    }

    public function editProfile()
    {
        return redirect($this->sso()->ssoUrl('profile'));
    }

    public function createUserOnServer($userArray)
    {
        $sso = $this->sso();
        $token = $this->token();
        [$status, $existing] = $sso->api(
            'GET',
            '/api/username?username=' . rawurlencode($userArray['username']),
            $token
        );

        if ($this->ok($status) && !empty($existing['data'])) {
            return $this->ok($sso->api('POST', '/api/oauthClientUsers', $token, [
                'user_id' => $existing['data']['id'],
                'oauth_client_role_id' => $userArray['oauth_client_role_id'],
            ])[0]);
        }

        [$status, $newUser] = $sso->api('POST', '/api/user', $token, [
            'name' => $userArray['name'],
            'username' => $userArray['username'],
            'phone' => $userArray['phone'],
            'prodi' => $userArray['prodi'],
            'password' => bcrypt($userArray['username']),
            'is_client' => true,
            'is_active' => $userArray['is_active'] ?? 1,
        ]);

        if (!$this->ok($status) || !$newUser) {
            return false;
        }

        return $this->ok($sso->api('POST', '/api/oauthClientUsers', $token, [
            'user_id' => $newUser['id'],
            'oauth_client_role_id' => $userArray['oauth_client_role_id'],
        ])[0]);
    }

    public function updateUserOnServer($userArray)
    {
        $sso = $this->sso();
        $token = $this->token();
        [$status] = $sso->api(
            'PUT',
            '/api/user/' . rawurlencode($userArray['old_username']) . '/' . rawurlencode($userArray['username']),
            $token,
            [
                'name' => $userArray['name'],
                'username' => $userArray['username'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'],
                'oauth_client_role_id' => $userArray['oauth_client_role_id'],
            ]
        );

        return $this->ok($status);
    }

    public function updateUserActiveOnServer($userArray)
    {
        [$status] = $this->sso()->api(
            'POST',
            '/api/user/actived/' . rawurlencode($userArray['username']),
            $this->token(),
            ['is_active' => $userArray['is_active']]
        );

        return $this->ok($status);
    }

    public function deleteUserOnServer($userData)
    {
        [$status] = $this->sso()->api(
            'DELETE',
            '/api/user/' . rawurlencode($userData['username']),
            $this->token(),
            ['oauth_client_role_id' => $userData['oauth_client_role_id']]
        );

        return $this->ok($status);
    }
}
