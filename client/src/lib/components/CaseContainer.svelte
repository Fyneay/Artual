<script lang="ts">
    import { Download, Archive, Loader2 } from "@lucide/svelte"
    import { createSignatureArchive, getSignatureArchivesByArticle, type SignatureArchive } from "../api/signatures"
    
    let { articleId }: { articleId: number | null } = $props();
    
    let archives = $state<SignatureArchive[]>([]);
    let loading = $state(false);
    let creating = $state(false);
    let error: string | null = $state(null);
    let successMessage: string | null = $state(null);
    
    async function loadArchives() {
        if (!articleId) return;
        
        loading = true;
        error = null;
        try {
            const response = await getSignatureArchivesByArticle(articleId);
            archives = response.data;
        } catch (err) {
            console.error('Ошибка при загрузке архивов:', err);
            error = err instanceof Error ? err.message : 'Не удалось загрузить архивы';
        } finally {
            loading = false;
        }
    }
    
    async function createArchive() {
        if (!articleId || creating) return;
        
        creating = true;
        error = null;
        successMessage = null;
        
        try {
            const response = await createSignatureArchive([articleId]);
            successMessage = 'Контейнер дела успешно создан';
            
            await loadArchives();
            
            setTimeout(() => {
                successMessage = null;
            }, 5000);
        } catch (err) {
            console.error('Ошибка при создании архива:', err);
            error = err instanceof Error ? err.message : 'Не удалось создать контейнер дела';
        } finally {
            creating = false;
        }
    }
    
    function formatDate(dateString: string | undefined): string {
        if (!dateString) return '—';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch {
            return dateString;
        }
    }
    
    async function downloadArchive(archive: SignatureArchive) {
        try {
            const { downloadArchive } = await import('../api/signatures');
            await downloadArchive(archive.id);
        } catch (err) {
            console.error('Ошибка скачивания архива:', err);
            error = err instanceof Error ? err.message : 'Ошибка при скачивании архива';
        }
    }
    
    $effect(() => {
        if (articleId) {
            loadArchives();
        }
    });

    $effect(() => {
        console.log('CaseContainer articleId:', articleId);
    });
</script>

<div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-surface-900-50">Контейнер дела</h2>
        {#if articleId && archives.length === 0 && !loading}
            <button 
                class="btn preset-filled-primary-500 btn-sm"
                on:click={createArchive}
                disabled={creating}
            >
                {#if creating}
                    <Loader2 class="w-4 h-4 mr-2 animate-spin" />
                {:else}
                    <Archive class="w-4 h-4 mr-2" />
                {/if}
                {creating ? 'Создание...' : 'Сформировать контейнер дела'}
            </button>
        {/if}
    </div>

    {#if successMessage}
        <div class="p-3 mb-4 bg-green-500/10 border border-green-500 rounded text-green-500 text-sm">
            {successMessage}
        </div>
    {/if}

    {#if error}
        <div class="p-3 mb-4 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
            {error}
        </div>
    {/if}

    {#if creating}
        <div class="flex items-center justify-center p-4">
            <Loader2 class="w-5 h-5 mr-2 animate-spin" />
            <span class="text-sm text-surface-600-400">Создание контейнера дела...</span>
        </div>
    {:else if loading}
        <div class="flex items-center justify-center p-4">
            <Loader2 class="w-5 h-5 mr-2 animate-spin" />
            <span class="text-sm text-surface-600-400">Загрузка архивов...</span>
        </div>
    {:else if archives.length === 0}
        <div class="text-sm text-surface-600-400">
            <p>Контейнер дела содержит все документы и материалы, связанные с этим делом.</p>
            <p class="mt-2">Нажмите кнопку "Сформировать контейнер дела" для создания архива.</p>
        </div>
    {:else}
        <div class="space-y-3">
            <p class="text-sm text-surface-600-400 mb-3">
                Созданные контейнеры дела:
            </p>
            {#each archives as archive}
                <div class="border border-surface-200-800 rounded-lg p-4 hover:bg-surface-100-800 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <Archive class="w-4 h-4 mr-2 text-surface-600-400" />
                                <h3 class="text-sm font-medium text-surface-900-50">
                                    {archive.archive_name}
                                </h3>
                            </div>
                            <div class="text-xs text-surface-600-400 space-y-1">
                                <p>Создан: {formatDate(archive.created_at)}</p>
                                {#if archive.archive_path}
                                    <p class="truncate">Путь: {archive.archive_path}</p>
                                {/if}
                            </div>
                        </div>
                        <button
                            class="btn btn-sm preset-outline-primary ml-4"
                            on:click={() => downloadArchive(archive)}
                            title="Скачать архив"
                        >
                            <Download class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            {/each}
        </div>
    {/if}
</div>
