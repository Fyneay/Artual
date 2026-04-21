<script lang="ts">
  import TreeBlockComponent from '$lib/components/TreeBlockComponent.svelte';

  let { children } = $props();
</script>

<div class="w-full h-full grid grid-cols-[auto_1fr] items-stretch">
  <TreeBlockComponent />

  <div class="overflow-auto">
    {@render children()}
  </div>
</div>
