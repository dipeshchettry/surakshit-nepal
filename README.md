# Surakshit Nepal 🇳🇵
**AI-Powered Weather & Disaster Early Warning Platform for Nepal**

Surakshit Nepal is a comprehensive, real-time web application designed to keep citizens of Nepal safe by providing highly localized weather forecasts, real-time disaster alerts (Earthquakes, Floods, Landslides), AI-powered safety guidance, and an interactive map of emergency services.

---

## ✨ Features
* **Real-time Weather Data:** Hyper-local current conditions, hourly forecasts, and 7-day outlooks using WeatherAPI.
* **Disaster Alerts:** Automated tracking of Earthquakes (USGS), Floods, and other disasters (ReliefWeb/GDACS) affecting the Nepal region.
* **AI Safety Guidance:** Context-aware, bilingual (English & Nepali) safety instructions generated dynamically using Google Gemini 1.5 Flash based on current local threats.
* **Interactive Map:** 100% free open-source map (Leaflet + OpenStreetMap) displaying risk zones and dynamically locating nearby emergency services (Hospitals, Police, Fire, Shelters) via the Overpass API.
* **Real-time Push Notifications:** Integrated OneSignal Web Push to broadcast critical disaster alerts to users instantly.
* **PWA Ready:** Installable as a Progressive Web App for offline access and native-like mobile experience.

---

## 🛠️ Technology Stack
* **Frontend:** HTML5, CSS3 (Vanilla/Custom CSS), JavaScript (ES6+), Leaflet.js
* **Backend:** PHP 8.x
* **Database:** MySQL / MariaDB
* **APIs Used:** WeatherAPI, Google Gemini API, OneSignal REST API, USGS Earthquake API, ReliefWeb API, Overpass API (OSM).

---

## 🚀 Setup & Installation (Local Development)

### 1. Prerequisites
* **XAMPP** (or any AMP stack) with PHP 8.0+ and MySQL enabled.
* API Keys for: [WeatherAPI](https://www.weatherapi.com/), [Google Gemini](https://aistudio.google.com/), and [OneSignal](https://onesignal.com/).

### 2. Database Setup
1. Open phpMyAdmin (`http://localhost/phpmyadmin`).
2. Create a new database named `surakshit_nepal` (Collation: `utf8mb4_unicode_ci`).
3. Import the schema file located at `database/schema.sql`.

### 3. Application Configuration
1. Clone the repository into your web server root (e.g., `C:\xampp\htdocs\weather`).
2. Navigate to `api/config/` and copy `config.example.php` to `config.php`.
3. Open `config.php` and fill in your private API keys:
   ```php
   define('WEATHERAPI_KEY',   'YOUR_WEATHERAPI_KEY');
   define('ONESIGNAL_APP_ID', 'YOUR_ONESIGNAL_APP_ID');
   define('ONESIGNAL_REST_API_KEY', 'YOUR_ONESIGNAL_REST_API_KEY');
   define('GEMINI_API_KEY',   'YOUR_GEMINI_API_KEY');
   ```

### 4. Background Push Notifications (Optional)
To enable the automated real-time push notification system:
* Set up a Cron Job (Linux) or a Task Scheduler task (Windows) to execute `api/cron_alerts.php` every 5 minutes.
* Note: A "Poor Man's Cron" is already built into the frontend to trigger this automatically when users browse the site.

---

## 🛡️ License
This project was built for the safety and betterment of Nepal. Feel free to fork, modify, and contribute.
