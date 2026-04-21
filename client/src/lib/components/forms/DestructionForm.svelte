<script lang="ts">
	import { createDestruction, type StoreDestructionRequest } from '$lib/api/destructions';
	import { getArticles, type Article } from '$lib/api/articles';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let articles = $state<Article[]>([]);
	let selectedArticleIds = $state<number[]>([]);

	let formData = $state<Partial<StoreDestructionRequest>>({
		name: '',
		article_ids: [],
	});

	interface Props {
		onSuccess?: () => void;
	}

	let { onSuccess }: Props = $props();

	async function loadArticles() {
		try {
			const response = await getArticles();
			articles = response.data;
		} catch (err) {
			console.error('Ошибка загрузки статей:', err);
			error = 'Ошибка загрузки списка статей';
		}
	}

	onMount(() => {
		loadArticles();
	});

	function toggleArticle(articleId: number) {
		if (selectedArticleIds.includes(articleId)) {
			selectedArticleIds = selectedArticleIds.filter(id => id !== articleId);
		} else {
			selectedArticleIds = [...selectedArticleIds, articleId];
		}
		formData.article_ids = selectedArticleIds;
	}

	async function handleSubmit() {
		if (selectedArticleIds.length === 0) {
			error = 'Необходимо выбрать хотя бы одну статью';
			return;
		}

		if (!formData.name || formData.name.trim() === '') {
			error = 'Название акта уничтожения обязательно';
			return;
		}

		error = null;
		loading = true;

		try {
			await createDestruction({
				name: formData.name!,
				article_ids: selectedArticleIds,
			});
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании акта уничтожения';
		} finally {
			loading = false;
		}
	}
</script>

<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
	<div>
		<label for="name" class="block text-sm font-medium text-surface-700-300 mb-2">
			Название акта уничтожения <span class="text-red-500">*</span>
		</label>
		<input
			type="text"
			id="name"
			bind:value={formData.name}
			required
			class="input w-full"
			placeholder="Введите название акта уничтожения"
		/>
	</div>

	<div>
		<label class="block text-sm font-medium text-surface-700-300 mb-2">
			Статьи для уничтожения <span class="text-red-500">*</span>
		</label>
		<div class="border border-surface-200-800 rounded-lg p-3 max-h-60 overflow-y-auto bg-surface-50-900">
			{#if articles.length === 0}
				<p class="text-sm text-surface-500-400">Загрузка статей...</p>
			{:else}
				<div class="space-y-2">
					{#each articles as article (article.id)}
						<label class="flex items-center cursor-pointer hover:bg-surface-100-800 p-2 rounded transition-colors">
							<input
								type="checkbox"
								checked={selectedArticleIds.includes(article.id)}
								onchange={() => toggleArticle(article.id)}
								class="checkbox"
							/>
							<span class="ml-2 text-sm text-surface-700-300 flex-1">{article.name}</span>
						</label>
					{/each}
				</div>
			{/if}
		</div>
		{#if selectedArticleIds.length > 0}
			<p class="text-xs text-surface-500-400 mt-1">
				Выбрано статей: {selectedArticleIds.length}
			</p>
		{/if}
	</div>

	{#if error}
		<div class="p-3 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
			{error}
		</div>
	{/if}

	<footer class="flex justify-end gap-2 pt-4">
		<button type="submit" class="btn preset-filled" disabled={loading || selectedArticleIds.length === 0}>
			{loading ? 'Создание...' : 'Создать'}
		</button>
	</footer>
</form>
