<script lang="ts">
	import { createSection, type StoreSectionRequest } from '$lib/api/sections';
	import { getTypeSections, type TypeSection } from '$lib/api/typeSections';
	import { onMount } from 'svelte';

	let loading = $state(false);
	let error = $state<string | null>(null);
	let typeSections = $state<TypeSection[]>([]);

	let formData = $state<StoreSectionRequest>({
		name: '',
		user_id: 0,
		type_id: 0,
	});

	interface Props {
		userId: number;
		onSuccess?: () => void;
	}

	let { userId, onSuccess }: Props = $props();

	$effect(() => {
		formData.user_id = userId;
	});

	async function loadTypeSections() {
		try {
			const response = await getTypeSections();
			typeSections = response.data;
		} catch (err) {
			console.error('Ошибка загрузки типов разделов:', err);
		}
	}

	onMount(() => {
		loadTypeSections();
	});

	async function handleSubmit() {
		error = null;
		loading = true;

		try {
			await createSection(formData);
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании раздела';
		} finally {
			loading = false;
		}
	}
</script>

<form on:submit|preventDefault={handleSubmit} class="space-y-4">
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
			placeholder="Введите название раздела"
		/>
	</div>

	<div>
		<label for="type_id" class="block text-sm font-medium text-surface-700-300 mb-2">
			Тип раздела <span class="text-red-500">*</span>
		</label>
		<select id="type_id" bind:value={formData.type_id} required class="select w-full">
			<option value={0}>Выберите тип...</option>
			{#each typeSections as typeSection}
				<option value={typeSection.id}>{typeSection.name}</option>
			{/each}
		</select>
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