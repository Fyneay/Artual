import { apiRequest, setAuthToken, setCurrentUser, removeCurrentUser, removeAuthToken } from './client';
import type { ApiError } from './client';

export interface LoginRequest {
	email?: string;
	nickname?: string;
	password: string;
}

export interface LoginResponse {
	user: {
		id: number;
		email: string;
		nickname: string;
	};
	token: string;
}

const API_BASE_URL = 'https://localhost/api';

export async function login(credentials: LoginRequest): Promise<LoginResponse> {
	const response = await fetch(`${API_BASE_URL}/auth/login`, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Accept': 'application/json',
		},
		body: JSON.stringify(credentials),
	});

	if (!response.ok) {
		const errorData: ApiError = await response.json().catch(() => ({
			message: 'Ошибка при входе в систему',
		}));

		throw new Error(
			errorData.errors?.email?.[0] || 
			errorData.errors?.nickname?.[0] || 
			errorData.message || 
			'Неверный email или пароль'
		);
	}

	const data = await response.json();
	setAuthToken(data.token);
	setCurrentUser(data.user);
	return data;
}

export async function logout(): Promise<{ message: string }> {
	try {
		const result = await apiRequest<{ message: string }>('/auth/logout', {
			method: 'POST',
		});
		removeAuthToken();
		removeCurrentUser();
		return result;
	} catch (error) {
		removeAuthToken();
		removeCurrentUser();
		throw error;
	}
}