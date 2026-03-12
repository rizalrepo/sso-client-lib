<?php

namespace App\Http\Controllers\SSO;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        // Simpan role_id ke session jika ada (dari pilihan user di portal)
        if ($request->has('role_id')) {
            $request->session()->put("selected_role_id", $request->query('role_id'));
        }

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

        // Validasi state parameter untuk keamanan OAuth
        if (empty($state) || $state !== $request->state) {
            return redirect()->route('sso.login')->withErrors([
                'message' => 'Invalid state parameter. Silakan coba login kembali.'
            ]);
        }

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

        // [V-SEC-19 FIX] Validasi response token — cegah bypass via session lama
        $tokenData = $response->json();

        if ($response->failed() || ! isset($tokenData['access_token'])) {
            Log::error('[SSO] Token exchange failed', [
                'status' => $response->status(),
                'error' => $tokenData['error'] ?? 'unknown',
                'error_description' => $tokenData['error_description'] ?? '',
            ]);

            // Bersihkan token lama dari session agar tidak bisa dipakai ulang
            $request->session()->forget(['access_token', 'refresh_token', 'token_type', 'expires_in']);

            return redirect()->route('sso.login')->withErrors([
                'message' => 'Gagal autentikasi dengan SSO. Silakan coba login kembali.'
            ]);
        }

        // Bersihkan token lama DULU, lalu simpan yang baru
        $request->session()->forget(['access_token', 'refresh_token', 'token_type', 'expires_in']);
        $request->session()->put($tokenData);

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
        $avatar = $userArray['avatar_url']
            ?? ($this->getConfig('serverUrl') . '/assets/images/dashboard/profile.png');

        $request->session()->put(
            [
                'countAccess' => $countAccess,
                'avatar' => $avatar,
                'access_token' => $access_token,
                'last_sso_profile_refresh' => time(),
            ]
        );

        $user = User::where("username", $userArray['username'])->first();

        // Filter oauth_client_users berdasarkan clientId yang sesuai
        $client = array_filter($userArray['oauth_client_users'], function ($item) {
            return $item['oauth_client_role']['oauth_client']['id'] === $this->getConfig('clientId');
        });

        // Baca role_id dari session (disimpan saat getLogin) atau dari query parameter
        $selectedRoleId = $request->session()->pull('selected_role_id') ?? $request->query('role_id');
        $oauthClientRoleId = null;

        if ($selectedRoleId) {
            // Cari oauth_client_role_id yang sesuai dengan role_id yang dipilih
            $selectedClient = array_filter($client, function ($item) use ($selectedRoleId) {
                // Cek apakah oauth_client_role['id'] sesuai dengan selectedRoleId
                return isset($item['oauth_client_role']['id']) && $item['oauth_client_role']['id'] == $selectedRoleId;
            });

            if (!empty($selectedClient)) {
                $selectedItem = reset($selectedClient);
                // Coba ambil oauth_client_role_id langsung, jika tidak ada gunakan nested structure
                $oauthClientRoleId = $selectedItem['oauth_client_role_id'] ?? $selectedItem['oauth_client_role']['id'] ?? null;
            }
        }

        // Fallback: jika role_id tidak valid atau tidak ada, ambil yang pertama
        if (!$oauthClientRoleId && !empty($client)) {
            $firstItem = reset($client);
            // Coba ambil oauth_client_role_id langsung, jika tidak ada gunakan nested structure
            $oauthClientRoleId = $firstItem['oauth_client_role_id'] ?? $firstItem['oauth_client_role']['id'] ?? null;
        }

        if (!$user) {
            $user = new User;
            $user->name = $userArray['name'];
            $user->username = $userArray['username'];
            $user->phone = $userArray['phone'];
            $user->prodi = !empty($userArray['prodi']) ? $userArray['prodi'] : null;
            $user->email_verified_at = $userArray['email_verified_at'];
            $user->oauth_client_role_id = $oauthClientRoleId;
            $user->save();

            // Log new user creation with prodi info
            if (empty($userArray['prodi'])) {
                Log::warning('[PRODI_SYNC] New user created without prodi', [
                    'username' => $userArray['username'],
                ]);
            }
        } else {
            $oldProdi = $user->prodi;
            $newProdi = $userArray['prodi'];

            // Build update data - always update name, phone, role
            $updateData = [
                'name' => $userArray['name'],
                'phone' => $userArray['phone'],
                'oauth_client_role_id' => $oauthClientRoleId,
            ];

            // Handle prodi sync with logging
            if (empty($newProdi)) {
                // SSO returned null/empty prodi - keep existing, log warning
                Log::warning('[PRODI_SYNC] SSO returned empty prodi, keeping existing', [
                    'username' => $userArray['username'],
                    'existing_prodi' => $oldProdi,
                ]);
            } elseif ($oldProdi !== $newProdi) {
                // Prodi changed - update and log
                $updateData['prodi'] = $newProdi;
                Log::info('[PRODI_SYNC] Prodi changed', [
                    'username' => $userArray['username'],
                    'old' => $oldProdi,
                    'new' => $newProdi,
                ]);
            } else {
                // Prodi unchanged - still include in update (no change, no log needed)
                $updateData['prodi'] = $newProdi;
            }

            $user->update($updateData);
        }

        Auth::login($user);

        $redirect = redirect()->route('home');

        return $redirect;
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

    public function editProfile()
    {
        return redirect($this->getConfig('serverUrl') . "/profile");
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
                'is_active' => $userArray['is_active'] ?? 1,
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
            $this->updateExistingUser($userArray['old_username'], $userArray['username'], $userArray, $headers, $serverUrl);
            return true;
        }

        return false;
    }

    private function updateExistingUser($username, $newUsername, $userArray, $headers, $serverUrl)
    {
        $response = Http::withHeaders($headers)
            ->put($serverUrl . '/api/user/' . $username . '/' . $newUsername, [
                'name' => $userArray['name'],
                'username' => $userArray['username'],
                'phone' => $userArray['phone'],
                'prodi' => $userArray['prodi'],
                'oauth_client_role_id' => $userArray['oauth_client_role_id'],
            ]);

        return $response->successful();
    }

    public function updateUserActiveOnServer($userArray)
    {
        $accessToken = session()->get('access_token');
        $serverUrl = $this->getConfig('serverUrl');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        // Mengambil data user yang ada berdasarkan username
        $existingUser = $this->getExistingUser($userArray['username'], $headers, $serverUrl);

        if ($existingUser) {
            // Memperbarui status aktif user
            $response = Http::withHeaders($headers)
                ->post($serverUrl . '/api/user/actived/' . $userArray['username'], [
                    'is_active' => $userArray['is_active']
                ]);

            return $response->successful();
        }

        return false;
    }

    public function deleteUserOnServer($userData)
    {
        $accessToken = session()->get('access_token');
        $serverUrl = $this->getConfig('serverUrl');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $accessToken,
        ];

        $existingUser = $this->getExistingUser($userData['username'], $headers, $serverUrl);
        if ($existingUser) {
            $response = Http::withHeaders($headers)
                ->delete($serverUrl . '/api/user/' . $userData['username'], [
                    'oauth_client_role_id' => $userData['oauth_client_role_id']
                ]);

            if ($response->successful()) {
                return true;
            }
            return false;
        }

        return false;
    }
}
