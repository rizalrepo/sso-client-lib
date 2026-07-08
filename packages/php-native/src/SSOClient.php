<?php

namespace Rizalrepo\SsoClient;

use RuntimeException;

/**
 * HTTP client SSO UNISM — PHP native (tanpa Laravel/framework).
 * OAuth2 Authorization Code + API user management.
 */
class SSOClient
{
    private string $serverUrl;
    private string $clientId;
    private string $clientSecret;
    private string $callbackUrl;
    private string $scope;

    /** @param array{serverUrl: string, clientId: string, clientSecret: string, callbackUrl: string, scope?: string} $config */
    public function __construct(array $config)
    {
        foreach (['serverUrl', 'clientId', 'clientSecret', 'callbackUrl'] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException("SSO config missing: {$key}");
            }
        }

        $this->serverUrl = rtrim($config['serverUrl'], '/');
        $this->clientId = $config['clientId'];
        $this->clientSecret = $config['clientSecret'];
        $this->callbackUrl = $config['callbackUrl'];
        $this->scope = $config['scope'] ?? 'access-user';
    }

    public static function fromEnv(?array $env = null): self
    {
        $env = $env ?? $_ENV;

        $serverUrl = $env['SSO_URL'] ?? $env['SSO_SERVER_URL'] ?? null;
        $clientId = $env['SSO_CLIENT_ID'] ?? null;
        $clientSecret = $env['SSO_CLIENT_SECRET'] ?? null;
        $callbackUrl = $env['SSO_CALLBACK_URL'] ?? null;

        if (!$serverUrl || !$clientId || !$clientSecret || !$callbackUrl) {
            throw new RuntimeException(
                'Missing SSO env: SSO_URL, SSO_CLIENT_ID, SSO_CLIENT_SECRET, SSO_CALLBACK_URL'
            );
        }

        return new self(compact('serverUrl', 'clientId', 'clientSecret', 'callbackUrl'));
    }

    public function generateState(): string
    {
        return bin2hex(random_bytes(20));
    }

    public function getAuthorizeUrl(string $state, $roleId = null): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->callbackUrl,
            'response_type' => 'code',
            'scope' => $this->scope,
            'state' => $state,
        ];

        if ($roleId !== null) {
            $params['role_id'] = (string) $roleId;
        }

        return $this->serverUrl . '/oauth/authorize?' . http_build_query($params);
    }

    /** @return array{token_type: string, expires_in: int, access_token: string, refresh_token?: string} */
    public function exchangeCodeForToken(string $code): array
    {
        [$status, $body] = $this->request('POST', '/oauth/token', [
            'headers' => [
                'Accept: application/json',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri' => $this->callbackUrl,
                'code' => $code,
            ]),
        ]);

        $data = json_decode($body, true) ?? [];

        if ($status < 200 || $status >= 300 || empty($data['access_token'])) {
            $msg = $data['error_description'] ?? $data['error'] ?? "Token exchange failed ({$status})";
            throw new RuntimeException($msg);
        }

        return $data;
    }

    /** @return array<string, mixed> */
    public function getUser(string $accessToken): array
    {
        [$status, $body] = $this->request('GET', '/api/user', [
            'token' => $accessToken,
        ]);

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("Failed to get user ({$status})");
        }

        return json_decode($body, true) ?? [];
    }

    /** @return array<string, mixed> */
    public function verifyToken(string $accessToken): array
    {
        [, $body] = $this->request('GET', '/api/verify-token', ['token' => $accessToken]);

        return json_decode($body, true) ?? [];
    }

    /** @return array<string, mixed> */
    public function verifyTokenFull(string $accessToken): array
    {
        [, $body] = $this->request('GET', '/api/authorize/verify-token', ['token' => $accessToken]);

        return json_decode($body, true) ?? [];
    }

    /** @return array{token: array<string, mixed>, user: array<string, mixed>} */
    public function handleCallback(string $code): array
    {
        $token = $this->exchangeCodeForToken($code);
        $user = $this->getUser($token['access_token']);

        return compact('token', 'user');
    }

    /** @param array<string, mixed> $user */
    public function resolveClientRoleId(array $user, $selectedRoleId = null): ?int
    {
        $clientUsers = array_values(array_filter(
            $user['oauth_client_users'] ?? [],
            fn ($item) => ($item['oauth_client_role']['oauth_client']['id'] ?? null) === $this->clientId
        ));

        if ($selectedRoleId !== null) {
            foreach ($clientUsers as $item) {
                if (($item['oauth_client_role']['id'] ?? null) == $selectedRoleId) {
                    return $item['oauth_client_role_id'] ?? $item['oauth_client_role']['id'] ?? null;
                }
            }
        }

        $first = $clientUsers[0] ?? null;

        return $first['oauth_client_role_id'] ?? $first['oauth_client_role']['id'] ?? null;
    }

    public function getLogoutUrl(): string
    {
        return $this->serverUrl . '/sso/logout';
    }

    public function getPortalUrl(): string
    {
        return $this->serverUrl . '/portal';
    }

    public function getProfileUrl(): string
    {
        return $this->serverUrl . '/profile';
    }

    public function getEditPasswordUrl(): string
    {
        return $this->serverUrl . '/edit-password';
    }

    /** @param array<string, mixed>|null $user */
    public function defaultAvatarUrl(?array $user = null): string
    {
        return $user['avatar_url'] ?? $this->serverUrl . '/assets/images/dashboard/profile.png';
    }

  // --- User management API ---

    public function findUserByUsername(string $accessToken, string $username): mixed
    {
        [$status, $body] = $this->request(
            'GET',
            '/api/username?username=' . rawurlencode($username),
            ['token' => $accessToken]
        );

        if ($status < 200 || $status >= 300) {
            return null;
        }

        $json = json_decode($body, true) ?? [];

        return $json['data'] ?? null;
    }

    /** @param array<string, mixed> $payload */
    public function createUser(string $accessToken, array $payload): mixed
    {
        [$status, $body] = $this->request('POST', '/api/user', [
            'token' => $accessToken,
            'json' => array_merge(['is_client' => true, 'is_active' => true], $payload),
        ]);

        return ($status >= 200 && $status < 300) ? json_decode($body, true) : null;
    }

    public function assignClientRole(string $accessToken, int $userId, int $oauthClientRoleId): bool
    {
        [$status] = $this->request('POST', '/api/oauthClientUsers', [
            'token' => $accessToken,
            'json' => [
                'user_id' => $userId,
                'oauth_client_role_id' => $oauthClientRoleId,
            ],
        ]);

        return $status >= 200 && $status < 300;
    }

    /** @param array<string, mixed> $payload */
    public function updateUser(
        string $accessToken,
        string $oldUsername,
        string $newUsername,
        array $payload
    ): bool {
        [$status] = $this->request(
            'PUT',
            '/api/user/' . rawurlencode($oldUsername) . '/' . rawurlencode($newUsername),
            ['token' => $accessToken, 'json' => $payload]
        );

        return $status >= 200 && $status < 300;
    }

    public function setUserActive(string $accessToken, string $username, bool $isActive): bool
    {
        [$status] = $this->request(
            'POST',
            '/api/user/actived/' . rawurlencode($username),
            ['token' => $accessToken, 'json' => ['is_active' => $isActive]]
        );

        return $status >= 200 && $status < 300;
    }

    public function deleteUser(string $accessToken, string $username, int $oauthClientRoleId): bool
    {
        [$status] = $this->request(
            'DELETE',
            '/api/user/' . rawurlencode($username),
            ['token' => $accessToken, 'json' => ['oauth_client_role_id' => $oauthClientRoleId]]
        );

        return $status >= 200 && $status < 300;
    }

    /**
     * @param array{token?: string, headers?: string[], body?: string, json?: array<string, mixed>} $options
     * @return array{0: int, 1: string}
     */
    private function request(string $method, string $path, array $options = []): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP curl extension required');
        }

        $url = $this->serverUrl . $path;
        $ch = curl_init($url);

        $headers = $options['headers'] ?? ['Accept: application/json'];

        if (!empty($options['token'])) {
            $headers[] = 'Authorization: Bearer ' . $options['token'];
        }

        if (!empty($options['json'])) {
            $headers[] = 'Content-Type: application/json';
            $options['body'] = json_encode($options['json']);
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);

        if (!empty($options['body'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
        }

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('HTTP request failed: ' . $error);
        }

        curl_close($ch);

        return [$status, $body];
    }
}

// ponytail: self-check — php packages/php-native/src/SSOClient.php
if (PHP_SAPI === 'cli' && ($argv[0] ?? '') === __FILE__) {
    $c = new SSOClient([
        'serverUrl' => 'https://example.com',
        'clientId' => 'id',
        'clientSecret' => 'secret',
        'callbackUrl' => 'https://app.example.com/callback',
    ]);
    assert(str_contains($c->getAuthorizeUrl('abc123'), 'client_id=id'));
    assert($c->generateState() !== '');
    echo "OK\n";
}
