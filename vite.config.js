import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

// export default defineConfig({
//     build: {
//         // generate manifest.json in outDir
//         // manifest: true,
//         rollupOptions: {
//             // overwrite default .html entry
//             input: "/resources/js/app.js",
//         },
//     },
//     server: {
//         hmr: {
//             host: "localhost",
//         },
//     },
//     plugins: [
//         laravel({
//             input: "resources/js/app.js",
//             refresh: true,
//         }),
//         vue({
//             template: {
//                 transformAssetUrls: {
//                     base: null,
//                     includeAbsolute: false,
//                 },
//             },
//         }),
//     ],
// });

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.js"], // puedes agregar más si usas CSS o múltiples entradas
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        hmr: {
            host: "localhost",
        },
    },
});
