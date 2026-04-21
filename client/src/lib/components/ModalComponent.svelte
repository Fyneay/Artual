<script lang="ts">
	import { XIcon } from '@lucide/svelte';
	import { Dialog, Portal } from '@skeletonlabs/skeleton-svelte';

	interface Props {
		title: string;
		description?: string;
		open?: boolean;
		onClose?: () => void;
		children?: any;
	}

	let {
		title,
		description,
		open = $bindable(false),
		onClose,
		children
	}: Props = $props();

	function handleClose() {
		open = false;
		if (onClose) {
			onClose();
		}
	}

	const animation =
		'transition transition-discrete opacity-0 translate-y-[100px] starting:data-[state=open]:opacity-0 starting:data-[state=open]:translate-y-[100px] data-[state=open]:opacity-100 data-[state=open]:translate-y-0';
</script>

<Dialog open={open} onOpenChange={(e)=>open=e.open}>
	<Portal>
		<Dialog.Backdrop class="fixed inset-0 z-50 bg-surface-50-950/50" />
		<Dialog.Positioner class="fixed inset-0 z-50 flex justify-center items-center p-4">
			<Dialog.Content class="card bg-surface-100-900 w-full max-w-xl p-4 space-y-4 shadow-xl {animation}">
				<header class="flex justify-between items-center">
					<Dialog.Title class="text-lg font-bold">{title}</Dialog.Title>
					<Dialog.CloseTrigger class="btn-icon hover:preset-tonal" on:click={handleClose}>
						<XIcon class="size-4" />
					</Dialog.CloseTrigger>
				</header>

				{#if description}
					<Dialog.Description>
						{description}
					</Dialog.Description>
				{/if}

				{#if children}
					{@render children()}
				{/if}
			</Dialog.Content>
		</Dialog.Positioner>
	</Portal>
</Dialog>