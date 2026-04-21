<script lang="ts">
  import { page } from '$app/stores';
  import { Upload, FileText, Calendar, User, MapPin, Shield, Download, Archive, Loader2, Eye } from '@lucide/svelte';
  import FileUploader from '$lib/components/FileUploader.svelte';
  import Drawer from '$lib/components/Drawer.svelte';
  import { getArticle, updateArticle, downloadArticleFile, type Article, type ArticleFile } from '$lib/api/articles';
  import { uploadFileForCheck } from '$lib/api/fileCheck';
  import { getListPeriods, type ListPeriod } from '$lib/api/listPeriods';
  import { getTypesDocument, type TypeDocument } from '$lib/api/typesDocument';
  import { getStatuses, type Status } from '$lib/api/status';
  import CaseContainer from '$lib/components/CaseContainer.svelte';
  import BadgeComponent from '$lib/components/BadgeComponent.svelte';
  import { onMount } from 'svelte';
  import { browser } from '$app/environment';

  let { id, doc } = $page.params;


  let article: Article | null = null;
  let files = $state<ArticleFile[]>([]);
  let loading = $state(false);
  let saving = $state(false);
  let error: string | null = null;
  let successMessage: string | null = null;
  let listPeriods = $state<ListPeriod[]>([]);
  let selectedListPeriodId = $state<number | null>(null);
  let typesDocument = $state<TypeDocument[]>([]);
  let statuses = $state<Status[]>([]);

  let uploadedFiles = $state<File[]>([]);
	
	function handleFilesChange(files: File[]) {
		uploadedFiles = files;
		console.log('Файлы:', files);
	}

  let caseData = $state({
    title: '—',
    location: '',
    isSecret: false,
    createdDate: '',
    created_by: {
      id: 0,
      nickname: '',
      email: '',
    },
    description: '',
    destructionDate: '',
    status_id: null as number | null,
    type_document_id: null as number | null,
    listPeriodId: null as number | null
  });



  let selectedPdfFile: ArticleFile | null = null;
  let pdfBlobUrl: string | null = null;
  let pdfLoading = $state(false);
  let pdfError = $state<string | null>(null);
  let pdfDocument = $state<any>(null);
  let currentPage = $state(1);
  let totalPages = $state(0);
  let pdfCanvas = $state<HTMLCanvasElement | null>(null);
  
  let pdfjsLib: any = null;
  let pdfjsWorker: string | null = null;

  async function initPdfJs() {
    if (!browser) {
      console.log('PDF.js: не в браузере, пропускаем инициализацию');
      return;
    }
    
    if (pdfjsLib) {
      console.log('PDF.js: уже инициализирован');
      return;
    }
    
    try {
      console.log('PDF.js: начинаем загрузку...');
      const pdfjs = await import('pdfjs-dist');
      const worker = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
      
      pdfjsLib = pdfjs.default || pdfjs;
      pdfjsWorker = worker.default;
      pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;
      console.log('PDF.js: успешно загружен', { pdfjsLib, pdfjsWorker });
    } catch (err) {
      console.error('Ошибка загрузки PDF.js:', err);
      throw err;
    }
  }
  async function handleDownloadFile(file: ArticleFile) {
    if (isDestroyed) {
      alert('Нельзя скачать файл уничтоженного дела');
      return;
    }
    
    try {
      await downloadArticleFile(file.id);
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при скачивании файла';
      console.error('Ошибка скачивания файла:', err);
    }
  }

  async function handleViewPdf(file: ArticleFile) {
    console.log('handleViewPdf вызвана', { file, isDestroyed });
    
    if (isDestroyed) {
      alert('Нельзя просмотреть файл уничтоженного дела');
      return;
    }

    if (file.mime_type !== 'application/pdf') {
      alert('Можно просматривать только PDF файлы');
      return;
    }

    console.log('Начинаем инициализацию PDF.js...');
    try {
      await initPdfJs();
    } catch (err) {
      console.error('Ошибка инициализации PDF.js:', err);
      pdfError = 'Не удалось загрузить PDF.js: ' + (err instanceof Error ? err.message : String(err));
      pdfLoading = false;
      return;
    }
    
    if (!pdfjsLib) {
      console.error('PDF.js не загружен после инициализации');
      pdfError = 'Не удалось загрузить PDF.js';
      pdfLoading = false;
      return;
    }

    console.log('PDF.js загружен, начинаем загрузку файла...');
    selectedPdfFile = file;
    pdfLoading = true;
    pdfError = null;
    currentPage = 1;

    try {
      const { getAuthToken, API_BASE_URL } = await import('$lib/api/client');
      const token = getAuthToken();
      console.log('Загружаем файл:', `${API_BASE_URL}/article-files/${file.id}/download`);
      
      const response = await fetch(`${API_BASE_URL}/article-files/${file.id}/download`, {
        headers: token ? {
          'Authorization': `Bearer ${token}`
        } : {}
      });

      if (!response.ok) {
        throw new Error(`Не удалось загрузить файл: ${response.status} ${response.statusText}`);
      }

      const blob = await response.blob();
      console.log('Файл загружен, размер:', blob.size);
      
      if (pdfBlobUrl) {
        URL.revokeObjectURL(pdfBlobUrl);
        pdfBlobUrl = null;
      }

      const arrayBuffer = await blob.arrayBuffer();
      const uint8Array = new Uint8Array(arrayBuffer);
      console.log('Данные подготовлены, загружаем в PDF.js...');

      if (!pdfjsLib.getDocument) {
        console.error('pdfjsLib.getDocument не найден', pdfjsLib);
        throw new Error('PDF.js не инициализирован корректно');
      }
      
      const loadingTask = pdfjsLib.getDocument({ 
        data: uint8Array,
        useSystemFonts: true
      });
      console.log('PDF документ загружается...');
      
      pdfDocument = await loadingTask.promise;
      totalPages = pdfDocument.numPages;
      console.log('PDF документ загружен, страниц:', totalPages);

      await renderPage(1);
      console.log('Первая страница отрендерена');

    } catch (err) {
      pdfError = err instanceof Error ? err.message : 'Ошибка при загрузке PDF';
      console.error('Ошибка загрузки PDF:', err);
    } finally {
      pdfLoading = false;
    }
  }

  async function renderPage(pageNum: number) {
    if (!pdfDocument || !pdfCanvas) return;

    try {
      const page = await pdfDocument.getPage(pageNum);
      const viewport = page.getViewport({ scale: 1.5 });
      
      const context = pdfCanvas.getContext('2d');
      if (!context) return;

      pdfCanvas.height = viewport.height;
      pdfCanvas.width = viewport.width;

      const renderContext = {
        canvasContext: context,
        viewport: viewport
      };

      await page.render(renderContext).promise;
      currentPage = pageNum;
    } catch (err) {
      console.error('Ошибка рендеринга страницы:', err);
    }
  }

  async function goToPage(pageNum: number) {
    if (pageNum < 1 || pageNum > totalPages) return;
    await renderPage(pageNum);
  }

  $effect(() => {
    return () => {
      if (pdfBlobUrl) {
        URL.revokeObjectURL(pdfBlobUrl);
      }
    };
  });

  async function loadListPeriods() {
    try {
      const response = await getListPeriods();
      listPeriods = response.data;
    } catch (err) {
      console.error('Ошибка загрузки классификаций:', err);
    }
  }

  async function loadTypesDocument() {
    try {
      const response = await getTypesDocument();
      typesDocument = response.data;
    } catch (err) {
      console.error('Ошибка загрузки типов документов:', err);
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

  function calculateExpirationDate() {
    if (selectedListPeriodId && caseData.createdDate) {
      const period = listPeriods.find(p => p.id === selectedListPeriodId);
      if (period) {
        if (period.retention_period === 0) {
          caseData.destructionDate = '';
          return;
        }
        
        const createdDate = new Date(caseData.createdDate);
        const expirationDate = new Date(createdDate);
        expirationDate.setFullYear(expirationDate.getFullYear() + period.retention_period);
        caseData.destructionDate = expirationDate.toISOString().slice(0, 10);
      }
    } else {
      if (article?.expiration_date) {
        caseData.destructionDate = article.expiration_date;
      } else {
        caseData.destructionDate = '';
      }
    }
  }

  async function loadArticle() {
    loading = true;
    error = null;
    try {
      const numericId = parseInt(doc.replace('article-', ''), 10);
      if (isNaN(numericId)) throw new Error('Неверный ID документа');
      const res = await getArticle(numericId);
      article = res.data;
      files = res.data.files ?? [];

      selectedListPeriodId = article.list_period_id ?? null;
      
      caseData = {
        ...caseData,
        title: article.name ?? '—',
        location: article.location ?? '',
        description: article.description ?? '',
        isSecret: article.secrecy_grade ?? false,
        createdDate: article.created_at ? new Date(article.created_at).toISOString().slice(0, 10) : '',
        destructionDate: article.expiration_date ?? '',
        listPeriodId: article.list_period_id ?? null,
        type_document_id: article.type_document_id ?? null,
        status_id: article.status_id ?? null,
        created_by: article.created_by ? {
          id: article.created_by.id ?? 0,
          nickname: article.created_by.nickname ?? '',
          email: article.created_by.email ?? '',
        } : caseData.created_by,
      };
    } catch (e: any) {
      error = e?.message ?? 'Ошибка при загрузке документа';
      article = null;
    } finally {
      loading = false;
    }
  }

  $effect(() => {
    if (selectedListPeriodId && caseData.createdDate) {
      calculateExpirationDate();
    }
  });

  $effect(() => {
    if (caseData.createdDate && selectedListPeriodId) {
      calculateExpirationDate();
    }
  });

  async function handleSave() {
    if (!article) {
      error = 'Документ не загружен';
      return;
    }

    if (!caseData.title || caseData.title.trim() === '') {
      error = 'Название дела не может быть пустым';
      saving = false;
      return;
    }

    saving = true;
    error = null;
    successMessage = null;

    try {
      const numericId = parseInt(doc.replace('article-', ''), 10);
      if (isNaN(numericId)) throw new Error('Неверный ID документа');

      const updateData: Partial<{
        name: string;
        secrecy_grade: boolean;
        created_at: string;
        user_id: number;
        section_id: number;
        list_period_id: number | null;
        location: string | null;
        type_document_id: number | null;
        description: string | null;
        status_id: number | null;
      }> = {
        name: caseData.title.trim(),
        secrecy_grade: caseData.isSecret,
        user_id: article.user_id!,
        section_id: article.section_id!,
      };

      if (caseData.createdDate) {
        updateData.created_at = new Date(caseData.createdDate).toISOString();
      }

      if (selectedListPeriodId !== null) {
        updateData.list_period_id = selectedListPeriodId;
      } else if (selectedListPeriodId === null && article.list_period_id !== null) {
        updateData.list_period_id = null;
      }

      updateData.location = caseData.location || null;
      
      if (caseData.type_document_id !== null) {
        updateData.type_document_id = caseData.type_document_id;
      } else if (caseData.type_document_id === null && article.type_document_id !== null) {
        updateData.type_document_id = null;
      }

      updateData.description = caseData.description || null;

      if (caseData.status_id !== null) {
        updateData.status_id = caseData.status_id;
      } else if (caseData.status_id === null && article.status_id !== null) {
        updateData.status_id = null;
      }

      const res = await updateArticle(numericId, updateData);
      article = res.data;

      if (uploadedFiles.length > 0) {
        const uploadPromises = uploadedFiles.map(file => 
          uploadFileForCheck({
            file,
            article_id: numericId,
            metadata: {
              uploaded_by: article?.user_id,
            }
          })
        );

        await Promise.all(uploadPromises);
        successMessage = `Данные сохранены. Загружено файлов: ${uploadedFiles.length}`;
        
        uploadedFiles = [];
      } else {
        successMessage = 'Данные успешно сохранены';
      }

      caseData = {
        ...caseData,
        title: article.name ?? caseData.title,
        location: article.location ?? caseData.location,
        description: article.description ?? caseData.description,
        isSecret: article.secrecy_grade ?? caseData.isSecret,
        createdDate: article.created_at ? new Date(article.created_at).toISOString().slice(0, 10) : caseData.createdDate,
        type_document_id: article.type_document_id ?? caseData.type_document_id,
        status_id: article.status_id ?? caseData.status_id,
      };

      await loadArticle();

      setTimeout(() => {
        successMessage = null;
      }, 5000);

    } catch (e: any) {
      error = e?.message ?? 'Ошибка при сохранении данных';
      console.error('Ошибка сохранения:', e);
    } finally {
      saving = false;
    }
  }

  onMount(() => {
    loadListPeriods();
    loadTypesDocument();
    loadStatuses();
    initPdfJs();
  });

  $effect(() => {
    doc = $page.params.doc;
    loadArticle();
  });

  $effect(() => {
    console.log('doc:', doc);
    const articleId = parseInt(doc.replace('article-', ''), 10);
    console.log('parsed articleId:', articleId);
});

  function formatFileSize(size: number | undefined): string {
  if (!size || size <= 0) return '0 Б';
  const units = ['Б', 'КБ', 'МБ', 'ГБ'];
  let idx = 0;
  let s = size;
  while (s >= 1024 && idx < units.length - 1) {
    s /= 1024;
    idx++;
  }
  return `${s.toFixed(1)} ${units[idx]}`;
}

let approvedFiles = $derived(files.filter(f => f.status === 'approved'));

let numericArticleId = $derived(() => {
    const id = parseInt(doc.replace('article-', ''), 10);
    return isNaN(id) ? null : id;
});

let shouldHideEditingElements = $derived.by(() => {
  if (!caseData.status_id || !article) return false;
  
  const currentStatus = statuses.find(s => s.id === caseData.status_id) || article.status;
  
  if (!currentStatus) return false;
  
  return currentStatus.name === 'передан' || currentStatus.name === 'уничтожен';
});

let isDestroyed = $derived.by(() => {
  if (!caseData.status_id || !article) return false;
  
  const currentStatus = statuses.find(s => s.id === caseData.status_id) || article.status;
  
  if (!currentStatus) return false;
  
  return currentStatus.name === 'уничтожен';
});

</script>

<div class="w-full h-full grid grid-cols-[1fr_1fr] gap-6 p-6">
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900-50">Карточка дела</h1>
      <div class="flex space-x-2">
        <button 
          class="btn preset-filled-primary-500"
          onclick={handleSave}
          disabled={saving || loading || !article}
        >
          {saving ? 'Сохранение...' : 'Сохранить'}
        </button>
        <button class="btn preset-outline-secondary-500">
          Редактировать
        </button>
      </div>
    </div>

    {#if successMessage}
      <div class="p-4 bg-green-500/10 border border-green-500 rounded text-green-500 text-sm">
        {successMessage}
      </div>
    {/if}

    {#if error}
      <div class="p-4 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
        {error}
      </div>
    {/if}

    <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6 space-y-6">
      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <FileText class="inline w-4 h-4 mr-2" />
          Название дела
        </label>
        <input 
          type="text" 
          bind:value={caseData.title}
          class="input w-full"
          placeholder="Введите название дела"
        />
      </div>

      {#if caseData.type_document_id !== 2}
      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <MapPin class="inline w-4 h-4 mr-2" />
          Местоположение документов
        </label>
        <input 
          type="text" 
          bind:value={caseData.location}
          class="input w-full"
          placeholder="Укажите местоположение"
        />
      </div>
      {/if}

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <Shield class="inline w-4 h-4 mr-2" />
          Секретность
        </label>
        <div class="flex space-x-4">
          <label class="flex items-center">
            <input 
              type="radio" 
              bind:group={caseData.isSecret} 
              value={true}
              class="radio"
            />
            <span class="ml-2 text-sm text-surface-700-300">Секретно</span>
          </label>
          <label class="flex items-center">
            <input 
              type="radio" 
              bind:group={caseData.isSecret} 
              value={false}
              class="radio"
            />
            <span class="ml-2 text-sm text-surface-700-300">Не секретно</span>
          </label>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <Calendar class="inline w-4 h-4 mr-2" />
          Дата создания документа
        </label>
        <input 
          type="date" 
          bind:value={caseData.createdDate}
          class="input w-full"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <User class="inline w-4 h-4 mr-2" />
          Пользователь создавший документ
        </label>
        <input 
          disabled
          type="text" 
          bind:value={caseData.created_by.nickname}
          class="input w-full"
          placeholder="Пользователь"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <FileText class="inline w-4 h-4 mr-2" />
          Классификация документа
        </label>
        <select 
          bind:value={selectedListPeriodId}
          class="select w-full"
          onchange={() => calculateExpirationDate()}
        >
          <option value={null}>Выберите классификацию...</option>
          {#each listPeriods as period}
            <option value={period.id}>
              {period.name} ({period.retention_period} {period.retention_period === 1 ? 'год' : period.retention_period < 5 ? 'года' : 'лет'})
            </option>
          {/each}
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <Calendar class="inline w-4 h-4 mr-2" />
          Дата уничтожения документа
        </label>
        <input 
          type="date" 
          disabled
          bind:value={caseData.destructionDate}
          class="input w-full bg-surface-100-800"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <Shield class="inline w-4 h-4 mr-2" />
          Статус документа
        </label>
        <select bind:value={caseData.status_id} class="select w-full">
          <option value={null}>Выберите статус...</option>
          {#each statuses as status}
            <option value={status.id}>{status.name}</option>
          {/each}
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <FileText class="inline w-4 h-4 mr-2" />
          Тип документа
        </label>
        <select bind:value={caseData.type_document_id} class="select w-full">
          <option value={null}>Выберите тип документа...</option>
          {#each typesDocument as typeDoc}
            <option value={typeDoc.id}>{typeDoc.name}</option>
          {/each}
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          Описание дела
        </label>
        <textarea 
          bind:value={caseData.description}
          class="textarea w-full h-24"
          placeholder="Дополнительная информация о деле..."
        ></textarea>
      </div>

      {#if caseData.type_document_id !== 1 && !shouldHideEditingElements}
      <div>
        <label class="block text-sm font-medium text-surface-700-300 mb-2">
          <Upload class="inline w-4 h-4 mr-2" />
          Прикрепленные файлы
        </label>
      
        <FileUploader onFilesChange={handleFilesChange} />

        {#if files.length > 0}
        <div class="mt-4 border border-surface-200-800 rounded-lg overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-surface-100-800">
              <tr>
                <th class="px-4 py-2 text-left">Наименование файла</th>
                <th class="px-4 py-2 text-left">Размер</th>
                <th class="px-4 py-2 text-left">Статус</th>
                <th class="px-4 py-2 text-left">Действия</th>
              </tr>
            </thead>
            <tbody>
              {#each files as file}
                <tr class="border-t border-surface-200-800">
                  <td class="px-4 py-2">{file.filename}</td>
                  <td class="px-4 py-2">{formatFileSize(file.file_size)}</td>
                  <td class="px-4 py-2">
                    <BadgeComponent status={file.status} />
                  </td>
                  <td class="px-4 py-2">
                    <div class="flex space-x-2">
                      {#if !isDestroyed}
                        {#if file.mime_type === 'application/pdf'}
                          <button
                            onclick={() => handleViewPdf(file)}
                            class="text-primary-600 hover:text-primary-900 transition-colors"
                            title="Просмотр PDF"
                          >
                            <Eye class="w-4 h-4" />
                          </button>
                        {/if}
                        <button
                          onclick={() => handleDownloadFile(file)}
                          class="text-blue-600 hover:text-blue-900 transition-colors"
                          title="Скачать файл"
                        >
                          <Download class="w-4 h-4" />
                        </button>
                      {/if}
                    </div>
                  </td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
        {:else}
          <p>Файлы пока не прикреплены.</p>
        {/if}
      </div>
      {/if}

      {#if !shouldHideEditingElements}
      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <Drawer 
            articleId={parseInt(doc.replace('article-', ''), 10)} 
            approvedFiles={approvedFiles}
        />
      </div>
      {/if}

      {#if !isDestroyed}
      <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 p-6">
        <CaseContainer articleId={parseInt(doc.replace('article-', ''), 10) || null} />
      </div>
      {/if}
    </div>
  </div>

  <div class="space-y-4">
    <h2 class="text-xl font-semibold text-surface-900-50">Просмотр документа</h2>
    
    <div class="bg-surface-50-900 rounded-lg border border-surface-200-800 h-full">
      {#if isDestroyed}
        <div class="h-full flex items-center justify-center text-surface-500-400">
          <div class="text-center">
            <Archive class="w-16 h-16 mx-auto mb-4 opacity-50 text-red-500" />
            <p class="text-lg font-medium mb-2 text-red-600">Дело уничтожено</p>
            <p class="text-sm">Это дело было уничтожено и больше недоступно для редактирования</p>
          </div>
        </div>
      {:else if selectedPdfFile}
        <div class="h-full flex flex-col">
          <div class="p-4 border-b border-surface-200-800 flex items-center justify-between">
            <div>
              <h3 class="font-medium text-surface-700-300">{selectedPdfFile.filename}</h3>
              {#if totalPages > 0}
                <p class="text-sm text-surface-600-400">Страница {currentPage} из {totalPages}</p>
              {/if}
            </div>
            <div class="flex items-center space-x-2">
              {#if totalPages > 1}
                <button
                  onclick={() => goToPage(currentPage - 1)}
                  disabled={currentPage <= 1}
                  class="btn btn-sm preset-outline"
                >
                  Назад
                </button>
                <span class="text-sm text-surface-600-400">
                  {currentPage} / {totalPages}
                </span>
                <button
                  onclick={() => goToPage(currentPage + 1)}
                  disabled={currentPage >= totalPages}
                  class="btn btn-sm preset-outline"
                >
                  Вперед
                </button>
              {/if}
              <button
                onclick={() => { selectedPdfFile = null; pdfBlobUrl = null; pdfDocument = null; }}
                class="btn btn-sm preset-outline"
              >
                Закрыть
              </button>
            </div>
          </div>
          <div class="flex-1 p-4 overflow-auto">
            {#if pdfLoading}
              <div class="flex items-center justify-center h-full">
                <Loader2 class="w-8 h-8 animate-spin text-primary-500" />
                <span class="ml-2 text-surface-600-400">Загрузка PDF...</span>
              </div>
            {:else if pdfError}
              <div class="flex items-center justify-center h-full">
                <div class="text-center">
                  <p class="text-error-500 mb-2">{pdfError}</p>
                  <button
                    onclick={() => handleViewPdf(selectedPdfFile!)}
                    class="btn btn-sm preset-filled-primary-500"
                  >
                    Повторить
                  </button>
                </div>
              </div>
            {:else if pdfCanvas}
              <div class="flex justify-center">
                <canvas bind:this={pdfCanvas} class="border border-surface-200-800 rounded"></canvas>
              </div>
            {/if}
          </div>
        </div>
      {:else}
        <div class="h-full flex items-center justify-center text-surface-500-400">
          <div class="text-center">
            <FileText class="w-16 h-16 mx-auto mb-4 opacity-50" />
            <p class="text-lg font-medium mb-2">Выберите PDF файл для просмотра</p>
            <p class="text-sm">Нажмите кнопку "Просмотр" рядом с PDF файлом в списке</p>
          </div>
        </div>
      {/if}
    </div>
  </div>
</div>
