# ADMIN USER GUIDE

## Giris
Bu rehber TRINOX Signature Manager admin panelinin gunluk kullanim adimlarini aciklar.

## 1) Kullanici nasil eklenir?
1. `Admin > Kullanicilar` ekranina gidin.
2. `+ Kullanici Ekle` butonuna basin.
3. Zorunlu alanlari doldurun: ad, e-posta.
4. Opsiyonel alanlari girin: departman, unvan, telefon, mobil, sirket.
5. `Kaydet` ile olusturun.

## 2) Sablon nasil olusturulur?
1. `Admin > Imza Sablonlari` ekranina gidin.
2. `Yeni Sablon Olustur` butonuna basin.
3. Sol panelde sablon adini, tipini ve versiyonu girin.
4. HTML ve Text icerigini yazin.
5. Sag panelde onizleme kullanicisini secin, `Onizlemeyi Guncelle` ile kontrol edin.
6. `Kaydet` ile sablonu olusturun.

## 3) Placeholder nasil kullanilir?
Sablon editorundeki placeholder butonlari ile otomatik ekleme yapabilirsiniz:
- `{{DisplayName}}`
- `{{Title}}`
- `{{Department}}`
- `{{Company}}`
- `{{Phone}}`
- `{{Mobile}}`
- `{{Email}}`
- `{{Website}}`
- `{{LogoUrl}}`
- `{{BannerUrl}}`

Conditional ornek:
```
{{#if Mobile}}
Mobil: {{Mobile}}<br>
{{/if}}
```

## 4) Sablon kullanici/departman/gruba nasil atanir?
1. `Admin > Atamalar` ekranina gidin.
2. `Yeni Atama` formunda kapsami secin: `user`, `group`, `department`, `default`.
3. Kapsama uygun hedefi secin.
4. Sablonu secin.
5. Oncelik, tarih araligi ve aktif/pasif durumunu girin.
6. `Atamayi Kaydet` ile tamamlayin.

Oncelik sirasi:
1. User
2. Group
3. Department
4. Default

## 5) Force update nasil baslatilir?
1. `Admin > Guncelleme Yonetimi` ekranina gidin.
2. 3 adimli wizard'i tamamlayin:
   - Kapsam sec
   - Neden + TTL (1/3/7 gun)
   - Ozet ve baslat
3. Kayit olustuktan sonra alt tablodan ilerlemeyi takip edin.

Not: Bu islem anlik push degildir. Add-in compose/check akisiyla uygulanir.

## 6) Cihazlar nasil takip edilir?
1. `Admin > Add-in Cihazlari` ekranina gidin.
2. Filtreleri kullanin: client type, status, add-in version, last seen.
3. Listeden `Detay` ile cihaza girin.
4. Device metadata, son heartbeat, son 20 log ve compatibility bilgisini inceleyin.

## 7) Loglardan hata nasil incelenir?
1. `Admin > Loglar` ekranina gidin.
2. Filtreleyin: event type, status, kullanici, tarih.
3. Sorunlu kayitta `Detay` acin.
4. Payload JSON, request ID, error code ve device bilgisini kontrol edin.

## 8) Manifest nasil olusturulur?
1. `Admin > Add-in Dagitimi` ekranina gidin.
2. Mevcut Add-in Config alanlarini kontrol edin/kaydedin.
3. Validation checklist sonucunu inceleyin.
4. Build Actions'tan secin:
   - `Basic Manual Manifest olustur`
   - `Automatic Event-based Manifest olustur`
5. Download bolumunden `Manifest indir` veya `Paket indir` kullanin.
