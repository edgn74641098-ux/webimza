import { checkSignature, registerAddin, reportResult, sendHeartbeat } from "./apiClient";
import { storageGet, storageSet } from "./storageService";
import { config } from "../shared/config";
import type { SignatureCache } from "../shared/types";

declare const Office: any;

const DEFAULT_SIGNATURE_CHECK_INTERVAL_MS = 24 * 60 * 60 * 1000;
function getMailbox() { return Office.context?.mailbox; }
function getUserProfileRaw(): any { return getMailbox()?.userProfile ?? {}; }
function getEmail(): string {
  const email = String(getUserProfileRaw()?.emailAddress ?? "").trim();
  if (!email) throw new Error("Outlook mailbox context hazir degil. Add-in'i yeni e-posta yazma penceresinde acin.");
  return email;
}
function getDisplayName(): string { return String(getUserProfileRaw()?.displayName ?? "").trim() || "-"; }
function getItemBody() { return Office.context?.mailbox?.item?.body; }
function mailboxRequirementSupported(): boolean { return Boolean(Office.context.requirements?.isSetSupported?.("Mailbox", "1.10")); }
function signatureApiSupported(): boolean { return Boolean(getItemBody()?.setSignatureAsync); }

export function getDiagnostics() {
  const profile = getUserProfileRaw();
  return {
    email: String(profile?.emailAddress ?? ""),
    displayName: String(profile?.displayName ?? ""),
    host: Office.context?.diagnostics?.hostName ?? "Outlook",
    platform: Office.context?.diagnostics?.platform ?? "unknown",
    officeVersion: Office.context?.diagnostics?.version ?? "unknown",
    addinVersion: config.addinVersion,
    apiBaseUrl: config.apiBaseUrl,
    mailboxContextAvailable: Boolean(getMailbox()?.userProfile),
    mailboxRequirement110Supported: mailboxRequirementSupported(),
    setSignatureAsyncSupported: signatureApiSupported(),
  };
}

function getHostInfo() { return { host: Office.context?.diagnostics?.hostName ?? "Outlook", platform: Office.context?.diagnostics?.platform ?? "unknown", officeVersion: Office.context?.diagnostics?.version ?? "unknown" }; }
function cacheKey(email: string): string { return `trinox_signature_cache_${email}`; }
function stableHash(input: string): string {
  let hash = 2166136261;
  for (let i = 0; i < input.length; i += 1) {
    hash ^= input.charCodeAt(i);
    hash += (hash << 1) + (hash << 4) + (hash << 7) + (hash << 8) + (hash << 24);
  }

  return (hash >>> 0).toString(16).padStart(8, "0");
}

function buildStableDeviceId(email: string): string {
  const { host, platform } = getHostInfo();
  const clientType = getClientType();
  const seed = `${email.toLowerCase()}|${host}|${platform}|${clientType}`;
  return `trinox-${stableHash(seed)}`;
}

function getClientType(): string {
  const host = String(Office.context?.diagnostics?.hostName ?? "").toLowerCase();
  if (host.includes("outlookwebapp") || host.includes("web")) return "outlook_web";
  if (host.includes("new")) return "new_outlook";
  if (host.includes("outlook")) return "classic_outlook";
  return "unknown";
}

function getOfficeIdentityFields() {
  const profile = getUserProfileRaw();
  const email = getEmail();
  const displayName = getDisplayName();
  const username = email.includes("@") ? email.split("@")[0] : "";

  return {
    displayName,
    username,
    title: String(profile?.jobTitle ?? "").trim() || undefined,
    department: String(profile?.department ?? "").trim() || undefined,
    company: String(profile?.companyName ?? profile?.company ?? "").trim() || undefined,
    phone: String(profile?.phoneNumber ?? profile?.businessPhone ?? "").trim() || undefined,
    mobile: String(profile?.mobilePhone ?? "").trim() || undefined,
    office: String(profile?.timeZone ?? profile?.officeLocation ?? "").trim() || undefined,
    website: String(profile?.website ?? profile?.webPage ?? "").trim() || undefined,
  };
}

export async function getClientSummary(): Promise<{ email: string; displayName: string; clientType: string; deviceId: string }> {
  const email = getEmail();
  const cache = await getCache(email);
  return {
    email,
    displayName: getDisplayName(),
    clientType: getClientType(),
    deviceId: cache.deviceId,
  };
}

async function getCache(email: string): Promise<SignatureCache> {
  const raw = await storageGet(cacheKey(email));
  if (!raw) return { deviceId: buildStableDeviceId(email), lastUserEmail: email };
  try { const parsed = JSON.parse(raw) as SignatureCache; if (!parsed.deviceId) parsed.deviceId = buildStableDeviceId(email); return parsed; }
  catch { return { deviceId: buildStableDeviceId(email), lastUserEmail: email }; }
}

async function saveCache(email: string, cache: SignatureCache): Promise<void> { await storageSet(cacheKey(email), JSON.stringify(cache)); }

export async function testConnection(): Promise<string> {
  const email = getEmail();
  const identity = getOfficeIdentityFields();
  const cache = await getCache(email);
  const { host, platform, officeVersion } = getHostInfo();
  await registerAddin({
    email,
    displayName: identity.displayName,
    username: identity.username,
    title: identity.title,
    department: identity.department,
    company: identity.company,
    phone: identity.phone,
    mobile: identity.mobile,
    office: identity.office,
    website: identity.website,
    host,
    platform,
    officeVersion,
    clientType: getClientType(),
    addinVersion: config.addinVersion,
    deviceId: cache.deviceId
  });
  return "API baglantisi basarili.";
}

function shouldCheckSignature(cache: SignatureCache, forceUpdateHint = false): boolean {
  if (forceUpdateHint) return true;
  if (cache.nextCheckAt) {
    const nextCheckAt = Date.parse(cache.nextCheckAt);
    if (!Number.isNaN(nextCheckAt)) {
      return Date.now() >= nextCheckAt;
    }
  }
  if (!cache.lastCheckedAt) return true;
  const lastCheckedAt = Date.parse(cache.lastCheckedAt);
  if (Number.isNaN(lastCheckedAt)) return true;
  return (Date.now() - lastCheckedAt) >= DEFAULT_SIGNATURE_CHECK_INTERVAL_MS;
}

function setSignatureAsync(html: string): Promise<void> {
  return new Promise((resolve, reject) => {
    const body = getItemBody();
    if (!body?.setSignatureAsync) {
      reject(new Error("Unsupported client"));
      return;
    }
    body.setSignatureAsync(html, { coercionType: "html" }, (result: any) => {
      if (result.status === Office.AsyncResultStatus.Succeeded) resolve(); else reject(new Error(result.error?.message ?? "setSignatureAsync failed"));
    });
  });
}

export async function applySignatureFlow(forceCheck = false): Promise<{ status: string; detail: string; state: string }> {
  if (!mailboxRequirementSupported() || !signatureApiSupported()) {
    return { status: "error", detail: "Unsupported client", state: "unsupported_client" };
  }

  const email = getEmail();
  const identity = getOfficeIdentityFields();
  const cache = await getCache(email);
  const { host, platform, officeVersion } = getHostInfo();

  const clientType = getClientType();
  await registerAddin({
    email,
    displayName: identity.displayName,
    username: identity.username,
    title: identity.title,
    department: identity.department,
    company: identity.company,
    phone: identity.phone,
    mobile: identity.mobile,
    office: identity.office,
    website: identity.website,
    host,
    platform,
    officeVersion,
    clientType,
    addinVersion: config.addinVersion,
    deviceId: cache.deviceId
  });
  await sendHeartbeat({ email, deviceId: cache.deviceId, addinVersion: config.addinVersion, clientType, officeVersion });

  const doCheck = shouldCheckSignature(cache, forceCheck);
  if (!doCheck && cache.lastSignatureHtml) {
    try {
      await setSignatureAsync(cache.lastSignatureHtml);
      await reportResult({ email, deviceId: cache.deviceId, signatureVersion: cache.lastSignatureVersion, status: "success", eventType: "signature_applied", message: "Signature applied from cache" });
      return { status: "success", detail: `Signature applied from cache (${cache.lastSignatureVersion ?? "unknown"})`, state: "cache_used" };
    } catch (error) {
      await reportResult({ email, deviceId: cache.deviceId, status: "error", eventType: "signature_failed", message: (error as Error).message });
      return { status: "error", detail: (error as Error).message, state: "signature_failed" };
    }
  }

  let signature;
  try {
    signature = await checkSignature({
      email,
      deviceId: cache.deviceId,
      currentSignatureVersion: cache.lastSignatureVersion,
      currentSignatureHash: cache.lastSignatureHash,
      lastCheckAt: cache.lastSuccessfulApplyAt,
    });
  } catch (error) {
    if (cache.lastSignatureHtml) {
      try {
        await setSignatureAsync(cache.lastSignatureHtml);
        await reportResult({ email, deviceId: cache.deviceId, signatureVersion: cache.lastSignatureVersion, status: "success", eventType: "signature_applied", message: "API offline, signature applied from cache" });
        return { status: "success", detail: `API offline. Cached signature applied (${cache.lastSignatureVersion ?? "unknown"})`, state: "cache_used" };
      } catch (cacheError) {
        await reportResult({ email, deviceId: cache.deviceId, status: "error", eventType: "signature_failed", message: (cacheError as Error).message });
        return { status: "error", detail: (cacheError as Error).message, state: "signature_failed" };
      }
    }

    return { status: "error", detail: `API offline ve cache bulunamadi: ${(error as Error).message}`, state: "api_offline" };
  }

  if (!signature.html || signature.html.trim() === "") {
    return { status: "error", detail: "No template assigned", state: "no_template_assigned" };
  }
  const htmlToApply = signature.updateRequired || !cache.lastSignatureHtml ? signature.html : cache.lastSignatureHtml;
  await setSignatureAsync(htmlToApply);

  cache.lastCheckedAt = new Date().toISOString();
  const cacheSeconds = Number(signature.cacheSeconds);
  const intervalMs = Number.isFinite(cacheSeconds) && cacheSeconds > 0
    ? cacheSeconds * 1000
    : DEFAULT_SIGNATURE_CHECK_INTERVAL_MS;
  cache.cacheSeconds = Math.floor(intervalMs / 1000);
  cache.nextCheckAt = new Date(Date.now() + intervalMs).toISOString();
  cache.lastSignatureVersion = signature.signatureVersion;
  cache.lastSignatureHash = signature.signatureHash ?? cache.lastSignatureHash;
  cache.lastSignatureHtml = htmlToApply;
  cache.lastUserEmail = email;
  cache.lastSuccessfulApplyAt = new Date().toISOString();
  await saveCache(email, cache);

  await reportResult({ email, deviceId: cache.deviceId, signatureVersion: signature.signatureVersion, status: "success", eventType: "signature_applied", message: signature.updateRequired ? "Signature updated from API" : "Signature applied" });
  return { status: "success", detail: `Signature applied (${signature.signatureVersion})`, state: "signature_applied" };
}

export async function getTaskpaneState(): Promise<{
  userName: string;
  email: string;
  clientType: string;
  deviceId: string;
  currentVersion: string;
  serverVersion: string;
  lastApplied: string;
  updateRequired: boolean;
}> {
  const email = getEmail();
  const cache = await getCache(email);
  const signature = await checkSignature({
    email,
    deviceId: cache.deviceId,
    currentSignatureVersion: cache.lastSignatureVersion,
    currentSignatureHash: cache.lastSignatureHash,
    lastCheckAt: cache.lastSuccessfulApplyAt,
  });

  return {
    userName: getDisplayName(),
    email,
    clientType: getClientType(),
    deviceId: cache.deviceId,
    currentVersion: cache.lastSignatureVersion ?? "-",
    serverVersion: signature.signatureVersion ?? "-",
    lastApplied: cache.lastSuccessfulApplyAt ?? "-",
    updateRequired: Boolean(signature.updateRequired),
  };
}

export async function clearSignatureCache(): Promise<void> {
  const email = getEmail();
  await storageSet(cacheKey(email), "");
}

export async function sendDiagnostic(): Promise<void> {
  const email = getEmail();
  const cache = await getCache(email);
  const diag = getDiagnostics();
  const clientType = getClientType();

  await reportResult({
    email,
    deviceId: cache.deviceId,
    status: "success",
    eventType: "diagnostic",
    message: "Structured diagnostic report",
    addinVersion: diag.addinVersion,
    clientType,
    officeVersion: diag.officeVersion,
    host: diag.host,
    platform: diag.platform,
    apiBaseUrl: diag.apiBaseUrl,
    metadata: {
      diagnostics: diag,
      cache: {
        deviceId: cache.deviceId,
        lastCheckedAt: cache.lastCheckedAt ?? null,
        nextCheckAt: cache.nextCheckAt ?? null,
        cacheSeconds: cache.cacheSeconds ?? null,
        lastSignatureVersion: cache.lastSignatureVersion ?? null,
        lastSignatureHash: cache.lastSignatureHash ?? null,
        lastSuccessfulApplyAt: cache.lastSuccessfulApplyAt ?? null,
      },
      context: {
        sentAt: new Date().toISOString(),
        userAgent: typeof navigator !== "undefined" ? navigator.userAgent : "unknown",
      },
    },
  });
}

export async function reportAutorunTelemetry(stage: "triggered" | "completed" | "failed", detail?: string): Promise<void> {
  const email = getEmail();
  const cache = await getCache(email);

  await reportResult({
    email,
    deviceId: cache.deviceId,
    status: stage === "failed" ? "error" : "success",
    eventType: `autorun_${stage}`,
    message: detail,
  });
}
