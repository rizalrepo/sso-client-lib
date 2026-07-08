export interface SSOConfig {
	serverUrl: string;
	clientId: string;
	clientSecret: string;
	callbackUrl: string;
}

export interface SSOUser {
	name: string;
	username: string;
	phone?: string;
	email?: string;
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
	private readonly serverUrl: string;
	private readonly clientId: string;
	private readonly clientSecret: string;
	private readonly callbackUrl: string;

	constructor(config: SSOConfig) {
		this.serverUrl = config.serverUrl.replace(/\/$/, "");
		this.clientId = config.clientId;
		this.clientSecret = config.clientSecret;
		this.callbackUrl = config.callbackUrl;
	}

	generateState(): string {
		return crypto.randomUUID().replace(/-/g, "");
	}

	ssoUrl(path: string): string {
		return `${this.serverUrl}/${path.replace(/^\//, "")}`;
	}

	getAuthorizeUrl(state: string, roleId?: string | number): string {
		const params = new URLSearchParams({
			client_id: this.clientId,
			redirect_uri: this.callbackUrl,
			response_type: "code",
			scope: "access-user",
			state,
		});
		if (roleId != null) params.set("role_id", String(roleId));
		return `${this.ssoUrl("oauth/authorize")}?${params}`;
	}

	async exchangeCodeForToken(code: string) {
		const res = await fetch(this.ssoUrl("oauth/token"), {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/x-www-form-urlencoded",
			},
			body: new URLSearchParams({
				grant_type: "authorization_code",
				client_id: this.clientId,
				client_secret: this.clientSecret,
				redirect_uri: this.callbackUrl,
				code,
			}),
		});

		const data = await res.json();
		if (!res.ok || !data.access_token) {
			throw new Error(data.error_description ?? data.error ?? `Token exchange failed (${res.status})`);
		}
		return data;
	}

	async getUser(accessToken: string): Promise<SSOUser> {
		const res = await this.fetchApi("api/user", accessToken);
		if (!res.ok) throw new Error(`Failed to get user (${res.status})`);
		return res.json();
	}

	async verifyToken(accessToken: string, full = false) {
		const path = full ? "api/authorize/verify-token" : "api/verify-token";
		return (await this.fetchApi(path, accessToken)).json();
	}

	resolveClientRoleId(user: SSOUser, selectedRoleId?: string | number | null): number | null {
		const clientUsers = user.oauth_client_users.filter(
			(item) => item.oauth_client_role?.oauth_client?.id === this.clientId,
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

	private fetchApi(path: string, accessToken: string) {
		return fetch(this.ssoUrl(path), {
			headers: {
				Accept: "application/json",
				Authorization: `Bearer ${accessToken}`,
			},
		});
	}
}
