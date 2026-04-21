<script lang="ts">
	import '../app.css';
	import favicon from '$lib/assets/favicon.svg';
	import AppBarComponent from '$lib/components/AppBarComponent.svelte';
	import { page } from '$app/stores';
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { getAuthToken, getCurrentUser } from '$lib/api/client';
	import { withBase } from '$lib/utils/paths';

	//isAuthPage требуется для устранения компонента в auth страницах
	let isAuthPage = $derived($page.url.pathname.startsWith('/auth'));
	let { children } = $props();

	// Проверка аутентификации
	onMount(() => {
		const token = getAuthToken();
		const user = getCurrentUser();
		const isAuthenticated = token !== null && user !== null;

		// Если пользователь не аутентифицирован и не на странице авторизации, перенаправляем на login
		if (!isAuthenticated && !isAuthPage) {
			goto(withBase('/auth/login'));
		}
	});

	// Реактивная проверка при изменении страницы
	$effect(() => {
		const token = getAuthToken();
		const user = getCurrentUser();
		const isAuthenticated = token !== null && user !== null;

		// Если пользователь не аутентифицирован и не на странице авторизации, перенаправляем на login
		if (!isAuthenticated && !isAuthPage) {
			goto(withBase('/auth/login'));
		}
	});

</script>

<svelte:head>
	<link rel="icon" href={favicon} />
</svelte:head>

{#if isAuthPage}
	{@render children()}
{:else}
	<AppBarComponent>
		{@render children()}
	</AppBarComponent>
{/if}

