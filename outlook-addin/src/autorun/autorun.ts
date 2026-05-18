import { applySignatureFlow, reportAutorunTelemetry } from "../services/signatureService";

declare const Office: any;

async function onNewMessageComposeHandler(event: any) {
  try {
    await reportAutorunTelemetry("triggered");
    const result = await applySignatureFlow(true);
    await reportAutorunTelemetry("completed", result.state);
  } catch (error) {
    await reportAutorunTelemetry("failed", (error as Error)?.message ?? "unknown_error");
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
