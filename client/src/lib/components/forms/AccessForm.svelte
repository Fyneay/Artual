<script lang="ts">
	import { createAccess, type StoreAccessRequest } from '$lib/api/access';
	import { getUsers, type User } from '$lib/api/users';
	import { getArticles, type Article } from '$lib/api/articles';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let users = $state<User[]>([]);
	let articles = $state<Article[]>([]);

	let formData = $state<Partial<StoreAccessRequest>>({
		name: '',
		granted_by: undefined,
		article_id: undefined,
		access_date: new Date().toISOString().split('T')[0],
	});

	interface Props {
		onSuccess?: () => void;
	}

	let { onSuccess }: Props = $props();

	async function loadUsers() {
		try {
			const response = await getUsers();
			users = response.data;
		} catch (err) {
			console.error('Ошибка загрузки пользователей:', err);
			error = 'Ошибка загрузки списка пользователей';
		}
	}

	async function loadArticles() {
		try {
			const response = await getArticles();
			articles = response.data;
		} catch (err) {
			console.error('Ошибка загрузки дел:', err);
			error = 'Ошибка загрузки списка дел';
		}
	}

	onMount(() => {
		loadUsers();
		loadArticles();
	});

	async function handleSubmit() {
		if (!formData.name || formData.name.trim() === '') {
			error = 'Название допуска обязательно';
			return;
		}

		if (!formData.granted_by) {
			error = 'Необходимо выбрать пользователя, которому предоставляется допуск';
			return;
		}

		if (!formData.article_id) {
			error = 'Необходимо выбрать дело';
			return;
		}

		error = null;
		loading = true;

		try {
			await createAccess({
				name: formData.name!,
				granted_by: formData.granted_by!,
				article_id: formData.article_id!,
				access_date: formData.access_date || new Date().toISOString().split('T')[0],
			});
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании допуска';
		} finally {
			loading = false;
		}
	}
</script>

<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
	<div>
		<label for="name" class="block text-sm font-medium text-surface-700-300 mb-2">
			Название допуска <span class="text-red-500">*</span>
		</label>
		<input
			type="text"
			id="name"
			bind:value={formData.name}
			required
			class="input w-full"
			placeholder="Введите название допуска"
		/>
	</div>

	<div>
		<label for="granted_by" class="block text-sm font-medium text-surface-700-300 mb-2">
			Кому выдается допуск <span class="text-red-500">*</span>
		</label>
		<select 
			id="granted_by" 
			bind:value={formData.granted_by} 
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
		<label for="article_id" class="block text-sm font-medium text-surface-700-300 mb-2">
			Дело <span class="text-red-500">*</span>
		</label>
		<select 
			id="article_id" 
			bind:value={formData.article_id} 
			required 
			class="select w-full"
		>
			<option value={undefined}>Выберите дело...</option>
			{#each articles as article}
				<option value={article.id}>
					{article.name}
				</option>
			{/each}
		</select>
	</div>

	<div>
		<label for="access_date" class="block text-sm font-medium text-surface-700-300 mb-2">
			Дата создания
		</label>
		<input
			type="date"
			id="access_date"
			bind:value={formData.access_date}
			readonly
			disabled
			class="input w-full bg-surface-100-800"
		/>
	</div>

	{#if error}
		<div class="p-3 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
			{error}
		</div>
	{/if}

	<footer class="flex justify-end gap-2 pt-4">
		<button type="submit" class="btn preset-filled" disabled={loading || !formData.granted_by || !formData.article_id}>
			{loading ? 'Создание...' : 'Создать'}
		</button>
	</footer>
</form>