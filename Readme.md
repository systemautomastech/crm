# 🚀 Automas CRM

**Automas CRM** is a modern, modular, and scalable **Customer Relationship Management (CRM)** system built on **Laravel 12** and **Inertia.js (React 18)**.  
It is designed for small to enterprise-level businesses to manage sales pipelines, leads, proposals, PBX telephony, inventory, and business operations efficiently from a single centralized platform.

---

## 📌 Key Features

- 🔐 **Role & Permission Management** 
- 🏢 **Multi-Company / Multi-Tenant Support** 
- 💼 **CRM & Lead Management** 
- 📞 **PBX & WebRTC Softphone Dialer** 
- 📜 **Proposals, Sales Orders, Invoicing System** 
- 📦 **Inventory & Warehouse Management** 
- 👥 **User & Employee Management** 
- 💬 **Messaging & Omnichannel Integrations**  
- 💬 **Facebook & WhatsApp Integrations**  
- ⚙️ **Centralized Settings Panel**  
- 🧩 **Modular Package-Based Architecture**  
- 🌐 **SaaS Ready**  

---

## 🛠️ Tech Stack

- **Backend Framework:** Laravel 12 (PHP 8.2+)  
- **Frontend Stack:** Inertia.js, React 18, TypeScript  
- **Styling & UI:** Tailwind CSS, Shadcn UI  
- **Database:** MySQL / MariaDB  
- **Build Tools:** Vite, NPM, Cross-Env  
- **Authentication:** Laravel Authentication  
- **Architecture:** Modular (Custom Laravel Packages)
- **Server:** Apache / Nginx

---

## 📂 Project Structure

AutomasCRM/
├── app/
├── bootstrap/
├── config/
├── database/
├── packages/ # Custom CRM modules
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── artisan

---

## ⚙️ Installation Guide

### 1️⃣ Server Requirements

- PHP >= 8.2  
- Composer  
- MySQL >= 5.7  
- Node.js & NPM  
- Apache / Nginx  

---

### 2️⃣ Setup Steps

```bash
# 1. Clone the repository
git clone https://github.com/systemautomastech/crm.git
cd crm

# 2. Install PHP Dependencies
composer install

# 3. Install Node Dependencies
npm install

# 4. Build Frontend Assets
npm run build
```

---

### 3️⃣ Environment Configuration

```bash
# Copy sample environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

> **Note:** Update the `.env` file with your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`), mail configuration, and `APP_URL`.

---

Update the .env file with your database credentials.

```bash
# Run database migrations and seed default data
php artisan migrate
php artisan db:seed
```

---

### 5️⃣ Storage Link & Permissions

```bash
# Create symbolic link for public storage
php artisan storage:link
```

Ensure the following directories are writable by your web server:
- `storage/`
- `bootstrap/cache/`

---

### 6️⃣ Run the Application

For local development:

```bash
# Option A: Run Laravel & Vite together
php artisan serve
npm run dev

# Access the application at: http://127.0.0.1:8000
```

---

## 🔐 Licensing & Activation

- This application includes a license verification system.
- License validation occurs during installation or initial configuration.
- ⚠️ **Notice:** Do not alter or remove core licensing files. Modifying these files violates the software license agreement.

---

## 📦 Modular Packages

- **Lead:** CRM lead pipelines, deal tracking, and stage management.
- **Pbx:** WebRTC dialer, active call interface, and softphone integration.
- **ProductService:** Products, services, multi-warehouse stock management, and stock transfers.
- **SalesOrder:** Quotations, proposals, sales orders, and invoicing workflows.
- **LandingPage:** SaaS landing page builder, order management, and Marketplace addons.
- **FacebookChat:** Facebook Messenger integration via Meta Webhooks.
- **WhatsAppChat:** WhatsApp Business API integration.

Each module can be independently updated, customized, or extended.

---

## 🧪 Testing

Run PHP test suites using Artisan:

```bash
php artisan test
```

---

## 🔒 Security Notes

- Never commit or expose your `.env` file publicly.
- Enable HTTPS in production environments.
- Restrict directory permissions to secure system files.
- Keep dependencies updated via Composer and NPM.

---

## 🚀 Deployment

Recommended production stack:
- **OS:** Ubuntu 20.04 LTS / 22.04 LTS
- **Web Server:** Nginx with PHP-FPM
- **Process Manager:** Supervisor for queue workers
- **Scheduler:** Cron job for `php artisan schedule:run`

---

## 👨‍💻 Developer Information

**Lead Developer / Senior Software Engineer**  
- **Name:** Mesbah Uddin  
- **Role:** Senior Software Engineer  

**Specialization:**
- Laravel & Inertia.js Architecture
- CRM, ERP & SaaS System Engineering
- Telephony & WebRTC Integrations
- Secure Licensing Systems
- Modular Package Architecture
- API & Backend Optimization

*This project adheres to enterprise architectural standards, clean code principles, and scalable design patterns.*

---

## 📜 License

This software is commercial and protected by copyright law.

- ❌ Redistribution without express permission is prohibited.  
- ❌ Reselling or unauthorized sublicensing is strictly prohibited.  
- ✅ Usage is authorized only for licensed domains and client installations.  

---

## 🤝 Support & Contact

For technical support, custom module development, or licensing inquiries:

**Automas Technologies**  
📧 **Email:** [support@automas.com.bd](mailto:support@automas.com.bd)  
🌐 **Website:** [https://automas.com.bd](https://automas.com.bd)  

---

⭐ **Developed & Maintained by Automas Technologies**