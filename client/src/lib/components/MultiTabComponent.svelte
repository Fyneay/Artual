<script lang="ts">
	import { Tabs } from '@skeletonlabs/skeleton-svelte';
	import type { Snippet } from 'svelte';

	interface Tab {
		id: string;
		label: string;
		disabled?: boolean;
	}

	interface Props {
		tabs: Tab[];
		defaultValue?: string;
		class?: string;
		children: Snippet<[string]>;
	}

	let { tabs, defaultValue, class: className, children }: Props = $props();
	const activeTab = defaultValue || tabs[0]?.id;
</script>

<Tabs defaultValue={activeTab} class={className}>
	<Tabs.List>
		{#each tabs as tab}
			<Tabs.Trigger value={tab.id} disabled={tab.disabled}>
				{tab.label}
			</Tabs.Trigger>
		{/each}
	</Tabs.List>
	
	{#each tabs as tab}
		<Tabs.Content value={tab.id}>
			{@render children(tab.id)}
		</Tabs.Content>
	{/each}
</Tabs>