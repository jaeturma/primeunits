# 🧠 CODEX INSTRUCTION: Monetization Module (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **Monetization Module** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a flexible monetization system that supports:

- Featured / Boosted Listings
- Subscription Plans for sellers (optional but scalable)
- Commission tracking integration (already exists)
- Payment tracking (manual or future gateway-ready)
- Admin control over pricing and plans

---

# 🏗️ SYSTEM OVERVIEW

Seller → chooses boost or subscription → pays → system activates feature → increases visibility

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

---

## 1. Database Design

---

### plans table

Fields:

- id
- name
- type (boost, subscription)
- price (decimal 10,2)
- duration_days (nullable)
- features (json nullable)
- is_active (boolean default true)

---

### subscriptions table

Fields:

- id
- user_id (FK)
- plan_id (FK)

- starts_at
- ends_at

- status (active, expired, cancelled)

- created_at
- updated_at

---

### listing_boosts table

Fields:

- id
- listing_id (FK)
- plan_id (FK)

- starts_at
- ends_at

- is_active (boolean)

---

### payments table

Fields:

- id
- user_id (FK)
- payable_type (polymorphic: subscription, listing_boost, etc.)
- payable_id

- amount (decimal 10,2)
- method (cash, gcash, bank_transfer)
- reference_number (nullable)

- status (pending, confirmed, rejected)

- proof_file (nullable)

- paid_at (nullable)

- created_at
- updated_at

---

## 2. Model Relationships

User:
- hasMany subscriptions
- hasMany payments

Plan:
- hasMany subscriptions
- hasMany listing_boosts

Listing:
- hasMany boosts

---

## 3. Business Rules

- Sellers can:
  - boost listings
  - subscribe to plans

- Boost:
  - applies to specific listing
  - expires after duration

- Subscription:
  - applies to seller account
  - unlocks features (e.g. unlimited listings)

- Payment must be confirmed before activation

---

## 4. Plan Types

### Boost Plan Example:
- name: "7-Day Boost"
- duration_days: 7
- feature: "highlight listing"

### Subscription Example:
- name: "Pro Seller"
- duration_days: 30
- features:
  - unlimited listings
  - priority placement

---

## 5. Controllers

Create:

PlanController
- index()
- store()
- update()

SubscriptionController
- subscribe()
- mySubscriptions()

ListingBoostController
- boostListing()

PaymentController
- store()
- confirm()
- reject()

---

## 6. Activation Logic

### Boost Activation

- after payment confirmed:
  - set starts_at = now()
  - ends_at = now() + duration_days
  - is_active = true

---

### Subscription Activation

- same logic as boost
- applied to user

---

## 7. Commission Integration

- Commission remains separate
- But payments table should support:
  - commission payments (future extension)

---

## 8. Middleware

- Only seller can purchase boosts/subscriptions

---

# ⚛️ FRONTEND REQUIREMENTS (React + Inertia)

---

## 1. Seller Monetization Dashboard

Route:

/seller/monetization

Display:

- active subscriptions
- available plans
- boosted listings

---

## 2. Plan Selection UI

- Show:
  - plan name
  - price
  - duration
  - features

Buttons:
👉 Subscribe  
👉 Boost Listing  

---

## 3. Boost Listing Flow

- Seller selects listing
- Chooses boost plan
- Redirect to payment page

---

## 4. Payment UI

Fields:

- payment method
- reference number
- upload proof

Status:
- pending
- confirmed
- rejected

---

## 5. Listing Display Impact

Boosted listings should:

- appear first in listing results
- show "Featured" badge

---

## 6. Admin Panel

Route:

/adm/plans
/adn/payments

Features:

- create/edit plans
- view payments
- approve/reject payments

---

## 7. UX Rules

- Clearly show:
  - active boosts
  - expiration dates
- Prevent duplicate active boosts

---

# 📁 STRUCTURE RULES

- Use polymorphic payments
- Use services for activation logic
- Keep controllers clean

---

# 🚫 CONSTRAINTS

- Do NOT hardcode plans
- Do NOT auto-activate without payment confirmation
- Must support future payment gateway integration

---

# ✅ OUTPUT REQUIREMENTS

Generate:

1. Migrations:
   - plans
   - subscriptions
   - listing_boosts
   - payments

2. Models:
   - Plan
   - Subscription
   - ListingBoost
   - Payment

3. Controllers:
   - PlanController
   - SubscriptionController
   - ListingBoostController
   - PaymentController

4. Service:
   - PaymentService (handles activation)

5. Routes

6. Inertia React Pages:
   - Seller Monetization Dashboard
   - Plan Selection
   - Payment Page
   - Admin Plans
   - Admin Payments

All code must be complete and functional.

---

# ⚡ EXECUTION RULES

- Generate full working code
- Follow Laravel 13 + Inertia best practices
- Ensure scalability

---

# END