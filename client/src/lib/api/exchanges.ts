import { apiRequest, downloadFile } from './client';
import { type Article } from './articles';

export interface Exchange {
	id: number;
	name: string;
	reason?: string;
	fund_name?: string;
	receiving_organization?: string;
	created_by?: number;
	articles?: Article[];
	articles_count?: number;
	creator?: {
		id: number;
		nickname: string;
		email: string;
	};
	created_at?: string;
	updated_at?: string;
}

export interface StoreExchangeRequest {
	name: string;
	reason?: string;
	fund_name?: string;
	receiving_organization?: string;
	article_ids: number[];
}

export interface UpdateExchangeRequest {
	name?: string;
	reason?: string;
	fund_name?: string;
	receiving_organization?: string;
	article_ids?: number[];
}

export interface ExchangeListResponse {
	data: Exchange[];
}

export async function getExchanges(): Promise<ExchangeListResponse> {
	return apiRequest<ExchangeListResponse>('/exchanges');
}

export async function getExchange(id: number): Promise<{ data: Exchange }> {
	return apiRequest<{ data: Exchange }>(`/exchanges/${id}`);
}

export async function createExchange(
	data: StoreExchangeRequest
): Promise<{ data: Exchange }> {
	return apiRequest<{ data: Exchange }>('/exchanges', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateExchange(
	id: number,
	data: UpdateExchangeRequest
): Promise<{ data: Exchange }> {
	return apiRequest<{ data: Exchange }>(`/exchanges/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteExchange(id: number): Promise<{ data: Exchange }> {
	return apiRequest<{ data: Exchange }>(`/exchanges/${id}`, {
		method: 'DELETE',
	});
}

export async function downloadExchangeAct(id: number): Promise<void> {
	return downloadFile(`/exchanges/${id}/download`);
}

export async function transferArticles(id: number): Promise<{ data: Exchange }> {
	return apiRequest<{ data: Exchange }>(`/exchanges/${id}/transfer-articles`, {
		method: 'POST',
	});
}
