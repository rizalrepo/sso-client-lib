/** Konfigurasi client SSO — credential dari .env, jangan hardcode */
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

export interface OAuthClientRole {
	id: number;
	oauth_client_id: string;
	role_id: number;
	oauth_client?: { id: string; name: string; redirect: string };
	role?: { id: number; name: string };
}

export interface OAuthClientUser {
	id: number;
	user_id: number;
	oauth_client_role_id: number;
	oauth_client_role: OAuthClientRole;
}

export interface SSOUser {
	id: number;
	name: string;
	username: string;
	email?: string;
	phone?: string;
	prodi?: string;
	is_client?: boolean;
	is_active?: boolean;
	foto?: string;
	avatar_url?: string;
	email_verified_at?: string;
	oauth_client_users: OAuthClientUser[];
}

export interface VerifyTokenMinimal {
	valid: boolean;
	token_id?: number;
	user_id?: number;
	scopes?: string[];
	error?: string;
}

export interface VerifyTokenFull {
	status: boolean;
	message: string;
	data?: {
		token_id: number;
		username: string;
		role: number;
		client_id: string;
	};
}

export interface CreateUserPayload {
	name: string;
	username: string;
	phone?: string;
	prodi?: string;
	password: string;
	is_client?: boolean;
	is_active?: boolean;
	email?: string;
}
