const local = {
  async getItem(key: string): Promise<string | null> {
    return window.localStorage.getItem(key);
  },
  async setItem(key: string, value: string): Promise<void> {
    window.localStorage.setItem(key, value);
  },
};

export async function storageGet(key: string): Promise<string | null> {
  try {
    const officeRuntime = (window as any).OfficeRuntime;
    if (officeRuntime?.storage?.getItem) {
      return await officeRuntime.storage.getItem(key);
    }
  } catch {
    // Fallback below.
  }

  return local.getItem(key);
}

export async function storageSet(key: string, value: string): Promise<void> {
  try {
    const officeRuntime = (window as any).OfficeRuntime;
    if (officeRuntime?.storage?.setItem) {
      await officeRuntime.storage.setItem(key, value);
      return;
    }
  } catch {
    // Fallback below.
  }

  await local.setItem(key, value);
}
