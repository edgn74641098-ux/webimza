import { applySignatureFlow } from "../services/signatureService";

declare const Office: any;

async function onNewMessageComposeHandler(event: any) {
  try {
    await applySignatureFlow();
  } catch {
    // Fail-safe by design: do not break compose flow.
  } finally {
    event.completed();
  }
}

Office.onReady(() => {
  if (Office.actions && Office.actions.associate) {
    Office.actions.associate("onNewMessageComposeHandler", onNewMessageComposeHandler);
  }
});
