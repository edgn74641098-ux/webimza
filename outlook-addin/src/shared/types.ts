export type SignatureCheckResponse = {
  success: boolean;
  updateRequired: boolean;
  forceUpdate: boolean;
  signatureVersion: string;
  signatureHash?: string;
  signatureName: string;
  html: string;
  text: string;
  cacheSeconds: number;
};

export type SignatureCache = {
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
