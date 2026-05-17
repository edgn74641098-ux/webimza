import { checkSignature, registerAddin, reportResult, sendHeartbeat } from "./apiClient";
import { storageGet, storageSet } from "./storageService";
import { config } from "../shared/config";
import type { SignatureCache } from "../shared/types";

declare const Office: any;

function todayIsoDate(): string { return new Date().toISOString().slice(0, 10); }
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
  if (!raw) return { deviceId: crypto.randomUUID(), lastUserEmail: email };
  try { const parsed = JSON.parse(raw) as SignatureCache; if (!parsed.deviceId) parsed.deviceId = crypto.randomUUID(); return parsed; }
  catch { return { deviceId: crypto.randomUUID(), lastUserEmail: email }; }
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

function shouldCheckSignature(cache: SignatureCache, forceUpdateHint = false): boolean { return forceUpdateHint || cache.lastCheckDate !== todayIsoDate(); }

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
      await reportResult({ email, deviceId: cache.deviceId, signatureVersion: cache.lastSignatureVersion, status: "success", message: "Signature applied from cache" });
      return { status: "success", detail: `Signature applied from cache (${cache.lastSignatureVersion ?? "unknown"})`, state: "cache_used" };
    } catch (error) {
      await reportResult({ email, deviceId: cache.deviceId, status: "error", message: (error as Error).message });
      return { status: "error", detail: (error as Error).message, state: "signature_failed" };
    }
  }

  const signature = await checkSignature({ email, deviceId: cache.deviceId, currentSignatureVersion: cache.lastSignatureVersion, lastCheckAt: cache.lastSuccessfulApplyAt });
  if (!signature.html || signature.html.trim() === "") {
    return { status: "error", detail: "No template assigned", state: "no_template_assigned" };
  }
  const htmlToApply = signature.updateRequired || !cache.lastSignatureHtml ? signature.html : cache.lastSignatureHtml;
  await setSignatureAsync(htmlToApply);

  cache.lastCheckDate = todayIsoDate();
  cache.lastSignatureVersion = signature.signatureVersion;
  cache.lastSignatureHtml = htmlToApply;
  cache.lastUserEmail = email;
  cache.lastSuccessfulApplyAt = new Date().toISOString();
  await saveCache(email, cache);

  await reportResult({ email, deviceId: cache.deviceId, signatureVersion: signature.signatureVersion, status: "success", message: signature.updateRequired ? "Signature updated from API" : "Signature applied" });
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
  await reportResult({
    email,
    deviceId: cache.deviceId,
    status: "success",
    message: `diagnostic:${JSON.stringify(diag)}`,
  });
}
