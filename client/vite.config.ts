import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
	plugins: [tailwindcss(),sveltekit()],
	base: '/app/',
	server: {
		host: 'client',
		hmr: { //настройка реверсивного проксирования
			clientPort: 443,
			protocol: 'wss',
		//	overlay: false
		}
	},
	// ssr: {
	// 	noExternal: [],
	// },
	// optimizeDeps: {
	// 	exclude: ['crypto-pro-actual-cades-plugin'],
	// },
});
