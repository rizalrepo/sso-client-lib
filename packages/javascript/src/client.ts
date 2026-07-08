import type {
	CreateUserPayload,
	SSOConfig,
	SSOUser,
	TokenResponse,
	VerifyTokenFull,
	VerifyTokenMinimal,
} from "./types.js";

/**
 * HTTP client SSO UNISM — bahasa-agnostik (Node 18+, Bun, Deno, browser).
 * OAuth2 Authorization Code + API user management.
 */
export class SSOClient {
	private readonly config: Required<SSOConfig>;

	constructor(config: SSOConfig) {
		this.config = {
			scope: "access-user",
			...config,
			serverUrl: config.serverUrl.replace(/\/$/, ""),
		};
	}

	/** CSRF state untuk OAuth authorize */
	generateState(): string {
		const bytes = new Uint8Array(20);
		crypto.getRandomValues(bytes);
		return Array.from(bytes, (b) => b.toString(16).padStart(2, "0")).join("");
	}

	/** URL redirect ke SSO login */
	getAuthorizeUrl(state: string, roleId?: string | number): string {
		const params = new URLSearchParams({
			client_id: this.config.clientId,
			redirect_uri: this.config.callbackUrl,
			response_type: "code",
			scope: this.config.scope,
			state,
		});
		if (roleId != null) params.set("role_id", String(roleId));
		return `${this.config.serverUrl}/oauth/authorize?${params}`;
	}

	/** Tukar authorization code → access token */
	async exchangeCodeForToken(code: string): Promise<TokenResponse> {
		const body = new URLSearchParams({
			grant_type: "authorization_code",
			client_id: this.config.clientId,
			client_secret: this.config.clientSecret,
			redirect_uri: this.config.callbackUrl,
			code,
		});

		const res = await fetch(`${this.config.serverUrl}/oauth/token`, {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body,
		});

		const data = (await res.json()) as TokenResponse & {
			error?: string;
			error_description?: string;
		};

		if (!res.ok || !data.access_token) {
			throw new Error(
				data.error_description ?? data.error ?? `Token exchange failed (${res.status})`,
			);
		}

		return data;
	}

	/** Profil user + oauth_client_users */
	async getUser(accessToken: string): Promise<SSOUser> {
		const res = await this.fetchApi("/api/user", accessToken);
		if (!res.ok) throw new Error(`Failed to get user (${res.status})`);
		return res.json() as Promise<SSOUser>;
	}

	/** Verifikasi token — response minimal */
	async verifyToken(accessToken: string): Promise<VerifyTokenMinimal> {
		const res = await this.fetchApi("/api/verify-token", accessToken);
		return res.json() as Promise<VerifyTokenMinimal>;
	}

	/** Verifikasi token — response lengkap (username, role, client_id) */
	async verifyTokenFull(accessToken: string): Promise<VerifyTokenFull> {
		const res = await this.fetchApi("/api/authorize/verify-token", accessToken);
		return res.json() as Promise<VerifyTokenFull>;
	}

	/** Alur callback lengkap: code → token → user */
	async handleCallback(code: string): Promise<{ token: TokenResponse; user: SSOUser }> {
		const token = await this.exchangeCodeForToken(code);
		const user = await this.getUser(token.access_token);
		return { token, user };
	}

	/** Resolve oauth_client_role_id untuk client ini */
	resolveClientRoleId(
		user: SSOUser,
		selectedRoleId?: string | number | null,
	): number | null {
		const clientUsers = user.oauth_client_users.filter(
			(item) => item.oauth_client_role?.oauth_client?.id === this.config.clientId,
		);

		if (selectedRoleId != null) {
			const match = clientUsers.find(
				(item) => item.oauth_client_role?.id === Number(selectedRoleId),
			);
			if (match) {
				return match.oauth_client_role_id ?? match.oauth_client_role?.id ?? null;
			}
		}

		const first = clientUsers[0];
		return first?.oauth_client_role_id ?? first?.oauth_client_role?.id ?? null;
	}

	getLogoutUrl(): string {
		return `${this.config.serverUrl}/sso/logout`;
	}

	getPortalUrl(): string {
		return `${this.config.serverUrl}/portal`;
	}

	getProfileUrl(): string {
		return `${this.config.serverUrl}/profile`;
	}

	getEditPasswordUrl(): string {
		return `${this.config.serverUrl}/edit-password`;
	}

	defaultAvatarUrl(user?: Pick<SSOUser, "avatar_url">): string {
		return user?.avatar_url ?? `${this.config.serverUrl}/assets/images/dashboard/profile.png`;
	}

	// --- User management API (scope write-user / access-user) ---

	async findUserByUsername(accessToken: string, username: string) {
		const res = await this.fetchApi(
			`/api/username?username=${encodeURIComponent(username)}`,
			accessToken,
		);
		if (!res.ok) return null;
		const json = (await res.json()) as { data?: unknown };
		return json.data ?? null;
	}

	async createUser(accessToken: string, payload: CreateUserPayload) {
		const res = await this.fetchApi("/api/user", accessToken, {
			method: "POST",
			body: JSON.stringify({
				is_client: true,
				is_active: true,
				...payload,
			}),
		});
		if (!res.ok) return null;
		return res.json();
	}

	async assignClientRole(
		accessToken: string,
		userId: number,
		oauthClientRoleId: number,
	): Promise<boolean> {
		const res = await this.fetchApi("/api/oauthClientUsers", accessToken, {
			method: "POST",
			body: JSON.stringify({
				user_id: userId,
				oauth_client_role_id: oauthClientRoleId,
			}),
		});
		return res.ok;
	}

	async updateUser(
		accessToken: string,
		oldUsername: string,
		newUsername: string,
		payload: Record<string, unknown>,
	): Promise<boolean> {
		const res = await this.fetchApi(
			`/api/user/${encodeURIComponent(oldUsername)}/${encodeURIComponent(newUsername)}`,
			accessToken,
			{ method: "PUT", body: JSON.stringify(payload) },
		);
		return res.ok;
	}

	async setUserActive(
		accessToken: string,
		username: string,
		isActive: boolean,
	): Promise<boolean> {
		const res = await this.fetchApi(
			`/api/user/actived/${encodeURIComponent(username)}`,
			accessToken,
			{ method: "POST", body: JSON.stringify({ is_active: isActive }) },
		);
		return res.ok;
	}

	async deleteUser(
		accessToken: string,
		username: string,
		oauthClientRoleId: number,
	): Promise<boolean> {
		const res = await this.fetchApi(
			`/api/user/${encodeURIComponent(username)}`,
			accessToken,
			{
				method: "DELETE",
				body: JSON.stringify({ oauth_client_role_id: oauthClientRoleId }),
			},
		);
		return res.ok;
	}

	private fetchApi(path: string, accessToken: string, init: RequestInit = {}) {
		return fetch(`${this.config.serverUrl}${path}`, {
			...init,
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${accessToken}`,
				...(init.body ? { "Content-Type": "application/json" } : {}),
				...init.headers,
			},
		});
	}
}

/** Buat client dari env vars (Node/Bun) — pass `env` eksplisit di browser/Deno */
export function createClientFromEnv(
	env?: Record<string, string | undefined>,
): SSOClient {
	const source =
		env ??
		(typeof process !== "undefined" && process.env ? process.env : {});
	const serverUrl = source.SSO_URL ?? source.SSO_SERVER_URL;
	const clientId = source.SSO_CLIENT_ID;
	const clientSecret = source.SSO_CLIENT_SECRET;
	const callbackUrl = source.SSO_CALLBACK_URL;

	if (!serverUrl || !clientId || !clientSecret || !callbackUrl) {
		throw new Error(
			"Missing SSO env: SSO_URL, SSO_CLIENT_ID, SSO_CLIENT_SECRET, SSO_CALLBACK_URL",
		);
	}

	return new SSOClient({ serverUrl, clientId, clientSecret, callbackUrl });
}
