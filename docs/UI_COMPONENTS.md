# UI_COMPONENTS

## Nihai Component Listesi (Ana Standart)
Admin panelde ana standart olarak su componentler kullanilir:

1. `x-ui.page-header`
2. `x-ui.card`
3. `x-ui.stat-card`
4. `x-ui.badge`
5. `x-ui.button`
6. `x-ui.input`
7. `x-ui.select`
8. `x-ui.textarea`
9. `x-ui.table`
10. `x-ui.empty-state`
11. `x-ui.drawer`
12. `x-ui.modal`
13. `x-ui.tabs`
14. `x-ui.alert`

---

## Component Amaci ve Kullanim Ornekleri

## 1) x-ui.page-header
- Amac: Sayfa basligi + alt aciklama + opsiyonel aksiyonlar.
- Ornek:
```blade
<x-ui.page-header title="Kullanicilar" subtitle="Liste ve operasyonlar">
  <x-slot name="actions">
    <x-ui.button>Yeni</x-ui.button>
  </x-slot>
</x-ui.page-header>
```
- Kullanim: dashboard, users, templates, force-updates, settings, groups, logs, addin-config, addin-devices.

## 2) x-ui.card
- Amac: Icerik bolumu kapsulleme (title/subtitle/actions destekli).
- Ornek:
```blade
<x-ui.card title="Hata Ozeti" subtitle="Son hatalar">
  ...
</x-ui.card>
```
- Kullanim: tum admin ekranlarinin ana icerik bloklari.

## 3) x-ui.stat-card
- Amac: KPI/kisa metrik gostermek.
- Ornek:
```blade
<x-ui.stat-card label="Toplam Kullanici" :value="$count" />
```
- Kullanim: dashboard, groups.

## 4) x-ui.badge
- Amac: Durum/kategori etiketi (active, pending, error vb).
- Ornek:
```blade
<x-ui.badge status="active">Aktif</x-ui.badge>
```
- Kullanim: users, groups, logs, devices, force-updates, addin-config.

## 5) x-ui.button
- Amac: Standart buton varyantlari (`primary`, `secondary`, `danger`, `ghost`).
- Ornek:
```blade
<x-ui.button variant="secondary" type="button">Temizle</x-ui.button>
```
- Kullanim: tum admin ekranlari.

## 6) x-ui.input
- Amac: Tek satir metin/tarih/sayi girisi.
- Ornek:
```blade
<x-ui.input name="q" placeholder="Ara" />
```
- Kullanim: users, logs, devices, templates, deployment, groups, force-updates.

## 7) x-ui.select
- Amac: Secim alanlari.
- Ornek:
```blade
<x-ui.select name="status">
  <option value="">Durum</option>
</x-ui.select>
```
- Kullanim: users, logs, devices, templates, deployment, groups, force-updates.

## 8) x-ui.textarea
- Amac: Cok satir metin/HTML/text editor alanlari.
- Ornek:
```blade
<x-ui.textarea name="reason" rows="4"></x-ui.textarea>
```
- Kullanim: templates formu, force-updates, groups, deployment.

## 9) x-ui.table
- Amac: Tablo + mobilde card-list donusumu + footer slot.
- Ornek:
```blade
<x-ui.table>
  <x-slot name="head"><tr><th>Ad</th></tr></x-slot>
  <tr><td>Ornek</td></tr>
  <x-slot name="footer">{{ $rows->links() }}</x-slot>
</x-ui.table>
```
- Kullanim: users, logs, devices, dashboard hata listesi, force-updates, groups.

## 10) x-ui.empty-state
- Amac: Bos veri durumunu net mesaj + CTA ile gostermek.
- Ornek:
```blade
<x-ui.empty-state title="Kayit yok" description="Ilk kaydi olusturun." />
```
- Kullanim: users, templates, logs, devices, force-updates, groups, deployment, settings.

## 11) x-ui.drawer
- Amac: Sag/sol panel detay inceleme.
- Ornek:
```blade
<x-ui.drawer>
  ...detay...
</x-ui.drawer>
```
- Kullanim: logs, addin-devices.

## 12) x-ui.modal
- Amac: Overlay modal icerigi.
- Ornek:
```blade
<x-ui.modal :open="false">...</x-ui.modal>
```
- Kullanim: Hazir component, aktif admin ekranlarinda sinirli.

## 13) x-ui.tabs
- Amac: Tab icerik konteyneri.
- Ornek:
```blade
<x-ui.tabs>...</x-ui.tabs>
```
- Kullanim: Hazir component (kullanici detayinda custom tab yaklasimi var).

## 14) x-ui.alert
- Amac: Basari/uyari/hata/info mesajlari.
- Ornek:
```blade
<x-ui.alert type="success">Kaydedildi.</x-ui.alert>
```
- Kullanim: deployment, force-updates, dashboard, groups, settings.

---

## Kaldirilan / Birlestirilen Componentler

Birleştirilenler:
- `x-ui.section-card` -> `x-ui.card`
- `x-ui.status-badge` -> `x-ui.badge`
- `x-ui.data-table` -> `x-ui.table`

Gecis uyumlulugu:
- Yukaridaki eski component dosyalari wrapper olarak tutuldu (geriye donuk kirilim olmamasi icin), ancak admin view'larda ana kullanim yeni standart isimlere cekildi.

Ana standart disi (yardimci) componentler:
- `x-ui.app-shell`, `x-ui.dropdown`, `x-ui.code-block`, `x-ui.copy-button`, `x-ui.timeline`
- Bunlar simdilik operasyon ekranlarinda yardimci rolde tutuldu.

Aday deprecate/temizlik:
- `x-ui.error-state`, `x-ui.loading-state`, `x-ui.table-toolbar` (aktif kullanim sinirli/none)

---

## Hangi View'larda Kullanildigi (Ozet)

- `admin/dashboard.blade.php`:
  - page-header, card, stat-card, table, button, empty-state, alert, badge
- `admin/users/index.blade.php`:
  - page-header, card, input/select/button, table, badge, empty-state
- `admin/users/show.blade.php`:
  - page-header, card, button, table, badge, empty-state
- `admin/templates/form.blade.php`:
  - page-header, card, input/select/textarea/button
- `admin/assignments/index.blade.php`:
  - input/select/button + table/card yapisi (kademeli sadeleştirme)
- `admin/force-updates/index.blade.php`:
  - page-header, card, input/select/textarea/button, table, badge, alert, empty-state
- `admin/addin-devices/index.blade.php`:
  - page-header, card, input/select/button, table, badge, drawer, empty-state
- `admin/logs/index.blade.php`:
  - page-header, card, input/select/button, table, badge, drawer, empty-state
- `admin/addin-config/index.blade.php`:
  - page-header, card, input/select/textarea/button, badge, empty-state, alert
- `admin/groups/*` ve `admin/settings/index.blade.php`:
  - page-header, card, stat-card/input/select/textarea/button/table/alert/empty-state

---

## Notlar
- Bu adimda yeni ekran eklenmedi.
- Amaç, mevcut ekranlarda component adlandirma ve kullanim dilini sadeleştirmekti.
- Route/menu sadeleştirme adimindan kalan ekranlarla uyum korunmustur.
