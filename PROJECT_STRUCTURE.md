# 📁 Project Structure - MancinginAje E-Commerce

```
MancinginAje/
│
├── 🔧 Configuration Files
│   ├── .env                              # Environment variables (Midtrans, DB, dll)
│   ├── .env.example                      # Template .env
│   ├── composer.json                     # PHP dependencies
│   ├── package.json                      # Node dependencies (optional)
│   ├── phpunit.xml                       # Testing configuration
│   ├── vite.config.js                    # Frontend build tool
│   └── artisan                           # Laravel CLI tool
│
├── 📄 Documentation
│   ├── README.md                         # Project overview
│   ├── SETUP_GUIDE.md                    # Installation & setup
│   ├── IMPLEMENTATION_CHECKLIST.md       # Feature checklist
│   ├── MIDTRANS_GUIDE.md                 # Payment integration guide
│   └── ADDITIONAL_FEATURES.md            # Future enhancement ideas
│
├── 🎨 Frontend
│   └── resources/
│       ├── css/
│       │   ├── app.css                   # Global styles
│       │   └── style.css                 # Custom styles
│       ├── js/
│       │   ├── app.js                    # Main JS app
│       │   └── bootstrap.js              # Bootstrap initialization
│       └── views/
│           ├── layouts/
│           │   └── main.blade.php        # Master layout template
│           ├── components/
│           │   ├── navbar.blade.php      # Navigation bar
│           │   ├── footer.blade.php      # Footer
│           │   └── sidebar.blade.php     # Sidebar (if needed)
│           ├── auth/
│           │   ├── login.blade.php       # Login form
│           │   └── register.blade.php    # Register form
│           ├── pages/
│           │   ├── beranda.blade.php     # Homepage
│           │   ├── orders.blade.php      # Product catalog
│           │   ├── detail-product.blade.php # Product detail
│           │   ├── cart.blade.php        # Shopping cart
│           │   ├── payment.blade.php     # Payment form
│           │   ├── payment-process.blade.php # Midtrans embed
│           │   ├── payment-success.blade.php # Success page
│           │   └── YourOrders.blade.php  # Order history
│           └── dashboard/
│               ├── index.blade.php       # Admin dashboard
│               └── create.blade.php      # Create product form
│
├── ⚙️ Backend
│   └── app/
│       ├── Http/
│       │   ├── Controllers/
│       │   │   ├── AuthController.php         # Auth logic
│       │   │   ├── BerandaController.php      # Homepage
│       │   │   ├── ProductController.php      # Products
│       │   │   ├── CartController.php         # Cart operations
│       │   │   ├── PaymentController.php      # Payment handling
│       │   │   ├── OrderController.php        # Orders
│       │   │   └── DashboardController.php    # Dashboard
│       │   └── Middleware/
│       │       └── Authenticate.php           # Auth middleware
│       ├── Models/
│       │   ├── User.php                      # User model
│       │   ├── Category.php                  # Category model
│       │   ├── Products.php                  # Product model
│       │   ├── Orders.php                    # Order model
│       │   └── Order_items.php               # OrderItem model
│       ├── Services/
│       │   └── MidtransService.php           # Payment service
│       └── View/
│           └── Components/                   # Reusable components
│
├── 🗄️ Database
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_10_01_024423_create_categories_table.php
│   │   ├── 2025_10_31_130445_create_products_table.php
│   │   ├── 2025_11_12_160643_create_orders_table.php
│   │   ├── 2025_11_12_162015_create_order_items_table.php
│   │   ├── 2025_12_15_000000_update_users_table.php
│   │   └── 2025_12_15_000001_update_orders_table.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php               # Main seeder runner
│   │   ├── CategoriesSeeder.php             # Insert categories
│   │   ├── ProductSeeder.php                # Insert products
│   │   └── ProductSeederSample.php          # Sample products
│   └── factories/
│       └── UserFactory.php                  # User factory for testing
│
├── 🔧 Configuration
│   ├── config/
│   │   ├── app.php                         # App configuration
│   │   ├── auth.php                        # Auth configuration
│   │   ├── cache.php                       # Cache configuration
│   │   ├── database.php                    # Database configuration
│   │   ├── filesystems.php                 # File storage config
│   │   ├── logging.php                     # Logging configuration
│   │   ├── mail.php                        # Email configuration
│   │   ├── queue.php                       # Queue configuration
│   │   ├── session.php                     # Session configuration
│   │   ├── services.php                    # Third-party services
│   │   └── midtrans.php                    # Midtrans configuration
│   ├── bootstrap/
│   │   ├── app.php                         # Bootstrap app
│   │   ├── providers.php                   # Service providers
│   │   └── cache/
│   │       ├── packages.php
│   │       └── services.php
│   └── routes/
│       ├── web.php                         # Web routes
│       ├── api.php                         # API routes (optional)
│       └── console.php                     # Console commands
│
├── 📦 Public Files
│   ├── index.php                           # Entry point
│   ├── robots.txt                          # SEO robots file
│   ├── css/
│   │   ├── style.css                       # Custom CSS
│   │   └── mancinginaje.css                # Theme CSS
│   └── images/                             # Product images
│
├── 💾 Storage
│   ├── app/
│   │   ├── private/                        # Private files
│   │   └── public/                         # Public files
│   ├── framework/
│   │   ├── cache/                          # Cache files
│   │   ├── sessions/                       # Session files
│   │   ├── testing/                        # Test files
│   │   └── views/                          # Compiled views
│   └── logs/
│       └── laravel.log                     # Application logs
│
├── 🧪 Tests
│   ├── TestCase.php                        # Base test class
│   ├── Feature/                            # Feature tests
│   │   └── ExampleTest.php
│   └── Unit/                               # Unit tests
│       └── ExampleTest.php
│
├── 📚 Vendor (Dependencies)
│   ├── laravel/                            # Laravel packages
│   ├── symfony/                            # Symfony components
│   ├── midtrans/                           # Midtrans SDK
│   └── ... (other packages)
│
└── 🔌 Core Directories
    ├── bootstrap/
    ├── storage/
    └── vendor/
```

## 📊 Database Schema

### Users Table
```sql
id | name | email | password | role | address | phone | created_at | updated_at
```

### Categories Table
```sql
id | nama | created_at | updated_at
```

### Products Table
```sql
id | nama | deskripsi | harga | stok | gambar | category_id | created_at | updated_at
```

### Orders Table
```sql
id | user_id | total_harga | status | customer_name | customer_email | customer_phone
shipping_address | payment_method | transaction_id | payment_status | created_at | updated_at
```

### Order Items Table
```sql
id | order_id | product_id | quantity | price | created_at | updated_at
```

## 🔗 Key Files Relationships

```
routes/web.php
    ↓
Controllers/
    ├── AuthController → Models/User
    ├── BerandaController → Models/Category, Products
    ├── ProductController → Models/Products
    ├── CartController → Session
    ├── PaymentController → Models/Orders, Services/MidtransService
    └── OrderController → Models/Orders

Models/
    ├── User (has many Orders)
    ├── Category (has many Products)
    ├── Products (belongs to Category)
    ├── Orders (belongs to User, has many Order_items)
    └── Order_items (belongs to Order, Products)

Views/
    ├── layouts/main (parent template)
    ├── components/navbar (navigation)
    └── pages/* (content pages)

Services/
    └── MidtransService (payment processing)
```

## 🎯 Important Files to Modify

When customizing the application:

1. **.env** - Update with your credentials
2. **config/midtrans.php** - Payment settings
3. **routes/web.php** - Add/modify routes
4. **app/Models/** - Extend models if needed
5. **resources/views/** - Customize templates
6. **public/css/mancinginaje.css** - Customize styling

## 📋 Development Workflow

1. **Clone/Setup**
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. **Database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

3. **Development**
   ```bash
   php artisan serve
   ```

4. **Testing**
   ```bash
   php artisan test
   ```

5. **Deployment**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

**Total Files**: 50+ PHP files, 10+ Blade templates, 2+ CSS files
**Database Tables**: 5 main tables
**Controllers**: 7 main controllers
**Models**: 5 models

**Last Updated**: December 2025
