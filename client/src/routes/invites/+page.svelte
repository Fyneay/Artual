<script lang="ts">
  import { Info, User, Mail, Calendar } from '@lucide/svelte';
  import { getInvites, deleteInvite, type Invite } from '$lib/api/invites';
  import InviteForm from '$lib/components/forms/InviteForm.svelte';
  import ModalComponent from '$lib/components/ModalComponent.svelte';

  let invites = $state<Invite[]>([]);
  let loading = $state(false);
  let error = $state<string | null>(null);
  let inviteModalOpen = $state(false);

  async function loadInvites() {
    loading = true;
    error = null;
    try {
      const response = await getInvites();
      invites = response.data;
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке инвайтов';
      console.error('Ошибка загрузки инвайтов:', err);
    } finally {
      loading = false;
    }
  }

  function handleSuccess() {
    inviteModalOpen = false;
    loadInvites();
  }

  async function handleDelete(key: string) {
    if (!confirm('Вы уверены, что хотите удалить эту ссылку?')) {
      return;
    }

    try {
      await deleteInvite(key);
      await loadInvites();
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при удалении инвайта';
      console.error('Ошибка удаления инвайта:', err);
    }
  }

  // Функция для форматирования статуса
  function getInviteStatus(invite: Invite): string {
    if (invite.used) return 'Использовано';
    const expires = new Date(invite.expires_at);
    const now = new Date();
    return expires > now ? 'Активен' : 'Истек';
  }

  function getStatusClass(invite: Invite): string {
    if (invite.used) return 'text-gray-600 bg-gray-100';
    const expires = new Date(invite.expires_at);
    const now = new Date();
    return expires > now ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100';
  }

  function formatDate(dateString: string): string {
    try {
      return new Date(dateString).toLocaleString('ru-RU');
    } catch {
      return dateString;
    }
  }

  function formatShortDate(dateString: string): string {
    try {
      return new Date(dateString).toLocaleDateString('ru-RU');
    } catch {
      return dateString;
    }
  }

  $effect(() => {
    loadInvites();
  });
</script>

<div class="p-6">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900-50 mb-2">Управление ссылками-регистрации</h1>
      <p class="text-surface-600-400">Просмотр и управление регистрациями гостей в системе</p>
    </div>
    <div class="flex space-x-3">
      <button 
        onclick={() => inviteModalOpen = true}
        class="btn preset-filled-primary-500"
      >
        Добавить ссылку
      </button>
    </div>
  </div>

  <ModalComponent bind:open={inviteModalOpen} title="Создать инвайт-ссылку">
    {#snippet children()}
      <InviteForm onSuccess={handleSuccess} />
    {/snippet}
  </ModalComponent>

  {#if error && !loading}
    <div class="p-4 bg-error-500/10 border border-error-500 rounded mb-6">
      <p class="text-error-500 text-sm">{error}</p>
      <button 
        onclick={loadInvites}
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
  {:else}
    <div class="bg-surface-50-900 rounded-lg shadow-sm border border-surface-200-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-surface-100-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Идентификатор ссылки
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Статус
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Дата создания
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Дата истечения
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Почта гостя
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500-400 uppercase tracking-wider">
                Действия
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-200-800">
            {#if invites.length === 0}
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-sm text-surface-500-400">
                  Нет инвайт-ссылок
                </td>
              </tr>
            {:else}
              {#each invites as invite (invite.key)}
                <tr class="hover:bg-surface-100-800 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <User class="w-4 h-4 text-surface-500-400 mr-2" />
                      <code class="text-sm font-mono text-surface-900-50">{invite.key}</code>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {getStatusClass(invite)}">
                      {getInviteStatus(invite)}
                    </span>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-600-400">
                    {formatShortDate(invite.created_at)}
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-600-400">
                    {formatShortDate(invite.expires_at)}
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <Mail class="w-4 h-4 text-surface-500-400 mr-2" />
                      <span class="text-sm text-surface-700-300">{invite.email}</span>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button 
                      onclick={() => handleDelete(invite.key)}
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Удалить ссылку"
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
    </div>
  {/if}

  <div class="mt-6 grid grid-cols-3 gap-4">
    <div class="bg-surface-100-800 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-surface-500-400">Всего ссылок</p>
          <p class="text-2xl font-bold text-surface-900-50">{invites.length}</p>
        </div>
        <User class="w-8 h-8 text-surface-400" />
      </div>
    </div>
    <div class="bg-surface-100-800 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-surface-500-400">Активных</p>
          <p class="text-2xl font-bold text-green-600">
            {invites.filter(i => !i.used && new Date(i.expires_at) > new Date()).length}
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
          <p class="text-sm text-surface-500-400">Использовано</p>
          <p class="text-2xl font-bold text-gray-600">
            {invites.filter(i => i.used).length}
          </p>
        </div>
        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
          <User class="w-5 h-5 text-gray-600" />
        </div>
      </div>
    </div>
  </div>
</div>
