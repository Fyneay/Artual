export const API_BASE_URL = 'https://localhost/api';

export interface ApiError {
	message: string;
	errors?: Record<string, string[]>;
}

export function getAuthToken(): string | null {
	if (typeof window !== 'undefined') {
		return localStorage.getItem('auth_token');
	}
	return null;
}

export function setAuthToken(token: string): void {
	if (typeof window !== 'undefined') {
		localStorage.setItem('auth_token', token);
	}
}

export function removeAuthToken(): void {
	if (typeof window !== 'undefined') {
		localStorage.removeItem('auth_token');
	}
}

export interface CurrentUser {
	id: number;
	email: string;
	nickname: string;
}

const USER_STORAGE_KEY = 'current_user';

export function setCurrentUser(user: CurrentUser): void {
	if (typeof window !== 'undefined') {
		localStorage.setItem(USER_STORAGE_KEY, JSON.stringify(user));
	}
}

export function getCurrentUser(): CurrentUser | null {
	if (typeof window !== 'undefined') {
		const userJson = localStorage.getItem(USER_STORAGE_KEY);
		if (userJson) {
			try {
				return JSON.parse(userJson) as CurrentUser;
			} catch {
				return null;
			}
		}
	}
	return null;
}

export function removeCurrentUser(): void {
	if (typeof window !== 'undefined') {
		localStorage.removeItem(USER_STORAGE_KEY);
	}
}

export async function apiRequest<T>(
	endpoint: string,
	options: RequestInit = {}
): Promise<T> {
	const token = getAuthToken();
	const headers: HeadersInit = {
		'Accept': 'application/json',
		...(options.headers as Record<string, string> || {}),
	};

	if (token) {
		headers['Authorization'] = `Bearer ${token}`;
	}

	if (!(options.body instanceof FormData)) {
		headers['Content-Type'] = 'application/json';
	}

	const response = await fetch(`${API_BASE_URL}${endpoint}`, {
		...options,
		headers,
	});

	if (!response.ok) {
		const errorData: ApiError = await response.json().catch(() => ({
			message: 'Произошла ошибка при выполнении запроса',
		}));

		throw new Error(
			errorData.errors 
				? Object.values(errorData.errors).flat().join(', ') 
				: errorData.message || 'Произошла ошибка'
		);
	}

	return response.json();
}

export async function downloadFile(
	endpoint: string,
	filename?: string
): Promise<void> {
	const token = getAuthToken();
	const headers: HeadersInit = {};
	
	if (token) {
		headers['Authorization'] = `Bearer ${token}`;
	}

	const response = await fetch(`${API_BASE_URL}${endpoint}`, {
		method: 'GET',
		headers,
	});

	if (!response.ok) {
		const errorData: ApiError = await response.json().catch(() => ({
			message: 'Ошибка при скачивании файла',
		}));
		throw new Error(
			errorData.errors 
				? Object.values(errorData.errors).flat().join(', ') 
				: errorData.message || 'Ошибка при скачивании файла'
		);
	}

	let downloadFilename = filename || 'download';
	const contentDisposition = response.headers.get('Content-Disposition');
	
	if (contentDisposition) {
		const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
		if (filenameMatch && filenameMatch[1]) {
			downloadFilename = filenameMatch[1].replace(/['"]/g, '');
			try {
				downloadFilename = decodeURIComponent(downloadFilename);
			} catch {
			}
		}
	}

	const blob = await response.blob();
	const url = window.URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;
	link.download = downloadFilename;
	document.body.appendChild(link);
	link.click();
	document.body.removeChild(link);
	window.URL.revokeObjectURL(url);
}