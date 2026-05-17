# PRODUCT READY CHECKLIST

## 1) Admin panel hazir mi?
- [x] Dashboard sade KPI + aktivite + hata + hizli aksiyon yapisinda.
- [x] Kullanicilar ekranlari (liste/create/show/edit) calisiyor.
- [x] Imza Sablonlari split-view + preview calisiyor.
- [x] Atamalar tek sayfa sade form ile calisiyor.
- [x] Guncelleme Yonetimi 3 adimli wizard ile calisiyor.
- [x] Add-in Cihazlari ve Loglar liste + detay ekranlari aciliyor.
- [x] Add-in Dagitimi ekrani config/validation/build/download adimlarini sunuyor.

## 2) Add-in hazir mi?
- [x] Taskpane UI sade ve dar panel uyumlu.
- [x] API baglanti testi mevcut.
- [x] Imza ekle/yenile akisi mevcut.
- [x] Cache temizleme mevcut.
- [x] Diagnostic gonderme mevcut.
- [x] `outlook-addin npm run build` basarili.

## 3) Deployment hazir mi?
- [x] Manifest build akisi mevcut.
- [x] Validation checklist mevcut.
- [x] Manifest/Paket indirme mevcut.
- [x] M365 dagitim adimlari dokumante edildi.

## 4) Testler gecti mi?
- [x] `php artisan test` basarili (26 passed).
- [x] `backend npm run build` basarili.
- [x] `outlook-addin npm run build` basarili.

## 5) Bilinen eksikler neler?
- [ ] `admin.groups.*` route'lari kod tabaninda mevcut, menude gizli.
- [x] Legacy view klasor adlari standardize edildi (`updates`, `deployment`).
- [ ] Add-in uretim dagitimi oncesi tenant bazli manuel dogrulama gerekli.

## 6) Production'a gecmeden once yapilacaklar
- [ ] API URL, Taskpane URL, Autorun URL alanlarini production HTTPS endpointleriyle guncelle.
- [ ] Manifest ID ve version politikasini netlestir (release proseduru).
- [ ] Pilot grup ile dagitim smoke test yap.
- [ ] Outlook Classic/New/Web istemcilerde E2E test tamamla.
- [ ] Monitoring/alerting ve log retention ayarlarini netlestir.
- [ ] Rollback plani (onceki manifest/surum) hazirla.
