<script lang="ts">
	import { createArticle, type StoreArticleRequest } from '$lib/api/articles';
	import { getSections, type Section } from '$lib/api/sections';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let sections = $state<Section[]>([]);

	let formData = $state<Partial<StoreArticleRequest>>({
		name: '',
		user_id: 0,
		section_id: 0,
		secrecy_grade: false,
	});

	let selectedFile: File | null = $state(null);

	interface Props {
		userId: number;
		sectionId?: number;
		onSuccess?: () => void;
	}

	let { userId, sectionId, onSuccess }: Props = $props();

	$effect(() => {
		formData.user_id = userId;
		if (sectionId) {
			formData.section_id = sectionId;
		}
	});

	async function loadSections() {
		try {
			const response = await getSections();
			sections = response.data;
		} catch (err) {
			console.error('Ошибка загрузки разделов:', err);
		}
	}

	onMount(() => {
		loadSections();
	});

	async function handleSubmit() {
		// if (!selectedFile) {
		// 	error = 'Выберите файл';
		// 	return;
		// }

		error = null;
		loading = true;

		try {
			await createArticle({
				name: formData.name!,
				user_id: formData.user_id!,
				section_id: formData.section_id!,
				secrecy_grade: formData.secrecy_grade,
			});
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании статьи';
		} finally {
			loading = false;
		}
	}
</script>

<form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-4">
	<div>
		<label for="name" class="block text-sm font-medium text-surface-700-300 mb-2">
			Название <span class="text-red-500">*</span>
		</label>
		<input
			type="text"
			id="name"
			bind:value={formData.name}
			required
			class="input w-full"
			placeholder="Введите название статьи"
		/>
	</div>

	<div>
		<label for="section_id" class="block text-sm font-medium text-surface-700-300 mb-2">
			Раздел <span class="text-red-500">*</span>
		</label>
		<select id="section_id" bind:value={formData.section_id} required class="select w-full">
			<option value={0}>Выберите раздел...</option>
			{#each sections as section}
				<option value={section.id}>{section.name}</option>
			{/each}
		</select>
	</div>

	<div>
		<label class="flex items-center">
			<input
				type="checkbox"
				bind:checked={formData.secrecy_grade}
				class="checkbox"
			/>
			<span class="ml-2 text-sm">Секретно</span>
		</label>
	</div>


	{#if error}
		<div class="p-3 bg-error-500/10 border border-error-500 rounded text-error-500 text-sm">
			{error}
		</div>
	{/if}

	<footer class="flex justify-end gap-2 pt-4">
		<button type="submit" class="btn preset-filled" disabled={loading}>
			{loading ? 'Создание...' : 'Создать'}
		</button>
	</footer>
</form>