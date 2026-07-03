# Vita Systems — Backend Case

Laravel 12 tabanlı REST API. JWT authentication, ürün arama ve sipariş oluşturma iş akışları; katmanlı ve SOLID prensiplerine uygun bir mimari üzerine kuruludur. API dokümantasyonu Scramble ile OpenAPI/Swagger olarak otomatik üretilir.

## Teknoloji Yığını

| Alan | Tercih |
|------|--------|
| Framework | Laravel 12 (PHP 8.3) |
| Authentication | JWT (`php-open-source-saver/jwt-auth`) |
| DTO / Request-Response | Spatie Laravel Data |
| API Dokümantasyonu | Scramble (OpenAPI 3) |
| Veritabanı | SQLite (case baz veritabanı) |
| Test | Pest 4 |
| Konteyner | Docker / Docker Compose |

## Mimari ve Tasarım Desenleri

Kod, her katmanın tek bir sorumluluğa sahip olduğu bir yapı üzerine kuruludur. Kullanılan desenler ve SOLID karşılıkları:

| Desen | Konum | SOLID |
|-------|-------|-------|
| **Repository Pattern** | `App\Repositories\Contracts` (interface) + `App\Repositories\Eloquent` (implementasyon) | Dependency Inversion — controller/action somut Eloquent'e değil arayüze bağımlıdır |
| **Action Pattern** | `App\Actions\*` — her use-case tek `__invoke` metodu | Single Responsibility |
| **DTO Pattern** | `App\Data\*` (Spatie Laravel Data) — validation'lı request + tip güvenli response | Interface Segregation — net veri kontratları |
| **Observer Pattern** | `App\Observers\*` — `uuid` ve `reference_no` model oluşturulurken otomatik atanır | Open/Closed — model kodu değişmeden davranış eklenir |
| **IoC Container Binding** | `App\Providers\RepositoryServiceProvider` | Dependency Inversion |
| **Strateji ayrımı** | `App\Support\ReferenceNumberGenerator` — benzersiz referans üretimi izole | Single Responsibility |

### İstek yaşam döngüsü (sipariş oluşturma örneği)

```
Route → CreateOrderData (Spatie Data, validation)
      → OrderController (ince)
      → CreateOrderAction (transaction + iş kuralı: fiyat & total_price hesabı)
      → OrderRepository / ProductRepository (kalıcılık)
      → OrderData (tip güvenli response)
```

Controller'lar iş mantığı içermez; yalnızca doğrulanmış DTO'yu ilgili Action'a iletir ve dönen modeli response DTO'suna çevirir.

### Veri tutarlılığı

Sipariş oluşturma tek bir `DB::transaction` içinde yürütülür. Sipariş başlığı, ürün satırları ve `orders.total_price` toplamı atomik olarak yazılır; herhangi bir satırda hata olursa tüm işlem geri alınır. Ürün fiyatları tek bir `whereIn` sorgusuyla toplu çekilir (N+1 sorgu önlenir).

### Hesaplama kuralı

```
total_price = price * (1 - (discount / 100)) * quantity
```

`price` her zaman `products.price` kolonundan alınır (istemciden gelen fiyata güvenilmez). Siparişin toplamı, tüm satır toplamlarının toplamıdır.

## Klasör Yapısı

```
app/
├── Actions/            Use-case sınıfları (Auth, Products, Orders)
├── Data/               Spatie Data DTO'ları (request + response)
├── Exceptions/         Alan (domain) istisnaları
├── Http/Controllers/   İnce controller'lar
├── Models/             Eloquent modelleri
├── Observers/          Model event dinleyicileri (uuid, reference_no)
├── Providers/          RepositoryServiceProvider (arayüz bağlama)
├── Repositories/       Contracts + Eloquent implementasyonları
└── Support/            ApiResponse, ReferenceNumberGenerator
```

## Kurulum

### Yöntem 1 — Yerel (PHP 8.3 + Composer)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan serve
```

> `pdo_sqlite` eklentisi gereklidir: `sudo apt-get install -y php8.3-sqlite3`

### Yöntem 2 — Docker

```bash
docker compose up --build
```

Uygulama `http://localhost:8000` üzerinde çalışır (imaj `pdo_sqlite` dahil tüm eklentileri içerir).

## Veritabanı

Case'in sağladığı `database/database.sqlite` dosyası `users`, `products`, `manufacturers` tablolarını hazır içerir. Migration'lar bu dosyaya `orders` ve `order_products` tablolarını ekler ve `products(name)`, `products(code)` kolonlarına arama performansı için index tanımlar. Mevcut tabloların migration'ları `Schema::hasTable` ile korumalıdır; gerçek veriye dokunmaz, yalnızca test ortamında (bellek içi SQLite) şemayı kurar.

> Uyarı: Gerçek veritabanında `migrate:fresh` çalıştırmayın — sağlanan veri silinir. Normal `migrate` güvenlidir.

Örnek kullanıcı: `john@doe.com` / `vita123`

## Endpoint'ler

| Method | Path | Auth | Açıklama |
|--------|------|------|----------|
| POST | `/api/auth/login` | — | JWT token döner |
| GET | `/api/auth/me` | JWT | Giriş yapmış kullanıcı |
| POST | `/api/auth/logout` | JWT | Token geçersiz kılınır |
| GET | `/api/products?search=lc1d` | JWT | `name`/`code` üzerinde arama (min 3 karakter) |
| POST | `/api/orders` | JWT | Sipariş + ürün satırları oluşturur |
| GET | `/api/orders` | JWT | Kullanıcının siparişleri |

Hatalı istekler tutarlı bir zarf ile döner: `{ "status": false, "message": "...", "errors": { ... } }`.

## API Kullanım Detayları

### Ürün Arama — `GET /api/products`

| Query param | Zorunlu | Varsayılan | Açıklama |
|-------------|---------|-----------|----------|
| `search` | Evet | — | Aranacak metin. **En az 3 karakter.** `name` ve `code` kolonlarında kısmi (substring) arama yapılır. |
| `per_page` | Hayır | `15` | Sayfa başına sonuç sayısı (1–100 arası). |

- `search` 3 karakterden kısa ise **422** ve `"Arama terimi en az 3 karakter olmalıdır."` döner.
- Sonuçlar sayfalanır; örn. `?search=lc1d&per_page=50`.
- Örnek: `?search=lc1d` → `code`/`name` içinde "lc1d" geçen ürünler (Schneider TeSys D kontaktör kodları gibi).

### Sipariş Oluşturma — `POST /api/orders`

Request gövdesi ve validation kuralları:

| Alan | Kural |
|------|-------|
| `products` | Zorunlu dizi, en az 1 satır |
| `products[].product_uuid` | Zorunlu, geçerli UUID, `products` tablosunda var olmalı |
| `products[].quantity` | Zorunlu, tam sayı, en az 1 |
| `products[].discount` | Zorunlu, sayı, **0–100 arası** |

- `price` istemciden alınmaz; ilgili ürünün `products.price` değerinden okunur.
- `total_price = price * (1 - discount/100) * quantity` (2 ondalığa yuvarlanır).
- Siparişin `total_price`'ı tüm satır toplamlarının toplamıdır.
- Başarılı yanıt **201** döner.

### Sipariş Listeleme — `GET /api/orders`

| Query param | Zorunlu | Varsayılan | Açıklama |
|-------------|---------|-----------|----------|
| `per_page` | Hayır | `15` | Sayfa başına sipariş sayısı (max 100). |

### Kimlik Doğrulama

Korumalı tüm uçlar `Authorization: Bearer <token>` başlığı ister. Token yoksa/geçersizse **401** döner. Token, `POST /api/auth/login` cevabındaki `token` alanından alınır.

## Swagger / OpenAPI

Scramble, dokümantasyonu route'lardan ve DTO tiplerinden otomatik üretir:

- Arayüz: `http://localhost:8000/docs/api`
- Ham JSON: `http://localhost:8000/docs/api.json`

Request ve response şemaları doğrudan Spatie Data DTO'larından türetildiği için, dokümandaki tipler API'nin gerçekte döndürdüğü tiplerle birebir aynıdır.

## Postman

`postman/VitaSystems.postman_collection.json` içe aktarılabilir. `base_url` varsayılanı `http://localhost:8000/api`.

Koleksiyon, elle kopyalama gerektirmeyecek şekilde otomatikleştirilmiştir:

- **Login** — dönen `token`'ı otomatik olarak collection değişkenine yazar; sonraki tüm istekler bu token'ı kullanır.
- **Search Products** — dönen ilk iki ürünün `uuid`'sini `product_uuid` ve `product_uuid_2` değişkenlerine otomatik kaydeder.
- **Create Order** — gövdesi bu değişkenleri kullanır. Ayrıca bir **pre-request script** içerir: `token` veya `product_uuid` boşsa otomatik olarak login olup ürün araması yapar. Böylece istek **tek başına** (önce Search çalıştırılmasa bile) doğrudan gönderilebilir.

İki kullanım yolu da çalışır:

1. Sırayla: **Login → Search Products → Create Order → List Orders** (her birine Send).
2. Doğrudan **Create Order → Send** (pre-request script token ve ürün uuid'lerini kendisi doldurur).

Arama ve listeleme isteklerinde `per_page` query parametresi (varsayılan kapalı) sayfalama için hazır olarak eklenmiştir.

## Testler

```bash
php artisan test
```

Testler bellek içi (`:memory:`) SQLite üzerinde çalışır; sağlanan veritabanına dokunmaz. Kapsam: login (başarılı/başarısız), korumalı uçların yetki kontrolü, arama validasyonu ve eşleşme, sipariş oluşturma + toplam hesabı, sipariş listeleme.

## Sonraki Adımlar (Mimari Notlar)

Ölçek büyüdüğünde uygulanabilecek ve mevcut mimarinin doğrudan desteklediği geliştirmeler:

- **PostgreSQL'e geçiş**: Repository katmanı sayesinde yalnızca konfigürasyon değişikliği; JSONB/CTE ve gelişmiş index stratejileri.
- **Asenkron iş akışları**: Sipariş sonrası bildirim/stok güncelleme gibi işler bir queue'ya (RabbitMQ/SQS) taşınabilir; Action katmanı event dispatch noktası olarak hazırdır.
- **Servisler arası iletişim**: gRPC/Protobuf ile senkron entegrasyon katmanı.
- **RoadRunner/Octane** ile yüksek trafik altında kalıcı worker'lar.
