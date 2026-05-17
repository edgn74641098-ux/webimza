# TRINOX Signature Manager

## Monorepo Yapisi
- `backend`: Laravel 12 API + Admin Panel (Breeze/Blade)
- `outlook-addin`: Vite + TypeScript Outlook add-in (manual + event-based)

## Tamamlanan Fazlar
- Faz 1: Backend MVP iskeleti (modeller, migration, API endpointleri, seed)
- Faz 2: Outlook add-in manuel "Imzayi Ekle" butonu ile `setSignatureAsync`
- Faz 3 (ilk surum): Event-based activation + gunluk check/cache + heartbeat/report
- Admin: login + kullanici/sablon/atama/force update/log ekranlari

## Backend Calistirma
```bash
cd backend
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Demo giris:
- Email: `erkan.degnekci@trinoxmetal.com`
- Sifre: `password`

Panel:
- `http://127.0.0.1:8000/login`
- `http://127.0.0.1:8000/admin`

## Add-in Calistirma
```bash
cd outlook-addin
npm install
npm run dev
```

- Manifest: `outlook-addin/manifest.xml`
- Taskpane: `https://localhost:5173/index.html`
- Autorun (event): `https://localhost:5173/autorun.html`
- API URL: `outlook-addin/src/shared/config.ts`

## Event-Based Akis
- `OnNewMessageCompose` tetiklenince `onNewMessageComposeHandler` calisir.
- Akis: register -> heartbeat -> signature/check -> setSignatureAsync -> report
- Cache alanlari: `deviceId`, `lastCheckDate`, `lastSignatureVersion`, `lastSignatureHtml`, `lastSuccessfulApplyAt`
- Storage: once `OfficeRuntime.storage`, yoksa `localStorage`

## Test Onerisi
1. Outlook'ta add-in manifestini tekrar yukle (versiyon guncellendi: `1.1.0.0`).
2. Taskpane butonuyla manuel imza eklemeyi dogrula.
3. Yeni mail olusturup otomatik imza event'ini dogrula.
4. Panelden force update kaydi acip sonraki yeni mailde guncellemeyi kontrol et.
