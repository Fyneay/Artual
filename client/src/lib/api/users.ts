import { apiRequest } from './client';

export interface User {
	id: number;
	email: string;
	nickname: string;
	role_id?: number;
	created_at?: string;
	updated_at?: string;
}

export interface StoreUserRequest {
	nickname: string;
	email: string;
	password: string;
	role_id: number;
}

export interface UserListResponse {
	data: User[];
}

export async function getUsers(): Promise<UserListResponse> {
	return apiRequest<UserListResponse>('/users');
}

export async function getUser(id: number): Promise<{ data: User }> {
	return apiRequest<{ data: User }>(`/users/${id}`);
}

export async function createUser(
	data: StoreUserRequest
): Promise<{ data: User }> {
	return apiRequest<{ data: User }>('/users', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateUser(
	id: number,
	data: Partial<StoreUserRequest>
): Promise<{ data: User }> {
	return apiRequest<{ data: User }>(`/users/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteUser(id: number): Promise<{ data: User }> {
	return apiRequest<{ data: User }>(`/users/${id}`, {
		method: 'DELETE',
	});
}