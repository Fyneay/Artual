import { apiRequest } from './client';

export interface ListPeriod {
	id: number;
	name: string;
	retention_period: number;
	created_at?: string;
	updated_at?: string;
}

export interface ListPeriodListResponse {
	data: ListPeriod[];
}

export async function getListPeriods(): Promise<ListPeriodListResponse> {
	return apiRequest<ListPeriodListResponse>('/list-periods');
}
