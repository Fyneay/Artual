<script lang="ts">
	import { createInvite, type StoreInviteRequest } from '$lib/api/invites';
	import { getUsers, type User } from '$lib/api/users';
	import { getUserGroups, type UserGroup } from '$lib/api/userGroups';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let users = $state<User[]>([]);
	let userGroups = $state<UserGroup[]>([]);

	let defaultExpiresAt = $derived(() => {
		const date = new Date();
		date.setDate(date.getDate() + 7);
		return date.toISOString().slice(0, 16);
	});

	let formData = $state<Partial<StoreInviteRequest>>({
		email: '',
		user_id: undefined,
		user_role_id: undefined,
		expires_at: defaultExpiresAt(),
		created_at: new Date().toISOString(),
		ttl: undefined,
	});

	interface Props {
		userId?: number;
		onSuccess?: () => void;
	}

	let { userId, onSuccess }: Props = $props();

	$effect(() => {
		if (userId) {
			formData.user_id = userId;
		}
	});

	async function loadUsers() {
		try {
			const response = await getUsers();
			users = response.data;
		} catch (err) {
			console.error('Ошибка загрузки пользователей:', err);
			error = 'Ошибка загрузки списка пользователей';
		}
	}

	async function loadUserGroups() {
		try {
			const response = await getUserGroups();
			userGroups = response.data;
		} catch (err) {
			console.error('Ошибка загрузки групп пользователей:', err);
			error = 'Ошибка загрузки списка групп пользователей';
		}
	}

	onMount(() => {
		loadUsers();
		loadUserGroups();
	});

	async function handleSubmit() {
		if (!formData.email || !formData.email.trim()) {
			error = 'Email обязателен';
			return;
		}

		if (!formData.user_id) {
			error = 'Необходимо выбрать пользователя, который создает инвайт';
			return;
		}

		if (!formData.user_role_id) {
			error = 'Необходимо выбрать роль для нового пользователя';
			return;
		}

		if (!formData.expires_at) {
			error = 'Дата истечения обязательна';
			return;
		}

		const expiresAtISO = new Date(formData.expires_at).toISOString();

		error = null;
		loading = true;

		try {
			await createInvite({
				email: formData.email!,
				user_id: formData.user_id!,
				user_role_id: formData.user_role_id!,
				expires_at: expiresAtISO,
				created_at: formData.created_at || new Date().toISOString(),
				ttl: formData.ttl,
			});
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании инвайта';
		} finally {
			loading = false;
		}
	}
</script>

<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
	<div>
		<label for="email" class="block text-sm font-medium text-surface-700-300 mb-2">
			Email получателя <span class="text-red-500">*</span>
		</label>
		<input
			type="email"
			id="email"
			bind:value={formData.email}
			required
			class="input w-full"
			placeholder="example@mail.com"
		/>
	</div>

	<div>
		<label for="user_id" class="block text-sm font-medium text-surface-700-300 mb-2">
			Создатель инвайта <span class="text-red-500">*</span>
		</label>
		<select 
			id="user_id" 
			bind:value={formData.user_id} 
			required 
			class="select w-full"
		>
			<option value={undefined}>Выберите пользователя...</option>
			{#each users as user}
				<option value={user.id}>
					{user.nickname} ({user.email})
				</option>
			{/each}
		</select>
	</div>

	<div>
		<label for="user_role_id" class="block text-sm font-medium text-surface-700-300 mb-2">
			Роль для нового пользователя <span class="text-red-500">*</span>
		</label>
		<select 
			id="user_role_id" 
			bind:value={formData.user_role_id} 
			required 
			class="select w-full"
		>
			<option value={undefined}>Выберите роль...</option>
			{#each userGroups as group}
				<option value={group.id}>
					{group.name}
				</option>
			{/each}
		</select>
	</div>

	<div>
		<label for="expires_at" class="block text-sm font-medium text-surface-700-300 mb-2">
			Дата истечения <span class="text-red-500">*</span>
		</label>
		<input
			type="datetime-local"
			id="expires_at"
			bind:value={formData.expires_at}
			required
			class="input w-full"
			min={new Date().toISOString().slice(0, 16)}
		/>
	</div>

	<div>
		<label for="ttl" class="block text-sm font-medium text-surface-700-300 mb-2">
			TTL (в секундах, опционально)
		</label>
		<input
			type="number"
			id="ttl"
			bind:value={formData.ttl}
			min="60"
			max="86400"
			class="input w-full"
			placeholder="3600 (по умолчанию)"
		/>
		<p class="text-xs text-surface-500-400 mt-1">
			Время жизни ссылки в Redis (от 60 до 86400 секунд)
		</p>
	</div>

	{#if error}
		<div class="p-3 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
			{error}
		</div>
	{/if}

	<footer class="flex justify-end gap-2 pt-4">
		<button type="submit" class="btn preset-filled" disabled={loading || !formData.user_id || !formData.user_role_id}>
			{loading ? 'Создание...' : 'Создать'}
		</button>
	</footer>
</form>
