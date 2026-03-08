# Insider One Champions League Simulation

<img width="1645" height="1262" alt="image" src="https://github.com/user-attachments/assets/c00e6734-509c-4aca-a1ac-54fcb13b3da5" />

Bu proje, dört futbol takımından oluşan bir Şampiyonlar Ligi grup aşamasının tam kapsamlı bir simülasyonudur. Proje, hem Backend (Laravel) hem de Frontend (Vue.js) katmanlarını içeren, modern bir "Decoupled" (Ayrık) mimariyle geliştirilmiştir.

## ✨ Temel Özellikler

- **Dinamik Maç Motoru:** Maç sonuçları basit bir `rand()` fonksiyonuyla değil; takımların hücum/savunma güçleri, ev sahibi avantajı, anlık form durumları ve istatistiksel **Poisson Dağılımı** algoritması kullanılarak hesaplanır.
- **Bayesian Öğrenme Modeli:** Takımlar, her maçtan sonra gösterdikleri performansa göre (beklenenden iyi veya kötü) dinamik olarak güçlenir veya zayıflar.
- **Monte Carlo Tahmin Sistemi:** 4. haftadan itibaren, kalan maçlar **5000 kez** sanal olarak simüle edilerek takımların şampiyonluk ihtimalleri yüzdesel olarak hesaplanır.
- **Tam Etkileşimli Arayüz:**
  - Maçları hafta hafta veya tek tıkla ("Play All") oynatma.
  - Oynanmış maçların skorlarını **manuel olarak düzenleme** ve puan tablosunun anında güncellenmesi.
  - Lig durumunu tek tuşla başlangıç ayarlarına sıfırlama ("Reset").
- **Profesyonel UI/UX:**
  - Puan tablosunda takımların son 5 maçlık form durumunu gösteren renkli ikonlar (W-D-L).
  - Şampiyonluk ihtimallerini gösteren animasyonlu ilerleme çubukları (Progress Bars).
  - Tüm API isteklerinde kullanıcıya geri bildirim sağlayan zarif, buton içi yükleme animasyonları.
- **Mimarî Özellikler:**
  - **Single Source of Truth:** Puan tablosu veritabanında tutulmaz, her seferinde maç sonuçlarından hesaplanır. Bu, veri tutarlılığını garanti altına alır ve "Edit Match" gibi özellikleri basitleştirir.
  - **Component Design Pattern:** Frontend, tekrar kullanılabilir ve yönetimi kolay bileşenlere (LeagueTable, WeeklyFixture vb.) ayrılmıştır.
  - **OOP ve Servis Katmanı:** Backend'deki tüm iş mantığı (Business Logic), Controller'lardan ayrı, test edilebilir Servis katmanlarında (LeagueService, MatchEngineService) toplanmıştır.
  - **Unit Testler:** Projenin çekirdek işlevlerinin (Fikstür oluşturma, Puan hesaplama, Tahmin doğruluğu) doğru çalıştığını garanti eden otomatik testler içerir.

## 🛠️ Kullanılan Teknolojiler

- **Backend:** PHP (Laravel)
- **Frontend:** JavaScript (Vue.js)
- **Veritabanı:** MySQL
- **Geliştirme Ortamı:** Docker
- **Test:** PHPUnit

## 🚀 Projeyi Çalıştırma

Bu proje, tüm bağımlılıkları izole bir ortamda yönetmek için **Docker** kullanır. Bilgisayarınıza PHP, Composer veya Node.js kurmanıza gerek yoktur.

### Ön Gereksinimler
- [Docker Desktop](https://www.docker.com/products/docker-desktop/)'ın bilgisayarınızda kurulu ve çalışır durumda olması gerekmektedir.

### Kurulum Adımları

1.  **Projeyi Klonla:**
    ```bash
    git clone https://github.com/doclock4715/insider-champions-league.git
    cd insider-champions-league
    ```

2.  **Backend'i Başlat (Laravel API):**
    Yeni bir terminal açın ve `backend` klasörüne gidin.
    ```bash
    cd backend
    ```
    İlk olarak `.env` dosyasını oluşturun:
    ```bash
    cp .env.example .env
    ```
    Docker konteynerlerini ayağa kaldırın:
    ```bash
    docker compose up -d
    ```
    Gerekli bağımlılıkları kurun ve veritabanını hazırlayın:
    ```bash
    docker compose exec laravel.test composer install
    docker compose exec laravel.test php artisan key:generate
    docker compose exec laravel.test php artisan migrate:fresh --seed
    ```
    Backend API'si şimdi `http://localhost/api` adresinde çalışıyor olmalıdır.

3.  **Frontend'i Başlat (Vue.js):**
    Yeni bir terminal daha açın ve `frontend` klasörüne gidin.
    ```bash
    cd frontend
    ```
    Gerekli bağımlılıkları (Node modüllerini) Docker üzerinden kurun:
    ```bash
    docker run --rm -v ${PWD}:/app -w /app node:20-alpine npm install
    ```
    Geliştirme sunucusunu başlatın:
    ```bash
    docker run -it --rm -v ${PWD}:/app -w /app -p 5180:5180 node:20-alpine npm run dev
    ```
    Uygulama şimdi tarayıcınızda **`http://localhost:5180`** adresinde çalışıyor olacak!

### 🧪 Testleri Çalıştırma
Backend testlerini çalıştırmak için `backend` klasöründeyken şu komutu girin:
```bash
docker compose exec laravel.test php artisan test
```

## 🧠 Kaynaklar ve İlham

Bu projenin geliştirilmesi sırasında, konsept oluşturma, algoritma tasarımı ve kodlama süreçlerinde aşağıdaki yapay zeka araçlarından ve kaynaklardan aktif olarak yararlanılmıştır:

-   **OpenAI ChatGPT:** Kodlama ve algoritma mantığı üzerine beyin fırtınası, "boilerplate" kod üretimi ve hata ayıklama (debugging) için kullanılmıştır.
-   **Google Gemini:** Proje mimarisi, en iyi pratikler (best practices) ve alternatif algoritma yaklaşımları konusunda danışmanlık sağlamıştır.
-   **Google NotebookLM:** Proje dokümantasyonunu, teknik gereksinimleri ve geliştirme notlarını organize etmek, özetlemek ve slayt metinleri hazırlamak için kullanılmıştır.
    -   [Geliştirme Notları 1](https://notebooklm.google.com/notebook/a7c3f2d8-b3a4-4941-a4b1-0d5640ae479c)
    -   [Geliştirme Notları 2](https://notebooklm.google.com/notebook/93931642-ca84-4adb-8137-1fa935679145)
    -   [Proje Slaytı](https://notebooklm.google.com/notebook/e1744ffa-545b-425f-afe7-bc520bf43eaa?artifactId=eeeb6322-1d7b-4f89-a7d1-373de1f2836c)

Bu araçlar, projenin daha hızlı ve daha yüksek kalitede tamamlanmasında önemli bir rol oynamıştır.
