<script lang="ts">
  import { page } from '$app/stores';
  import { Check, Ban, Book, FileBadge, Server, FileText } from '@lucide/svelte';
  import WidgetComponent from '$lib/components/WidgetComponent.svelte';
  import { withBase } from '$lib/utils/paths';
  import { getArticlesBySection, type Article, getSectionStatistics, type SectionStatistics } from '$lib/api/index';
  import { downloadOpis } from '$lib/api/sections';
  import ArticleForm from '$lib/components/forms/ArticleForm.svelte';
  import ModalComponent from '$lib/components/ModalComponent.svelte';
  import { getCurrentUser } from '$lib/api/client';
  import { onMount } from 'svelte';
  
  let articleModalOpen = $state(false);
  let currentUserId = $state<number | null>(null);

  let articles = $state<Article[]>([]);
  let loading = $state(false);
  let error = $state<string | null>(null);
  let statistics = $state<SectionStatistics | null>(null);
  let statisticsLoading = $state(false);
  let generatingOpis = $state(false);

  let numericId = $derived(parseInt($page.params.id.replace('section-', ''), 10));

  async function loadArticles(sectionId: number) {
    if (isNaN(sectionId)) {
      error = 'Неверный ID секции';
      return;
    }

    loading = true;
    error = null;    
    try {
      const response = await getArticlesBySection(sectionId);
      articles = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке статей';
      console.error('Ошибка загрузки статей:', err);
    } finally {
      loading = false;
    }
  }

  async function loadStatistics(sectionId: number) {
    if (isNaN(sectionId)) {
      return;
    }

    statisticsLoading = true;
    try {
      const response = await getSectionStatistics(sectionId);
      statistics = response.data;
    } catch (err) {
      console.error('Ошибка загрузки статистики:', err);
      statistics = null;
    } finally {
      statisticsLoading = false;
    }
  }

  $effect(() => {
    loadArticles(numericId);
    loadStatistics(numericId);
  });

  onMount(() => {
    const user = getCurrentUser();
    if (user) {
      currentUserId = user.id;
    }
  });

  function getStatusClass(status: string) {
    switch (status) {
      case 'Активен': return 'text-green-600 bg-green-100';
      case 'Неактивен': return 'text-gray-600 bg-gray-100';
      case 'Ошибка': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  }

  function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Б';
    const k = 1024;
    const sizes = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  }

  let totalDocuments = $derived(statistics?.total_documents ?? articles.length);
  let totalAccesses = $derived(statistics?.total_accesses ?? 0);
  let totalWeight = $derived(statistics?.total_weight ?? 0);
  let formattedWeight = $derived(formatFileSize(totalWeight));

  async function handleGenerateOpis() {
    if (isNaN(numericId)) {
      error = 'Неверный ID секции';
      return;
    }

    generatingOpis = true;
    error = null;
    try {
      await downloadOpis(numericId);
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при генерации описи дел';
      console.error('Ошибка генерации описи:', err);
    } finally {
      generatingOpis = false;
    }
  }
</script>

<div class="p-6 flex flex-row justify-around">
  <WidgetComponent name="Всего Документов" count={totalDocuments.toString()} icon={Book} size=30 color=sky-500/>
  <WidgetComponent name="Дано Допусков" count={totalAccesses.toString()} icon={FileBadge} size=30 color=indigo-500/>
  <WidgetComponent name="Вес Документов" count={formattedWeight} icon={Server} size=30 color=emerald-500/>
</div>

<div class="p-6">
  {#if loading}
    <div class="flex items-center justify-center p-4">
      <span class="text-surface-600-300">Загрузка...</span>
    </div>
  {:else if error}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={() => loadArticles(numericId)}
        class="btn btn-sm mt-2 preset-filled-primary-500"
      >
        Повторить
      </button>
    </div>
  {:else}
    <div class="bg-surface-50-900 rounded-lg shadow-sm border border-surface-200-800 overflow-hidden">
      <div class="overflow-x-auto">
        <div class="flex gap-2 m-4">
          <button onclick={() => articleModalOpen = true} class="btn preset-filled-primary-500">
            Добавить статью
          </button>
          <button 
            onclick={handleGenerateOpis} 
            class="btn preset-filled-secondary-500"
            disabled={generatingOpis || loading}
          >
            <FileText class="w-4 h-4 mr-2" />
            {generatingOpis ? 'Формирование...' : 'Сформировать опись дел'}
          </button>
        </div>
        <ModalComponent bind:open={articleModalOpen} title="Создать статью">
          {#snippet children()}
            {#if currentUserId}
              <ArticleForm userId={currentUserId} sectionId={numericId} onSuccess={() => articleModalOpen = false} />
            {:else}
              <div class="p-4 text-error-500">Пользователь не найден. Пожалуйста, войдите в систему.</div>
            {/if}
          {/snippet}
        </ModalComponent>
        <table class="w-full">
          <thead class="bg-surface-100-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Название
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Секретность
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Статус
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Дата
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-200-800">
            {#if articles.length === 0}
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-surface-500-400">
                  Нет документов в этом разделе
                </td>
              </tr>
            {:else}
              {#each articles as article (article.id)}
                <tr class="hover:bg-surface-100-800 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-700-300">
                    {article.name}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {article.secrecy_grade ? 'text-red-600 bg-red-100' : 'text-green-600 bg-green-100'}">
                      {article.secrecy_grade ? 'Секретно' : 'Обычный'}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {#if article.status}
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-gray-600 bg-gray-100">
                        {article.status.name}
                      </span>
                    {:else}
                      <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full text-gray-400 bg-gray-50">
                        Не указан
                      </span>
                    {/if}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-600-400">
                    {article.created_at ? new Date(article.created_at).toLocaleDateString('ru-RU') : '-'}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <a 
                        href={withBase(`/archive/${$page.params.id}/${article.id}`)}
                        class="text-primary-600 hover:text-primary-900 transition-colors"
                        title="Открыть карточку дела"
                      >
                        <Check/>
                      </a>
                    </div>
                  </td>
                </tr>
              {/each}
            {/if}
          </tbody>
        </table>
      </div>
    </div>
  {/if}
</div>
