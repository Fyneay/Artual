<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { acceptInvite, getInvite, type AcceptInviteRequest, type Invite } from '$lib/api/invites';
	import { UserPlus, Mail, Lock, AlertCircle, Loader2, User } from '@lucide/svelte';
	import { withBase } from '$lib/utils/paths';

	let { key } = $page.params;
	
	let invite = $state<Invite | null>(null);
	let nickname = $state('');
	let password = $state('');
	let passwordConfirmation = $state('');
	let isLoading = $state(false);
	let isLoadingInvite = $state(false);
	let error = $state<string | null>(null);
	let showPassword = $state(false);
	let showPasswordConfirmation = $state(false);

	async function loadInvite() {
		if (!key) {
			error = 'Ключ приглашения не указан';
			return;
		}

		isLoadingInvite = true;
		error = null;

		try {
			const response = await getInvite(key);
			invite = response.data;
			
			if (invite.used) {
				error = 'Это приглашение уже было использовано';
			} else {
				const expiresAt = new Date(invite.expires_at);
				const now = new Date();
				if (expiresAt <= now) {
					error = 'Это приглашение истекло';
				}
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при загрузке приглашения';
			console.error('Ошибка загрузки приглашения:', err);
		} finally {
			isLoadingInvite = false;
		}
	}

	async function handleSubmit(event: Event) {
		event.preventDefault();
		error = null;

		// Валидация
		if (!nickname.trim()) {
			error = 'Никнейм обязателен';
			return;
		}

		if (password.length < 8) {
			error = 'Пароль должен содержать минимум 8 символов';
			return;
		}

		if (password !== passwordConfirmation) {
			error = 'Пароли не совпадают';
			return;
		}

		if (!key) {
			error = 'Ключ приглашения не указан';
			return;
		}

		isLoading = true;

		try {
			const data: AcceptInviteRequest = {
				nickname: nickname.trim(),
				password: password,
				password_confirmation: passwordConfirmation,
			};

			const response = await acceptInvite(key, data);
			
			await goto(withBase('/'));
		} catch (err) {
			error = err instanceof Error ? err.message : 'Произошла ошибка при регистрации';
		} finally {
			isLoading = false;
		}
	}

	function togglePasswordVisibility() {
		showPassword = !showPassword;
	}

	function togglePasswordConfirmationVisibility() {
		showPasswordConfirmation = !showPasswordConfirmation;
	}

	$effect(() => {
		loadInvite();
	});
</script>

<div class="min-h-screen flex items-center justify-center bg-surface-50-950 p-4">
	<div class="w-full max-w-md">
		<div class="text-center mb-8">
			<div class="inline-flex items-center justify-center w-16 h-16 bg-primary-500 rounded-full mb-4">
				<UserPlus class="size-8 text-white" />
			</div>
			<h1 class="text-3xl font-bold text-surface-900-50 mb-2">Регистрация</h1>
			<p class="text-surface-600-400">Создайте аккаунт по приглашению</p>
		</div>

		<div class="card border-2 border-surface-300-700 rounded-xl shadow-lg">
			<div class="p-6 space-y-6">
				{#if isLoadingInvite}
					<div class="flex items-center justify-center p-8">
						<Loader2 class="size-6 animate-spin text-primary-500" />
						<span class="ml-3 text-surface-600-400">Загрузка приглашения...</span>
					</div>
				{:else if error && !invite}
					<div class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
						<AlertCircle class="size-5 text-red-500 flex-shrink-0" />
						<div class="flex-1">
							<p class="text-sm text-red-500 font-medium">{error}</p>
							<a 
								href={withBase('/login')} 
								class="text-sm text-primary-500 hover:text-primary-600 mt-2 inline-block"
							>
								Вернуться на страницу входа
							</a>
						</div>
					</div>
				{:else if invite}
					{#if invite.email}
						<div class="flex items-center gap-3 p-4 bg-primary-500/10 border border-primary-500/20 rounded-lg">
							<Mail class="size-5 text-primary-500 flex-shrink-0" />
							<div class="flex-1">
								<p class="text-xs text-primary-500/70 mb-1">Приглашение для</p>
								<p class="text-sm font-medium text-primary-700">{invite.email}</p>
							</div>
						</div>
					{/if}

					{#if error}
						<div class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
							<AlertCircle class="size-5 text-red-500 flex-shrink-0" />
							<p class="text-sm text-red-500">{error}</p>
						</div>
					{/if}

					<form on:submit={handleSubmit} class="space-y-5">
						<div>
							<label for="nickname" class="block text-sm font-medium text-surface-700-300 mb-2">
								Никнейм
							</label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<User class="size-5 text-surface-500-400" />
								</div>
								<input
									id="nickname"
									type="text"
									bind:value={nickname}
									required
									disabled={isLoading}
									class="input preset-outlined w-full pl-10"
									placeholder="Введите никнейм"
									autocomplete="username"
									minlength="3"
									maxlength="255"
								/>
							</div>
						</div>

						<div>
							<label for="password" class="block text-sm font-medium text-surface-700-300 mb-2">
								Пароль
							</label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<Lock class="size-5 text-surface-500-400" />
								</div>
								<input
									id="password"
									type={showPassword ? 'text' : 'password'}
									bind:value={password}
									required
									disabled={isLoading}
									class="input preset-outlined w-full pl-10 pr-10"
									placeholder="Минимум 8 символов"
									autocomplete="new-password"
									minlength="8"
								/>
								<button
									type="button"
									on:click={togglePasswordVisibility}
									disabled={isLoading}
									class="absolute inset-y-0 right-0 pr-3 flex items-center text-surface-500-400 hover:text-surface-700-300 transition-colors"
									title={showPassword ? 'Скрыть пароль' : 'Показать пароль'}
								>
									{#if showPassword}
										<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
										</svg>
									{:else}
										<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
										</svg>
									{/if}
								</button>
							</div>
							<p class="text-xs text-surface-500-400 mt-1">Пароль должен содержать минимум 8 символов</p>
						</div>

						<div>
							<label for="password_confirmation" class="block text-sm font-medium text-surface-700-300 mb-2">
								Подтверждение пароля
							</label>
							<div class="relative">
								<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
									<Lock class="size-5 text-surface-500-400" />
								</div>
								<input
									id="password_confirmation"
									type={showPasswordConfirmation ? 'text' : 'password'}
									bind:value={passwordConfirmation}
									required
									disabled={isLoading}
									class="input preset-outlined w-full pl-10 pr-10"
									placeholder="Повторите пароль"
									autocomplete="new-password"
									minlength="8"
								/>
								<button
									type="button"
									on:click={togglePasswordConfirmationVisibility}
									disabled={isLoading}
									class="absolute inset-y-0 right-0 pr-3 flex items-center text-surface-500-400 hover:text-surface-700-300 transition-colors"
									title={showPasswordConfirmation ? 'Скрыть пароль' : 'Показать пароль'}
								>
									{#if showPasswordConfirmation}
										<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
										</svg>
									{:else}
										<svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
										</svg>
									{/if}
								</button>
							</div>
						</div>

						<button
							type="submit"
							disabled={isLoading || !nickname.trim() || !password || !passwordConfirmation}
							class="btn preset-filled-primary-500 w-full"
						>
							{#if isLoading}
								<Loader2 class="size-5 mr-2 animate-spin" />
								Регистрация...
							{:else}
								<UserPlus class="size-5 mr-2" />
								Зарегистрироваться
							{/if}
						</button>
					</form>
				{/if}

				<div class="pt-4 border-t border-surface-200-800">
					<p class="text-center text-sm text-surface-600-400">
						Уже есть аккаунт? 
						<a href={withBase('/auth/login')} class="text-primary-500 hover:text-primary-600 font-medium transition-colors">
							Войти
						</a>
					</p>
				</div>
			</div>
		</div>

		<div class="mt-6 text-center text-sm text-surface-500-400">
			<p>© 2025 Artual. ВКР.</p>
		</div>
	</div>
</div>
