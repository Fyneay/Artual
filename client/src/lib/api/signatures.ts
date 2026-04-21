import { apiRequest } from './client';

export interface Signature {
	id: number;
	signature_data: string;
	certificate_name: string;
	certificate_subject: string;
	signed_by: number;
	article_id?: number;
	created_at?: string;
	updated_at?: string;
}

export interface StoreSignatureRequest {
	signature_data: string;
	certificate_name: string;
	certificate_subject: string;
	signed_by: number;
	article_id?: number;
}

export interface SignatureArchive {
	id: number;
	archive_name: string;
	archive_path: string;
	article_id?: number;
	created_by?: number;
	created_at?: string;
	updated_at?: string;
}

export interface SigningData {
	article_id: number;
	files_count: number;
	combined_hash: string;
	files: Array<{
		id: number;
		filename: string;
		content: string;
		hash: string;
	}>;
}

export async function createSignature(
	data: StoreSignatureRequest
): Promise<{ data: Signature }> {
	return apiRequest<{ data: Signature }>('/signatures', {
		method: 'POST',
		body: JSON.stringify(data),
	});
}

export async function getSignature(id: number): Promise<{ data: Signature }> {
	return apiRequest<{ data: Signature }>(`/signatures/${id}`);
}

export async function createSignatureArchive(
	articleIds: number[]
): Promise<{ data: SignatureArchive }> {
	return apiRequest<{ data: SignatureArchive }>('/signatures/archive', {
		method: 'POST',
		body: JSON.stringify({ article_ids: articleIds }),
	});
}

export async function getSignatureArchivesByArticle(
	articleId: number
): Promise<{ data: SignatureArchive[] }> {
	return apiRequest<{ data: SignatureArchive[] }>(`/signatures/article/${articleId}/archives`);
}

export async function getSigningData(
	articleId: number
): Promise<{ data: SigningData }> {
	return apiRequest<{ data: SigningData }>(`/signatures/article/${articleId}/signing-data`);
}

export async function downloadArchive(archiveId: number): Promise<void> {
	const { downloadFile } = await import('./client');
	return downloadFile(`/signatures/archive/${archiveId}/download`);
}