<script lang="ts">
	import { createTypeSection, type StoreTypeSectionRequest } from '$lib/api/typeSections';

	let loading = $state(false);
	let error = $state<string | null>(null);

	let formData = $state<StoreTypeSectionRequest>({
		name: '',
	});

	interface Props {
		onSuccess?: () => void;
	}

	let { onSuccess }: Props = $props();

	async function handleSubmit() {
		error = null;
		loading = true;

		try {
			await createTypeSection(formData);
			if (onSuccess) {
				onSuccess();
			}
		} catch (err) {
			error = err instanceof Error ? err.message : 'Ошибка при создании типа раздела';
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
			maxlength="40"
			class="input w-full"
			placeholder="Введите название типа раздела"
		/>
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