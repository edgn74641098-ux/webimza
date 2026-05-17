export type SignatureCheckResponse = {
  success: boolean;
  updateRequired: boolean;
  forceUpdate: boolean;
  signatureVersion: string;
  signatureName: string;
  html: string;
  text: string;
  cacheSeconds: number;
};

export type SignatureCache = {
  deviceId: string;
  lastCheckDate?: string;
  lastSignatureVersion?: string;
  lastSignatureHtml?: string;
  lastUserEmail?: string;
  lastSuccessfulApplyAt?: string;
};
