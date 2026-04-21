import { apiRequest, downloadFile } from './client';

export interface Section {
	id: number;
	name: string;
	user_id: number;
	type_id: number;
	created_at?: string;
	updated_at?: string;
}

export interface StoreSectionRequest {
	name: string;
	user_id: number;
	type_id: number;
	created_at?: string;
	updated_at?: string;
}

export interface TreeSectionNode {
	id: number;
	name: string;
	type: 'type' | 'section';
	type_id?: number;
	children?: TreeSectionNode[];
}

export interface SectionListResponse {
	data: Section[];
}

export interface SectionTreeResponse {
	data: TreeSectionNode[];
}

export async function getSectionsTree(): Promise<SectionTreeResponse> {
	return apiRequest<SectionTreeResponse>('/sections/types');
}

export async function getSections(): Promise<SectionListResponse> {
	return apiRequest<SectionListResponse>('/sections');
}

export async function getSection(id: number): Promise<{ data: Section }> {
	return apiRequest<{ data: Section }>(`/sections/${id}`);
}

export async function createSection(
	data: StoreSectionRequest
): Promise<{ data: Section }> {
	return apiRequest<{ data: Section }>('/sections', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function updateSection(
	id: number,
	data: Partial<StoreSectionRequest>
): Promise<{ data: Section }> {
	return apiRequest<{ data: Section }>(`/sections/${id}`, {
		method: 'PUT',
		body: JSON.stringify(data),
	});
}

export async function deleteSection(id: number): Promise<{ data: Section }> {
	return apiRequest<{ data: Section }>(`/sections/${id}`, {
		method: 'DELETE',
	});
}

export async function downloadOpis(sectionId: number): Promise<void> {
	return downloadFile(`/sections/${sectionId}/opis`);
}