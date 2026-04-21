import { apiRequest, downloadFile } from './client';

export interface ArticleFile {
	id: number;
	article_id: number;
	filename: string;
	path: string;
	status: string;
	mime_type: string;
	file_size: number;
	created_at: string;
	updated_at: string;
  }

export interface Article {
	id: number;
	name: string;
	// path_file?: string;
	user_id: number;
	secrecy_grade?: boolean;
	section_id: number;
	list_period_id?: number | null;
	list_period?: {
		id: number;
		name: string;
		retention_period: number;
	} | null;
	expiration_date?: string | null;
	location?: string | null;
	type_document_id?: number | null;
	type_document?: {
		id: number;
		name: string;
	} | null;
	description?: string | null;
	status_id?: number | null;
	status?: {
		id: number;
		name: string;
	} | null;
	created_by: {
		id: number;
		nickname: string;
		email: string;
	};
	files?: ArticleFile[];
	created_at?: string;
	updated_at?: string;
}

export interface StoreArticleRequest {
	name: string;//	'path-file': File;
	user_id: number;
	secrecy_grade?: boolean;
	section_id: number;
	list_period_id?: number | null;
	location?: string | null;
	type_document_id?: number | null;
	description?: string | null;
	status_id?: number | null;
	created_at?: string;
	updated_at?: string;
}

export interface ArticleListResponse {
	data: Article[];
}

export interface SectionStatistics {
	total_documents: number;
	total_accesses: number;
	total_weight: number; // в байтах
}

export interface SectionStatisticsResponse {
	data: SectionStatistics;
}

export async function getArticles(): Promise<ArticleListResponse> {
	return apiRequest<ArticleListResponse>('/articles');
}

export async function getArticle(id: number): Promise<{ data: Article }> {
	return apiRequest<{ data: Article }>(`/articles/${id}`);
}

export async function getArticlesBySection(sectionId: number): Promise<ArticleListResponse> {
	return apiRequest<ArticleListResponse>(`/articles/section/${sectionId}`);
}

export async function getReadyForDestructionArticles(): Promise<ArticleListResponse> {
	return apiRequest<ArticleListResponse>('/articles/ready-for-destruction');
}

export async function createArticle(
	data: StoreArticleRequest
): Promise<{ data: Article }> {
	const formData = new FormData();
	formData.append('name', data.name);
	formData.append('user_id', data.user_id.toString());
	formData.append('section_id', data.section_id.toString());
	
	if (data.secrecy_grade !== undefined) {
		formData.append('secrecy_grade', data.secrecy_grade ? '1' : '0');
	}
	
	if (data.list_period_id !== undefined && data.list_period_id !== null) {
		formData.append('list_period_id', data.list_period_id.toString());
	}
	
	if (data.location !== undefined && data.location !== null) {
		formData.append('location', data.location);
	}
	
	if (data.type_document_id !== undefined && data.type_document_id !== null) {
		formData.append('type_document_id', data.type_document_id.toString());
	}
	
	if (data.status_id !== undefined && data.status_id !== null) {
		formData.append('status_id', data.status_id.toString());
	}
	
	if (data.created_at) {
		formData.append('created_at', data.created_at);
	}
	
	if (data.updated_at) {
		formData.append('updated_at', data.updated_at);
	}

	return apiRequest<{ data: Article }>('/articles', {
		method: 'POST',
		body: formData,
	});
}

export async function updateArticle(
	id: number,
	data: Partial<StoreArticleRequest>
): Promise<{ data: Article }> {
	const formData = new FormData();

	formData.append('_method', 'PUT');

	if (data.name !== undefined) {
		formData.append('name', data.name);
	}
	if (data.user_id !== undefined) formData.append('user_id', data.user_id.toString());
	if (data.section_id !== undefined) formData.append('section_id', data.section_id.toString());
	if (data.secrecy_grade !== undefined) {
		formData.append('secrecy_grade', data.secrecy_grade ? '1' : '0');
	}
	if (data.list_period_id !== undefined) {
		if (data.list_period_id !== null) {
			formData.append('list_period_id', data.list_period_id.toString());
		}
	}
	if (data.location !== undefined && data.location !== null) {
		formData.append('location', data.location);
	}
	if (data.type_document_id !== undefined) {
		if (data.type_document_id !== null) {
			formData.append('type_document_id', data.type_document_id.toString());
		}
	}
	if (data.description !== undefined && data.description !== null) {
		formData.append('description', data.description);
	}
	if (data.status_id !== undefined) {
		if (data.status_id !== null) {
			formData.append('status_id', data.status_id.toString());
		}
	}
	if (data.created_at) formData.append('created_at', data.created_at);
	if (data.updated_at) formData.append('updated_at', data.updated_at);

	return apiRequest<{ data: Article }>(`/articles/${id}`, {
		method: 'POST',
		body: formData,
	});
}

export async function deleteArticle(id: number): Promise<{ data: Article }> {
	return apiRequest<{ data: Article }>(`/articles/${id}`, {
		method: 'DELETE',
	});
}

export async function downloadArticleFile(fileId: number): Promise<void> {
	return downloadFile(`/article-files/${fileId}/download`);
}

export async function getSectionStatistics(sectionId: number): Promise<SectionStatisticsResponse> {
	return apiRequest<SectionStatisticsResponse>(`/articles/section/${sectionId}/statistics`);
}