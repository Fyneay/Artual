<script lang="ts">
	import { MonitorIcon, WifiIcon, Rss } from '@lucide/svelte';
	import MultiTabComponent from '$lib/components/MultiTabComponent.svelte';
    import LightSwitchComponent from '$lib/components/LightSwitchComponent.svelte';
	
    
	let darkTheme = $state(false);
	let serverUrl = $state('https://localhost');
    let authenticationType = $state('jwt');
    let ldapServer = $state('ldap://localhost:389');
    let redisUrl = $state('redis:6379');
	let dbUrl = $state('postgres:5432');
    let brokerMessage = $state('rabbitmq:5672');
    let debugMode = $state(false);

	const tabs = [
		{ id: 'interface', label: 'Интерфейс' },
		{ id: 'admin', label: 'Администрирование' },
	];

	function saveSettings() {
		console.log('Сохраняем:', { darkTheme, serverUrl, dbUrl });
	}

</script>

<div class="p-8 max-w-4xl mx-auto">
	<MultiTabComponent {tabs} defaultValue="interface">
		{#snippet children(tabId)}
			{#if tabId === 'interface'}
				<div class="card border-2 border-surface-300-700 rounded-xl">
					<div class="bg-surface-50-950 px-6 py-4 border-b-2 border-surface-300-700">
						<div class="flex items-center gap-2">
							<MonitorIcon class="size-5" />
							<h2 class="text-xl font-semibold">Интерфейс</h2>
						</div>
					</div>
					<div class="p-6 space-y-4">
						<div>
							<label class="block mb-2 font-medium">Тема</label>
                                <LightSwitchComponent />
						</div>
					</div>
				</div>

			{:else if tabId === 'admin'}
				<div class="card mb-2 border-2 border-surface-300-700 rounded-xl">
					<div class="bg-surface-50-950 px-6 py-4 border-b-2 border-surface-300-700">
						<div class="flex items-center gap-2">
							<WifiIcon class="size-5" />
							<h2 class="text-xl font-semibold">Подключение</h2>
						</div>
					</div>
					<div class="p-6 space-y-4">
						<div>
							<label class="block mb-2 font-medium">Сервер</label>
							<input 
								type="text" 
								class="input preset-outlined w-full" 
								bind:value={serverUrl}
							/>
						</div>
                        <div>
							<label class="block mb-2 font-medium">Redis</label>
							<input 
								type="text" 
								class="input preset-outlined w-full" 
								bind:value={redisUrl}
							/>
						</div>
                        <div>
							<label class="block mb-2 font-medium">Брокер сообщений</label>
							<input 
								type="text" 
								class="input preset-outlined w-full" 
								bind:value={brokerMessage}
							/>
						</div>
						<div>
							<label class="block mb-2 font-medium">База данных</label>
							<input 
								type="text" 
								class="input preset-outlined w-full" 
								bind:value={dbUrl}
							/>
						</div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                class="checkbox" 
                                bind:checked={debugMode}
                            />
                            <span>Отладка</span>
                        </label>
						<button class="btn preset-outlined">Проверить подключение</button>
					</div>
				</div>

                <div class="card mb-2 border-2 border-surface-300-700 rounded-xl">
                    <div class="bg-surface-50-950 px-6 py-4 border-b-2 border-surface-300-700">
                        <div class="flex items-center gap-2">
                            <Rss class="size-5" />
                            <h2 class="text-xl font-semibold">Аутентификация</h2>
                        </div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block mb-2 font-medium">Тип аутентификации</label>
                            <select class="select preset-outlined w-full" bind:value={authenticationType}>
                                <option value="ldap">LDAP</option>
                                <option value="jwt">JWT</option>
                            </select>
                        </div>
                        {#if authenticationType === 'ldap'}
                        <div>
                            <label class="block mb-2 font-medium">LDAP сервер</label>
                            <input 
                                type="text" 
                                class="input preset-outlined w-full" 
                                bind:value={ldapServer}
                            />
                        </div>
                        {/if}
                    </div>
                </div>
			{/if}
		{/snippet}
	</MultiTabComponent>

	<div class="flex gap-4 mt-6">
		<button class="btn preset-filled-primary" onclick={saveSettings}>
			Сохранить
		</button>
		<button class="btn preset-outlined">Отменить</button>
	</div>

    {#if debugMode}
        <div class="mt-6 p-4 bg-surface-100-900 rounded border border-surface-300-700">
            <h4 class="font-bold mb-2">Текущее состояние:</h4>
            <pre class="text-sm">{JSON.stringify({ darkTheme, serverUrl, redisUrl, brokerMessage, dbUrl, authenticationType, ldapServer }, null, 2)}</pre>
        </div>
    {/if}
</div>