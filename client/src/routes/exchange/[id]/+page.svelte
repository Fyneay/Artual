<script lang="ts">
  import { page } from '$app/stores';
  import { FileText, Calendar, User, List, Archive, Upload, FileQuestion, Building2, FolderOpen } from '@lucide/svelte';
  import { getExchange, updateExchange, type Exchange, downloadExchangeAct, transferArticles } from '$lib/api/exchanges';
  import { type Article } from '$lib/api/articles';
  import FileUploadComponent from '$lib/components/FileUploadComponent.svelte';
  import ModalComponent from '$lib/components/ModalComponent.svelte';
  import AddArticlesModal from '$lib/components/AddArticlesModal.svelte';

  let { id } = $page.params;
  let numericId = $derived(parseInt(id, 10));

  let exchange = $state<Exchange | null>(null);
  let originalExchange = $state<Exchange | null>(null);
  let loading = $state(false);
  let error = $state<string | null>(null);
  let saving = $state(false);
  let addArticlesModalOpen = $state(false);
  let downloading = $state(false);
  let transferring = $state(false);

  const formattedDocuments = [
    'Акт приема-передачи',
  ];

  let attachedFiles: File[] = [];

  async function loadExchange() {
    if (isNaN(numericId)) {
      error = 'Неверный ID акта приема';
      return;
    }

    loading = true;
    error = null;
    try {
      const response = await getExchange(numericId);
      exchange = response.data;
      originalExchange = JSON.parse(JSON.stringify(response.data));
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке акта приема';
      console.error('Ошибка загрузки акта приема:', err);
    } finally {
      loading = false;
    }
  }

  async function handleSave() {
    if (!exchange || !originalExchange) return;

    saving = true;
    error = null;
    try {
      const updateData: {
        name?: string;
        reason?: string;
        fund_name?: string;
        receiving_organization?: string;
        article_ids?: number[];
      } = {};

      if (exchange.name !== originalExchange.name) {
        updateData.name = exchange.name.trim();
      }

      const currentReason = exchange.reason?.trim() || '';
      const originalReason = originalExchange.reason?.trim() || '';
      if (currentReason !== originalReason) {
        updateData.reason = currentReason || undefined;
      }

      const currentFundName = exchange.fund_name?.trim() || '';
      const originalFundName = originalExchange.fund_name?.trim() || '';
      if (currentFundName !== originalFundName) {
        updateData.fund_name = currentFundName || undefined;
      }

      const currentReceivingOrg = exchange.receiving_organization?.trim() || '';
      const originalReceivingOrg = originalExchange.receiving_organization?.trim() || '';
      if (currentReceivingOrg !== originalReceivingOrg) {
        updateData.receiving_organization = currentReceivingOrg || undefined;
      }

      const currentArticleIds = (exchange.articles || []).map(a => a.id).sort();
      const originalArticleIds = (originalExchange.articles || []).map(a => a.id).sort();
      if (JSON.stringify(currentArticleIds) !== JSON.stringify(originalArticleIds)) {
        updateData.article_ids = currentArticleIds;
      }

      if (Object.keys(updateData).length === 0) {
        saving = false;
        return;
      }

      const response = await updateExchange(numericId, updateData);
      exchange = response.data;
      originalExchange = JSON.parse(JSON.stringify(response.data));
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при сохранении';
    } finally {
      saving = false;
    }
  }

  async function handleUpdateArticles(articleIds: number[]) {
    if (!exchange) return;

    error = null;
    try {
      const response = await updateExchange(numericId, {
        article_ids: articleIds,
      });
      exchange = response.data;
      originalExchange = JSON.parse(JSON.stringify(response.data));
      addArticlesModalOpen = false;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при обновлении статей';
      throw err;
    }
  }

  async function handleRemoveArticle(articleId: number) {
    if (!exchange || !exchange.articles) return;

    const newArticleIds = exchange.articles
      .filter(a => a.id !== articleId)
      .map(a => a.id);

    error = null;
    try {
      const response = await updateExchange(numericId, {
        article_ids: newArticleIds,
      });
      exchange = response.data;
      originalExchange = JSON.parse(JSON.stringify(response.data));
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при удалении статьи';
      console.error('Ошибка удаления статьи:', err);
    }
  }

  async function handleDownloadAct() {
    if (!exchange) return;
    
    downloading = true;
    try {
      await downloadExchangeAct(numericId);
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при скачивании акта';
      console.error('Ошибка скачивания акта:', err);
    } finally {
      downloading = false;
    }
  }

  async function handleTransferArticles() {
    if (!exchange || !exchange.articles || exchange.articles.length === 0) {
      alert('Нет дел для передачи');
      return;
    }
    
    if (confirm(`Вы уверены, что хотите передать ${exchange.articles.length} дел? Это действие нельзя отменить.`)) {
      transferring = true;
      error = null;
      try {
        const response = await transferArticles(numericId);
        exchange = response.data;
        originalExchange = JSON.parse(JSON.stringify(response.data));
        alert('Дела успешно переданы');
        await loadExchange();
      } catch (err) {
        error = err instanceof Error ? err.message : 'Ошибка при передаче дел';
        console.error('Ошибка передачи дел:', err);
      } finally {
        transferring = false;
      }
    }
  }

  function downloadDocument(docName: string) {
    if (docName === 'Акт приема-передачи') {
      handleDownloadAct();
    } else {
      console.log('Скачать документ:', docName);
    }
  }

  function addFilesToCase() {
    if (attachedFiles.length === 0) {
      alert('Нет файлов для добавления');
      return;
    }
    
    console.log('Добавление файлов в дело...', attachedFiles);
    alert(`Добавлено ${attachedFiles.length} файлов в дело`);
  }

  $effect(() => {
    loadExchange();
  });
</script>

<div class="w-full h-full p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Карта акта приема-передачи</h1>
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
        disabled={downloading || loading || !exchange}
      >
        {downloading ? 'Скачивание...' : 'Печать акта'}
      </button>
      <button 
        onclick={addFilesToCase}
        class="btn preset-filled-primary-500"
      >
        <Upload class="w-4 h-4 mr-2" />
        Добавить файлы в дело
      </button>
      <button 
        onclick={handleTransferArticles}
        class="btn preset-filled-green-500"
        disabled={transferring || loading || !exchange || !exchange.articles || exchange.articles.length === 0}
      >
        {transferring ? 'Передача...' : 'Передать дела'}
      </button>
    </div>
  </div>

  {#if error && !loading}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded mb-6">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={loadExchange}
        class="btn btn-sm mt-2 preset-filled-primary-500"
      >
        Повторить
      </button>
    </div>
  {/if}

  {#if exchange}
    <ModalComponent bind:open={addArticlesModalOpen} title="Добавить дела из архива">
      {#snippet children()}
        {#if exchange}
          <AddArticlesModal
            currentArticleIds={(exchange.articles || []).map(a => a.id)}
            onSave={handleUpdateArticles}
            onClose={() => addArticlesModalOpen = false}
          />
        {/if}
      {/snippet}
    </ModalComponent>
  {/if}

  {#if loading}
    <div class="flex items-center justify-center p-4">
      <span class="text-surface-600-300">Загрузка...</span>
    </div>
  {:else if exchange}
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
              bind:value={exchange.name}
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
              value={exchange.created_at ? new Date(exchange.created_at).toLocaleDateString('ru-RU') : '—'}
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
              value={exchange.creator ? exchange.creator.nickname : '—'}
              readonly
              class="input w-full bg-surface-100-800"
            />
          </div>
      </div>

      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-surface-900-50">Причина создания акта</h2>
          <FileQuestion class="w-5 h-5 text-surface-500-400" />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            Описание причины
          </label>
          <textarea 
            bind:value={exchange.reason}
            class="textarea w-full min-h-[120px]"
            placeholder="Введите причину создания акта приема-передачи"
          />
          {#if !exchange.reason || exchange.reason.trim() === ''}
            <p class="text-xs text-surface-500-400 mt-1">
              Причина не указана
            </p>
          {/if}
        </div>
      </div>

      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6 space-y-6">
        <h2 class="text-lg font-semibold text-surface-900-50 mb-4">Информация о передаче</h2>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <FolderOpen class="inline w-4 h-4 mr-2" />
            Название передаваемого фонда
          </label>
          <input 
            type="text" 
            bind:value={exchange.fund_name}
            class="input w-full"
            placeholder="Введите название передаваемого фонда"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-surface-700-300 mb-2">
            <Building2 class="inline w-4 h-4 mr-2" />
            Название организации принимающей документы
          </label>
          <input 
            type="text" 
            bind:value={exchange.receiving_organization}
            class="input w-full"
            placeholder="Введите название организации принимающей документы"
          />
        </div>
      </div>

      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-surface-900-50">Сформированные документы</h2>
          <List class="w-5 h-5 text-surface-500-400" />
        </div>

        <div class="space-y-2">
          {#each formattedDocuments as doc, index (doc)}
            <div class="flex items-center justify-between p-3 bg-surface-100-800 rounded-lg hover:bg-surface-200-700 transition-colors">
              <div class="flex items-center space-x-3">
                <FileText class="w-4 h-4 text-surface-500-400" />
                <span class="text-sm text-surface-700-300">{doc}</span>
              </div>
              <button 
                onclick={() => downloadDocument(doc)}
                class="btn btn-sm preset-filled-primary-500"
                title="Скачать документ"
              >
                Скачать
              </button>
            </div>
          {/each}
        </div>
      </div>

      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-surface-900-50">Файлы</h2>
          <Upload class="w-5 h-5 text-surface-500-400" />
        </div>

        <FileUploadComponent 
          files={attachedFiles} 
          onFilesChange={(files) => attachedFiles = files}
          maxFiles={20}
          acceptedTypes="*"
        />
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
              {exchange.articles?.length || 0}
            </span>
          </p>
        </div>

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
              {#if !exchange.articles || exchange.articles.length === 0}
                <tr>
                  <td colspan="4" class="px-4 py-4 text-center text-sm text-surface-500-400">
                    Нет дел в этом акте
                  </td>
                </tr>
              {:else}
                {#each exchange.articles as article (article.id)}
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
