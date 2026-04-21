<script lang="ts">

  import { FileIcon, FolderIcon, Plus, ChevronLeft, ChevronRight } from '@lucide/svelte';
  import { TreeView, createTreeViewCollection } from '@skeletonlabs/skeleton-svelte';
  import { goto } from '$app/navigation';
  import { withBase } from '$lib/utils/paths';
  import {getSectionsTree} from '$lib/api/sections';
  import { onMount } from 'svelte';
  import type { TreeSectionNode } from '$lib/api/sections';
  import ModalComponent from '$lib/components/ModalComponent.svelte';
  import TypeSectionForm from '$lib/components/forms/TypeSectionForm.svelte';
  import SectionForm from './forms/SectionForm.svelte';
  import { getCurrentUser } from '$lib/api/client';

  let typeSectionModalOpen = $state(false);
  let sectionModalOpen = $state(false);
  let collapsed = $state(false);
  let currentUserId = $state<number | null>(null);

  let { title = "Номенклатура дел", href = '/archive' } = $props();
  let loading = false;
  let error: string | null = null;



  let treeData: Node[] = $state([]);

  interface Node {
    id: string;
    name: string;
    type?: 'type' | 'section';
    children?: Node[];
  }

  const collection = createTreeViewCollection<Node>({
    nodeToValue: (node) => node.id,
    nodeToString: (node) => node.name,
    rootNode: {
      id: 'root',
      name: '',
      get children() {
        return treeData;
      },
    },
  });

  function transformApiDataToNodes(apiNodes: TreeSectionNode[]): Node[] {
    return apiNodes.map((apiNode) => {
      const node: Node = {
        id: apiNode.type === 'type' ? `type-${apiNode.id}` : `section-${apiNode.id}`,
        name: apiNode.name,
        type: apiNode.type,
      };

      if (apiNode.children && apiNode.children.length > 0) {
        node.children = transformApiDataToNodes(apiNode.children);
      }

      return node;
    });
  }

  async function loadTreeData() {
    loading = true;
    error = null;
  
    try {
      const response = await getSectionsTree();
      const treeNodes = transformApiDataToNodes(response.data);
      treeData = treeNodes;
      
    } catch (err) {
      error = err instanceof Error ? err.message : 'Ошибка при загрузке данных';
      console.error('Ошибка загрузки дерева:', err);
    } finally {
      loading = false;
    }
  }
  
  onMount(() => {
    loadTreeData();
    const user = getCurrentUser();
    if (user) {
      currentUserId = user.id;
    }
  });

  function handleNodeClick(node: Node, event: MouseEvent) {
    event.preventDefault();
    event.stopPropagation();
    
    if (node.type === 'section') {
      const sectionId = node.id.replace('section-', '');
      goto(withBase(`${href}/${sectionId}`));
    }
  }

  function toggleCollapse() {
    collapsed = !collapsed;
  }
</script>

<div class="h-full bg-surface-100-800 border-r border-surface-200-800 flex transition-all duration-300 {collapsed ? 'w-12' : 'w-80'}">
  {#if collapsed}
    <div class="flex flex-col items-center p-2">
      <button 
        on:click={toggleCollapse}
        class="btn btn-icon btn-icon-sm preset-tonal"
        title="Развернуть дерево"
      >
        <ChevronRight class="size-4" />
      </button>
    </div>
  {:else}
    <div class="w-full p-4 overflow-auto">
      <div class="mb-4">
        <div class="flex items-center gap-2 mb-3">
          <h2 class="text-lg font-semibold text-surface-900-50 flex-1">{title}</h2>
          <button 
            on:click={() => typeSectionModalOpen = true}
            class="btn btn-icon btn-icon-sm preset-filled-primary-500"
            title="Добавить тип раздела"
          >
            <Plus class="size-4" />
          </button>
          <button 
            on:click={toggleCollapse}
            class="btn btn-icon btn-icon-sm preset-tonal"
            title="Свернуть дерево"
          >
            <ChevronLeft class="size-4" />
          </button>
          <ModalComponent bind:open={typeSectionModalOpen} title="Создать тип раздела">
            {#snippet children()}
              <TypeSectionForm onSuccess={() => typeSectionModalOpen = false} />
            {/snippet}
          </ModalComponent>
        </div>
      </div>
  
      {#if loading}
      <div class="flex items-center justify-center p-4">
        <span class="text-surface-600-300">Загрузка...</span>
      </div>
    {:else if error}
      <div class="p-4 bg-error-500/10 border border-error-500 rounded">
        <p class="text-error-500 text-sm">{error}</p>
        <button 
          on:click={loadTreeData}
          class="btn btn-sm mt-2 preset-filled-primary-500"
        >
          Повторить
        </button>
      </div>
    {:else}

      <div class="space-y-2">
        <TreeView {collection}>
          <TreeView.Tree>
            {#each collection.rootNode.children || [] as node, index (node)}
              {@render treeNode(node, [index])}
            {/each}
          </TreeView.Tree>
        </TreeView>
        
        {#snippet treeNode(node: Node, indexPath: number[])}
          <TreeView.NodeProvider value={{ node, indexPath }}>
            {#if node.children && node.children.length > 0}
                <TreeView.Branch>
                  <TreeView.BranchControl>
                    <TreeView.BranchIndicator />
                    <TreeView.BranchText>
                      <FolderIcon class="size-4" />
                      {node.name}
                    </TreeView.BranchText>
                  </TreeView.BranchControl>
                  <TreeView.BranchContent>
                    <TreeView.BranchIndentGuide />
                    {#each node.children as childNode, childIndex (childNode)}
                      {@render treeNode(childNode, [...indexPath, childIndex])}
                    {/each}
                  </TreeView.BranchContent>
                </TreeView.Branch>
              {:else}
                <TreeView.Item>
                  <div 
                    on:click={(event) => handleNodeClick(node, event)} 
                    class="cursor-pointer hover:bg-surface-200-700 p-1 rounded flex items-center gap-2"
                  >
                    <FileIcon class="size-4" />
                    {node.name}
                  </div>
                </TreeView.Item>
              {/if}
          </TreeView.NodeProvider>
        {/snippet}
      </div>
    {/if}

      <div class="space-y-4">
        <div class="flex gap-4 justify-center mt-4">
          <button on:click={() => sectionModalOpen = true} class="btn preset-filled-primary-500">
            Добавить раздел
          </button>
        </div>
        <ModalComponent bind:open={sectionModalOpen} title="Создать раздел">
          {#snippet children()}
            {#if currentUserId}
              <SectionForm userId={currentUserId} onSuccess={() => sectionModalOpen = false} />
            {:else}
              <div class="p-4 text-error-500">Пользователь не найден. Пожалуйста, войдите в систему.</div>
            {/if}
          {/snippet}
        </ModalComponent>
      </div>
    </div>
  {/if}
</div>

<style>
  .tree-node {
    transition: all 0.2s ease;
  }
  
  .tree-node:hover {
    background-color: var(--color-surface-200-700);
    border-radius: 4px;
  }
</style>
