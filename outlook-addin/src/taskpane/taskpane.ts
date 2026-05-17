import "../style.css";
import {
  applySignatureFlow,
  clearSignatureCache,
  getClientSummary,
  getDiagnostics,
  getTaskpaneState,
  sendDiagnostic,
  testConnection,
} from "../services/signatureService";

declare const Office: any;

const root = document.querySelector<HTMLDivElement>("#app");
if (root) {
  root.innerHTML = `
    <div class="tp-shell">
      <header class="tp-header">
        <h1>TRINOX Signature</h1>
        <span id="api-badge" class="tp-badge tp-badge-neutral">API kontrol</span>
      </header>

      <div id="state-banner" class="tp-state tp-state-loading">Loading</div>

      <section class="tp-card">
        <h2>Kullanici</h2>
        <div class="tp-row"><span>Email</span><strong id="u-email">-</strong></div>
        <div class="tp-row"><span>Display name</span><strong id="u-name">-</strong></div>
        <div class="tp-row"><span>Client type</span><strong id="u-client">-</strong></div>
      </section>

      <section class="tp-card">
        <h2>Imza Durumu</h2>
        <div class="tp-row"><span>Son imza versiyonu</span><strong id="s-version">-</strong></div>
        <div class="tp-row"><span>Son uygulama zamani</span><strong id="s-last">-</strong></div>
        <div class="tp-row"><span>Guncelleme gerekli</span><span id="s-update" class="tp-badge tp-badge-neutral">-</span></div>
      </section>

      <section class="tp-card">
        <h2>Aksiyonlar</h2>
        <div class="tp-actions">
          <button id="btn-apply">Imzayi Ekle</button>
          <button id="btn-refresh" class="tp-btn-secondary">Imzayi Yenile</button>
          <button id="btn-test" class="tp-btn-secondary">Baglantiyi Test Et</button>
          <button id="btn-clear" class="tp-btn-secondary">Cache Temizle</button>
          <button id="btn-diagnostic" class="tp-btn-secondary">Diagnostic Gonder</button>
        </div>
      </section>

      <details class="tp-card">
        <summary>Diagnostic</summary>
        <div class="tp-row"><span>Host</span><strong id="d-host">-</strong></div>
        <div class="tp-row"><span>Platform</span><strong id="d-platform">-</strong></div>
        <div class="tp-row"><span>Mailbox requirement 1.10</span><strong id="d-req">-</strong></div>
        <div class="tp-row"><span>setSignatureAsync</span><strong id="d-sign">-</strong></div>
        <div class="tp-row"><span>Device ID</span><strong id="d-device">-</strong></div>
        <div class="tp-row"><span>Add-in version</span><strong id="d-version">-</strong></div>
        <div class="tp-row"><span>API URL</span><strong id="d-api">-</strong></div>
      </details>

      <p id="status" class="tp-status">Hazir.</p>
    </div>
  `;
}

function setText(id: string, value: string) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setStatus(text: string, type: "ok" | "error" | "neutral" = "neutral") {
  const el = document.getElementById("status");
  if (!el) return;
  el.textContent = text;
  el.className = "tp-status";
  if (type === "ok") el.classList.add("tp-status-ok");
  if (type === "error") el.classList.add("tp-status-error");
}

function setApiBadge(connected: boolean) {
  const badge = document.getElementById("api-badge");
  if (!badge) return;
  badge.className = connected ? "tp-badge tp-badge-ok" : "tp-badge tp-badge-error";
  badge.textContent = connected ? "API bagli" : "API offline";
}

function setUpdateBadge(required: boolean) {
  const badge = document.getElementById("s-update");
  if (!badge) return;
  badge.className = required ? "tp-badge tp-badge-error" : "tp-badge tp-badge-ok";
  badge.textContent = required ? "Evet" : "Hayir";
}

function setStateBanner(state: string) {
  const el = document.getElementById("state-banner");
  if (!el) return;
  el.className = "tp-state";

  const map: Record<string, { text: string; cls: string }> = {
    loading: { text: "Loading", cls: "tp-state-loading" },
    api_offline: { text: "API offline", cls: "tp-state-error" },
    unsupported_client: { text: "Unsupported client", cls: "tp-state-warn" },
    no_template_assigned: { text: "No template assigned", cls: "tp-state-warn" },
    signature_applied: { text: "Signature applied", cls: "tp-state-ok" },
    signature_failed: { text: "Signature failed", cls: "tp-state-error" },
    cache_used: { text: "Cache used", cls: "tp-state-ok" },
  };

  const resolved = map[state] ?? map.loading;
  el.textContent = resolved.text;
  el.classList.add(resolved.cls);
}

async function refreshTaskpaneState() {
  try {
    const state = await getTaskpaneState();
    setText("u-email", state.email || "-");
    setText("u-name", state.userName || "-");
    setText("u-client", state.clientType || "-");
    setText("s-version", state.currentVersion || "-");
    setText("s-last", state.lastApplied || "-");
    setUpdateBadge(Boolean(state.updateRequired));
  } catch (error) {
    setApiBadge(false);
    setStateBanner("api_offline");
    setText("s-version", "-");
    setText("s-last", "-");
    setStatus(`API durumu alinamadi: ${(error as Error).message}`, "error");
  }
}

Office.onReady(async () => {
  try {
    setStateBanner("loading");

    const diagnostics = getDiagnostics();
    setText("d-host", String(diagnostics.host ?? "-"));
    setText("d-platform", String(diagnostics.platform ?? "-"));
    setText("d-req", diagnostics.mailboxRequirement110Supported ? "Destekli" : "Desteksiz");
    setText("d-sign", diagnostics.setSignatureAsyncSupported ? "Destekli" : "Desteksiz");
    setText("d-version", String(diagnostics.addinVersion ?? "-"));
    setText("d-api", String(diagnostics.apiBaseUrl ?? "-"));

    if (!diagnostics.mailboxContextAvailable) {
      setStateBanner("unsupported_client");
      setStatus("Outlook mailbox context hazir degil. Add-in'i yeni e-posta yazma penceresinde acin.", "error");
      return;
    }

    const client = await getClientSummary();
    setText("u-email", client.email || "-");
    setText("u-name", client.displayName || "-");
    setText("u-client", client.clientType || "-");
    setText("d-device", client.deviceId || "-");

    if (!diagnostics.mailboxRequirement110Supported || !diagnostics.setSignatureAsyncSupported) {
      setStateBanner("unsupported_client");
      setStatus("Bu Outlook istemcisinde imza API destegi yok.", "error");
    }

    try {
      await testConnection();
      setApiBadge(true);
    } catch (error) {
      setApiBadge(false);
      setStateBanner("api_offline");
      setStatus(`API baglantisi kurulamadi: ${(error as Error).message}`, "error");
    }

    await refreshTaskpaneState();
    if (document.getElementById("api-badge")?.textContent === "API bagli") {
      setStatus("Durum guncellendi.", "ok");
    }
  } catch (error) {
    setStateBanner("signature_failed");
    setStatus(`Taskpane baslatilamadi: ${(error as Error).message}`, "error");
  }

  document.getElementById("btn-apply")?.addEventListener("click", async () => {
    try {
      setStatus("Imza ekleniyor...");
      const result = await applySignatureFlow(false);
      setStateBanner(result.state);
      setStatus(result.detail, result.status === "success" ? "ok" : "error");
      await refreshTaskpaneState();
    } catch (error) {
      setStateBanner("signature_failed");
      setStatus(`Imza eklenemedi: ${(error as Error).message}`, "error");
    }
  });

  document.getElementById("btn-refresh")?.addEventListener("click", async () => {
    try {
      setStatus("Imza yenileniyor...");
      const result = await applySignatureFlow(true);
      setStateBanner(result.state);
      setStatus(result.detail, result.status === "success" ? "ok" : "error");
      await refreshTaskpaneState();
    } catch (error) {
      setStateBanner("signature_failed");
      setStatus(`Imza yenilenemedi: ${(error as Error).message}`, "error");
    }
  });

  document.getElementById("btn-test")?.addEventListener("click", async () => {
    try {
      setStatus(await testConnection(), "ok");
      setApiBadge(true);
      setStateBanner("loading");
    } catch (error) {
      setApiBadge(false);
      setStateBanner("api_offline");
      setStatus(`Baglanti testi basarisiz: ${(error as Error).message}`, "error");
    }
  });

  document.getElementById("btn-clear")?.addEventListener("click", async () => {
    await clearSignatureCache();
    setStatus("Cache temizlendi.", "ok");
    setStateBanner("cache_used");
    await refreshTaskpaneState();
  });

  document.getElementById("btn-diagnostic")?.addEventListener("click", async () => {
    try {
      await sendDiagnostic();
      setStatus("Diagnostic raporu gonderildi.", "ok");
    } catch (error) {
      setStatus(`Diagnostic gonderilemedi: ${(error as Error).message}`, "error");
    }
  });
});
