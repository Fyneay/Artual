<script lang="ts">

    interface Props {
        name : string;
        icon : any;
        count : string;
        size : string
        color : string
    }

    let {name, count, icon, size, color } : Props = $props()

    let text = $derived(name.split(' '))


</script>

<div class="card p-4 shadow-md">
    <div class="flex flex-row-reverse">
        <div class="px-2 pb-4 rounded-full bg-{color}/20">
            <svelte:component this={icon} size={size} class="mt-4 text-{color}"/>
        </div>
        <div class="flex flex-col">
            <p class="px-4 pb-4 text-lg opacity-75">
                {#each text as word}
                    <p>{word}</p>
                {/each}
            </p>
            <p class="font-bold text-center text-lg">{count}</p>
        </div>
    </div>
</div>