# PRODUCT_STRUCTURE

## Yeni Menu Yapisi
TRINOX Signature Manager admin panelinde ana menu sadece su 9 basliktan olusur:

1. Dashboard
2. Kullanicilar
3. Imza Sablonlari
4. Atamalar
5. Guncelleme Yonetimi
6. Add-in Cihazlari
7. Loglar
8. Add-in Dagitimi
9. Ayarlar

---

## Yeni Route Standardi

### Dashboard
- `GET /admin/dashboard`

### Kullanicilar
- `GET /admin/users`
- `GET /admin/users/create`
- `POST /admin/users`
- `GET /admin/users/{user}`
- `GET /admin/users/{user}/edit`
- `PUT /admin/users/{user}`
- `DELETE /admin/users/{user}`

### Imza Sablonlari
- `GET /admin/templates`
- `GET /admin/templates/create`
- `POST /admin/templates`
- `GET /admin/templates/{template}/edit`
- `PUT /admin/templates/{template}`
- `DELETE /admin/templates/{template}`
- `POST /admin/templates/preview`

### Atamalar
- `GET /admin/assignments`
- `POST /admin/assignments`
- `PUT /admin/assignments/{assignment}`
- `DELETE /admin/assignments/{assignment}`

### Guncelleme Yonetimi
- `GET /admin/updates`
- `POST /admin/updates`
- `GET /admin/updates/{forceUpdate}`

### Add-in Cihazlari
- `GET /admin/devices`
- `GET /admin/devices/{device}`

### Loglar
- `GET /admin/logs`
- `GET /admin/logs/{log}`

### Add-in Dagitimi
- `GET /admin/deployment`
- `POST /admin/deployment/build`

### Ayarlar
- `GET /admin/settings`

---

## Ekran Bazli Urun Tanimi

## 1) Dashboard
- Ekranin amaci:
  - Operasyon ozeti ve kritik aksiyonlarin tek noktadan gorulmesi.
- Admin burada ne gorur?
  - KPI kartlari, hata ozeti, istemci dagilimi, son aktiviteler, bekleyen guncellemeler.
- Admin burada hangi aksiyonu alir?
  - Sablon olusturma, force update baslatma, cihaz/log/deployment ekranina gecis.
- Hangi veriler gerekir?
  - users, addin_devices, addin_logs, force_updates, signature_versions.
- Kullanilacak route:
  - `GET /admin/dashboard`
- Kullanilacak controller:
  - `Admin\DashboardController`
- Kullanilacak view:
  - `resources/views/admin/dashboard.blade.php`
- Ekranin MVP kapsami:
  - 8 KPI + hizli aksiyon + son hata/aktivite.
- Sonraki faz:
  - Trend grafigi, alarm esikleri, export.

## 2) Kullanicilar
- Ekranin amaci:
  - Kullanici yasam dongusu + imza durumu + cihaz/log iliskisi.
- Admin burada ne gorur?
  - Liste, filtre, kullanici detayinda imza kaynagi/preview, cihazlar, loglar, atamalar.
- Admin burada hangi aksiyonu alir?
  - Ekle/duzenle/sil, kullanici bazli inceleme, ilgili aksiyon ekranlarina yonlenme.
- Hangi veriler gerekir?
  - users, departments, groups, addin_devices, addin_logs, signature_assignments.
- Kullanilacak route:
  - `/admin/users*`
- Kullanilacak controller:
  - `Admin\UserController`
- Kullanilacak view:
  - `admin/users/index.blade.php`, `show.blade.php`, `form.blade.php`
- Ekranin MVP kapsami:
  - Liste + detay (5 sekme).
- Sonraki faz:
  - Bulk islemler, CSV import/export.

## 3) Imza Sablonlari
- Ekranin amaci:
  - Kurumsal imza iceriginin yonetimi ve canli onizleme.
- Admin burada ne gorur?
  - Sablon listesi, versiyon, aktiflik; editorde split-view preview.
- Admin burada hangi aksiyonu alir?
  - Sablon olusturma/duzenleme/silme, preview alma.
- Hangi veriler gerekir?
  - signature_templates, assets, preview user dataseti.
- Kullanilacak route:
  - `/admin/templates*` + `POST /admin/templates/preview`
- Kullanilacak controller:
  - `Admin\TemplateController`
- Kullanilacak view:
  - `admin/templates/index.blade.php`, `form.blade.php`
- Ekranin MVP kapsami:
  - CRUD + split-view + placeholder helper.
- Sonraki faz:
  - Sablon karsilastirma, draft/publish akisi.

## 4) Atamalar
- Ekranin amaci:
  - Imza dagitim kurallarinin merkezi yonetimi.
- Admin burada ne gorur?
  - Kural listesi (oncelik/kapsam/hedef/sablon/etki).
- Admin burada hangi aksiyonu alir?
  - Kural ekleme, guncelleme, silme.
- Hangi veriler gerekir?
  - signature_assignments, users, groups, departments, templates.
- Kullanilacak route:
  - `/admin/assignments*`
- Kullanilacak controller:
  - `Admin\AssignmentController`
- Kullanilacak view:
  - `admin/assignments/index.blade.php`
- Ekranin MVP kapsami:
  - Rule builder + list + etkilenen kullanici.
- Sonraki faz:
  - Cakisma analizi, simulasyon modu.

## 5) Guncelleme Yonetimi
- Ekranin amaci:
  - Force update planlama ve ilerleme takibi.
- Admin burada ne gorur?
  - Wizard + update kayitlari + progress.
- Admin burada hangi aksiyonu alir?
  - Yeni update baslatir, etkisini izler.
- Hangi veriler gerekir?
  - force_updates, users, devices, resolved template map.
- Kullanilacak route:
  - `/admin/updates*`
- Kullanilacak controller:
  - `Admin\ForceUpdateController`
- Kullanilacak view:
  - `admin/force-updates/index.blade.php` (+ detay view)
- Ekranin MVP kapsami:
  - 5-step wizard + takip tablosu.
- Sonraki faz:
  - Cancel/retry, detay timeline, bildirim.

## 6) Add-in Cihazlari
- Ekranin amaci:
  - Cihaz envanteri, uyumluluk ve diagnostic yonetimi.
- Admin burada ne gorur?
  - Device listesi, compatibility, son gorulme, surum durumu.
- Admin burada hangi aksiyonu alir?
  - Diagnostic inceleme, ilgili user/log/force update aksiyonu.
- Hangi veriler gerekir?
  - addin_devices, users, addin_logs.
- Kullanilacak route:
  - `/admin/devices`, `/admin/devices/{device}`
- Kullanilacak controller:
  - `Admin\DeviceController`
- Kullanilacak view:
  - `admin/addin-devices/index.blade.php` (detay drawer veya detail page)
- Ekranin MVP kapsami:
  - Filtre + tablo + diagnostic drawer.
- Sonraki faz:
  - Cihaz detay sayfasi, health score.

## 7) Loglar
- Ekranin amaci:
  - Olay bazli gozlem ve hata kok neden analizi.
- Admin burada ne gorur?
  - Filtreli log listesi, payload, request id, duration.
- Admin burada hangi aksiyonu alir?
  - Detay drawer acma, payload kopyalama, user/device gecisi.
- Hangi veriler gerekir?
  - addin_logs, users, addin_devices.
- Kullanilacak route:
  - `/admin/logs`, `/admin/logs/{log}`
- Kullanilacak controller:
  - `Admin\LogController`
- Kullanilacak view:
  - `admin/logs/index.blade.php`
- Ekranin MVP kapsami:
  - Filtre + tablo + drawer.
- Sonraki faz:
  - Kayitli sorgu, anomaly detection.

## 8) Add-in Dagitimi
- Ekranin amaci:
  - Build, validation, artifact download ve M365 deploy rehberi.
- Admin burada ne gorur?
  - Deployment center bolumleri ve build gecmisi.
- Admin burada hangi aksiyonu alir?
  - Config kaydeder, build alir, manifest/zip indirir.
- Hangi veriler gerekir?
  - addin_configs, addin_builds, checklist runtime validation.
- Kullanilacak route:
  - `/admin/deployment`, `/admin/deployment/build`
- Kullanilacak controller:
  - `Admin\AddinConfigController`
- Kullanilacak view:
  - `admin/addin-config/index.blade.php`
- Ekranin MVP kapsami:
  - Config + build + download + validation.
- Sonraki faz:
  - Tenant rollout status senkronizasyonu.

## 9) Ayarlar
- Ekranin amaci:
  - Sistem geneli operasyon ayarlari ve guvenlik tercihleri.
- Admin burada ne gorur?
  - Genel panel ayarlari, varsayilan davranislar, entegrasyon toggles.
- Admin burada hangi aksiyonu alir?
  - Sistem parametrelerini gunceller.
- Hangi veriler gerekir?
  - app settings tablosu (yeni) veya config proxy.
- Kullanilacak route:
  - `GET /admin/settings`
- Kullanilacak controller:
  - `Admin\SettingsController` (planlanan)
- Kullanilacak view:
  - `admin/settings/index.blade.php` (planlanan)
- Ekranin MVP kapsami:
  - Read-only settings + temel guncelleme formu.
- Sonraki faz:
  - Audit trail, role-based settings.

---

## Kaldirilacak veya Birlestirilecek Eski Route/View/Controller Onerileri
- `GET /admin` -> `GET /admin/dashboard` alias/redirect ile standardize edilmeli.
- `GET /admin/addin-devices` route'u, hedef standard `GET /admin/devices` altinda birlestirilmeli.
- `GET /admin/devices` + `GET /admin/addin-devices` ikilisi tek route setine indirilmeli.
- `GET /admin/force-updates` route'u urun dilinde `GET /admin/updates` olarak yeniden adlandirilmali.
- `GET /admin/addin-config` urun dilinde `GET /admin/deployment` olmalı.
- `resources/views/admin/devices/index.blade.php` ile `admin/addin-devices/index.blade.php` ciftligi teklestirilmeli.
- Legacy componentlerin (primary-button vb.) admin panelde kullanimı azaltilip `x-ui` standartlastirilmali.

---

## Nihai Urun Akisi
1. Dashboard: genel operasyon gorunumu.
2. Kullanicilar: kisi bazli durum/cihaz/log/atama gorunumu.
3. Imza Sablonlari: icerik yonetimi ve preview.
4. Atamalar: dagitim kurallari.
5. Guncelleme Yonetimi: force update planlama/takip.
6. Add-in Cihazlari: inventory ve diagnostic.
7. Loglar: olay/hata analizi.
8. Add-in Dagitimi: build + deployment operasyonu.
9. Ayarlar: sistem tercihlerinin merkezi.

Bu akis ile admin paneli "daginik CRUD koleksiyonu" yerine tek bir operasyon urunu gibi davranir.
