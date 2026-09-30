import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
    plugins: [vue()],
    base: './',
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './src')
        } ,
        dedupe: ['vue', 'vue-router', 'axios'] ,
    },
    build: {
        outDir: 'dist',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                main: path.resolve(__dirname, 'index.html'),
                'media-dialog': path.resolve(__dirname, 'media-dialog.html')
            },
            output: {
                entryFileNames: 'assets/js/[name].js',
                chunkFileNames: 'assets/js/[name].js',
                assetFileNames: 'assets/[ext]/[name].[ext]' ,
                manualChunks(id) {
                    if (id.includes('src/composables/')) {
                        return 'shared-composables'
                    }
                    if (id.includes('node_modules')) {
                        return 'vendor'
                    }
                }
            }
        }
    }
})