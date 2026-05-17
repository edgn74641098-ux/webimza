# PRODUCT_AUDIT

## Genel Durum Ozeti
TRINOX Signature Manager teknik olarak calisir durumda ancak urun yapisi hala parca parca gelistirilmis bir izlenim veriyor.

Guclu taraflar:
- Ana domainler (kullanicilar, sablonlar, atamalar, force update, devices, logs, deployment, add-in) mevcut.
- Kritik operasyon ekranlari olusturulmus.
- Add-in ile backend arasinda temel telemetry ve signature check akisi var.

Temel sorunlar:
- Bilgi mimarisi ve naming tutarsiz (aynı domain icin birden fazla route/ekran).
- Bazı ekranlar urun akisi acisindan tekrarli veya yarim.
- UI component katmani guclu ama paralel eski component setleriyle birlikte yasiyor.
- Add-in tarafinda demo kalintilari ve operasyonel olmayan bilesenler duruyor.

## Mevcut Route Listesi
Ozetle aktif route gruplari:

- Admin:
  - `/admin` dashboard
  - `/admin/users/*`
  - `/admin/templates/*`
  - `/admin/assignments*`
  - `/admin/force-updates*`
  - `/admin/groups/*`
  - `/admin/logs`
  - `/admin/devices`
  - `/admin/addin-devices`
  - `/admin/addin-config*`
  - `/admin/addin-builds/{build}/manifest|package`
- API:
  - `POST /api/addin/register`
  - `POST /api/addin/heartbeat`
  - `POST /api/addin/signature/check`
  - `POST /api/addin/signature/report`
- Auth/profile default Laravel routelari.

Not: `admin/devices` ve `admin/addin-devices` ayni controller action'a gidiyor.

## Mevcut Controller Listesi
Admin:
- AddinConfigController
- AssignmentController
- DashboardController
- DeviceController
- ForceUpdateController
- GroupController
- LogController
- TemplateController
- UserController

API:
- AddinRegisterController
- HeartbeatController
- SignatureCheckController
- SignatureReportController

Auth/Profile:
- Laravel default auth + ProfileController

## Mevcut View Listesi
Admin view ana dosyalari:
- `admin/dashboard.blade.php`
- `admin/users/*` (index, show, form/create/edit)
- `admin/templates/*` (index, form/create/edit)
- `admin/assignments/index.blade.php`
- `admin/force-updates/index.blade.php`
- `admin/groups/*` (index, form)
- `admin/logs/index.blade.php`
- `admin/addin-devices/index.blade.php`
- `admin/addin-config/index.blade.php`
- `admin/devices/index.blade.php`

## Mevcut Component Listesi
Iki paralel component seti bir arada:

1. Yeni UI library (`resources/views/components/ui/*`):
- alert, app-shell, button, code-block, copy-button, data-table, drawer, dropdown, empty-state, error-state, input, loading-state, modal, page-header, section-card, select, stat-card, status-badge, table-toolbar, tabs, textarea, timeline

2. Legacy/default Laravel bileşenleri (`resources/views/components/*`):
- nav-link, responsive-nav-link, dropdown, modal, primary/secondary/danger button, text-input, input-label, input-error, auth-session-status, application-logo

Sonuc: component dogasi guclu ama ikili sistem karmasa uretiyor.

## Outlook Add-in Dosya Yapisi
Kaynak kod (odak):
- `src/taskpane/taskpane.ts`
- `src/autorun/autorun.ts`
- `src/services/apiClient.ts`
- `src/services/signatureService.ts`
- `src/services/storageService.ts`
- `src/shared/config.ts`
- `src/shared/types.ts`
- `src/style.css`

Kalinti/yan etkili klasorler:
- `dist/` (build output)
- `node_modules/`
- `src/counter.ts`, `src/assets/vite.svg`, `typescript.svg`, `hero.png` (urunle dogrudan iliskisiz kalintilar)

## Gereksiz / Tekrarli / Yarim Kalan Parcalar
- `admin/devices` ve `admin/addin-devices` tekrarli.
- `resources/views/admin/devices/index.blade.php` ile `addin-devices/index.blade.php` domain cakismasi yaratiyor.
- Legacy Blade componentleri ve `x-ui.*` birlikte kullaniliyor.
- Add-in icinde demo kalintilari (`counter.ts`, varsayilan assetler) duruyor.
- Deployment ekraninda build type secimi backendde parcial (metadata/behavior ayrimi tam net degil).
- Group/Assignment/Force Update arasi kapsam dili tutarsiz (hedefleme kavramlari farkli ifadelerle tekrar ediyor).

## Mantiksiz veya Karmasik UI Akislari
- Navigation’da `Devices` menu item’i teknik olarak `admin.devices` route’una giderken urun dili `add-in inventory` beklentisiyle cakisiyor.
- Force update, assignment ve user detail ekranlarinda benzer hedefleme bilgisi farkli terminolojiyle sunuluyor.
- Deployment center guclu ama "kaydet/build/indir" akislari tek bakista role-based operasyon kartlarina bolunmemis.
- Bazi ekranlarda (ozellikle form ekranlari) urun operasyon amaci yerine CRUD form hissi hala baskin.

## Backend ile UI Arasindaki Kopukluklar
- Log filtrelerinde bazi alanlar fiziksel kolon degil, `payload_json like` ile filtreleniyor; performans/guvenilirlik farki olusturur.
- Taskpane client type tespiti host string heuristics ile yapiliyor; backend raporlariyla birebir uyum her ortamda garanti degil.
- Validation checklist’te bazi kontroller (icon/url health) runtime network kosullarina bagli; UI’da deterministic durum gibi gorunebiliyor.
- Assignment effective date var ama tum ilgili ekranlarda ayni operasyonel netlikte aciklanmiyor.

## Oncelikli Temizlik Listesi
1. `admin/devices` route/view’ini emekli et; tek kaynak `admin/addin-devices` olsun.
2. Navigation bilgisini tek domain diline cek (`Add-in Inventory`).
3. Legacy blade component seti ile `x-ui` setini birlestir/katmanlandir (tek design system).
4. Admin form ekranlarini CRUD’dan operasyon paneline standartlastir.
5. Assignment/Force Update/Users terminolojisini teklestir (kapsam/hedef/etki).
6. Log payload filtreleme alanlari icin normalize alan stratejisi planla.
7. Add-in repo temizligi: demo dosyalari ve gereksiz assetleri kaldir.
8. Deployment center’i roller bazli aksiyon bloklarina ayir (config/build/deploy/verify).
9. Empty state + error state + loading state dilini tum ekranlarda ayni sozlukle sabitle.
10. Sayfa seviyesinde bilgi hiyerarsisini standartla (KPI -> Filtre -> Liste -> Detay aksiyon).

## Riskler
- Yapilan eklemelerde domainler birbirine yakin oldugu icin teknik borc birikimi hizli artiyor.
- Duplicated route/view kalirsa onboarding ve support maliyeti artar.
- `payload_json` uzerinden arama uzun vadede performans sorunu yaratabilir.
- UI’da gorunen "durum" ile backenddeki gercek kaynak farkliysa operasyonel karar hatasi dogurabilir.

## Onerilen Sade Urun Akisi
1. Dashboard: operasyon ozet + kritik alarmlar + hizli aksiyonlar.
2. Kullanicilar: kullanici merkezi + imza kaynagi + cihaz/log/atama tek detay ekraninda.
3. Sablonlar: split-view editor + onizleme + versiyon.
4. Atamalar: rule builder + oncelik + etki analizi.
5. Force Update: wizard + takip/progress.
6. Add-in Inventory: cihaz envanteri + diagnostic + uyumluluk.
7. Logs: filtre + drawer + root-cause akislari.
8. Deployment Center: config -> build -> download -> M365 deploy -> validation.
9. Taskpane: kullanici durumu + signature status + net aksiyon + diagnostic.

---
Bu rapor mevcut yapinin urun seviyesi sadeleştirme oncesi fotografini verir. Bir sonraki adimda feature eklemeden, sadece "temizlik + birlestirme + akış netlestirme" backlog'una gecilmesi onerilir.
