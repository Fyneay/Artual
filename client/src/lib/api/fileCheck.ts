import { apiRequest } from './client';

export interface FileCheckUploadRequest {
	file: File;
	article_id: number;
	metadata?: Record<string, any>;
}

export interface FileCheckUploadResponse {
	success: boolean;
	message: string;
	data: {
		article_file_id: number;
		article_id: number;
		quarantine_path: string;
		original_name: string;
		size: number;
		mime_type: string;
		status: string;
	};
}

export interface FileCheckStatusResponse {
	success: boolean;
	data?: {
		path: string;
		exists: boolean;
		size: number;
		last_modified: string;
	};
	message?: string;
	path?: string;
}

export interface FileCheckDeleteResponse {
	success: boolean;
	message: string;
	path: string;
}

export async function uploadFileForCheck(
	data: FileCheckUploadRequest
): Promise<FileCheckUploadResponse> {
	const formData = new FormData();
	formData.append('file', data.file);
	formData.append('article_id', data.article_id.toString());
	
	if (data.metadata) {
		Object.entries(data.metadata).forEach(([key, value]) => {
			formData.append(`metadata[${key}]`, String(value));
		});
	}

	return apiRequest<FileCheckUploadResponse>('/file-check/upload', {
		method: 'POST',
		body: formData,
	});
}

export async function checkFileStatus(
	path: string
): Promise<FileCheckStatusResponse> {
	return apiRequest<FileCheckStatusResponse>(`/file-check/status/${encodeURIComponent(path)}`);
}

export async function deleteFileFromQuarantine(
	path: string
): Promise<FileCheckDeleteResponse> {
	return apiRequest<FileCheckDeleteResponse>(`/file-check/quarantine/${encodeURIComponent(path)}`, {
		method: 'DELETE',
	});
}