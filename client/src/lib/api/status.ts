import { apiRequest } from './client';

export interface Status {
	id: number;
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface StatusListResponse {
	data: Status[];
}

export async function getStatuses(): Promise<StatusListResponse> {
	return apiRequest<StatusListResponse>('/status');
}

