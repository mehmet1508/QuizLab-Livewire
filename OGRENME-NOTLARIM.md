# Öğrenme Notlarım — `quiz.app` → `quiz-livewire` Taşıma Süreci

Bu dosya, eski projeyi (klasik Controller+Blade) Livewire'ın tek-dosya component mimarisine taşırken öğrendiklerimin kişisel özeti. Kod detayı için `PROJE.md`'ye bakın — burası daha çok "yöntem ve tuzaklar" notu.

## Genel yöntem — bir controller'ı nasıl taşırım?

1. **Eski controller'ı aç, her metodun `return` ettiği şeye bak.**
   - `return view(...)` → bu metot **ayrı bir sayfa**, kendi Livewire component'i olacak.
   - `return redirect()->route(...)` (aynı sayfaya) veya `return back()` → bu metot ayrı component **olmaz**, hedef sayfanın component'inin içine `public function` olarak gömülür.

2. **Component iskeletini oluştur:**
   ```bash
   php artisan make:livewire pages::klasor.isim --sfc --no-interaction
   ```

3. **PHP tarafını taşı:**
   - Route parametreleri (`Quiz $quiz`) → `mount()` metoduna.
   - View'a `compact(...)` ile giden veri → `#[Computed]` işaretli bir metoda (Blade'de `$this->isim` ile okunur).
   - **UYARI:** `render()` metodu yazıp içinde `view('pages::...', [...])` çağırmayın! `pages::` namespace'i sadece Livewire'ın kendi `⚡` dosya bulucusu tanır, düz `view()` çağrısı tanımaz ve "View not found" hatası verir. Veri her zaman `#[Computed]` üzerinden gitmeli.
   - POST/PUT/DELETE action'ları (`store`, `update`, `destroy` vb.) → aynı class içinde `public function` (örn. `ekle()`, `sil()`, `guncelle()`).

4. **Blade tarafını taşı:**
   - `<x-app-layout>` sarmalayıcısını kaldırın — Livewire sayfa component'leri otomatik `layouts::app`'a sarılıyor.
   - `<form action method="POST"> @csrf` → `<form wire:submit="metotAdi">`.
   - `name="alan"` → `wire:model="property"`.
   - Silme formu (`method="DELETE"` + `confirm()`) → `wire:click="sil({{ $id }})" wire:confirm="Emin misiniz?"`.
   - `$this->authorize('yetki')` Livewire component'lerde **hazır gelir** (`AuthorizesRequests` trait built-in), elle `use` etmeye gerek yok.

5. **Route'u güncelle:**
   ```php
   Route::livewire('yol/{param}', 'pages::klasor.isim')->name('isim');
   ```
   Middleware gruplarını eski dosyadan **satır satır** kontrol edin — kopyala-yapıştırda yanlış gruba düşürmek kolay (bende `quizzes.show`'u yanlışlıkla `verified` middleware'ine sokmuştum, eskisinde sadece `auth` vardı).

6. **Test döngüsü:**
   ```bash
   vendor/bin/pint --dirty --format agent
   php artisan view:cache   # syntax hatası var mı
   php artisan view:clear
   npm run build            # YENİ Tailwind class kullandıysanız ŞART, yoksa CSS'e girmez
   php artisan test --compact
   ```
   Sonra mutlaka tarayıcıdan tıklayarak da deneyin — log/curl, `wire:click`/`wire:model` etkileşimini göstermez.

## Karşılaştığım en büyük tuzaklar

1. **Bir model/dosyayı eski projeden birebir kopyalamak, yeni projenin kendi ihtiyaçlarını siler.** `User.php`'yi taşırken `initials()`, 2FA (`TwoFactorAuthenticatable`), Passkey (`PasskeyAuthenticatable`), `MustVerifyEmail` gibi starter-kit'e özel parçalar gitmiş, dashboard/settings sayfaları çökmüştü. **Ders:** Bir dosyayı taşırken sadece *kendi eklediğiniz* kısımları (ilişkiler, özel metotlar) alın, dosyanın geri kalanını olduğu gibi bırakın ya da elle birleştirin.

2. **Bir controller'ın `$this->authorize(...)` çağırdığını gördüğünüzde, arkasındaki altyapıyı da kontrol edin.** `AppServiceProvider`'daki dinamik `Gate::define(...)` döngüsü (Permission tablosundan) de taşınmamıştı — controller'ı taşımak yetmedi, o servis sağlayıcı kodu da lazımdı.

3. **Statik asset'ler (görsel, favicon, storage linki) migration'larla gelmez, elle taşınmalı.** `public/images/...`, `favicon.ico`, `php artisan storage:link` — hiçbiri kod taşırken otomatik gelmiyor, ayrı ayrı akla gelmesi lazım.

4. **Eski dosyaların uzantısı yalan söyleyebilir.** Eski projedeki `favicon.ico` aslında JPEG içerikti (biri dosyayı yanlış uzantıyla kaydetmiş). `file dosyaadı` komutuyla gerçek formatı kontrol etmeden taşımak, nginx'in yanlış `Content-Type` ile sunup tarayıcının reddetmesine yol açtı.

5. **CSS/Tailwind class'ları anlık görünmez.** Yeni bir Blade dosyasında ilk kez kullandığınız her Tailwind class'ı, `npm run build` (ya da arka planda `npm run dev`) çalıştırılana kadar derlenmiş CSS'e girmez — sayfa "bozuk" görünür ama kod doğrudur.

## Valet / sunucu kurulumunda çıkan sorunlar (özet)

Sırasıyla yaşadıklarımız ve çözümleri — aynı hatayı tekrar görürsem diye:

- `composer global require cpriego/valet-linux` → sürüm çakışması → **`genesisweb/valet-linux-plus`**'a geçtik, ayrı bir `COMPOSER_HOME` ile izole kurduk (global `laravel/installer` ile çakışmasın diye).
- `sudo valet install` → `sudo` PATH'i sıfırladığı için komut bulunamadı → tam yol (`sudo ~/.config/valet-linux-plus/vendor/bin/valet`) veya `sudo env "PATH=$PATH" valet ...` kullanmak gerekti.
- Eksik sistem paketleri: `xsel`, `nginx`, `php8.3-fpm` — `valet install` bunları kurmuyor, önceden kurulu olmalarını bekliyor.
- "Valet home directory is inside /root" hatası → `sudo`, `HOME`'u `/root`'a çeviriyor → `sudo env HOME=$HOME PATH=$PATH valet install` ile gerçek kullanıcının home'unu korumak gerekti.
- `valet install` sonrası **tüm internet DNS'i bozuldu** → `dnsmasq` kurulumu `systemd-resolved`'ı durdurdu ama `/etc/resolv.conf` hâlâ ona işaret ediyordu → `/etc/resolv.conf`'u `127.0.0.1`'e (dnsmasq) çevirip `systemd-resolved`'ı kalıcı kapattık.
- nginx başlamıyordu: nginx 1.24, Valet'in ürettiği bazı config dosyalarındaki **yeni** `http2 on;` sözdizimini desteklemiyordu (bu sözdizimi nginx 1.25.1+ ister) → eski `listen 443 ssl http2;` sözdizimine çevirdik.
- E-posta doğrulama isteği geliyor ama mail gelmiyordu → `.env`'de `MAIL_MAILER=log` kalmıştı (hiçbir yere gitmiyor) → `smtp` + `MAIL_PORT=1025` yapıp zaten kurulu olan **Mailpit**'e bağladık (`https://mails.test`'ten okunuyor).

## Faydalı komut özetleri

```bash
# Yeni Livewire sayfası
php artisan make:livewire pages::klasor.isim --sfc --no-interaction

# Route'ları listele / filtrele
php artisan route:list --path=admin

# Veritabanını hızlı kontrol
php artisan tinker --execute='echo App\Models\Quiz::count();'

# Nginx config'i test et (Valet ile ilgili sorunlarda)
sudo nginx -t && sudo systemctl restart nginx

# Mailpit'e giden son e-postaları API'den gör
curl -sk https://mails.test/api/v1/messages
```
