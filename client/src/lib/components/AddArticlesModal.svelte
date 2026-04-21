<script lang="ts">
	import { getReadyForDestructionArticles, type Article } from '$lib/api/articles';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let allArticles = $state<Article[]>([]);
	let selectedArticleIds = $state<number[]>([]);

	interface Props {
		currentArticleIds: number[];
		onSave: (articleIds: number[]) => Promise<void>;
		onClose: () => void;
	}

	let { currentArticleIds, onSave, onClose }: Props = $props();

	$effect(() => {
		selectedArticleIds = [...currentArticleIds];
	});

	async function loadArticles() {
		loading = true;
		error = null;
		try {
			const response = await getReadyForDestructionArticles();
			allArticles = response.data;
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка загрузки статей';
			console.error('Ошибка загрузки статей:', err);
		} finally {
			loading = false;
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
	}

	async function handleSave() {
		if (selectedArticleIds.length === 0) {
			error = 'Необходимо выбрать хотя бы одну статью';
			return;
		}

		error = null;
		loading = true;

		try {
			await onSave(selectedArticleIds);
			onClose();
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при сохранении статей';
		} finally {
			loading = false;
		}
	}

	function isArticleSelected(articleId: number): boolean {
		return selectedArticleIds.includes(articleId);
	}
</script>

<div class="space-y-4">
	<div>
		<label class="block text-sm font-medium text-surface-700-300 mb-2">
			Выберите статьи для добавления (отображаются только дела с истекшим сроком хранения или со статусом "выделен к уничтожению")
		</label>
		<div class="border border-surface-200-800 rounded-lg p-3 max-h-96 overflow-y-auto bg-surface-50-900">
			{#if loading}
				<p class="text-sm text-surface-500-400">Загрузка статей...</p>
			{:else if error}
				<p class="text-sm text-error-500">{error}</p>
			{:else if allArticles.length === 0}
				<p class="text-sm text-surface-500-400">Нет доступных статей</p>
			{:else}
				<div class="space-y-2">
					{#each allArticles as article (article.id)}
						<label class="flex items-center cursor-pointer hover:bg-surface-100-800 p-2 rounded transition-colors">
							<input
								type="checkbox"
								checked={isArticleSelected(article.id)}
								onchange={() => toggleArticle(article.id)}
								class="checkbox"
							/>
							<div class="ml-2 flex-1">
								<span class="text-sm text-surface-700-300 font-medium">{article.name}</span>
								{#if article.created_at}
									<span class="text-xs text-surface-500-400 ml-2">
										({new Date(article.created_at).toLocaleDateString('ru-RU')})
									</span>
								{/if}
							</div>
						</label>
					{/each}
				</div>
			{/if}
		</div>
		{#if selectedArticleIds.length > 0}
			<p class="text-xs text-surface-500-400 mt-2">
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
		<button type="button" onclick={onClose} class="btn preset-outline" disabled={loading ? true : undefined}>
			Отмена
		</button>
		<button type="button" onclick={handleSave} class="btn preset-filled" disabled={loading || selectedArticleIds.length === 0}>
			{loading ? 'Сохранение...' : 'Сохранить'}
		</button>
	</footer>
</div>