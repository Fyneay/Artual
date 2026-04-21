<script lang="ts">
    import {XIcon} from "@lucide/svelte"
    import {Dialog, Portal} from "@skeletonlabs/skeleton-svelte"
    import {getCertificates, createSignature, type Certificate} from "../certificates"
    import {createSignature as apiCreateSignature, getSigningData, type SigningData} from "../api/signatures"
    import type {ArticleFile} from "../api/articles"
    
    let { articleId = null, approvedFiles = [] }: { articleId?: number | null, approvedFiles?: ArticleFile[] } = $props();
    
    let expandedCert: string | null = null;
    let signing = false;
    let signingCert: string | null = null;
    let signingData: SigningData | null = null;
    let loadingSigningData = false;
    
    function toggleCert(certName: string) {
        expandedCert = expandedCert === certName ? null : certName;
    }
    
    async function loadSigningData() {
        if (!articleId) return;
        
        loadingSigningData = true;
        try {
            const response = await getSigningData(articleId);
            signingData = response.data;
        } catch (error) {
            console.error('Ошибка при загрузке данных для подписи:', error);
        } finally {
            loadingSigningData = false;
        }
    }
    
    async function handleSign(cert: Certificate) {
        if (signing || !articleId) return;
        
        signing = true;
        signingCert = cert.name;
        
        try {
            if (!signingData) {
                await loadSigningData();
            }
            
            if (!signingData) {
                throw new Error('Не удалось загрузить данные для подписи');
            }
            
            const signature = await createSignature(signingData.combined_hash, cert);
            
            const result = await apiCreateSignature({
                signature_data: signature,
                certificate_name: cert.name,
                certificate_subject: cert.subjectName,
                article_id: articleId,
                signed_by: 0
            });
            
            console.log('Подпись сохранена:', result);
            alert(`Подпись успешно создана для ${signingData.files_count} файлов`);
            
        } catch (error) {
            console.error('Ошибка:', error);
            alert(`Ошибка при создании подписи: ${error instanceof Error ? error.message : 'Неизвестная ошибка'}`);
        } finally {
            signing = false;
            signingCert = null;
        }
    }
    
    $effect(() => {
        if (articleId && approvedFiles.length > 0) {
            loadSigningData();
        }
    });
</script>

<Dialog>
    <Dialog.Trigger class="btn preset-filled">Подписать документ</Dialog.Trigger>
    <Portal>
        <Dialog.Backdrop class="fixed inset-0 z-50 bg-surface-50-950/50 transition"/>
        <Dialog.Positioner class="fixed inset-0 z-50 flex justify-end">
            <Dialog.Content class="h-screen card bg-surface-100-900 w-sm p-4 space-y-4 shadow-xl">
                <header class="flex justify-between items-center">
                    <Dialog.Title class="text-2xl font-bold">Подпись документа</Dialog.Title>
                    <Dialog.CloseTrigger class="btn-icon preset-tonal">
                        <XIcon/>
                    </Dialog.CloseTrigger>
                </header>
                
                {#if articleId && approvedFiles.length > 0}
                    <div class="text-sm text-surface-600-400 mb-4">
                        <p>Будет подписано файлов: <strong>{approvedFiles.length}</strong></p>
                        <ul class="list-disc list-inside mt-2">
                            {#each approvedFiles as file}
                                <li>{file.filename}</li>
                            {/each}
                        </ul>
                    </div>
                {:else}
                    <div class="text-sm text-error-500 mb-4">
                        Нет файлов со статусом "approved" для подписи
                    </div>
                {/if}
                
                {#if loadingSigningData}
                    <p class="text-sm text-surface-600-400">Загрузка данных для подписи...</p>
                {/if}
                
                {#await getCertificates()}
                    <p class="text-sm text-surface-600-400">Загрузка сертификатов...</p>
                {:then certifications}
                    {#if certifications.length === 0}
                        <div class="text-sm text-error-500 p-3 bg-error-500/10 border border-error-500 rounded-lg">
                            <p>Не найдено сертификатов для подписи.</p>
                            <p class="mt-2 text-xs">Убедитесь, что плагин КриптоПро установлен и доступен в браузере.</p>
                        </div>
                    {:else}
                        <div class="space-y-2">
                            {#each certifications as cert}
                                <div class="border border-surface-200-800 rounded-lg overflow-hidden">
                                    <div class="flex items-center justify-between p-2 hover:bg-surface-200-800 transition-colors">
                                        <button 
                                            class="flex-1 text-left"
                                            on:click={() => toggleCert(cert.name)}
                                        >
                                            <p class="text-sm font-medium">{cert.name}</p>
                                        </button>
                                        <button 
                                            class="btn btn-sm preset-filled-primary ml-2"
                                            on:click={() => handleSign(cert)}
                                            disabled={signing || !articleId || approvedFiles.length === 0}
                                        >
                                            {signing && signingCert === cert.name ? 'Подписание...' : 'Подписать'}
                                        </button>
                                    </div>
                                    {#if expandedCert === cert.name}
                                        <div class="p-3 bg-surface-200-800 border-t border-surface-200-800">
                                            <p class="text-sm text-surface-600-400">{cert.subjectName}</p>
                                        </div>
                                    {/if}
                                </div>
                            {/each}
                        </div>
                    {/if}
                {:catch error}
                    <div class="text-sm text-error-500 p-3 bg-error-500/10 border border-error-500 rounded-lg">
                        <p class="font-medium">Ошибка при загрузке сертификатов:</p>
                        <p class="mt-2 text-xs">{error instanceof Error ? error.message : 'Неизвестная ошибка'}</p>
                        <p class="mt-2 text-xs">Убедитесь, что плагин КриптоПро установлен и доступен в браузере.</p>
                    </div>
                {/await}
            </Dialog.Content>
        </Dialog.Positioner>
    </Portal>
</Dialog>