import { apiRequest, downloadFile } from './client';
import { type Article } from './articles';

export interface Destruction {
	id: number;
	name: string;
	created_by?: {
		id: number;
		nickname: string;
		email: string;
	};
	articles?: Article[];
	articles_count?: number;
	created_at?: string;
	updated_at?: string;
}

export interface StoreDestructionRequest {
	name: string;
	article_ids: number[];
}

export interface UpdateDestructionRequest {
	name?: string;
	article_ids?: number[];
}

export interface DestructionListResponse {
	data: Destruction[];
}

export async function getDestructions(): Promise<DestructionListResponse> {
	return apiRequest<DestructionListResponse>('/destructions');
}

export async function getDestruction(id: number): Promise<{ data: Destruction }> {
	return apiRequest<{ data: Destruction }>(`/destructions/${id}`);
}

export async function createDestruction(
	data: StoreDestructionRequest
): Promise<{ data: Destruction }> {
	return apiRequest<{ data: Destruction }>('/destructions', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateDestruction(
	id: number,
	data: UpdateDestructionRequest
): Promise<{ data: Destruction }> {
	return apiRequest<{ data: Destruction }>(`/destructions/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteDestruction(id: number): Promise<{ data: Destruction }> {
	return apiRequest<{ data: Destruction }>(`/destructions/${id}`, {
		method: 'DELETE',
	});
}

export async function downloadDestructionAct(id: number): Promise<void> {
	return downloadFile(`/destructions/${id}/download`);
}

export async function destroyArticles(id: number): Promise<{ data: Destruction }> {
	return apiRequest<{ data: Destruction }>(`/destructions/${id}/destroy-articles`, {
		method: 'POST',
	});
}
