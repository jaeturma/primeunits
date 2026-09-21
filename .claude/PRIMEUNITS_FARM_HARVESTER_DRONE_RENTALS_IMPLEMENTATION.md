# PrimeUnits Farm Machinery and Agricultural Drone Expansion

## Claude CLI Implementation Prompt

You are working inside the existing **PrimeUnits** marketplace project.

Your task is to expand the **Farm Machines** and **Rentals** modules to prominently support:

1. Rice combine harvesters
2. Agricultural drones and agricultural drone services
3. Licensed or verified agricultural drone pilots
4. Realistic database seeders that demonstrate the complete feature after seeding

Do not begin coding immediately. First inspect the project structure, framework versions, database schema, models, category system, listing workflow, rental workflow, user roles, verification features, media storage, authorization policies, tests, and frontend design patterns.

Follow the existing architecture and naming conventions. Reuse working components and services. Do not rebuild unrelated modules or introduce a second implementation pattern when the project already has one.

If the application uses Laravel, React, Inertia, TypeScript, or another framework, follow the versions and conventions already installed in the repository. Do not downgrade or replace existing dependencies.

---

## 1. Business Objective

PrimeUnits must treat modern rice combine harvesters as a major agricultural equipment category because they are among the most in-demand machines used for rice harvesting.

PrimeUnits must also support the growing agricultural drone industry. Agricultural drones may be used for spraying, fertilizer application, seed spreading, field mapping, crop monitoring, and multispectral imaging.

The platform must clearly distinguish among:

- Equipment offered for sale
- Equipment offered for rent
- Equipment supplied with an operator
- Complete agricultural services charged by area, time, or project quotation

Agricultural drone operations must include pilot credential verification and configurable compliance controls.

---

## 2. Category Structure

Review the existing category tables and seeders. Extend them safely instead of duplicating categories.

Use the following suggested structure where compatible with the current system:

- Farm Machines
  - Tractors
  - Rice Combine Harvesters
  - Rice Transplanters
  - Tillers and Cultivators
  - Irrigation Equipment
  - Agricultural Drones
  - Other Farm Equipment
- Rentals
  - Farm Equipment Rentals
  - Rice Harvester Rentals
  - Agricultural Drone Rentals
  - Agricultural Drone Services

If PrimeUnits uses one canonical category tree, do not create duplicate category records for sale and rental. Use listing type, transaction type, service type, or category relationships to make the same equipment discoverable in the Farm Machines and Rentals sections.

Add navigation entries, category cards, search suggestions, filters, listing-form options, related-listing logic, and mobile-friendly views.

---

## 3. Rice Combine Harvester Listings

Add **Rice Combine Harvester** as a first-class farm-machine type.

Support sale listings, rental listings, and full harvesting-service listings.

### Harvester specifications

Use structured and filterable attributes where appropriate:

- Brand
- Model
- Model year
- Condition: Brand New, Used, or Reconditioned
- Engine power
- Fuel type
- Cutting width
- Feeding or threshing capacity
- Grain tank capacity
- Operating capacity in hectares per hour
- Supported crops
- Hour-meter reading
- Machine location
- Maintenance history
- Ownership or supporting documents
- Operator availability
- Transportation or delivery availability
- Service coverage area
- Availability schedule

### Commercial options

- Sale price
- Rate per hour
- Rate per hectare
- Rate per day
- Fixed project quotation
- Minimum rental duration or area
- Security deposit
- Operator fee
- Transportation fee
- Fuel included or excluded

### Fulfillment choices

Allow the provider to select one or more of the following:

- Machine only
- Machine with qualified operator
- Machine, operator, and transportation
- Complete harvesting service charged per hectare

The booking summary must itemize the base rate, operator fee, transportation fee, fuel charge when applicable, deposit, platform fee, taxes when supported, discounts, and total.

---

## 4. Agricultural Drone Listings and Services

Add **Agricultural Drone** as a first-class farm-machine type.

Support the following uses:

- Crop spraying
- Fertilizer application
- Seed spreading
- Field mapping
- Crop monitoring
- Multispectral imaging

### Drone specifications

- Brand
- Model
- Model year
- Condition
- Intended agricultural uses
- Maximum payload
- Spray tank capacity
- Flight time
- Number of batteries
- Charging equipment included
- Estimated coverage in hectares per hour
- GPS capability
- RTK capability
- Mapping capability
- Camera or sensor type
- Controller included
- Maintenance history
- Operating location
- Service coverage area
- Availability schedule

### Commercial options

- Sale price
- Rental rate per hour
- Rental rate per day
- Service rate per hectare
- Fixed project quotation
- Operator included
- Transportation fee
- Consumables included or excluded
- Security deposit

Clearly distinguish an equipment rental from an agricultural drone service performed by a verified operator.

---

## 5. Drone Pilot Credential and Compliance Module

Agricultural drone operation may require a qualified or licensed remote pilot and other authorization under applicable aviation or government regulations.

Build this requirement as an administrator-configurable verification system. Do not hardcode one country, issuing authority, credential name, document type, or legal conclusion into the core business logic.

PrimeUnits does not issue pilot licenses and must not claim to guarantee government approval. The platform only reviews submitted credentials for marketplace eligibility.

### Pilot credential fields

- User or operator ID
- Credential type
- Issuing authority
- Credential or license number
- Issue date
- Expiration date
- Country or jurisdiction
- Front document image when applicable
- Back document image when applicable
- Supporting PDF when applicable
- Verification status
- Reviewer ID
- Review date
- Reviewer notes
- Rejection reason
- Suspension reason
- Audit timestamps

### Verification statuses

- Not Submitted
- Pending Review
- Verified
- Rejected
- Expired
- Suspended

### Required behavior

- Only an authorized reviewer can view private credential documents.
- The operator can view their own submissions and status.
- Public pages must never expose credential files or complete credential numbers.
- Display a **Verified Drone Operator** badge only while the credential is verified and unexpired.
- Prevent expired, rejected, suspended, and unverified operators from accepting new drone-operation bookings.
- Allow administrators to define the credential types required for each jurisdiction or drone-service category.
- Support automatic expiration detection and configurable reminders.
- Record all approval, rejection, suspension, expiration, and re-verification actions in an audit trail.

### Rental restriction

Add an administrative setting that determines whether a particular agricultural drone may be rented without an operator.

When verified pilot credentials are required:

- Prefer **Drone with Verified Operator** as the default arrangement.
- Block an unverified renter from choosing self-operation.
- Allow self-operation only when the renter has a valid verified credential and the listing permits it.
- Revalidate the credential before booking confirmation and again before the scheduled rental begins.
- Store the verification decision used for the booking without exposing the private credential document.

### Compliance acknowledgement

Display this configurable notice before booking confirmation:

> Operation of agricultural drones may require a qualified or licensed remote pilot and authorization from the appropriate aviation or government authority. The renter and operator are responsible for complying with applicable laws, safety requirements, operating restrictions, and local regulations.

Require explicit acknowledgement and store:

- User ID
- Booking ID
- Notice version
- Notice text or immutable snapshot
- Acknowledgement date and time
- IP address or equivalent audit metadata if the existing privacy policy permits it

---

## 6. Search, Discovery, and User Interface

Add featured and discoverable entries for:

- Rice Combine Harvesters
- Agricultural Drones
- Drone Services
- Farm Equipment Rentals

### Harvester filters

- Brand
- Condition
- Location
- Sale, rental, or service
- Operator included
- Transportation included
- Rate per hour
- Rate per hectare
- Operating capacity
- Availability

### Drone filters

- Brand
- Intended use
- Payload
- Tank capacity
- Coverage capacity
- Sale, rental, or service
- Verified operator included
- Location
- Availability

Preserve the existing pagination, sorting, URL query parameters, responsive behavior, accessibility, loading states, validation feedback, and empty states.

Do not add visual elements that conflict with the current PrimeUnits design system.

---

## 7. Booking and Pricing Rules

Extend the existing rental and booking workflows to support:

- Hourly rental
- Daily rental
- Per-hectare service
- Fixed project quotation
- Operator fee
- Transportation fee
- Fuel or consumable inclusion
- Security deposit
- Booking location
- Service coverage validation
- Availability checking
- Cancellation policy

Use decimal-safe monetary calculations. Perform price calculation and booking validation on the server. Do not trust totals submitted by the browser.

Prevent overlapping confirmed bookings according to the project’s existing availability rules.

For drone bookings, show the operator’s public verification status and credential validity state without revealing private documents, birth dates, addresses, or full credential numbers.

---

## 8. Administration

Authorized administrators must be able to:

- Manage farm-equipment categories and attributes
- Configure required drone-pilot credentials
- Configure whether self-operated drone rentals are allowed
- Review credential submissions
- Approve, reject, suspend, expire, or return submissions for correction
- Configure credential-expiration reminders
- Manage compliance-notice versions
- Inspect credential and booking audit logs
- Filter listings by farm-equipment type
- Identify drone listings without a verified operator
- Disable or suspend noncompliant listings

Use the existing role and permission system. Add granular permissions only when the project requires them.

Suggested permissions:

- `farm-equipment.manage`
- `drone-credentials.review`
- `drone-credentials.view-private-documents`
- `drone-compliance.manage`
- `drone-listings.suspend`

Adapt permission names to the project’s established convention.

---

## 9. Database Design

Inspect the current schema before designing migrations.

Prefer a maintainable attribute model that can accommodate additional farm machines later. Avoid creating an entirely separate listing table for every equipment type unless the existing architecture explicitly follows that pattern.

Create safe migrations, indexes, foreign keys, model relationships, casts, enums or status objects, policies, requests or validators, resources, factories, and seeders as appropriate.

Requirements:

- Preserve all existing data.
- Do not rename or delete existing columns unless clearly safe and necessary.
- Make seeders idempotent where practical.
- Use stable slugs or lookup keys instead of assuming numeric IDs.
- Do not create duplicate categories when seeders run more than once.
- Store sensitive credential documents in private storage.
- Do not commit actual private identity documents to the repository.

---

## 10. Required Demo Seeders

Create realistic sample seeders so the feature can be reviewed immediately after running the project’s standard database seed command.

Integrate them into the main development/demo seeder using the project’s established environment safeguards. Do not seed fake production data in production environments.

Use deterministic demo emails and stable lookup values so automated tests can reference them. Use the project’s standard demo password convention and document it in the implementation summary. Do not create a new insecure password convention.

### A. Category seeder

Create or update categories for:

- Farm Machines
- Rice Combine Harvesters
- Agricultural Drones
- Farm Equipment Rentals
- Agricultural Drone Services

Use `updateOrCreate`, `firstOrCreate`, upserts, or the equivalent supported by the project.

### B. Demo provider accounts

Create these sample users or their equivalent roles:

1. **Golden Fields Machinery**
   - Role: Equipment seller and rental provider
   - Location: Tagum City, Davao del Norte
   - Verified provider

2. **AgriFlight Drone Services**
   - Role: Agricultural drone service provider
   - Location: Panabo City, Davao del Norte
   - Verified provider

3. **Demo Verified Drone Pilot**
   - Email: `pilot.verified@primeunits.test`
   - Pilot status: Verified
   - Credential must have a future expiration date calculated relative to the seed date
   - Use an obviously fictional masked credential number
   - Do not seed a fake government document image

4. **Demo Pending Drone Pilot**
   - Email: `pilot.pending@primeunits.test`
   - Pilot status: Pending Review

5. **Demo Expired Drone Pilot**
   - Email: `pilot.expired@primeunits.test`
   - Pilot status: Expired
   - Credential expiration date must be in the past

6. **Demo Customer/Farmer**
   - Email: `farmer.demo@primeunits.test`
   - Role: Buyer and renter
   - Location: Nabunturan, Davao de Oro

Use existing role names when they differ from these descriptions.

### C. Rice combine harvester listings

Seed at least three visible listings with placeholder-safe images or the project’s normal demo-image mechanism:

#### Listing 1: Modern rice combine harvester for rent

- Title: `Modern Rice Combine Harvester with Operator`
- Transaction type: Rental and harvesting service
- Condition: Used, excellent condition
- Location: Tagum City, Davao del Norte
- Supported crop: Rice
- Capacity: 0.8 to 1.2 hectares per hour
- Operator included: Yes
- Transportation available: Yes
- Pricing: per hectare and per day
- Availability: Available
- Featured: Yes

#### Listing 2: Compact rice harvester for sale

- Title: `Compact Rice Combine Harvester for Sale`
- Transaction type: Sale
- Condition: Reconditioned
- Location: Digos City, Davao del Sur
- Supported crop: Rice
- Hour-meter reading: realistic sample value
- Ownership documents: marked available for review
- Featured: No

#### Listing 3: Full rice harvesting service

- Title: `Complete Rice Harvesting Service per Hectare`
- Transaction type: Service
- Location: Nabunturan, Davao de Oro
- Operator included: Yes
- Transportation included: Yes within configured coverage area
- Pricing unit: Per hectare
- Minimum area: realistic demo value
- Service coverage: selected municipalities in Davao de Oro
- Featured: Yes

Use realistic but clearly demonstrative prices in Philippine pesos. Store money using the application’s existing convention, such as integer centavos, decimal columns, or a money value object. Do not introduce floating-point money errors.

### D. Agricultural drone listings

Seed at least four visible listings:

#### Listing 1: Agricultural spraying service

- Title: `Agricultural Drone Spraying Service with Verified Pilot`
- Transaction type: Service
- Uses: Crop spraying and fertilizer application
- Location: Panabo City, Davao del Norte
- Verified operator included: Yes
- Pricing unit: Per hectare
- Featured: Yes
- Associate it with the verified pilot

#### Listing 2: Agricultural drone with operator for rent

- Title: `Agricultural Spraying Drone with Licensed Pilot`
- Transaction type: Rental
- Tank capacity: realistic sample value
- Batteries and charging equipment included
- Self-operation allowed: No
- Verified operator required: Yes
- Associate it with the verified pilot

#### Listing 3: Mapping and crop-monitoring service

- Title: `Farm Mapping and Crop Monitoring Drone Service`
- Transaction type: Service
- Uses: Field mapping, crop monitoring, and multispectral imaging
- RTK capable: Yes
- Pricing: Per hectare or fixed quotation
- Verified operator included: Yes

#### Listing 4: Drone awaiting pilot verification

- Title: `Agricultural Drone Rental Pending Operator Verification`
- Transaction type: Rental
- Associate it with the pending pilot
- Listing compliance status: Pending or restricted
- It must not be bookable until verification is completed
- Display it only in the administrative review context unless the existing system supports a clearly labeled draft or pending state

Do not use real credential numbers, real identity documents, copyrighted product photographs without permission, or claims of government endorsement.

### E. Credential seeder

Seed the following credential cases:

- One verified, unexpired credential
- One pending credential
- One expired credential
- Optionally one rejected credential if useful for the administrative review screen

Use clearly fictional values such as `DEMO-RPAS-VERIFIED-001`. Mask the public version. Store no actual document; use a safe demo placeholder only if the existing development environment requires a file relationship.

### F. Availability and pricing seeder

Create availability records for the seeded rental and service listings covering several future dates.

Include examples of:

- Available date
- Unavailable maintenance date
- Reserved date
- Per-hectare pricing
- Daily pricing
- Operator fee
- Transportation fee
- Security deposit

### G. Demo booking seeder

Create representative bookings only if the existing schema supports safe demo bookings:

1. Confirmed harvester service booking charged per hectare
2. Pending agricultural drone spraying-service booking with the verified pilot
3. Completed farm-equipment rental for booking-history display

Do not create a confirmed drone-operation booking for the pending or expired pilot.

### H. Compliance notice seeder

Seed an active, versioned drone compliance notice using the wording in this document.

If acknowledgements can be safely seeded, add one acknowledgement connected to the pending drone service booking and the active notice version.

---

## 11. Seeder Execution and Documentation

Create clearly named seeders following the repository’s conventions. Suggested names, to be adapted if necessary:

- `FarmEquipmentCategorySeeder`
- `FarmEquipmentDemoUserSeeder`
- `DronePilotCredentialSeeder`
- `RiceHarvesterListingSeeder`
- `AgriculturalDroneListingSeeder`
- `FarmRentalAvailabilitySeeder`
- `FarmRentalBookingSeeder`
- `DroneComplianceNoticeSeeder`
- `PrimeUnitsFarmMarketplaceDemoSeeder`

The parent seeder must call dependencies in the correct order.

Verify all of the following:

- A clean database can be migrated and seeded successfully.
- Running the demo seeder again does not duplicate records.
- Foreign keys reference the correct seeded users, categories, listings, credentials, and bookings.
- Seeded image references do not break the UI.
- The seeded records appear in Farm Machines, Rentals, search, listing details, and administration screens as intended.

Document the exact commands needed to run only this demo dataset and to run the full development seed process.

---

## 12. Security and Privacy

Implement server-side authorization and validation for every new action.

- Validate credential file type, content, and size.
- Use private storage for credential documents.
- Never expose private storage URLs directly.
- Mask credential numbers on public and ordinary staff views.
- Restrict private-document access to the owner and specifically authorized reviewers.
- Prevent mass-assignment vulnerabilities.
- Prevent insecure direct-object references.
- Record administrative review actions.
- Prevent invalid or expired pilots from accepting operational drone bookings.
- Preserve historical booking and audit data when verification status changes.

---

## 13. Automated Tests

Add or update tests for:

- Category seeder idempotency
- Demo seeder execution on a clean test database
- Creating a rice combine harvester sale listing
- Creating a harvester rental listing
- Creating a per-hectare harvesting service
- Creating an agricultural drone listing
- Submitting pilot credentials
- Approving and rejecting credentials
- Automatic expired status handling
- Suspending a verified pilot
- Blocking self-operated drone rental by an unverified user
- Allowing self-operation by a verified pilot when the listing permits it
- Booking a drone service with a verified operator
- Preventing pending or expired pilots from accepting operational bookings
- Booking a harvester by hectare
- Price calculation and charge itemization
- Availability and overlapping-booking protection
- Private credential-document authorization
- Public credential-number masking
- Search and filtering
- Mobile-facing API resources when the project provides an API

Run the relevant backend and frontend test suites. Fix any regression introduced by this work.

---

## 14. Acceptance Criteria

The implementation is complete only when:

1. Users can find Rice Combine Harvesters and Agricultural Drones from the marketplace navigation and search.
2. Providers can create sale, rental, or service listings using the correct equipment-specific fields.
3. Harvester providers can charge per hour, day, hectare, or project quotation where supported.
4. Agricultural drone listings can require a verified pilot.
5. Unverified, pending, expired, rejected, or suspended users cannot perform protected drone-operation actions.
6. Private pilot documents remain protected.
7. The seeded verified operator displays a valid public badge.
8. The seeded pending drone listing cannot be booked.
9. Seeded harvester and drone listings appear correctly on desktop and mobile layouts.
10. Database seeders run successfully and do not create duplicate data when rerun.
11. Automated tests pass.
12. Existing marketplace categories and transactions continue to work.

---

## 15. Required Completion Report

After implementation, provide:

1. Summary of the completed changes
2. Architecture and database decisions
3. Files created or modified
4. Migration names
5. Seeder names and seeded demo accounts
6. Demo login instructions using the existing demo-password convention
7. Permissions and configuration added
8. Commands for migration, seeding, storage linking when applicable, frontend building, and testing
9. Automated test results
10. Manual verification checklist
11. Assumptions or unresolved decisions
12. Recommended next phase

Do not merely provide sample code or a plan. Implement the feature in the repository, run the migrations and seeders in a safe development or test environment, execute the relevant tests, and report the actual results.
