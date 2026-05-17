const PROD_API_BASE = "https://webimza.duckdns.org/api";

function resolveApiBaseUrl(): string {
  if (typeof window === "undefined") return PROD_API_BASE;

  const { origin, hostname } = window.location;
  const sameOriginAllowed = hostname === "webimza.duckdns.org" || hostname === "localhost" || hostname === "127.0.0.1";

  return sameOriginAllowed ? `${origin}/api` : PROD_API_BASE;
}

export const config = {
  apiBaseUrl: resolveApiBaseUrl(),
  addinVersion: "1.1.0",
};
