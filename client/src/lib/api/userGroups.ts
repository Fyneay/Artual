import { apiRequest } from './client';

export interface UserGroup {
	id: number;
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface StoreUserGroupRequest {
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface UserGroupListResponse {
	data: UserGroup[];
}

export async function getUserGroups(): Promise<UserGroupListResponse> {
	return apiRequest<UserGroupListResponse>('/groups');
}

export async function getUserGroup(id: number): Promise<{ data: UserGroup }> {
	return apiRequest<{ data: UserGroup }>(`/groups/${id}`);
}

export async function createUserGroup(
	data: StoreUserGroupRequest
): Promise<{ data: UserGroup }> {
	return apiRequest<{ data: UserGroup }>('/groups', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateUserGroup(
	id: number,
	data: Partial<StoreUserGroupRequest>
): Promise<{ data: UserGroup }> {
	return apiRequest<{ data: UserGroup }>(`/groups/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteUserGroup(id: number): Promise<{ data: UserGroup }> {
	return apiRequest<{ data: UserGroup }>(`/groups/${id}`, {
		method: 'DELETE',
	});
}