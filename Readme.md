# 🚀 Automas ERP

**Automas ERP** is a modern, modular, and scalable **Enterprise Resource Planning (ERP)** system built on **Laravel 12**.  
It is designed for small to medium businesses to manage operations efficiently from a single centralized platform.

---

## 📌 Features

- 🔐 Role & Permission Management  
- 🏢 Multi-Company / Multi-Tenant Support  
- 👥 User & Employee Management  
- 📦 Product & Service Management  
- 💼 HRM (Employee, Attendance, Leave)  
- 🧾 Accounts & Financial Modules  
- 📊 Reports & Analytics  
- 💬 Internal Messaging (Chat Module)  
- ⚙️ Centralized Settings Panel  
- 🧩 Modular Architecture (Package Based)  
- 🌐 Ready for SaaS & On-Premise Deployment  

---

## 🛠️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.2+)  
- **Frontend:** Blade, Bootstrap 5, Tailwind CSS  
- **Database:** MySQL / MariaDB  
- **Authentication:** Laravel Authentication  
- **Architecture:** Modular (Custom Laravel Packages)  
- **Build Tools:** Vite, NPM  
- **Server:** Apache / Nginx  

---

## 📂 Project Structure

AutomasERP/
├── app/
├── bootstrap/
├── config/
├── database/
├── packages/ # Custom ERP modules
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
git clone https://github.com/systemautomastech/erp.git
cd erp
composer install
npm install
npm run build

3️⃣ Environment Configuration
cp .env.example .env
php artisan key:generate


Update the .env file with your database credentials.

4️⃣ Database Migration
php artisan migrate
php artisan db:seed

5️⃣ Storage & Permissions
php artisan storage:link


Ensure the following directories are writable:

storage/

bootstrap/cache/

6️⃣ Run the Application
php artisan serve


Access the system via:

http://127.0.0.1:8000

🔐 Licensing & Activation

This application includes a license verification system

License is validated during installation or first use

Unauthorized usage may result in limited or restricted functionality

⚠️ Do not remove or modify licensing files.
Doing so violates the license agreement.

📦 Modules

Automas ERP uses a package-based modular architecture, including:

Account Management

Human Resource Management (HRM)

Product & Service Management

Landing Page Management

Task & Project Management

Internal Messaging (Chat)

Each module can be enabled, extended, or customized independently.

🧪 Testing
php artisan test

🔒 Security Notes

Never expose the .env file

Always use HTTPS in production

Keep file permissions properly restricted

Regularly update dependencies

🚀 Deployment

Recommended production stack:

Ubuntu 20.04+

Nginx

PHP-FPM

Supervisor (Queues)

Cron Jobs for scheduled tasks

👨‍💻 Developer Information

Lead Developer / Senior Software Engineer
Name: Mesbah Uddin
Role: Senior Software Engineer

Specialization:
Laravel Architecture
ERP & SaaS Systems
Secure Licensing Systems
Modular Application Design
API & Backend Optimization
This project follows industry best practices, clean architecture, and scalable design principles suitable for enterprise-level applications.
📜 License

This software is commercial and protected by copyright.

❌ Redistribution not allowed

❌ Reselling without permission is prohibited

✅ Usage allowed only for licensed domains

🤝 Support

For support, customization, or licensing inquiries:

Automas Technologies
📧 support@automas.com.bd

🌐 https://automas.com.bd

⭐ Credits

Developed & Maintained by
Automas Technologies