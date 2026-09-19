# 🧠 CODEX INSTRUCTION: SEO + Landing Page System (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **SEO + Landing Page System** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build an SEO-optimized system that:

- Generates dynamic landing pages for:
  - category
  - location
  - category + location
- Improves Google indexing and ranking
- Drives organic traffic
- Displays relevant listings per page
- Is scalable across all regions in the Philippines

---

# 🏗️ SYSTEM OVERVIEW

Example URLs:

- /vehicles
- /vehicles/davao
- /heavy-equipment/cebu
- /farm-equipment/mindanao

Each page:
→ dynamically loads listings  
→ generates SEO metadata  
→ supports filters  

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

---

## 1. Database Design

---

### seo_pages table

Fields:

- id
- slug (unique)
- title
- meta_title
- meta_description
- content (nullable)
- category_id (nullable)
- region (nullable)
- province (nullable)
- municipality (nullable)

- created_at
- updated_at

---

## 2. Routing

Dynamic routes:

```php
Route::get('/{category}', [SeoController::class, 'category']);
Route::get('/{category}/{location}', [SeoController::class, 'categoryLocation']);