import { defineConfig } from "vite";
import devCerts from "office-addin-dev-certs";

export default defineConfig(async () => {
  const httpsOptions = await devCerts.getHttpsServerOptions();

  return {
    base: "./",
    server: {
      host: "localhost",
      port: 5173,
      strictPort: true,
      https: httpsOptions,
      proxy: {
        "/api": {
          target: "http://127.0.0.1:8000",
          changeOrigin: true,
        },
      },
    },
    build: {
      modulePreload: {
        polyfill: false,
      },
      rollupOptions: {
        input: {
          taskpane: "index.html",
          autorun: "autorun.html",
        },
      },
    },
  };
});
