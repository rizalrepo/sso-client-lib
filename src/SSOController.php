<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SSOController extends Controller
{
    private function getConfig($configName)
    {
        return Config::get('sso.' . $configName);
    }

    public function ssoPage()
    {
        return redirect($this->getConfig('serverUrl'));
    }

    public function getLogin(Request $request)
    {
        $request->session()->put("state", $state = Str::random(40));

        $query = http_build_query([
            "client_id" => $this->getConfig('clientId'),
            "redirect_uri" => $this->getConfig('callbackUrl'),
            "response_type" => "code",
            "scope" => "access-user",
            "state" => $state,
        ]);

        return redirect($this->getConfig('serverUrl') . "/oauth/authorize?" . $query);
    }

    public function getCallback(Request $request)
    {
        $state = $request->session()->pull("state");

        throw_unless(strlen($state) > 0 && $state == $request->state, InvalidArgumentException::class);

        $response = Http::asForm()->post(
            $this->getConfig('serverUrl') . "/oauth/token",
            [
                "grant_type" => "authorization_code",
                "client_id" => $this->getConfig('clientId'),
                "client_secret" => $this->getConfig('clientSecret'),
                "redirect_uri" => $this->getConfig('callbackUrl'),
                "code" => $request->code
            ]
        );

        $request->session()->put($response->json());
        return redirect()->route("sso.connect");
    }

    public function connectUser(Request $request)
    {
        $access_token = $request->session()->get("access_token");
        $response = Http::withHeaders([
            "Accept" => "application/json",
            "Authorization" => "Bearer " . $access_token
        ])->get($this->getConfig('serverUrl') . "/api/user");

        $userArray = $response->json();

        $countAccess = count($userArray['oauth_client_users']);

        $request->session()->put('countAccess', $countAccess);

        $user = User::where("username", $userArray['username'])->first();

        $client = array_filter($userArray['oauth_client_users'], function ($item) {
            return $item['oauth_client_role']['oauth_client']['id'] === $this->getConfig('clientId');
        });

        if (!$user) {
            $user = new User;
            $user->name = $userArray['name'];
            $user->username = $userArray['username'];
            $user->phone = $userArray['phone'];
            $user->prodi = $userArray['prodi'];
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->oauth_client_role_id = reset($client)['oauth_client_role_id'];
            $user->save();
        } else {
            $user->update([
                'name' => $userArray['name'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'],
                'oauth_client_role_id' => reset($client)['oauth_client_role_id'],
            ]);
        }
        Auth::login($user);

        return redirect()->route('home');
    }

    public function logout()
    {
        Auth::logout();
        return redirect($this->getConfig('serverUrl') . "/sso/logout");
    }

    public function portal()
    {
        Auth::logout();
        return redirect($this->getConfig('serverUrl') . "/portal");
    }

    public function editPassword()
    {
        return redirect($this->getConfig('serverUrl') . "/edit-password");
    }

    public function createUserOnServer($userArray)
    {
        $accessToken = session()->get('access_token');
        $serverUrl = $this->getConfig('serverUrl');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $existingUser = $this->getExistingUser($userArray['username'], $headers, $serverUrl);

        if ($existingUser) {
            return $this->createOauthClientUser($existingUser['id'], $userArray['oauth_client_role_id'], $headers, $serverUrl);
        }

        $newUser = $this->createNewUser($userArray, $headers, $serverUrl);

        if ($newUser) {
            return $this->createOauthClientUser($newUser['id'], $userArray['oauth_client_role_id'], $headers, $serverUrl);
        }

        return false;
    }

    private function getExistingUser($username, $headers, $serverUrl)
    {
        $response = Http::withHeaders($headers)
            ->get($serverUrl . '/api/username', ['username' => $username]);

        if ($response->successful()) {
            $existingUsers = $response->json('data');
            return $existingUsers;
        }

        return null;
    }

    private function createNewUser($userArray, $headers, $serverUrl)
    {
        $response = Http::withHeaders($headers)
            ->post($serverUrl . '/api/user', [
                'name' => $userArray['name'],
                'username' => $userArray['username'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'],
                'password' => bcrypt($userArray['username']),
                'is_client' => 1,
                'is_active' => 1,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    private function createOauthClientUser($userId, $clientRoleId, $headers, $serverUrl)
    {
        $response = Http::withHeaders($headers)
            ->post($serverUrl . '/api/oauthClientUsers', [
                'user_id' => $userId,
                'oauth_client_role_id' => $clientRoleId,
            ]);

        return $response->successful();
    }

    public function updateUserOnServer($userArray)
    {
        $accessToken = session()->get('access_token');
        $serverUrl = $this->getConfig('serverUrl');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $existingUser = $this->getExistingUser($userArray['old_username'], $headers, $serverUrl);
        if ($existingUser) {
            $this->updateExistingUser($userArray['old_username'], $userArray, $headers, $serverUrl);
            return true;
        }

        return false;
    }

    private function updateExistingUser($username, $userArray, $headers, $serverUrl)
    {
        $response = Http::withHeaders($headers)
            ->put($serverUrl . '/api/user/' . $username, [
                'name' => $userArray['name'],
                'username' => $userArray['username'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'],
            ]);

        return $response->successful();
    }

    public function deleteUserOnServer($userName)
    {
        $accessToken = session()->get('access_token');
        $serverUrl = $this->getConfig('serverUrl');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $existingUser = $this->getExistingUser($userName, $headers, $serverUrl);
        if ($existingUser) {
            $response = Http::withHeaders($headers)
                ->delete($serverUrl . '/api/user/' . $userName);

            if ($response->successful()) {
                return true;
            } else {
                return false;
            }
        }

        return false;
    }
}
