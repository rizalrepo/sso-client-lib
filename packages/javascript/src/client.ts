export interface SSOConfig {
	serverUrl: string;
	clientId: string;
	clientSecret: string;
	callbackUrl: string;
	scope?: string;
}

export interface TokenResponse {
	token_type: string;
	expires_in: number;
	access_token: string;
	refresh_token?: string;
}

export interface SSOUser {
	id: number;
	name: string;
	username: string;
	email?: string;
	phone?: string;
	prodi?: string;
	avatar_url?: string;
	oauth_client_users: Array<{
		oauth_client_role_id?: number;
		oauth_client_role: {
			id: number;
			oauth_client?: { id: string };
		};
	}>;
}

/** OAuth2 HTTP client for UNISM SSO (Node 18+, Bun, Deno, browser). */
export class SSOClient {
	private readonly config: Required<SSOConfig>;

	constructor(config: SSOConfig) {
		this.config = {
			scope: "access-user",
			...config,
			serverUrl: config.serverUrl.replace(/\/$/, ""),
		};
	}

	generateState(): string {
		const bytes = new Uint8Array(20);
		crypto.getRandomValues(bytes);
		return Array.from(bytes, (b) => b.toString(16).padStart(2, "0")).join("");
	}

	ssoUrl(path: string): string {
		return `${this.config.serverUrl}/${path.replace(/^\//, "")}`;
	}

	getAuthorizeUrl(state: string, roleId?: string | number): string {
		const params = new URLSearchParams({
			client_id: this.config.clientId,
			redirect_uri: this.config.callbackUrl,
			response_type: "code",
			scope: this.config.scope,
			state,
		});
		if (roleId != null) params.set("role_id", String(roleId));
		return `${this.ssoUrl("oauth/authorize")}?${params}`;
	}

	async exchangeCodeForToken(code: string): Promise<TokenResponse> {
		const body = new URLSearchParams({
			grant_type: "authorization_code",
			client_id: this.config.clientId,
			client_secret: this.config.clientSecret,
			redirect_uri: this.config.callbackUrl,
			code,
		});

		const res = await fetch(this.ssoUrl("oauth/token"), {
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

	async getUser(accessToken: string): Promise<SSOUser> {
		const res = await this.fetchApi("/api/user", accessToken);
		if (!res.ok) throw new Error(`Failed to get user (${res.status})`);
		return res.json() as Promise<SSOUser>;
	}

	async verifyToken(accessToken: string, full = false): Promise<Record<string, unknown>> {
		const path = full ? "/api/authorize/verify-token" : "/api/verify-token";
		const res = await this.fetchApi(path, accessToken);
		return res.json() as Promise<Record<string, unknown>>;
	}

	async handleCallback(code: string): Promise<{ token: TokenResponse; user: SSOUser }> {
		const token = await this.exchangeCodeForToken(code);
		const user = await this.getUser(token.access_token);
		return { token, user };
	}

	resolveClientRoleId(user: SSOUser, selectedRoleId?: string | number | null): number | null {
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

	defaultAvatarUrl(user?: Pick<SSOUser, "avatar_url">): string {
		return user?.avatar_url ?? this.ssoUrl("assets/images/dashboard/profile.png");
	}

	private fetchApi(path: string, accessToken: string, init: RequestInit = {}) {
		return fetch(`${this.config.serverUrl}${path}`, {
			...init,
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${accessToken}`,
				...init.headers,
			},
		});
	}
}
