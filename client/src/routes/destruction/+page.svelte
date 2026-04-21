<script lang="ts">
  import { Info, Book } from '@lucide/svelte';
  import { withBase } from '$lib/utils/paths';
  import { getDestructions, type Destruction } from '$lib/api/destructions';
  import DestructionForm from '$lib/components/forms/DestructionForm.svelte';
  import ModalComponent from '$lib/components/ModalComponent.svelte';

  let destructionModalOpen = $state(false);
  let destructions = $state<Destruction[]>([]);
  let loading = $state(false);
  let error = $state<string | null>(null);

  async function loadDestructions() {
    loading = true;
    error = null;
    try {
      const response = await getDestructions();
      destructions = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке актов уничтожения';
      console.error('Ошибка загрузки актов уничтожения:', err);
    } finally {
      loading = false;
    }
  }

  function handleSuccess() {
    destructionModalOpen = false;
    loadDestructions();
  }

  $effect(() => {
    loadDestructions();
  });
</script>

<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Уничтожение дел</h1>
      <p class="text-surface-600-400">Просмотр и управление</p>
    </div>
    <div class="flex space-x-3">
      <button 
        on:click={() => destructionModalOpen = true} 
        class="btn preset-filled-primary-500"
      >
        Добавить акт
      </button>
      <button class="btn preset-outline-secondary-500">
        Экспорт
      </button>
    </div>
  </div>

  <ModalComponent bind:open={destructionModalOpen} title="Создать акт уничтожения">
    {#snippet children()}
      <DestructionForm onSuccess={handleSuccess} />
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
        on:click={loadDestructions}
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
                Акт уничтожения
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Дата создания
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-200-800">
            {#if destructions.length === 0}
              <tr>
                <td colspan="3" class="px-6 py-4 text-center text-sm text-surface-500-400">
                  Нет актов уничтожения
                </td>
              </tr>
            {:else}
              {#each destructions as row (row.id)}
                <tr class="hover:bg-surface-100-800 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <Book class="w-4 h-4 text-surface-500-400 mr-2" />
                      <span class="text-sm font-medium text-surface-900-50">{row.name}</span>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-600-400">
                    {row.created_at ? new Date(row.created_at).toLocaleDateString('ru-RU') : '—'}
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <a 
                      href={withBase(`/destruction/${row.id}`)}
                      class="inline-flex items-center text-primary-600 hover:text-primary-900 transition-colors"
                      title="Открыть {row.name}"
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
</div>
