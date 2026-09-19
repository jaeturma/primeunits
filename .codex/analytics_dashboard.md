# 🧠 CODEX INSTRUCTION: Analytics Dashboard Module (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement an **Analytics Dashboard Module** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a comprehensive analytics system that:

- Tracks platform performance
- Measures seller and listing performance
- Tracks leads → transactions conversion
- Displays revenue and commission insights
- Supports filtering by date, category, and location
- Is optimized for scalability and fast queries

---

# 🏗️ SYSTEM OVERVIEW

Data Sources:

- users
- listings
- leads
- transactions
- commissions
- payments

Dashboard aggregates and visualizes metrics.

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

---

## 1. Metrics to Compute

---

### 📊 Global Metrics

- total_users
- total_sellers
- total_buyers
- total_listings
- active_listings
- total_leads
- total_transactions

---

### 💰 Revenue Metrics

- total_commission
- total_paid_commission
- pending_commission
- total_payments_received

---

### 📈 Conversion Metrics

- leads_to_transactions_rate
- average_time_to_close (lead → transaction)

---

### 📦 Listing Performance

- listings per category
- top performing listings (most leads)
- top sellers (most closed transactions)

---

---

## 2. API Endpoints

Create:

AnalyticsController

Methods:

- dashboardSummary()
- revenueReport()
- listingStats()
- conversionStats()

---

## 3. Query Optimization (IMPORTANT)

- Use aggregated queries (COUNT, SUM)
- Avoid N+1 queries
- Use indexes on:
  - created_at
  - status
  - category_id

---

## 4. Filtering Support

Allow filters:

- date range (from, to)
- category_id
- location (region/province)

---

## 5. Service Layer

Create:

AnalyticsService

Handles:

- all calculations
- reusable queries

---

## 6. Data Format Example

```json
{
  "total_users": 1200,
  "total_sellers": 300,
  "total_listings": 1500,
  "total_leads": 4500,
  "total_transactions": 320,
  "conversion_rate": 7.1
}