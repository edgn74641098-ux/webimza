const PROD_API_BASE = "https://webimza.duckdns.org/api";
const DEFAULT_ADDIN_VERSION = "1.1.0";

function resolveApiBaseUrl(): string {
  if (typeof window === "undefined") return PROD_API_BASE;

  const { origin, hostname } = window.location;
  const sameOriginAllowed = hostname === "webimza.duckdns.org" || hostname === "localhost" || hostname === "127.0.0.1";

  return sameOriginAllowed ? `${origin}/api` : PROD_API_BASE;
}

export const config = {
  apiBaseUrl: resolveApiBaseUrl(),
  addinVersion: resolveAddinVersion(),
};

function resolveAddinVersion(): string {
  if (typeof window === "undefined") return DEFAULT_ADDIN_VERSION;

  const value = new URLSearchParams(window.location.search).get("v");
  return value && value.trim() ? value.trim() : DEFAULT_ADDIN_VERSION;
}
