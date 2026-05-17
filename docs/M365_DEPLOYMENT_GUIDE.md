# M365 DEPLOYMENT GUIDE

## 1) Microsoft 365 Admin Center
1. `https://admin.microsoft.com` adresine girin.
2. Global admin veya uygun yetkili hesapla oturum acin.

## 2) Integrated Apps
1. Sol menu veya Settings altindan `Integrated Apps` bolumunu acin.
2. `Upload custom app` secenegine girin.

## 3) Upload custom app
1. TRINOX panelinden indirilen manifest XML dosyasini secin.
2. Yuklemeyi baslatin.

## 4) Manifest yukleme
1. Manifest metadata'sini kontrol edin (ad, provider, version).
2. Domain/permission uyumlulugunu dogrulayin.

## 5) Test grubu secme
1. Once pilot/test grubunu secin.
2. Tum organizasyona acmadan once dogrulama yapin.

## 6) Deploy
1. Dagitimi onaylayin.
2. Policy'nin yayilmasini bekleyin.

## 7) Yayilim suresi
- Yayilim anlik olmayabilir.
- Tenant ve istemci tipine gore degiskenlik gosterebilir.
- Gecikme durumunda Outlook istemcisi yeniden acilip test edilmelidir.

## 8) Kullanici tarafi kontrol
1. Kullanici Outlook'ta yeni mail olusturur.
2. Add-in taskpane acilir.
3. API baglantisi ve imza uygulama durumu kontrol edilir.
4. Sorun varsa admin panelden cihaz ve log ekranlari incelenir.
