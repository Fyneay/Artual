<script lang="ts">
  import { Info, User } from '@lucide/svelte';
  import { withBase } from '$lib/utils/paths';
  import { getAccesses, type Access } from '$lib/api/access';
  import AccessForm from '$lib/components/forms/AccessForm.svelte';
  import ModalComponent from '$lib/components/ModalComponent.svelte';

  let accessModalOpen = $state(false);
  let accesses = $state<Access[]>([]);
  let loading = $state(false);
  let error = $state<string | null>(null);

  async function loadAccesses() {
    loading = true;
    error = null;
    try {
      const response = await getAccesses();
      accesses = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке допусков';
      console.error('Ошибка загрузки допусков:', err);
    } finally {
      loading = false;
    }
  }

  function handleSuccess() {
    accessModalOpen = false;
    loadAccesses();
  }

  $effect(() => {
    loadAccesses();
  });

  function getAccessStatus(closeDate?: string | null) {
    if (!closeDate) return 'Активен';
    const today = new Date();
    const close = new Date(closeDate);
    return close > today ? 'Активен' : 'Истек';
  }

  function getStatusClass(closeDate?: string | null) {
    if (!closeDate) return 'text-green-600 bg-green-100';
    const today = new Date();
    const close = new Date(closeDate);
    return close > today ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
  }
</script>

<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Управление доступом</h1>
      <p class="text-surface-600-400">Просмотр и управление картами доступа</p>
    </div>
    <div class="flex space-x-3">
      <button 
        onclick={() => accessModalOpen = true}
        class="btn preset-filled-primary-500"
      >
        Добавить доступ
      </button>
      <button class="btn preset-outline-secondary-500">
        Экспорт
      </button>
    </div>
  </div>

  <ModalComponent bind:open={accessModalOpen} title="Создать допуск">
    {#snippet children()}
      <AccessForm onSuccess={handleSuccess} />
    {/snippet}
  </ModalComponent>

  {#if loading}
    <div class="flex items-center justify-center p-4">
      <span class="text-surface-600-300">Загрузка...</span>
    </div>
  {:else if error}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={loadAccesses}
        class="btn btn-sm mt-2 preset-filled-primary-500"
      >
        Повторить
      </button>
    </div>
  {:else}
    <div class="bg-surface-50-900 rounded-lg shadow-sm border border-surface-200-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-surface-100-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Название
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Статус
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Выдан доступ
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Дата закрытия доступа
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Основание выдачи доступа
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-200-800">
            {#if accesses.length === 0}
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-surface-500-400">
                  Нет допусков
                </td>
              </tr>
            {:else}
              {#each accesses as access (access.id)}
                <tr class="hover:bg-surface-100-800 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <User class="w-4 h-4 text-surface-500-400 mr-2" />
                      <span class="text-sm font-medium text-surface-900-50">{access.name}</span>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {getStatusClass(access.close_date)}">
                      {getAccessStatus(access.close_date)}
                    </span>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-surface-700-300">
                      <p class="font-medium">{access.granted_by?.nickname || '—'}</p>
                      <p class="text-xs text-surface-500-400">{access.access_date ? new Date(access.access_date).toLocaleDateString('ru-RU') : '—'}</p>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-600-400">
                    {access.close_date ? new Date(access.close_date).toLocaleDateString('ru-RU') : '—'}
                  </td>
                  
                  <td class="px-6 py-4 text-sm text-surface-600-400 max-w-xs truncate">
                    {access.reason || '—'}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a 
                      href={withBase(`/access/${access.id}`)}
                      class="inline-flex items-center text-primary-600 hover:text-primary-900 transition-colors"
                      title="Открыть информацию о карте доступа"
                    >
                      <Info class="w-5 h-5" />
                    </a>
                  </td>
                </tr>
              {/each}
            {/if}
          </tbody>
        </table>
      </div>
    </div>
  {/if}

  <div class="mt-6 grid grid-cols-3 gap-4">
    <div class="bg-surface-100-800 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-surface-500-400">Всего записей</p>
          <p class="text-2xl font-bold text-surface-900-50">{accesses.length}</p>
        </div>
        <User class="w-8 h-8 text-surface-400" />
      </div>
    </div>
    <div class="bg-surface-100-800 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-surface-500-400">Активных</p>
          <p class="text-2xl font-bold text-green-600">
            {accesses.filter(a => !a.close_date || new Date(a.close_date) > new Date()).length}
          </p>
        </div>
        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
          <User class="w-5 h-5 text-green-600" />
        </div>
      </div>
    </div>
    <div class="bg-surface-100-800 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-surface-500-400">Истекших</p>
          <p class="text-2xl font-bold text-red-600">
            {accesses.filter(a => a.close_date && new Date(a.close_date) <= new Date()).length}
          </p>
        </div>
        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
          <User class="w-5 h-5 text-red-600" />
        </div>
      </div>
    </div>
  </div>
</div>