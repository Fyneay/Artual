import { apiRequest, setAuthToken, setCurrentUser } from './client';
import type { ApiError } from './client';

export interface Invite {
	key: string;
	email: string;
	created_at: string;
	expires_at: string;
	user_id: number;
	user_role_id: number;
	used: boolean;
}

export interface InviteListResponse {
	data: Invite[];
	meta?: {
		request_id: string;
		generated_at: string;
		invite_status?: string;
	};
}

export interface StoreInviteRequest {
	email: string;
	user_id: number;
	user_role_id: number;
	created_at: string;
	expires_at: string;
	ttl?: number;
}

export interface StoreInviteResponse {
	message: string;
	data: {
		key: string;
		invite_url: string;
		expires_at: string;
	};
}

export interface AcceptInviteRequest {
	password: string;
	password_confirmation: string;
	nickname: string;
}

export interface AcceptInviteResponse {
	message: string;
	data: {
		user: {
			id: number;
			email: string;
			nickname: string;
		};
		token: string;
	};
}

const API_BASE_URL = 'https://localhost/api';

export async function getInvites(): Promise<InviteListResponse> {
	return apiRequest<InviteListResponse>('/invites');
}

export async function getInvite(key: string): Promise<{ data: Invite }> {
	return apiRequest<{ data: Invite }>(`/invites/${key}`);
}

export async function createInvite(
	data: StoreInviteRequest
): Promise<StoreInviteResponse> {
	return apiRequest<StoreInviteResponse>('/invites', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function acceptInvite(
	key: string,
	data: AcceptInviteRequest
): Promise<AcceptInviteResponse> {
	try {
		const response = await apiRequest<AcceptInviteResponse>(`/invites/${key}/accept`, {
			method: 'POST',
			body: JSON.stringify(data),
		});

		if (response.data?.token) {
			setAuthToken(response.data.token);
		}
		
		if (response.data?.user) {
			setCurrentUser(response.data.user);
		}
		
		return response;
	} catch (error) {
		throw error;
	}
}

export async function deleteInvite(key: string): Promise<{ message: string }> {
	return apiRequest<{ message: string }>(`/invites/${key}`, {
		method: 'DELETE',
	});
}