# Kurulum Rehberi — Projeyi Sıfırdan Ayağa Kaldırma

Bu doküman, bu projeyi (veya bir kopyasını) **başka/temiz bir Linux makinede** sıfırdan çalışır hâle getirmek için gereken tüm komutları sırasıyla listeler. Sürüm detayları için [TEKNOLOJILER.md](TEKNOLOJILER.md)'ye, bir adımda hata alırsanız [OGRENME-NOTLARIM.md](OGRENME-NOTLARIM.md)'deki "Valet / sunucu kurulumunda çıkan sorunlar" bölümüne bakın.

laravel new <uygulama adi>

## 0. Genel bakış

```
Sistem paketleri → Valet (yerel sunucu) → Proje dosyaları → Bağımlılıklar →
.env + veritabanı → migration → storage link → Valet'e bağla → frontend derle
```

---

## 1. Sistem paketleri (bir kerelik, OS seviyesinde)

```bash
sudo apt update
sudo apt install -y php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml \
    php8.3-mysql php8.3-sqlite3 php8.3-curl php8.3-gd \
    nginx mysql-server redis-server dnsmasq-base xsel unzip curl \
    network-manager
```

**Composer:**
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

**Node.js** (nvm ile önerilir, sürüm için TEKNOLOJILER.md'ye bakın):
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.1/install.sh | bash
nvm install 22
```

---

## 2. Valet kurulumu (yerel `.test` sunucusu)

Laravel Valet macOS'a özeldir; Linux'ta **valet-linux-plus** fork'unu kullanıyoruz. Global composer kurulumlarınızla (ör. `laravel/installer`) sürüm çakışmasın diye **izole bir COMPOSER_HOME** ile kuruyoruz:

```bash
mkdir -p ~/.config/valet-linux-plus
COMPOSER_HOME=~/.config/valet-linux-plus composer global require genesisweb/valet-linux-plus
echo 'export PATH="$HOME/.config/valet-linux-plus/vendor/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

Kurulumu başlat (kullanıcının gerçek `HOME`'unu korumak için `sudo env` ile, `sudo` PATH'i sıfırladığı için de aynı şekilde):

```bash
sudo env HOME=$HOME PATH=$PATH valet install
```

> Bu adımda "http2" nginx hatası, DNS/`resolv.conf` bozulması gibi sorunlarla karşılaşabilirsiniz — hepsinin çözümü [OGRENME-NOTLARIM.md](OGRENME-NOTLARIM.md)'de var.

---

## 3. Proje dosyalarını edinme

```bash
cd ~/Desktop/code
git clone https://github.com/mehmet1508/QuizLab-Livewire.git quiz-livewire
cd quiz-livewire
```

---

## 4. Bağımlılıkları kurma

```bash
composer install
npm install
```

---

## 5. Ortam dosyası (`.env`)

```bash
cp .env.example .env
php artisan key:generate
```

Sonra `.env`'de şu değerleri düzenleyin:

```env
APP_NAME="Quiz Lab"
APP_URL=https://quiz-livewire.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=livewire_quiz
DB_USERNAME=root
DB_PASSWORD=            # kendi MySQL şifreniz

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025          # Mailpit (Valet ile kurulu geliyor)
```

---

## 6. Veritabanını oluşturma

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS livewire_quiz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## 7. Migration'ları çalıştırma

```bash
php artisan migrate
```

---

## 8. Görsel yükleme için depolama bağlantısı

```bash
php artisan storage:link
```

---

## 9. Siteyi Valet'e bağlama

```bash
valet link quiz-livewire
```

Artık `https://quiz-livewire.test` adresinden erişilebilir.

---

## 10. Frontend'i derleme

```bash
npm run build
```

Geliştirme sırasında (dosya değiştikçe otomatik derlemesi için):
```bash
npm run dev
```

---

## 11. Doğrulama

```bash
vendor/bin/pint --format agent      # kod stili
php artisan test --compact          # testler (41/41 geçmeli)
php artisan route:list              # route'lar doğru kayıtlı mı
```

Tarayıcıdan `https://quiz-livewire.test` adresini açın. İlk seferde Valet'in kendi imzaladığı SSL sertifikası için tarayıcı uyarı verebilir — yerel geliştirme sertifikası olduğu için "Advanced → Proceed" ile geçebilirsiniz.

---

## Tek komutluk özet (her şey hazırsa)

```bash
cd ~/Desktop/code/quiz-livewire
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan storage:link
valet link quiz-livewire
npm run build
php artisan test --compact
```
