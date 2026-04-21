<script lang="ts">
  import { page } from '$app/stores';
  import { FileText, Calendar, User, List, Archive, Trash2 } from '@lucide/svelte';
  import { getDestruction, updateDestruction, type Destruction, downloadDestructionAct, destroyArticles } from '$lib/api/destructions';
  import { type Article } from '$lib/api/articles';
  import ModalComponent from '$lib/components/ModalComponent.svelte';
  import AddArticlesModal from '$lib/components/AddArticlesModal.svelte';

  let { id } = $page.params;
  let numericId = $derived(parseInt(id, 10));

  let destruction = $state<Destruction | null>(null);
  let loading = $state(false);
  let error = $state<string | null>(null);
  let saving = $state(false);
  let addArticlesModalOpen = $state(false);
  let downloading = $state(false);
  let destroying = $state(false);

  async function loadDestruction() {
    if (isNaN(numericId)) {
      error = 'Неверный ID акта уничтожения';
      return;
    }

    loading = true;
    error = null;
    try {
      const response = await getDestruction(numericId);
      destruction = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке акта уничтожения';
      console.error('Ошибка загрузки акта уничтожения:', err);
    } finally {
      loading = false;
    }
  }

  async function handleSave() {
    if (!destruction) return;

    saving = true;
    error = null;
    try {
      const response = await updateDestruction(numericId, {
        name: destruction.name,
        article_ids: destruction.articles?.map(a => a.id) || [],
      });
      destruction = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при сохранении';
    } finally {
      saving = false;
    }
  }

  async function handleUpdateArticles(articleIds: number[]) {
    if (!destruction) return;

    error = null;
    try {
      const response = await updateDestruction(numericId, {
        article_ids: articleIds,
      });
      destruction = response.data;
      addArticlesModalOpen = false;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при обновлении статей';
      throw err;
    }
  }

  async function handleRemoveArticle(articleId: number) {
    if (!destruction || !destruction.articles) return;

    const newArticleIds = destruction.articles
      .filter(a => a.id !== articleId)
      .map(a => a.id);

    error = null;
    try {
      const response = await updateDestruction(numericId, {
        article_ids: newArticleIds,
      });
      destruction = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при удалении статьи';
      console.error('Ошибка удаления статьи:', err);
    }
  }

  async function handleDownloadAct() {
    if (!destruction) return;
    
    downloading = true;
    try {
      await downloadDestructionAct(numericId);
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при скачивании акта';
      console.error('Ошибка скачивания акта:', err);
    } finally {
      downloading = false;
    }
  }


  async function destroyCases() {
    if (!destruction || !destruction.articles || destruction.articles.length === 0) {
      alert('Нет дел для уничтожения');
      return;
    }
    
    if (confirm(`Вы уверены, что хотите уничтожить ${destruction.articles.length} дел? Это действие нельзя отменить.`)) {
      destroying = true;
      error = null;
      try {
        const response = await destroyArticles(numericId);
        destruction = response.data;
        alert('Дела успешно уничтожены');
        await loadDestruction();
      } catch (err) {
        error = err instanceof Error ? err.message : 'Ошибка при уничтожении дел';
        console.error('Ошибка уничтожения дел:', err);
      } finally {
        destroying = false;
      }
    }
  }

  $effect(() => {
    loadDestruction();
  });
</script>

<div class="w-full h-full p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Карта акта уничтожения</h1>
      <p class="text-surface-600-400">ID: {id}</p>
    </div>
    <div class="flex space-x-3">
      <button 
        onclick={handleSave}
        class="btn preset-filled-primary-500"
        disabled={saving || loading}
      >
        {saving ? 'Сохранение...' : 'Сохранить'}
      </button>
      <button 
        onclick={handleDownloadAct}
        class="btn preset-outline-secondary-500"
        disabled={downloading || loading || !destruction}
      >
        {downloading ? 'Скачивание...' : 'Печать акта'}
      </button>
      <button 
        onclick={destroyCases}
        class="btn preset-filled-red-500"
        disabled={destroying || !destruction || !destruction.articles || destruction.articles.length === 0}
      >
        <Trash2 class="w-4 h-4 mr-2" />
        {destroying ? 'Уничтожение...' : 'Уничтожить'}
      </button>
    </div>
  </div>

  {#if error && !loading}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded mb-6">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={loadDestruction}
        class="btn btn-sm mt-2 preset-filled-primary-500"
      >
        Повторить
      </button>
    </div>
  {/if}

  {#if loading}
    <div class="flex items-center justify-center p-4">
      <span class="text-surface-600-300">Загрузка...</span>
    </div>
  {:else if destruction}
    <div class="grid grid-cols-[1fr_1fr] gap-6">
      <div class="space-y-6">
        <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6 space-y-6">
          <h2 class="text-lg font-semibold text-surface-900-50 mb-4">Основная информация</h2>

          <div>
            <label class="block text-sm font-medium text-surface-700-300 mb-2">
              <FileText class="inline w-4 h-4 mr-2" />
              № акта
            </label>
            <input 
              type="text" 
              bind:value={destruction.name}
              class="input w-full"
              placeholder="Введите номер акта"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-surface-700-300 mb-2">
              <Calendar class="inline w-4 h-4 mr-2" />
              Дата создания
            </label>
            <input 
              type="text" 
              value={destruction.created_at ? new Date(destruction.created_at).toLocaleDateString('ru-RU') : '—'}
              readonly
              class="input w-full bg-surface-100-800"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-surface-700-300 mb-2">
              <User class="inline w-4 h-4 mr-2" />
              Кем создано
            </label>
            <input 
              type="text" 
              value={destruction.created_by ? destruction.created_by.nickname : '—'}
              readonly
              class="input w-full bg-surface-100-800"
            />
          </div>
        </div>

      </div>

      <div class="space-y-6">
        <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-surface-900-50">Дела из архива</h2>
            <Archive class="w-5 h-5 text-surface-500-400" />
          </div>

          <div class="mb-4">
            <p class="text-sm text-surface-600-400">
              Всего дел: <span class="font-semibold text-surface-900-50">
                {destruction.articles?.length || 0}
              </span>
            </p>
          </div>

          <ModalComponent bind:open={addArticlesModalOpen} title="Добавить дела из архива">
            {#snippet children()}
              <AddArticlesModal
                currentArticleIds={destruction.articles?.map(a => a.id) || []}
                onSave={handleUpdateArticles}
                onClose={() => addArticlesModalOpen = false}
              />
            {/snippet}
          </ModalComponent>

          <div class="overflow-x-auto mb-4">
            <table class="w-full">
              <thead class="bg-surface-100-800">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                    № дела
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                    Описание
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                    Дата создания
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                    Действия
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-surface-200-800">
                {#if !destruction.articles || destruction.articles.length === 0}
                  <tr>
                    <td colspan="4" class="px-4 py-4 text-center text-sm text-surface-500-400">
                      Нет дел в этом акте
                    </td>
                  </tr>
                {:else}
                  {#each destruction.articles as article (article.id)}
                    <tr class="hover:bg-surface-100-800 transition-colors">
                      <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-surface-900-50">
                        Дело №{article.id}
                      </td>
                      <td class="px-4 py-3 text-sm text-surface-600-400">
                        {article.name}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-sm text-surface-600-400">
                        {article.created_at ? new Date(article.created_at).toLocaleDateString('ru-RU') : '—'}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <button 
                          onclick={() => handleRemoveArticle(article.id)}
                          class="text-red-600 hover:text-red-900 transition-colors"
                          title="Удалить из списка"
                        >
                          Удалить
                        </button>
                      </td>
                    </tr>
                  {/each}
                {/if}
              </tbody>
            </table>
          </div>

          <div>
            <button 
              onclick={() => addArticlesModalOpen = true}
              class="btn preset-filled-primary-500 w-full"
            >
              + Добавить дело из архива
            </button>
          </div>
        </div>
      </div>
    </div>
  {/if}
</div>
