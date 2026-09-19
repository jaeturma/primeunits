# 🧠 CODEX INSTRUCTION: Seller Verification Module (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **Seller Module with Verification System** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a complete Seller Verification system that:

- Allows users to register as sellers
- Captures real-world business and personal data
- Supports optional but structured fields
- Requires admin approval before seller can post listings
- Is scalable for nationwide (Philippines) expansion

---

# 🏗️ SYSTEM OVERVIEW

Users can apply as sellers → submit profile → admin verifies → seller becomes "verified"

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

## 1. Database Design

Create the following tables:

### seller_profiles

Fields:

- id
- user_id (FK)
- seller_type (individual, business)

### Basic Info
- business_name (nullable)
- owner_name (nullable)
- contact_number (required)
- email (nullable)

### Address (Optional but structured)
- region (nullable)
- province (nullable)
- municipality (nullable)
- barangay (nullable)
- full_address (nullable)

### Business / Legal Info (Optional)
- permit_number (nullable)
- permit_file (nullable)
- accreditation (nullable)
- accreditation_file (nullable)

### Representative (Optional)
- representative_name (nullable)
- representative_contact (nullable)
- representative_id_file (nullable)

### Verification
- status (pending, verified, rejected)
- verified_at (nullable)
- rejected_reason (nullable)

### Media
- valid_id_file (nullable)
- selfie_file (nullable)

- created_at
- updated_at

---

## 2. Model Relationships

User:
- hasOne SellerProfile

SellerProfile:
- belongsTo User

---

## 3. Business Rules

- Only users with role "seller" can create seller profile
- Seller must be "verified" before:
  - creating listings
- Default status = pending
- Admin updates status

---

## 4. Controller

Create:

SellerProfileController

Methods:

- apply() → show form
- store() → save seller application
- show() → view profile
- update() → edit profile (if rejected)

Admin:

- approve($id)
- reject($id)

---

## 5. Validation

Implement strong validation:

Required:
- contact_number

Optional:
- all other fields

File uploads:
- image/pdf for:
  - valid ID
  - permit
  - accreditation
  - representative ID

---

## 6. File Storage

- Use Laravel storage (public disk)
- Organize:
  - sellers/ids/
  - sellers/permits/
  - sellers/accreditations/

---

## 7. Middleware Rule

Create restriction:

- Only VERIFIED sellers can access:
  - listing creation routes

---

# ⚛️ FRONTEND REQUIREMENTS (React + Inertia)

## 1. Seller Application Form

Create page:

/seller/apply

Form sections:

### Basic Info
- Seller type (dropdown)
- Business name (optional)
- Contact number

### Address
- Region
- Province
- Municipality
- Barangay
- Full address

### Business Info
- Permit number
- Upload permit file
- Accreditation
- Upload accreditation file

### Representative
- Name
- Contact
- Upload ID

### Verification
- Upload valid ID
- Upload selfie

---

## 2. UX Behavior

- Show status:
  - Pending → “Under review”
  - Verified → “Verified Seller”
  - Rejected → show reason + allow edit

---

## 3. Admin Panel

Route:

/adm/sellers

Features:

- List all seller applications
- Filter by:
  - status
- View full details
- Approve / Reject buttons

---

## 4. React State Handling

- Use Inertia form helper
- Show upload previews
- Show validation errors

---

# 📁 STRUCTURE RULES

- Follow Laravel MVC properly
- Use Form Request validation
- Keep controllers thin
- Use services if needed

---

# 🚫 CONSTRAINTS

- Do NOT use external packages
- Do NOT hardcode status logic in frontend
- Backend must control verification

---

# ✅ OUTPUT REQUIREMENTS

Generate:

1. Migration for seller_profiles
2. Model (SellerProfile)
3. Controller (SellerProfileController)
4. Form Request (StoreSellerProfileRequest)
5. Middleware (EnsureSellerVerified)
6. Routes (web.php)
7. Inertia React Pages:
   - Seller Apply Form
   - Seller Status Page
   - Admin Seller List

All code must be complete and functional.

---

# ⚡ EXECUTION RULES

- Generate real working code
- Assume Laravel 13 + Inertia setup is ready
- Follow best practices
- Ensure scalability

---

# END