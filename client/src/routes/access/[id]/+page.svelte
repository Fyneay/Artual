<script lang="ts">
  import { page } from '$app/stores';
  import { User, Calendar, Shield, X, FileText } from '@lucide/svelte';
  import { getAccess, updateAccess, type Access, type UpdateAccessRequest } from '$lib/api/access';
  import { getStatuses, type Status } from '$lib/api/status';
  import { getArticles, type Article } from '$lib/api/articles';
  import ModalComponent from '$lib/components/ModalComponent.svelte';
  import AddArticlesModal from '$lib/components/AddArticlesModal.svelte';

  let { id } = $page.params;
  let numericId = $derived(parseInt(id, 10));

  let access = $state<Access | null>(null);
  let statuses = $state<Status[]>([]);
  let articles = $state<Article[]>([]);
  let loading = $state(false);
  let saving = $state(false);
  let error = $state<string | null>(null);
  let addArticlesModalOpen = $state(false);

  let formData = $state({
    name: '',
    close_date: null as string | null,
    status_id: null as number | null,
    article_id: null as number | null,
  });

  async function loadAccess() {
    if (isNaN(numericId)) {
      error = 'Неверный ID доступа';
      return;
    }

    loading = true;
    error = null;
    try {
      const response = await getAccess(numericId);
      access = response.data;
      
      formData.name = access.name || '';
      formData.close_date = access.close_date || null;
      formData.status_id = access.status_id || null;
      formData.article_id = access.article_id || null;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке доступа';
      console.error('Ошибка загрузки доступа:', err);
    } finally {
      loading = false;
    }
  }

  async function loadStatuses() {
    try {
      const response = await getStatuses();
      statuses = response.data;
    } catch (err) {
      console.error('Ошибка загрузки статусов:', err);
    }
  }

  async function loadArticles() {
    try {
      const response = await getArticles();
      articles = response.data;
    } catch (err) {
      console.error('Ошибка загрузки дел:', err);
    }
  }

  async function handleSave() {
    if (!access) return;

    error = null;
    saving = true;

    try {
      const updateData: UpdateAccessRequest = {
        name: formData.name,
        close_date: formData.close_date || undefined,
        status_id: formData.status_id ?? undefined,
        article_id: formData.article_id ?? undefined,
      };

      const response = await updateAccess(numericId, updateData);
      access = response.data;
      
      formData.name = access.name || '';
      formData.close_date = access.close_date || null;
      formData.status_id = access.status_id || null;
      formData.article_id = access.article_id || null;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при сохранении изменений';
      console.error('Ошибка сохранения:', err);
    } finally {
      saving = false;
    }
  }

  async function handleUpdateArticles(articleIds: number[]) {
    if (articleIds.length > 0) {
      formData.article_id = articleIds[0];
      await handleSave();
    } else {
      formData.article_id = null;
      await handleSave();
    }
    addArticlesModalOpen = false;
  }

  async function revokeAccess() {
    if (!access) return;
    
    if (confirm('Вы уверены, что хотите отозвать доступ?')) {
      const revokedStatus = statuses.find(s => s.name.toLowerCase().includes('отозван'));
      if (revokedStatus) {
        formData.status_id = revokedStatus.id;
        await handleSave();
      }
    }
  }

  function getStatusClass(statusName?: string | null) {
    if (!statusName) return 'text-gray-600 bg-gray-100';
    const lower = statusName.toLowerCase();
    if (lower.includes('активен')) return 'text-green-600 bg-green-100';
    if (lower.includes('отозван') || lower.includes('приостановлен')) return 'text-red-600 bg-red-100';
    if (lower.includes('истек')) return 'text-gray-600 bg-gray-100';
        return 'text-gray-600 bg-gray-100';
  }

  function formatDate(dateString: string | null | undefined): string {
    if (!dateString) return '';
    try {
      return new Date(dateString).toISOString().split('T')[0];
    } catch {
      return '';
    }
  }

  $effect(() => {
    loadAccess();
    loadStatuses();
    loadArticles();
  });

  let currentArticle = $derived(articles.find(a => a.id === formData.article_id));
</script>

<div class="w-full h-full p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Карточка доступа</h1>
      <p class="text-surface-600-400">ID: {id}</p>
    </div>
    <div class="flex space-x-3">
      <button 
        onclick={handleSave}
        class="btn preset-filled-primary-500"
        disabled={saving || loading || !access}
      >
        {saving ? 'Сохранение...' : 'Сохранить изменения'}
      </button>
      <button 
        onclick={revokeAccess}
        class="btn preset-filled-red-500"
        disabled={saving || loading || !access}
      >
        <X class="w-4 h-4 mr-2" />
        Отозвать доступ
      </button>
    </div>
  </div>

  {#if error && !loading}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded mb-6">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={loadAccess}
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
  {:else if access}
  <div class="grid grid-cols-[1fr_1fr] gap-6">
    <div class="space-y-6">
      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6 space-y-6">
        <h2 class="text-lg font-semibold text-surface-900-50 mb-4">Информация о доступе</h2>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <FileText class="inline w-4 h-4 mr-2" />
            Название доступа
          </label>
          <input 
            type="text" 
              bind:value={formData.name}
            class="input w-full"
            placeholder="Введите название доступа"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <User class="inline w-4 h-4 mr-2" />
            Выдал
          </label>
          <input 
            type="text" 
              value={access.created_by?.nickname || '—'}
              readonly
              class="input w-full bg-surface-100-800"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <User class="inline w-4 h-4 mr-2" />
            Кому выдано
          </label>
          <input 
            type="text" 
              value={access.granted_by?.nickname || '—'}
              readonly
              class="input w-full bg-surface-100-800"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <Calendar class="inline w-4 h-4 mr-2" />
            Дата выдачи
          </label>
          <input 
            type="date" 
              value={formatDate(access.access_date)}
              readonly
              class="input w-full bg-surface-100-800"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <Calendar class="inline w-4 h-4 mr-2" />
            Дата закрытия доступа
          </label>
          <input 
            type="date" 
              value={formatDate(formData.close_date)}
              onchange={(e) => {
                const target = e.target as HTMLInputElement;
                formData.close_date = target.value || null;
              }}
            class="input w-full"
              placeholder="Выберите дату закрытия"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <Shield class="inline w-4 h-4 mr-2" />
            Статус
          </label>
            <select bind:value={formData.status_id} class="select w-full">
              <option value={null}>—</option>
              {#each statuses as status}
                <option value={status.id}>{status.name}</option>
            {/each}
          </select>
        </div>

        <div class="mt-4">
          <div class="flex items-center gap-2 p-3 bg-surface-100-800 rounded-lg">
            <Shield class="w-5 h-5 text-surface-500-400" />
            <div class="flex-1">
              <p class="text-xs text-surface-500-400">Текущий статус</p>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {getStatusClass(access.status?.name)}">
                  {access.status?.name || '—'}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-6">
      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-surface-900-50">Дело доступа</h2>
            <button 
              onclick={() => addArticlesModalOpen = true}
              class="btn preset-filled-primary-500 btn-sm"
            >
              Выбрать дело
          </button>
        </div>

          <ModalComponent bind:open={addArticlesModalOpen} title="Выберите дело">
            {#snippet children()}
              <AddArticlesModal
                currentArticleIds={formData.article_id ? [formData.article_id] : []}
                onSave={handleUpdateArticles}
                onClose={() => addArticlesModalOpen = false}
              />
            {/snippet}
          </ModalComponent>

          {#if currentArticle}
            <div class="border border-surface-200-800 rounded-lg p-4 bg-surface-100-800">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h3 class="text-sm font-medium text-surface-900-50 mb-1">
                    {currentArticle.name}
                  </h3>
                  <p class="text-xs text-surface-500-400">
                    Дело №{currentArticle.id}
                    {#if currentArticle.created_at}
                      • {new Date(currentArticle.created_at).toLocaleDateString('ru-RU')}
                    {/if}
            </p>
          </div>
                <button 
                  onclick={() => {
                    formData.article_id = null;
                    handleSave();
                  }}
                  class="text-red-600 hover:text-red-900 transition-colors"
                  title="Удалить дело"
                >
                  <X class="w-4 h-4" />
                </button>
              </div>
            </div>
          {:else}
            <div class="border border-surface-200-800 rounded-lg p-4 text-center text-sm text-surface-500-400">
              Дело не выбрано
            </div>
          {/if}
        </div>
      </div>
    </div>
  {/if}
</div>
