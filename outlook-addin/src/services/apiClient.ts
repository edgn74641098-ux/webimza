import { config } from "../shared/config";
import type { SignatureCheckResponse } from "../shared/types";

async function postJson<T>(url: string, payload: unknown): Promise<T> {
  const response = await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status} on ${url}`);
  }

  return response.json() as Promise<T>;
}

export async function registerAddin(payload: {
  email: string;
  displayName?: string;
  username?: string;
  title?: string;
  department?: string;
  company?: string;
  phone?: string;
  mobile?: string;
  office?: string;
  website?: string;
  clientType?: string;
  platform?: string;
  host?: string;
  officeVersion?: string;
  addinVersion?: string;
  deviceId: string;
}) {
  return postJson<{ success: boolean; userId: number; deviceId: string; serverTime: string }>(`${config.apiBaseUrl}/addin/register`, payload);
}

export async function checkSignature(payload: {
  email: string;
  deviceId: string;
  currentSignatureVersion?: string;
  currentSignatureHash?: string;
  lastCheckAt?: string;
}): Promise<SignatureCheckResponse> {
  return postJson<SignatureCheckResponse>(`${config.apiBaseUrl}/addin/signature/check`, payload);
}

export async function reportResult(payload: {
  email: string;
  deviceId: string;
  signatureVersion?: string;
  status: "success" | "error";
  message?: string;
  eventType?: string;
  addinVersion?: string;
  clientType?: string;
  officeVersion?: string;
  host?: string;
  platform?: string;
  apiBaseUrl?: string;
  metadata?: Record<string, unknown>;
}) {
  return postJson<{ success: boolean }>(`${config.apiBaseUrl}/addin/signature/report`, payload);
}

export async function sendHeartbeat(payload: {
  email: string;
  deviceId: string;
  addinVersion?: string;
  clientType?: string;
  officeVersion?: string;
}) {
  return postJson<{ success: boolean }>(`${config.apiBaseUrl}/addin/heartbeat`, payload);
}
