<script lang="ts">
  import { Upload, X, FileText, Image, File } from '@lucide/svelte';

  interface Props {
    files: File[];
    onFilesChange: (files: File[]) => void;
    maxFiles?: number;
    acceptedTypes?: string;
  }

  let { files, onFilesChange, maxFiles = 10, acceptedTypes = "*" }: Props = $props();

  let dragOver = false;

  function handleFileSelect(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files) {
      const newFiles = Array.from(target.files);
      const updatedFiles = [...files, ...newFiles].slice(0, maxFiles);
      onFilesChange(updatedFiles);
    }
  }

  function handleDrop(event: DragEvent) {
    event.preventDefault();
    dragOver = false;
    
    if (event.dataTransfer?.files) {
      const newFiles = Array.from(event.dataTransfer.files);
      const updatedFiles = [...files, ...newFiles].slice(0, maxFiles);
      onFilesChange(updatedFiles);
    }
  }

  function handleDragOver(event: DragEvent) {
    event.preventDefault();
    dragOver = true;
  }

  function handleDragLeave() {
    dragOver = false;
  }

  function removeFile(index: number) {
    const updatedFiles = files.filter((_, i) => i !== index);
    onFilesChange(updatedFiles);
  }

  function formatFileSize(bytes: number): string {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }

  function getFileIcon(type: string) {
    if (type.startsWith('image/')) return Image;
    if (type === 'application/pdf') return FileText;
    return File;
  }
</script>

<div class="space-y-4">
  <div
    class="border-2 border-dashed rounded-lg p-6 transition-colors {dragOver ? 'border-primary-500 bg-primary-50' : 'border-surface-300-700'}"
    on:drop={handleDrop}
    on:dragover={handleDragOver}
    on:dragleave={handleDragLeave}
  >
    <input 
      type="file" 
      multiple 
      {acceptedTypes}
      on:change={handleFileSelect}
      class="hidden" 
      id="file-upload-{Math.random()}"
    />
    <label for="file-upload-{Math.random()}" class="cursor-pointer flex flex-col items-center">
      <Upload class="w-8 h-8 text-surface-400 mb-2" />
      <span class="text-sm text-surface-600-400 mb-1">
        {dragOver ? 'Отпустите файлы здесь' : 'Перетащите файлы сюда или нажмите для выбора'}
      </span>
      <span class="text-xs text-surface-500-500">
        Максимум {maxFiles} файлов
      </span>
    </label>
  </div>

  {#if files.length > 0}
    <div class="space-y-2">
      <h4 class="text-sm font-medium text-surface-700-300">
        Загруженные файлы ({files.length})
      </h4>
      {#each files as file, index (file.name + index)}
        {@const Icon = getFileIcon(file.type)}
        <div class="flex items-center justify-between p-3 bg-surface-100-800 rounded-lg">
          <div class="flex items-center space-x-3">
            <Icon class="w-4 h-4 text-surface-500-400" />
            <div>
              <p class="text-sm font-medium text-surface-700-300">{file.name}</p>
              <p class="text-xs text-surface-500-400">{formatFileSize(file.size)}</p>
            </div>
          </div>
          <button 
            on:click={() => removeFile(index)}
            class="text-red-500 hover:text-red-700 transition-colors"
            title="Удалить файл"
          >
            <X class="w-4 h-4" />
          </button>
        </div>
      {/each}
    </div>
  {/if}
</div>
