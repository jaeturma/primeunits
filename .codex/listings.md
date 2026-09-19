# 🧠 CODEX INSTRUCTION: Listings Module with Dynamic Specs (PrimeUnits)

You are a senior Laravel 13 + React (Inertia.js) engineer.

Your task is to implement a **Listings Module with Dynamic Specifications per Category** for a marketplace web application called **PrimeUnits**.

---

# 🎯 GOAL

Build a flexible listing system that:

- Supports multiple categories:
  - Vehicles (cars, trucks, vans, buses)
  - Farm Equipment (tractors, harvesters)
  - Heavy Equipment (excavators, loaders)
  - Construction Equipment
- Allows dynamic specifications per category
- Only allows VERIFIED sellers to create listings
- Supports structured real-world data fields
- Is scalable and extensible

---

# 🏗️ SYSTEM OVERVIEW

Seller → creates listing → selects category → fills dynamic specs → submits → admin approves → listing goes live

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

## 1. Database Design

### listings table

Fields:

- id
- user_id (FK)
- seller_profile_id (FK)
- category_id (FK)
- title
- description (nullable)
- price (decimal 15,2)
- negotiable (boolean default false)

### General Info
- condition (brand_new, used, surplus)
- year_model (nullable)
- brand (nullable)
- model (nullable)

### Location
- region (nullable)
- province (nullable)
- municipality (nullable)
- barangay (nullable)

### Status
- status (pending, approved, rejected)
- approved_at (nullable)
- rejected_reason (nullable)

- created_at
- updated_at

---

### listing_images table

- id
- listing_id (FK)
- path
- is_primary (boolean)

---

### categories table

- id
- name
- slug

Seed with:

- vehicle
- farm_equipment
- heavy_equipment
- construction_equipment

---

### category_spec_fields table (DYNAMIC FIELDS)

- id
- category_id (FK)
- name (e.g. engine_power)
- label (e.g. Engine Power)
- type (text, number, select)
- options (json nullable)
- required (boolean default false)

---

### listing_spec_values table

- id
- listing_id (FK)
- spec_field_id (FK)
- value (text)

---

## 2. Model Relationships

Listing:
- belongsTo User
- belongsTo SellerProfile
- belongsTo Category
- hasMany ListingImage
- hasMany ListingSpecValue

Category:
- hasMany SpecFields

SpecField:
- belongsTo Category

---

## 3. Seeder (IMPORTANT)

Seed dynamic spec fields per category:

### Vehicles
- transmission (select: manual, automatic)
- fuel_type (gasoline, diesel, electric)
- mileage (number)
- color (text)

### Farm Equipment
- horsepower (number)
- fuel_type
- usage_hours (number)

### Heavy Equipment
- operating_weight (number)
- bucket_capacity (number)
- engine_power (number)

### Construction
- power_source
- capacity
- dimensions

---

## 4. Business Rules

- Only VERIFIED sellers can create listings
- Listing must be approved by admin before visible
- Dynamic specs must match selected category

---

## 5. Controller

Create:

ListingController

Methods:

- index() → public listings
- create() → show form
- store() → save listing
- show($id)
- myListings()
- update()
- destroy()

Admin:

- approve($id)
- reject($id)

---

## 6. Validation

- title, category_id, price required
- dynamic specs:
  - validate based on spec_field rules
- images:
  - max 10 files
  - jpg/png

---

## 7. File Uploads

Store in:

- listings/images/

---

## 8. Middleware

- EnsureSellerVerified

---

# ⚛️ FRONTEND REQUIREMENTS (React + Inertia)

## 1. Listing Create Page

Route:

/seller/listings/create

---

## 2. Form Sections

### Basic Info
- Title
- Category dropdown
- Price
- Negotiable toggle

---

### General Info
- Condition
- Year Model
- Brand
- Model

---

### Location
- Region
- Province
- Municipality
- Barangay

---

### Dynamic Specs (IMPORTANT)

- Fetch spec fields via API when category changes
- Render dynamically:
  - text input
  - number input
  - select dropdown

---

### Images
- Multi-upload
- Preview before submit

---

## 3. UX Behavior

- Changing category resets spec fields
- Required specs must be enforced
- Show validation errors clearly

---

## 4. Public Listing Page

- Display:
  - images
  - basic info
  - dynamic specs (formatted)

---

## 5. Seller Dashboard

Route:

/seller/listings

- List own listings
- Show status:
  - pending
  - approved
  - rejected

---

## 6. Admin Panel

Route:

/adm/listings

Features:

- View all listings
- Filter by status
- Approve / Reject

---

# 📁 STRUCTURE RULES

- Use Form Request validation
- Keep controllers clean
- Use services if needed for dynamic specs

---

# 🚫 CONSTRAINTS

- Do NOT hardcode specs per category in frontend
- Specs must come from database
- Must be scalable for new categories

---

# ✅ OUTPUT REQUIREMENTS

Generate:

1. All migrations
2. Models
3. Seeder (categories + spec fields)
4. Controller (ListingController)
5. Form Request
6. Middleware usage
7. Routes
8. React Pages:
   - Create Listing
   - Seller Listings
   - Admin Listings
   - Public Listing View

All code must be complete and working.

---

# ⚡ EXECUTION RULES

- Do NOT stop at explanation
- Generate full working code
- Assume Seller Verification module already exists
- Follow Laravel 13 + Inertia best practices

---

# END