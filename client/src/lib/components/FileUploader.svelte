<script lang="ts">
	import { FileIcon } from '@lucide/svelte';
	import { FileUpload } from '@skeletonlabs/skeleton-svelte';
	import { untrack } from 'svelte';

	interface Props {
		onFilesChange?: (files: File[]) => void;
	}
	
	let { onFilesChange }: Props = $props();
	let fileUploadContext: any = $state(null);
	let previousFilesLength = $state(0);
	
	$effect(() => {
		if (fileUploadContext && onFilesChange) {
			const currentFiles = Array.from(fileUploadContext.acceptedFiles);
			const currentLength = currentFiles.length;
			
			if (currentLength !== previousFilesLength) {
				previousFilesLength = currentLength;
				onFilesChange(currentFiles);
			}
		}
	});
	
	function setContext(context: any) {
		untrack(() => {
			fileUploadContext = context;
		});
	}
</script>

<FileUpload maxFiles={10} accept={['.pdf', '.doc', '.docx', '.jpg', '.jpeg', '.png', '.rtf', '.csv']}>
	<FileUpload.Label>Загрузите файлы</FileUpload.Label>
	<FileUpload.Dropzone>
		<FileIcon class="size-10" />
		<span>Перетащите файлы сюда или нажмите для выбора.</span>
		<FileUpload.Trigger>Выбрать файлы</FileUpload.Trigger>
		<FileUpload.HiddenInput />
	</FileUpload.Dropzone>
	<FileUpload.ItemGroup>
		<FileUpload.Context>
			{#snippet children(fileUpload)}
				{setContext(fileUpload())}
				{#each fileUpload().acceptedFiles as file (file.name)}
					<FileUpload.Item {file}>
						<FileUpload.ItemName>{file.name}</FileUpload.ItemName>
						<FileUpload.ItemSizeText>{file.size} байт</FileUpload.ItemSizeText>
						<FileUpload.ItemDeleteTrigger />
					</FileUpload.Item>
				{/each}
			{/snippet}
		</FileUpload.Context>
	</FileUpload.ItemGroup>
</FileUpload>
