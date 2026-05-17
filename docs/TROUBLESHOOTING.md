# TROUBLESHOOTING

## Add-in kuruldu ama buton gorunmuyor
- Kullaniciya deployment scope atanmis mi kontrol edin.
- Outlook istemcisi restart edin.
- Add-in policy yayilim suresini bekleyin.
- Manifest'in ilgili mailbox grubuna atandigini dogrulayin.

## Manifest validate hatasi
- Manifest ID GUID formatinda mi kontrol edin.
- Version formatini `x.x.x.x` olarak duzeltin.
- URL alanlarinin HTTPS oldugunu kontrol edin.
- XML namespace ve schema hatalarini tekrar validate edin.

## setSignatureAsync calismiyor
- Outlook client bu API'yi desteklemiyor olabilir.
- Taskpane diagnostic bolumunde `setSignatureAsync` kontrol edin.
- Compose modunda oldugunuzdan emin olun.

## Mailbox 1.10 desteklenmiyor
- Istemci/kanal eski olabilir.
- New Outlook veya Outlook Web ile tekrar deneyin.
- Office guncellemelerini yukleyin.

## WebView2 problemi
- Windows'ta WebView2 runtime kurulumunu kontrol edin.
- Kuruluysa onarim/yeniden kurulum deneyin.
- IT policy tarafinda engel olup olmadigini kontrol edin.

## localhost certificate problemi
- Local gelistirmede HTTPS sertifikasi guvenilir degilse taskpane acilmayabilir.
- Vite dev sertifikasini guvenilir olarak ekleyin.
- Gerekirse mkcert veya kurum sertifika yontemi kullanin.

## API offline
- Backend ayakta mi (`php artisan serve`) kontrol edin.
- Add-in config'teki API URL dogru mu kontrol edin.
- Firewall/proxy engeli olup olmadigini kontrol edin.

## Force update gelmiyor
- Force update anlik push degildir.
- Kullanici yeni compose acmali veya add-in check tetiklenmeli.
- Guncelleme kaydinin kapsam/TTL durumu kontrol edilmeli.

## Log dusmuyor
- Add-in API'ye erisiyor mu test edin.
- `/api/addin/*` endpointlerine istek dusuyor mu kontrol edin.
- Hata durumunda browser/devtools veya Outlook diagnostic loglarina bakin.
- Backend tarafinda DB yazma hatasi var mi inceleyin.
