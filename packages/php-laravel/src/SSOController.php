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
    private function sso(): SSOClient
    {
        return new SSOClient([
            'serverUrl'    => Config::get('sso.serverUrl'),
            'clientId'     => Config::get('sso.clientId'),
            'clientSecret' => Config::get('sso.clientSecret'),
            'callbackUrl'  => Config::get('sso.callbackUrl'),
        ]);
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
        $accessToken = $request->session()->get('access_token');
        $userArray = $sso->getUser($accessToken);

        $request->session()->put([
            'countAccess' => count($userArray['oauth_client_users'] ?? []),
            'avatar' => $sso->defaultAvatarUrl($userArray),
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
            $user->prodi = !empty($userArray['prodi']) ? $userArray['prodi'] : null;
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->oauth_client_role_id = $oauthClientRoleId;
            $user->save();

            if (empty($userArray['prodi'])) {
                Log::warning('[PRODI_SYNC] New user created without prodi', [
                    'username' => $userArray['username'],
                ]);
            }
        } else {
            $oldProdi = $user->prodi;
            $newProdi = $userArray['prodi'];
            $updateData = [
                'name' => $userArray['name'],
                'phone' => $userArray['phone'],
                'oauth_client_role_id' => $oauthClientRoleId,
            ];

            if (empty($newProdi)) {
                Log::warning('[PRODI_SYNC] SSO returned empty prodi, keeping existing', [
                    'username' => $userArray['username'],
                    'existing_prodi' => $oldProdi,
                ]);
            } elseif ($oldProdi !== $newProdi) {
                $updateData['prodi'] = $newProdi;
                Log::info('[PRODI_SYNC] Prodi changed', [
                    'username' => $userArray['username'],
                    'old' => $oldProdi,
                    'new' => $newProdi,
                ]);
            } else {
                $updateData['prodi'] = $newProdi;
            }

            $user->update($updateData);
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
        $token = session()->get('access_token');
        $existing = $sso->findUserByUsername($token, $userArray['username']);

        if ($existing) {
            return $sso->assignClientRole($token, $existing['id'], $userArray['oauth_client_role_id']);
        }

        $newUser = $sso->createUser($token, [
            'name' => $userArray['name'],
            'username' => $userArray['username'],
            'phone' => $userArray['phone'],
            'prodi' => $userArray['prodi'],
            'password' => bcrypt($userArray['username']),
            'is_active' => $userArray['is_active'] ?? 1,
        ]);

        return $newUser
            ? $sso->assignClientRole($token, $newUser['id'], $userArray['oauth_client_role_id'])
            : false;
    }

    public function updateUserOnServer($userArray)
    {
        $sso = $this->sso();
        $token = session()->get('access_token');

        if (!$sso->findUserByUsername($token, $userArray['old_username'])) {
            return false;
        }

        return $sso->updateUser($token, $userArray['old_username'], $userArray['username'], [
            'name' => $userArray['name'],
            'username' => $userArray['username'],
            'phone' => $userArray['phone'],
            'prodi' => $userArray['prodi'],
            'oauth_client_role_id' => $userArray['oauth_client_role_id'],
        ]);
    }

    public function updateUserActiveOnServer($userArray)
    {
        $sso = $this->sso();
        $token = session()->get('access_token');

        if (!$sso->findUserByUsername($token, $userArray['username'])) {
            return false;
        }

        return $sso->setUserActive($token, $userArray['username'], (bool) $userArray['is_active']);
    }

    public function deleteUserOnServer($userData)
    {
        $sso = $this->sso();
        $token = session()->get('access_token');

        if (!$sso->findUserByUsername($token, $userData['username'])) {
            return false;
        }

        return $sso->deleteUser($token, $userData['username'], $userData['oauth_client_role_id']);
    }
}
