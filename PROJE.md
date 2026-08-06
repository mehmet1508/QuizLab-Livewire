# Quiz Lab — Proje Dokümanı

## 1. Bu proje ne işe yarar

**Quiz Lab**, kullanıcıların kategori bazlı quizler çözebildiği, puan topladığı, sıralamalarda yarıştığı ve quizleri değerlendirebildiği bir Laravel + Livewire uygulaması. Bir admin paneli üzerinden quiz/soru/kategori/kullanıcı yönetimi yapılabiliyor.

Bu proje, `quiz.app` adlı klasik Laravel (Controller + Blade) mimarisiyle yazılmış bir uygulamanın, Livewire'ın **tek dosyalık component** (single-file component) mimarisine taşınmış hâlidir. Model ve migration'lar aynı, iş mantığı aynı — sadece controller+view ikilisi, tek bir `.blade.php` dosyasında birleşmiş PHP sınıfı + Blade şablonuna dönüştürüldü.

## 2. Teknoloji yığını

| Katman | Teknoloji |
|---|---|
| Backend framework | Laravel 13 (PHP 8.3) |
| Frontend/reaktivite | Livewire 4 (tek-dosya component'ler) |
| UI kütüphanesi | Flux UI 2 (`livewire/flux`) — hazır, tutarlı Blade component'leri |
| Kimlik doğrulama | Laravel Fortify (`laravel/fortify`) — login/register/2FA/passkey |
| Passkey desteği | `laravel/passkeys` |
| Veritabanı | MySQL (`livewire_quiz`) |
| Test | Pest |
| Kod stili | Laravel Pint |
| Yerel sunucu | Valet (valet-linux-plus) — `https://quiz-livewire.test` |
| Yerel e-posta yakalayıcı | Mailpit — `https://mails.test` |
| CSS | Tailwind CSS v4 (Vite ile derleniyor) |

## 3. Mimari — Livewire tek-dosya component (SFC) yaklaşımı

Klasik Laravel'de bir sayfa şu üçlüden oluşur: **Route → Controller metodu → ayrı Blade view dosyası**.

Bu projede bunun yerine:

```
Route::livewire('quizzes/{quiz}', 'pages::quizzes.show')->name('quizzes.show');
```

`resources/views/pages/quizzes/⚡show.blade.php` dosyasının **üst kısmı** eski controller metodunun yerini alan bir PHP sınıfı, **alt kısmı** ise eski Blade view'ıdır:

```php
<?php
// üst kısım = eski Controller
new class extends Component {
    public function mount(Quiz $quiz) { ... }   // eski show() metodunun mantığı
    public function submit() { ... }              // eski ayrı POST route'unun mantığı
}; ?>

<div>
    {{-- alt kısım = eski Blade view --}}
</div>
```

Dosya adındaki `⚡` işareti, Livewire'a "bu dosya tek-dosyalık bir component" olduğunu söyler — elle eklenmez, `php artisan make:livewire ... --sfc` komutu otomatik ekler.

**Önemli kural:** Bir controller metodu ayrı bir `view()` döndürmüyorsa (yani `redirect()` veya `back()` ile aynı sayfaya dönüyorsa), o metot ayrı bir component olmaz — hedef sayfanın component'i içine `public function` olarak gömülür. Örnek: eski `ResultController::submit()` ve `ReviewController::store()`, ayrı controller'lar olsa da kendi view'ları olmadığı için tamamen `⚡show.blade.php` ve `⚡sonuc.blade.php`'nin içine eridi.

## 4. Klasör yapısı (önemli noktalar)

```
app/
  Http/Middleware/IsAdmin.php       — admin paneli erişim kontrolü ('admin' middleware alias'ı)
  Models/                            — Quiz, Category, Question, Option, Result, Review, User, Permission
  Notifications/                     — YeniQuizEklendi, SiralamaGuncellendi
  Providers/AppServiceProvider.php   — Permission tablosundan dinamik Gate tanımları

resources/views/pages/                — Livewire "pages::" namespace'i buraya işaret eder
  ⚡dashboard.blade.php
  ⚡genel-siralama.blade.php
  quizzes/                            — kullanıcı tarafı: index, show, sonuc, leaderboard
  admin/
    categories/                       — tek component (liste + ekle + sil bir arada)
    users/                            — index, create, edit (3 ayrı sayfa)
    quizzes/                          — index, create, edit, leaderboard, puanlar
    questions/                        — index, create, edit, pool, import, create-global
  settings/                           — starter kit'ten gelen profil/güvenlik/görünüm sayfaları
  auth/                                — starter kit'ten gelen login/register/2FA sayfaları

routes/
  web.php                             — genel + quiz + admin route'ları
  settings.php                        — profil/güvenlik/2FA route'ları
```

## 5. Veri modeli

- **Quiz** — `baslik`, `aciklama`, `sure_dakika/tipi/birimi`, `gorsel`, `kategori_id`. `questions()` (many-to-many, pivot'ta `puan`), `results()`, `reviews()`, `kategori()`. `getEfektifPuanlar()` — sorulara otomatik/manuel puan dağıtımı hesaplar.
- **Category** — `ad`. `quizzes()`.
- **Question** — `soru_metni`, `puan`. `options()`, `quizzes()` (pivot).
- **Option** — `sik_metni`, `dogru_mu`. `question()`.
- **Result** — bir kullanıcının bir quiz'i bitirdiğinde oluşan kayıt: `puan`, `sure_saniye`. `enIyiSiralama()` gibi sıralama yardımcıları burada.
- **Review** — quiz değerlendirmesi: `puan` (1-5 yıldız), `yorum`.
- **Permission** — admin altındaki ince yetkiler (`anahtar`, `etiket`); `AppServiceProvider` her birini bir Laravel Gate'ine çevirir.
- **User** — starter kit'in tüm özellikleri (2FA, Passkey, e-posta doğrulama) + projeye özel `permissions()`, `results()`, `reviews()`, `hasPermission()`, `is_admin`.

## 6. Özellikler

**Kullanıcı tarafı**
- Ana sayfa (`/`) ve `/quizzes` — arama + kategori filtresiyle quiz listesi.
- Quiz çözme akışı: giriş ekranı → "Başla" → süreli/süresiz soru-cevap → bitir → sonuç ekranı → yıldızla değerlendirme.
- Quiz bazlı ve genel sıralama (leaderboard) sayfaları.
- Bildirimler: yeni quiz eklendiğinde ve bir quiz'in sıralaması değiştiğinde (Livewire ile anlık, sidebar'daki zil ikonundan).

**Admin paneli** (`/admin/...`, `is_admin` veya ilgili `Permission` gerektirir)
- Kategori, kullanıcı, quiz, soru yönetimi (CRUD).
- Soru havuzu, örnek dosyayla toplu soru içe aktarma.
- Quiz bazlı puan dağıtımı ve sıralama görüntüleme.

**Hesap/güvenlik** (starter kit'ten gelir, değiştirilmedi)
- E-posta/şifre girişi, e-posta doğrulama, şifre sıfırlama.
- İki faktörlü doğrulama (2FA) ve Passkey (parolasız giriş) desteği.

## 7. Yerel geliştirme ortamı

- Site: `https://quiz-livewire.test` (Valet ile servis ediliyor, `valet-linux-plus`).
- Veritabanı: MySQL, `livewire_quiz` (`.env`'de `DB_*`).
- E-posta: gerçek gönderilmiyor, **Mailpit** yakalıyor → `https://mails.test` adresinden okunur (`.env`: `MAIL_MAILER=smtp`, `MAIL_PORT=1025`).
- `storage/app/public` ↔ `public/storage` sembolik linki kurulu (yüklenen quiz görselleri için).

## 8. Sık kullanılan komutlar

```bash
# Kod stilini otomatik düzelt (her PHP değişikliğinden sonra)
vendor/bin/pint --dirty --format agent

# Testleri çalıştır
php artisan test --compact

# Yeni bir Livewire sayfası oluştur (tek dosyalık component)
php artisan make:livewire pages::klasor.isim --sfc --no-interaction

# Route listesini gör
php artisan route:list

# Frontend derle (CSS/JS değişikliğinden sonra ŞART, yoksa yeni class'lar görünmez)
npm run build
# veya geliştirirken arka planda açık tutun (otomatik derler):
npm run dev

# Blade syntax hatası var mı kontrol et (sonra view:clear ile temizle)
php artisan view:cache && php artisan view:clear
```

## 9. Route haritası (özet)

| Route adı | URL | Açıklama |
|---|---|---|
| `home`, `quizzes.index` | `/`, `/quizzes` | Quiz listesi (herkese açık) |
| `quizzes.show` | `/quizzes/{quiz}` | Giriş ekranı + quiz çözme (auth) |
| `quizzes.sonuc` | `/quizzes/{quiz}/sonuc/{result}` | Sonuç + değerlendirme (auth) |
| `quizzes.leaderboard` | `/quizzes/{quiz}/leaderboard` | Quiz bazlı sıralama (auth) |
| `dashboard` | `/dashboard` | Kullanıcı paneli (auth + verified) |
| `genel-siralama` | `/genel-siralama` | Genel sıralama (auth) |
| `admin.*` | `/admin/...` | Kategori/kullanıcı/quiz/soru yönetimi (auth + admin) |
| `profile.edit`, `security.edit`, `appearance.edit` | `/settings/...` | Hesap ayarları (auth) |
