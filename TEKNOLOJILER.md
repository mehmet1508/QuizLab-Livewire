# Kullanılan Teknolojiler, Araçlar ve Sürümler

Bu doküman, `quiz-livewire` projesinde kullanılan tüm diller, framework'ler, kütüphaneler ve yerel geliştirme araçlarının **bu makinede kurulu tam sürümlerini** listeler (Ağustos 2026 itibarıyla).

## Diller ve Çalışma Zamanları

| Araç | Sürüm |
|---|---|
| PHP | 8.3.6 |
| Node.js | 22.20.0 |
| npm | 10.9.3 |

## Backend — Framework ve Ana Paketler (Composer)

| Paket | Sürüm | Ne işe yarar |
|---|---|---|
| `laravel/framework` | 13.24.0 | Ana framework |
| `livewire/livewire` | 4.3.5 | Reaktif, tek-dosya component mimarisi |
| `livewire/flux` | 2.15.0 | Hazır UI component kütüphanesi (`flux:*` etiketleri) |
| `livewire/blaze` | 1.0.14 | Blade component performans optimizasyonu |
| `laravel/fortify` | 1.37.3 | Kimlik doğrulama iskeleti (login, 2FA, e-posta doğrulama) |
| `laravel/passkeys` | 0.2.1 | Passkey/WebAuthn (parolasız giriş) desteği |
| `maatwebsite/excel` | 3.1.69 | Soru içe/dışa aktarma (Excel/CSV) |

## Backend — Geliştirme Araçları (composer require-dev)

| Paket | Sürüm | Ne işe yarar |
|---|---|---|
| `pestphp/pest` | 4.7.8 | Test framework |
| `pestphp/pest-plugin-laravel` | 4.1.0 | Pest'in Laravel eklentisi |
| `laravel/pint` | 1.30.3 | Kod stili otomatik düzeltici |
| `larastan/larastan` | 3.10.0 | Statik analiz (PHPStan tabanlı) |
| `laravel/boost` | 2.5.0 | AI destekli geliştirme MCP sunucusu |
| `laravel/pail` | 1.2.7 | Canlı log takibi |
| `laravel/tinker` | 3.0.2 | REPL / interaktif konsol |
| `laravel/sail` | 1.65.0 | Docker tabanlı geliştirme ortamı (bu projede aktif kullanılmıyor, Valet tercih edildi) |
| `laravel/chisel` | 0.1.1 | Betik/script araçları |
| `laravel/pao` | 1.1.3 | Test çıktısını AI-dostu formatta sunar |
| `mockery/mockery` | 1.6.12 | Test mock kütüphanesi |
| `fakerphp/faker` | 1.24.1 | Sahte veri üretici (factory'lerde) |
| `nunomaduro/collision` | 8.9.5 | Gelişmiş hata/exception ekranı |

## Frontend (npm)

| Paket | Sürüm | Ne işe yarar |
|---|---|---|
| `vite` | 8.2.0 | Build aracı / dev server |
| `laravel-vite-plugin` | 3.1.3 | Laravel–Vite entegrasyonu |
| `tailwindcss` | 4.3.3 | Utility-first CSS framework |
| `@tailwindcss/vite` | 4.3.3 | Tailwind'in Vite eklentisi |
| `@laravel/passkeys` | 0.2.0 | Passkey için tarayıcı tarafı JS |
| `concurrently` | 9.2.4 | Birden fazla dev komutunu paralel çalıştırma |

## Veritabanı ve Depolama

| Araç | Sürüm | Kullanım |
|---|---|---|
| MySQL | 8.0.46 (Ubuntu) | Ana veritabanı (`livewire_quiz`) |
| Redis | 7.0.15 | Kurulu (Valet ile geldi), şu an session/queue için kullanılmıyor (onlar `database` sürücüsünde) |

## Yerel Geliştirme Ortamı (Sunucu Araçları)

| Araç | Sürüm | Kullanım |
|---|---|---|
| Valet (ValetLinux+) | 2.0.3 | Yerel `.test` alan adlarıyla siteleri servis etme |
| nginx | 1.24.0 (Ubuntu) | Web sunucusu (Valet arka planda kullanır) |
| php8.3-fpm | 8.3.6 | PHP FastCGI süreç yöneticisi |
| dnsmasq | 2.90 | Yerel `.test` alan adı çözümleme |
| Mailpit | 1.30.6 | Yerel e-posta yakalayıcı — gönderilen tüm e-postalar `https://mails.test` adresinde görünür |
| Composer | 2.10.2 | PHP bağımlılık yöneticisi |

## Notlar

- Sürümler `composer.lock`, `package-lock.json` ve ilgili CLI araçlarının `--version` çıktılarından alınmıştır — `composer.json`/`package.json`'daki `^` aralıkları değil, **gerçekte kurulu olan** tam sürümlerdir.
- Bu dosya bir anlık görüntüdür (Ağustos 2026); `composer update` / `npm update` sonrası sürümler değişebilir, güncel hâlini görmek için:
  ```bash
  composer show --direct
  npm list --depth=0
  ```
