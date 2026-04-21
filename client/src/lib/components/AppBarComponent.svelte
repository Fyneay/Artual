<script lang="ts">
    import {
      BookIcon,
      HouseIcon,
      SettingsIcon,
      SkullIcon,
      BookOpen,
      Archive,
      BookUp,
      BookX,
    } from '@lucide/svelte';
    import { Navigation } from '@skeletonlabs/skeleton-svelte';
    import { withBase } from '$lib/utils/paths';

    let { children } = $props();
  
    const linksSidebar = {
      entertainment: [
        { label: 'Допуски', href: '/access', icon: BookIcon },
        { label: 'Прием дел', href: '/exchange', icon: BookUp },
        { label: 'Уничтожение дел', href: '/destruction', icon: BookX },
      ],
      recreation: [
        { label: 'Архив', href: '/archive', icon: Archive },
      ],
      system: [
        { label: 'Ссылки-регистрации', href: '/invites', icon: BookIcon}
      ]
    };
  
    let anchorSidebar = 'btn hover:preset-tonal justify-start px-2 w-full';
  </script>
  
  <div class="w-full h-screen grid grid-cols-[auto_1fr] items-stretch border border-surface-200-800">
    <Navigation layout="sidebar" class="grid grid-rows-[auto_1fr_auto] gap-4">
      <Navigation.Header>
        <a href={withBase('/')} class="btn-icon btn-icon-lg preset-filled-primary-500">
          <BookOpen class="size-6" />
        </a>
      </Navigation.Header>
      <Navigation.Content>
        <Navigation.Group>
          <Navigation.Menu>
            <a href={withBase('/')} class={anchorSidebar}>
              <HouseIcon class="size-4" />
              <span>Главная</span>
            </a>
          </Navigation.Menu>
        </Navigation.Group>
        {#each Object.entries(linksSidebar) as [category, links]}
          <Navigation.Group>
            <Navigation.Label class="capitalize pl-2">{category}</Navigation.Label>
            <Navigation.Menu>
              {#each links as link (link)}
                {@const Icon = link.icon}
                <a href={withBase(link.href)} class={anchorSidebar} title={link.label} aria-label={link.label}>
                  <Icon class="size-4" />
                  <span>{link.label}</span>
                </a>
              {/each}
            </Navigation.Menu>
          </Navigation.Group>
        {/each}
      </Navigation.Content>
      <Navigation.Footer>
        <a href={withBase('/settings')} class={anchorSidebar} title="Settings" aria-label="Settings">
          <SettingsIcon class="size-4" />
          <span>Настройки</span>
        </a>
      </Navigation.Footer>
    </Navigation>

    <main class="overflow-auto">
      {@render children()}
    </main>
  </div>
