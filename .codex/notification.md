# 🧠 CODEX INSTRUCTION: Notification System Module (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **Notification System** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a scalable notification system that:

- Sends notifications for key events:
  - New inquiry (lead)
  - Listing approved/rejected
  - Seller verification status
  - Transaction updates
  - Payment updates
- Supports multiple channels:
  - In-app (database notifications)
  - Email (queued)
  - SMS-ready (future integration)
- Allows user-level notification preferences
- Is queue-based and production-ready

---

# 🏗️ SYSTEM OVERVIEW

Event occurs → Notification created → Delivered via channels → User views notification → Marks as read

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

---

## 1. Database Design

---

### notifications table (use Laravel default + extend)

Use Laravel's built-in notifications table:

```bash
php artisan notifications:table