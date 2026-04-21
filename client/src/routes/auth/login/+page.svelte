<script lang="ts">
	import { goto } from '$app/navigation';
	import { login, setAuthToken, type LoginRequest } from '$lib/api/index';
	import { LogIn, Mail, Lock, AlertCircle, Loader2 } from '@lucide/svelte';
	import { withBase } from '$lib/utils/paths';

	let identifier = $state('');
	let password = $state('');
	let isLoading = $state(false);
	let error = $state<string | null>(null);
	let showPassword = $state(false);

	function isEmail(value: string): boolean {
		return value.includes('@');
	}

	async function handleSubmit(event: Event) {
		event.preventDefault();
		error = null;
		isLoading = true;

		try {
			const trimmedIdentifier = identifier.trim();
			const credentials: LoginRequest = {
				password: password,
			};

			if (isEmail(trimmedIdentifier)) {
				credentials.email = trimmedIdentifier;
			} else {
				credentials.nickname = trimmedIdentifier;
			}

			const response = await login(credentials);
			
			setAuthToken(response.token);
			
			await goto(withBase('/'));
		} catch (err) {
			error = err instanceof Error ? err.message : 'Произошла ошибка при входе';
		} finally {
			isLoading = false;
		}
	}

	function togglePasswordVisibility() {
		showPassword = !showPassword;
	}
</script>

<div class="min-h-screen flex items-center justify-center bg-surface-50-950 p-4">
	<div class="w-full max-w-md">
		<div class="text-center mb-8">
			<div class="inline-flex items-center justify-center w-16 h-16 bg-primary-500 rounded-full mb-4">
				<LogIn class="size-8 text-white" />
			</div>
			<h1 class="text-3xl font-bold text-surface-900-50 mb-2">Вход в систему</h1>
			<p class="text-surface-600-400">Введите свои учетные данные для доступа</p>
		</div>

		<div class="card border-2 border-surface-300-700 rounded-xl shadow-lg">
			<div class="p-6 space-y-6">
				{#if error}
					<div class="flex items-center gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
						<AlertCircle class="size-5 text-red-500 flex-shrink-0" />
						<p class="text-sm text-red-500">{error}</p>
					</div>
				{/if}

				<form on:submit={handleSubmit} class="space-y-5">
					<div>
						<label for="identifier" class="block text-sm font-medium text-surface-700-300 mb-2">
							Email или никнейм
						</label>
						<div class="relative">
							<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
								<Mail class="size-5 text-surface-500-400" />
							</div>
							<input
								id="identifier"
								type="text"
								bind:value={identifier}
								required
								disabled={isLoading}
								class="input preset-outlined w-full pl-10"
								placeholder="Введите email или никнейм"
								autocomplete="username"
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
								placeholder="Введите пароль"
								autocomplete="current-password"
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
					</div>

					<button
						type="submit"
						disabled={isLoading || !identifier.trim() || !password}
						class="btn preset-filled-primary-500 w-full"
					>
						{#if isLoading}
							<Loader2 class="size-5 mr-2 animate-spin" />
							Вход...
						{:else}
							<LogIn class="size-5 mr-2" />
							Войти
						{/if}
					</button>
				</form>
			</div>
		</div>

		<div class="mt-6 text-center text-sm text-surface-500-400">
			<p>© 2025 Artual. ВКР.</p>
		</div>
	</div>
</div>

