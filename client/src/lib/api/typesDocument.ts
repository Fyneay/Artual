import { apiRequest } from './client';

export interface TypeDocument {
	id: number;
	name: string;
	created_at?: string;
	updated_at?: string;
}

export interface TypeDocumentListResponse {
	data: TypeDocument[];
}

export async function getTypesDocument(): Promise<TypeDocumentListResponse> {
	return apiRequest<TypeDocumentListResponse>('/types-document');
}
