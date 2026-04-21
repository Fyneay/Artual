<script lang="ts">
	type BadgeStatus = 'pending' | 'approved' | 'rejected';

	interface Props {
		status: BadgeStatus | string;
	}

	let { status }: Props = $props();

	const statusConfig: Record<
		BadgeStatus,
		{ text: string; classes: string }
	> = {
		pending: {
			text: 'Отправлено',
			classes: 'bg-yellow-500/20 text-yellow-600 dark:text-yellow-400',
		},
		approved: {
			text: 'Проверено',
			classes: 'bg-green-500/20 text-green-600 dark:text-green-400',
		},
		rejected: {
			text: 'Заражен',
			classes: 'bg-red-500/20 text-red-600 dark:text-red-400',
		},
	};

	const config = $derived(
		statusConfig[status as BadgeStatus] || {
			text: status,
			classes: 'bg-surface-200-700 text-surface-600-400',
		}
	);
</script>

<span
	class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {config.classes}"
>
	{config.text}
</span>
