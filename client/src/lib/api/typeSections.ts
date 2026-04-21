import { apiRequest } from './client';

export interface TypeSection {
	id: number;
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface StoreTypeSectionRequest {
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface TypeSectionListResponse {
	data: TypeSection[];
}

export async function getTypeSections(): Promise<TypeSectionListResponse> {
	return apiRequest<TypeSectionListResponse>('/types');
}

export async function getTypeSection(id: number): Promise<{ data: TypeSection }> {
	return apiRequest<{ data: TypeSection }>(`/types/${id}`);
}

export async function createTypeSection(
	data: StoreTypeSectionRequest
): Promise<{ data: TypeSection }> {
	return apiRequest<{ data: TypeSection }>('/types', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateTypeSection(
	id: number,
	data: Partial<StoreTypeSectionRequest>
): Promise<{ data: TypeSection }> {
	return apiRequest<{ data: TypeSection }>(`/types/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteTypeSection(id: number): Promise<{ data: TypeSection }> {
	return apiRequest<{ data: TypeSection }>(`/types/${id}`, {
		method: 'DELETE',
	});
}