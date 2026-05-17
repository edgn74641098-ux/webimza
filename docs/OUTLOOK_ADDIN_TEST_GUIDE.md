# OUTLOOK ADDIN TEST GUIDE

## 1) Local dev server nasil acilir?
### Backend
1. `backend` klasorune girin.
2. `php artisan serve` calistirin.
3. API adresini not edin (or: `http://127.0.0.1:8000`).

### Add-in
1. `outlook-addin` klasorune girin.
2. `npm install`
3. `npm run dev`
4. Taskpane URL'yi not edin (or: `https://localhost:5173/index.html`).

## 2) Manifest nasil sideload edilir?
1. Admin panelde `Add-in Dagitimi` ekranindan manifest olusturun.
2. `Manifest indir` ile XML dosyasini alin.
3. Test ortamina gore sideload adimlarini uygulayin (Classic/New/Web).

## 3) Klasik Outlook'ta nasil test edilir?
1. Outlook Classic acin.
2. Test mailbox ile giris yapin.
3. Add-in'i sideload edin (manifest yukleme).
4. Yeni mail olusturup taskpane'i acin.
5. `Baglantiyi Test Et` ve `Imzayi Ekle` butonlarini deneyin.

## 4) Yeni Outlook'ta nasil test edilir?
1. New Outlook acin.
2. Uygun mailbox ile giris yapin.
3. Add-in'i sideload edin.
4. Compose ekraninda add-in panelini acin.
5. Durum banner'i ve imza uygulamasini kontrol edin.

## 5) Outlook Web'de nasil test edilir?
1. `https://outlook.office.com` acin.
2. Test kullanicisi ile giris yapin.
3. Add-in'i custom app olarak yukleyin/sideload edin.
4. Yeni mail olusturup taskpane'i acin.
5. `Imzayi Yenile` ve `Diagnostic Gonder` butonlarini test edin.

## 6) Imza gelmezse ne kontrol edilir?
1. Taskpane'de API badge `API bagli` mi?
2. Durum mesaji `Unsupported client` mi?
3. Admin panelde kullaniciya aktif sablon atanmis mi?
4. Add-in cihaz kaydi olusmus mu? (`Admin > Add-in Cihazlari`)
5. Log dusuyor mu? (`Admin > Loglar`)
6. `setSignatureAsync` ve Mailbox 1.10 diagnostic'te destekli mi?
7. API URL / Taskpane URL dogru mu?
8. Cache temizleyip tekrar deneyin.
