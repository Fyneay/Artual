import { apiRequest } from './client';
import { type Article } from './articles';

export interface Access {
	id: number;
	name: string;
	created_by?: {
		id: number;
		nickname: string;
		email: string;
	};
	granted_by?: {
		id: number;
		nickname: string;
		email: string;
	};
	article?: {
		id: number;
		name: string;
	};
	article_id: number;
	access_date: string;
	close_date?: string | null;
	reason?: string | null;
	status?: {
		id: number;
		name: string;
	};
	status_id?: number | null;
	created_at?: string;
	updated_at?: string;
}

export interface StoreAccessRequest {
	name: string;
	granted_by: number;
	article_id: number;
	access_date: string;
	close_date?: string | null;
	reason?: string | null;
	status_id?: number | null;
}

export interface UpdateAccessRequest {
	name?: string;
	granted_by?: number;
	article_id?: number;
	access_date?: string;
	close_date?: string | null;
	reason?: string | null;
	status_id?: number | null;
}

export interface AccessListResponse {
	data: Access[];
}

export async function getAccesses(): Promise<AccessListResponse> {
	return apiRequest<AccessListResponse>('/access');
}

export async function getAccess(id: number): Promise<{ data: Access }> {
	return apiRequest<{ data: Access }>(`/access/${id}`);
}

export async function getAccessesByArticle(articleId: number): Promise<AccessListResponse> {
	return apiRequest<AccessListResponse>(`/access/article/${articleId}`);
}

export async function getMyAccesses(): Promise<AccessListResponse> {
	return apiRequest<AccessListResponse>('/access/my');
}

export async function createAccess(
	data: StoreAccessRequest
): Promise<{ data: Access }> {
	return apiRequest<{ data: Access }>('/access', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateAccess(
	id: number,
	data: UpdateAccessRequest
): Promise<{ data: Access }> {
	return apiRequest<{ data: Access }>(`/access/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteAccess(id: number): Promise<{ data: Access }> {
	return apiRequest<{ data: Access }>(`/access/${id}`, {
		method: 'DELETE',
	});
}

