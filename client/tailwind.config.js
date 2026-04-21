/** @type {import('tailwindcss').Config} */
export default {
    content: [
      './src/**/*.{html,js,svelte,ts}',
      './node_modules/@skeletonlabs/skeleton/**/*.{html,js,svelte,ts}',
      './node_modules/@skeletonlabs/skeleton-svelte/**/*.{html,js,svelte,ts}'
    ],
    safelist: [
      'bg-sky-500/20',
      'bg-indigo-500/20',
      'bg-emerald-500/20',
      'text-indigo-500',
      'text-emerald-500',
      'text-sky-500',
    ],
    theme: {
      extend: {},
    },
    plugins: [],
    darkMode: 'class'
  }