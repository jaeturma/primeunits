# 🧠 CODEX INSTRUCTION: Lead, Transaction & Commission Module (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **Lead / Inquiry + Transaction Tracking + Commission System** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a system that:

- Tracks all buyer inquiries (leads)
- Generates unique transaction/reference codes
- Manages deal pipeline (inquiry → closed)
- Prevents commission leakage
- Calculates and tracks commissions
- Supports buyer and seller confirmation

---

# 🏗️ SYSTEM OVERVIEW

Buyer → clicks inquire → lead created → reference code generated  
→ buyer contacts seller → negotiation → deal closed  
→ both confirm → commission recorded

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

## 1. Database Design

---

### leads table

Fields:

- id
- listing_id (FK)
- buyer_id (FK users)
- seller_id (FK users)

### Lead Tracking
- reference_code (unique)
- status (inquiry, contacted, negotiating, reserved, closed, cancelled)

### Communication
- message (nullable)

### Timeline
- contacted_at (nullable)
- negotiated_at (nullable)
- closed_at (nullable)

- created_at
- updated_at

---

### transactions table

Fields:

- id
- lead_id (FK)
- listing_id (FK)

### Financial
- agreed_price (decimal 15,2)
- commission_rate (decimal 5,2)
- commission_amount (decimal 15,2)

### Status
- status (pending, confirmed, disputed)

### Confirmation
- buyer_confirmed (boolean)
- seller_confirmed (boolean)
- confirmed_at (nullable)

### Proof
- proof_file (nullable)

- created_at
- updated_at

---

### commission_logs table

Fields:

- id
- transaction_id (FK)
- amount
- status (unpaid, paid)
- paid_at (nullable)

- created_at
- updated_at

---

## 2. Reference Code Generator

Format:

PU-XXXXXXX

Example:
PU-A8F3K9L

Must be:
- unique
- auto-generated

---

## 3. Business Rules (CRITICAL)

- Every inquiry MUST create a lead
- Buyer must be logged in
- Lead automatically assigns seller from listing
- Commission is based on agreed price

---

## 4. Commission Logic

- Default commission_rate = configurable (e.g. 2%)
- commission_amount = agreed_price * rate

---

## 5. Controller

Create:

LeadController

Methods:

- store() → create inquiry
- myLeads() → buyer view
- sellerLeads() → seller view
- updateStatus()

---

TransactionController

Methods:

- createFromLead($lead_id)
- confirmByBuyer()
- confirmBySeller()
- uploadProof()
- markAsPaid()

---

---

## 6. Validation

- buyer must be authenticated
- cannot create duplicate active lead for same listing
- only seller/buyer can confirm transaction

---

## 7. Middleware

- auth required
- EnsureSellerVerified for seller routes

---

# ⚛️ FRONTEND REQUIREMENTS (React + Inertia)

---

## 1. Inquiry Flow (VERY IMPORTANT)

On listing page:

Button:
👉 "Inquire"

Action:
- POST /leads

Response:
- show modal with:
  - reference code
  - seller contact instructions

---

## 2. Buyer Dashboard

Route:

/buyer/leads

Display:

- listing
- seller
- status
- reference code

---

## 3. Seller Dashboard

Route:

/seller/leads

Display:

- buyer info
- listing
- status
- action buttons:
  - mark contacted
  - mark negotiating
  - create transaction

---

## 4. Transaction Flow

Seller creates transaction:

- enters agreed price
- system calculates commission

---

## 5. Confirmation UI

Buyer:
- confirm purchase

Seller:
- confirm sale

When both confirmed:
→ transaction = confirmed

---

## 6. Proof Upload

Allow upload:
- receipt
- OR/CR
- agreement

---

## 7. Admin Panel

Route:

/adm/transactions

Features:

- view all transactions
- filter by status
- view commission
- mark as paid

---

## 8. UX Rules

- Always show reference code prominently
- Show progress timeline:
  inquiry → negotiating → closed
- Prevent duplicate submissions

---

# 📁 STRUCTURE RULES

- Use Form Request validation
- Keep controllers clean
- Use service class for commission logic

---

# 🚫 CONSTRAINTS

- Do NOT rely on manual tracking
- Do NOT skip reference code system
- Do NOT hardcode commission logic in frontend

---

# ✅ OUTPUT REQUIREMENTS

Generate:

1. Migrations:
   - leads
   - transactions
   - commission_logs

2. Models:
   - Lead
   - Transaction
   - CommissionLog

3. Controllers:
   - LeadController
   - TransactionController

4. Service:
   - CommissionService

5. Form Requests

6. Routes

7. Inertia React Pages:
   - Buyer Leads
   - Seller Leads
   - Transaction View
   - Admin Transactions

All code must be complete and functional.

---

# ⚡ EXECUTION RULES

- Generate full working code
- Follow Laravel 13 + Inertia best practices
- Ensure all parts connect correctly

---

# END