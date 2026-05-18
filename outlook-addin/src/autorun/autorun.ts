declare const Office: any;

type SignatureCache = {
  deviceId: string;
  lastCheckedAt?: string;
  nextCheckAt?: string;
  cacheSeconds?: number;
  lastSignatureVersion?: string;
  lastSignatureHash?: string;
  lastSignatureHtml?: string;
  lastUserEmail?: string;
  lastSuccessfulApplyAt?: string;
};

const DEFAULT_ADDIN_VERSION = "1.1.0";
const PROD_API_BASE = "https://webimza.duckdns.org/api";

function resolveAddinVersion(): string {
  const value = new URLSearchParams(globalThis.location?.search ?? "").get("v");
  return value && value.trim() ? value.trim() : DEFAULT_ADDIN_VERSION;
}

function resolveApiBaseUrl(): string {
  const location = globalThis.location;
  if (!location) return PROD_API_BASE;
  const sameOriginAllowed = ["webimza.duckdns.org", "localhost", "127.0.0.1"].includes(location.hostname);
  return sameOriginAllowed ? `${location.origin}/api` : PROD_API_BASE;
}

const config = {
  addinVersion: resolveAddinVersion(),
  apiBaseUrl: resolveApiBaseUrl(),
};

function getMailbox() { return Office.context?.mailbox; }
function getProfile(): any { return getMailbox()?.userProfile ?? {}; }
function getItemBody() { return Office.context?.mailbox?.item?.body; }
function getEmail(): string {
  const email = String(getProfile()?.emailAddress ?? "").trim();
  if (!email) throw new Error("Mailbox context unavailable");
  return email;
}
function getHostInfo() {
  return {
    host: Office.context?.diagnostics?.hostName ?? "Outlook",
    platform: Office.context?.diagnostics?.platform ?? "unknown",
    officeVersion: Office.context?.diagnostics?.version ?? "unknown",
  };
}
function getClientType(): string {
  const host = String(Office.context?.diagnostics?.hostName ?? "").toLowerCase();
  if (host.includes("outlookwebapp") || host.includes("web")) return "outlook_web";
  if (host.includes("new")) return "new_outlook";
  if (host.includes("outlook")) return "classic_outlook";
  return "unknown";
}
function cacheKey(email: string): string { return `trinox_signature_cache_${email}`; }
function stableHash(input: string): string {
  let hash = 2166136261;
  for (let i = 0; i < input.length; i += 1) {
    hash ^= input.charCodeAt(i);
    hash += (hash << 1) + (hash << 4) + (hash << 7) + (hash << 8) + (hash << 24);
  }
  return (hash >>> 0).toString(16).padStart(8, "0");
}
function buildDeviceId(email: string): string {
  const { host, platform } = getHostInfo();
  return `trinox-${stableHash(`${email.toLowerCase()}|${host}|${platform}|${getClientType()}`)}`;
}

async function storageGet(key: string): Promise<string | null> {
  const runtimeStorage = (globalThis as any).OfficeRuntime?.storage;
  if (runtimeStorage?.getItem) return await runtimeStorage.getItem(key);
  return globalThis.localStorage?.getItem(key) ?? null;
}
async function storageSet(key: string, value: string): Promise<void> {
  const runtimeStorage = (globalThis as any).OfficeRuntime?.storage;
  if (runtimeStorage?.setItem) {
    await runtimeStorage.setItem(key, value);
    return;
  }
  globalThis.localStorage?.setItem(key, value);
}
async function getCache(email: string): Promise<SignatureCache> {
  const raw = await storageGet(cacheKey(email));
  if (!raw) return { deviceId: buildDeviceId(email), lastUserEmail: email };
  try {
    const parsed = JSON.parse(raw) as SignatureCache;
    if (!parsed.deviceId) parsed.deviceId = buildDeviceId(email);
    return parsed;
  } catch {
    return { deviceId: buildDeviceId(email), lastUserEmail: email };
  }
}
async function saveCache(email: string, cache: SignatureCache): Promise<void> {
  await storageSet(cacheKey(email), JSON.stringify(cache));
}

async function postJson<T>(path: string, payload: unknown): Promise<T> {
  const response = await fetch(`${config.apiBaseUrl}${path}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });
  if (!response.ok) throw new Error(`HTTP ${response.status} on ${path}`);
  return response.json() as Promise<T>;
}
async function report(stage: string, status: "success" | "error", message?: string): Promise<void> {
  const email = getEmail();
  const cache = await getCache(email);
  const { host, platform, officeVersion } = getHostInfo();
  await postJson("/addin/signature/report", {
    email,
    deviceId: cache.deviceId,
    status,
    eventType: `autorun_${stage}`,
    message,
    addinVersion: config.addinVersion,
    clientType: getClientType(),
    officeVersion,
    host,
    platform,
    apiBaseUrl: config.apiBaseUrl,
  });
}

function setSignatureAsync(html: string): Promise<void> {
  return new Promise((resolve, reject) => {
    const body = getItemBody();
    if (!body?.setSignatureAsync) {
      reject(new Error("setSignatureAsync unavailable"));
      return;
    }
    body.setSignatureAsync(html, { coercionType: "html" }, (result: any) => {
      result.status === Office.AsyncResultStatus.Succeeded
        ? resolve()
        : reject(new Error(result.error?.message ?? "setSignatureAsync failed"));
    });
  });
}

async function applySignature(): Promise<string> {
  const email = getEmail();
  const profile = getProfile();
  const cache = await getCache(email);
  const { host, platform, officeVersion } = getHostInfo();
  const clientType = getClientType();

  await postJson("/addin/register", {
    email,
    displayName: String(profile?.displayName ?? ""),
    username: email.includes("@") ? email.split("@")[0] : "",
    host,
    platform,
    officeVersion,
    clientType,
    addinVersion: config.addinVersion,
    deviceId: cache.deviceId,
  });
  await postJson("/addin/heartbeat", { email, deviceId: cache.deviceId, addinVersion: config.addinVersion, clientType, officeVersion });

  const signature = await postJson<any>("/addin/signature/check", {
    email,
    deviceId: cache.deviceId,
    currentSignatureVersion: cache.lastSignatureVersion,
    currentSignatureHash: cache.lastSignatureHash,
    lastCheckAt: cache.lastSuccessfulApplyAt,
  });
  if (!signature.html || String(signature.html).trim() === "") return "no_template_assigned";

  await setSignatureAsync(signature.html);
  cache.lastCheckedAt = new Date().toISOString();
  cache.lastSignatureVersion = signature.signatureVersion;
  cache.lastSignatureHash = signature.signatureHash ?? cache.lastSignatureHash;
  cache.lastSignatureHtml = signature.html;
  cache.lastUserEmail = email;
  cache.lastSuccessfulApplyAt = new Date().toISOString();
  await saveCache(email, cache);

  await postJson("/addin/signature/report", {
    email,
    deviceId: cache.deviceId,
    signatureVersion: signature.signatureVersion,
    status: "success",
    eventType: "signature_applied",
    message: signature.updateRequired ? "Signature updated from autorun" : "Signature applied from autorun",
    addinVersion: config.addinVersion,
    clientType,
    officeVersion,
    host,
    platform,
    apiBaseUrl: config.apiBaseUrl,
  });

  return "signature_applied";
}

async function onNewMessageComposeHandler(event: any) {
  try {
    await report("triggered", "success");
    const state = await applySignature();
    await report("completed", "success", state);
  } catch (error) {
    await report("failed", "error", (error as Error)?.message ?? "unknown_error");
  } finally {
    event.completed();
  }
}

(globalThis as any).onNewMessageComposeHandler = onNewMessageComposeHandler;
if (Office.actions?.associate) {
  Office.actions.associate("onNewMessageComposeHandler", onNewMessageComposeHandler);
}
