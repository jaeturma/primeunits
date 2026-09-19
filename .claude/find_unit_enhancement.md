You are Claude Sonnet 5 acting as a senior product architect, UX designer, and Laravel 13 + React/Inertia engineer.

We are enhancing the existing **PrimeUnits** marketplace.

Your task is specifically to **inspect, redesign, and improve the front-end “Find a Unit” search experience**, including the category and classification logic behind it.

Do NOT immediately start coding.

First inspect the existing project thoroughly and understand how PrimeUnits currently represents:

* Categories
* Classifications
* Unit types
* Makes/brands
* Models
* Conditions
* Listing types
* Listing plans
* Seller/business types
* Rental units
* Search/filter logic
* Database relationships
* Existing React/Inertia components
* Existing routes/controllers
* Existing API/query logic
* Existing seeders/factories
* Existing landing page

The goal is to improve the system WITHOUT creating duplicate or conflicting category structures.

---

# PRIMEUNITS BUSINESS CONCEPT

PrimeUnits is not limited to automobiles.

It is a marketplace for:

### PERSONAL & COMMERCIAL VEHICLES

* Cars
* Motorcycles
* Electric Vehicles
* Trucks
* Vans
* Buses
* Commercial vehicles
* Utility vehicles

### AGRICULTURAL

* Tractors
* Farm tractors
* Hand tractors
* Harvesters
* Rice machinery
* Corn machinery
* Farm implements
* Agricultural equipment

### HEAVY EQUIPMENT

* Excavators
* Backhoes
* Bulldozers
* Wheel loaders
* Road graders
* Forklifts
* Cranes
* Construction equipment

### OTHER MOBILITY / EQUIPMENT

* Boats
* Generators
* Industrial equipment
* Specialty vehicles

### RENTALS

Rental listings may exist across several categories:

* Car rental
* Motorcycle rental
* Van rental
* Truck rental
* Bus rental
* Heavy equipment rental
* Farm equipment rental
* Event/wedding vehicles
* Tourist transport

The taxonomy must therefore support both **vehicle/equipment type** and **business purpose/listing type**.

---

# CORE PRINCIPLE

Do NOT treat everything as a single flat category.

PrimeUnits should conceptually distinguish:

**Category → Classification → Type → Make → Model**

where appropriate.

For example:

Automotive
→ Passenger Vehicle
→ SUV
→ Toyota
→ Fortuner

Automotive
→ Motorcycle
→ Scooter
→ Honda
→ Click 160

Agricultural
→ Farm Machinery
→ Tractor
→ Kubota
→ M6040

Heavy Equipment
→ Construction Equipment
→ Excavator
→ Caterpillar
→ 320

However, do not blindly impose this structure if the existing application already has a better data model.

Inspect first.

---

# STEP 1 — AUDIT THE EXISTING TAXONOMY

Before modifying anything, identify:

1. What is currently called Category?
2. What is currently called Classification?
3. Are Category and Classification separate database entities?
4. Are they parent/child relationships?
5. Are they being used consistently?
6. Are some classifications actually product types?
7. Are some categories actually business/listing types?
8. Are there duplicate concepts?
9. Are categories hardcoded or database-driven?
10. How are listings associated with them?
11. How does the current search query use them?

Produce a short internal assessment before implementation.

Look specifically for problems such as:

* “Car” being both category and classification
* “SUV” being treated as a category
* “EV” being mixed with body type
* “Rental” being treated as a vehicle category
* “Dealer” being treated as a category
* “Truck” being mixed with commercial usage
* Agricultural and heavy equipment not fitting naturally
* Brand/model filtering being coupled too tightly to categories

---

# STEP 2 — DESIGN A CLEAN SEARCH TAXONOMY

Design the taxonomy around how a real buyer thinks.

A user should be able to start with:

**What are you looking for?**

Examples:

🚗 Cars
🏍 Motorcycles
⚡ Electric Vehicles
🚚 Trucks & Commercial
🚜 Farm Machines
🏗 Heavy Equipment
🚤 Other Units
🔑 Rentals

Then progressively narrow the search.

Example:

Cars
→ SUV
→ Toyota
→ Fortuner
→ 2022–2025
→ Diesel
→ Automatic
→ Davao Region

Motorcycles
→ Scooter
→ Honda
→ Click
→ Automatic

Heavy Equipment
→ Excavator
→ Caterpillar
→ 320

Farm Machines
→ Tractor
→ Kubota

Do not expose unnecessary complexity initially.

---

# STEP 3 — REDESIGN THE FRONT “FIND A UNIT”

The current landing-page search should become one of the strongest components on the PrimeUnits homepage.

It should feel like a premium marketplace search rather than a generic website search box.

Create a prominent:

## FIND A UNIT

Search experience.

Recommended structure:

### Main Search Bar

Placeholder:

“Search cars, motorcycles, EVs, trucks, farm machines, heavy equipment…”

Allow natural search such as:

* Toyota Fortuner
* Honda Click 160
* electric SUV
* excavator
* tractor
* truck for sale
* motorcycle rental

Then provide structured filters below/around it.

---

# SEARCH MODES

Consider supporting:

### 1. Buy

Find units for sale.

### 2. Rent

Find rental units/services.

### 3. Browse

Explore the entire marketplace.

If the existing application already has listing-purpose logic, integrate with it rather than creating another competing system.

---

# CATEGORY SELECTOR

Create a visually excellent category selector.

Desktop:

[ All ] [ Cars ] [ Motorcycles ] [ EV ] [ Trucks ] [ Farm ] [ Heavy Equipment ] [ Rentals ]

Mobile:

Use horizontally scrollable category chips/cards or a compact selector.

Each category should have:

* Icon
* Name
* Optional listing count
* Hover/active state
* Clear visual hierarchy

Do not overload the user with classifications at this stage.

---

# SMART CLASSIFICATION FILTER

After selecting a category, dynamically show relevant classifications.

For example:

### Cars

Classification:

* Sedan
* Hatchback
* SUV
* MPV
* Pickup
* Van
* Coupe
* Convertible
* Wagon

### Motorcycles

* Scooter
* Underbone
* Standard
* Sport Bike
* Cruiser
* Adventure
* Touring
* Off-road
* Electric Motorcycle

### Electric Vehicles

Depending on the existing data model:

* EV Car
* EV SUV
* EV Sedan
* EV Van
* EV Motorcycle
* E-Trike
* E-Bike
* Other EV

Do not duplicate EV unnecessarily if EV is represented as a powertrain/fuel attribute.

This is important:

Determine whether **EV should be a Category, Classification, or attribute/filter** based on the existing architecture and the intended search behavior.

Make a recommendation based on the current data model before changing it.

---

# CONTEXTUAL FILTERS

Once category/classification is selected, reveal relevant filters.

For Cars:

* Make
* Model
* Year
* Price
* Transmission
* Fuel
* Mileage
* Condition
* Location

For Motorcycles:

* Make
* Model
* Year
* Engine displacement
* Transmission
* Condition
* Price
* Location

For EV:

* Make
* Model
* Year
* Battery capacity
* Range
* Charging type
* Price
* Location

For Trucks:

* Make
* Model
* Year
* Truck type
* Payload/capacity
* Body type
* Price
* Location

For Farm Machinery:

* Equipment type
* Make
* Model
* Year
* Engine/hours
* Condition
* Price
* Location

For Heavy Equipment:

* Equipment type
* Make
* Model
* Year
* Operating hours
* Capacity
* Condition
* Price
* Location

Do not show irrelevant filters.

---

# LOCATION

PrimeUnits should support Philippine marketplace searches.

Allow:

* Region
* Province
* City/Municipality

Examples:

Davao Region
→ Davao de Oro
→ Compostela

or:

Cebu
→ Cebu Province
→ Cebu City

If the existing location structure is already implemented, reuse it.

---

# CONDITION

Support relevant conditions such as:

* Brand New
* Used
* Pre-Owned
* Refurbished
* For Parts
* Dealer Demo

Only expose conditions that make sense for the selected category.

---

# SELLER TYPE

Consider a filter for:

* Individual
* Reseller
* Dealer
* Official Brand
* Company
* Rental Provider

But do not put this ahead of the core vehicle search.

This is a secondary filter.

---

# LISTING QUALITY / TRUST

Where supported by the existing system, allow:

* Verified Seller
* Verified Dealer
* Prime Listing
* Sponsored
* Featured

Do not create fake verification capabilities just for UI appearance.

Only display these if the backend supports them.

---

# PRICE

The search interface should support:

* Min price
* Max price
* Flexible price range

Format Philippine peso naturally:

₱500,000

Avoid confusing raw numeric formatting.

---

# UX REQUIREMENTS

The search should be:

* Extremely easy for first-time users
* Fast
* Mobile-first
* Visually premium
* Minimal
* Responsive
* Accessible
* Keyboard-friendly
* Touch-friendly

Avoid creating a giant filter panel immediately.

Use progressive disclosure.

The first interaction should be simple:

**Find a unit → choose category → narrow down**

---

# LANDING PAGE VISUAL DESIGN

The search component should become a visual focal point of the homepage.

Consider:

* Large premium search container
* Subtle glass/blur effect if compatible with the existing design
* Strong depth and elevation
* Modern rounded corners
* Excellent typography
* Subtle animation
* Category icons
* Clear active states
* Elegant dropdowns
* Smooth transitions
* Premium vehicle imagery where appropriate

Do not make it look like a generic Bootstrap form.

It should feel like a modern:

**mobility marketplace + premium automotive platform + SaaS-quality interface.**

---

# SEARCH RESULTS EXPERIENCE

Inspect the existing results page and make sure the new search parameters integrate correctly.

The search should generate clean query parameters, for example conceptually:

/units?
category=cars
&classification=suv
&make=toyota
&model=fortuner
&min_price=500000
&max_price=1500000
&location=davao

Use the application's actual route/query conventions rather than blindly adopting this example.

Important:

Search URLs should ideally be:

* Shareable
* Bookmarkable
* SEO-friendly where appropriate
* Preserving filters during navigation
* Easy to understand

---

# DATABASE / BACKEND

Do not duplicate taxonomy tables unnecessarily.

Inspect existing migrations/models first.

If changes are required:

* Explain why
* Create proper migrations
* Preserve existing data
* Add relationships cleanly
* Avoid destructive migrations
* Maintain backward compatibility where practical

Consider whether the application needs:

Category
Classification
Type
Make
Model

or whether some of these can be represented using existing structures.

Do not add database complexity simply because it looks architecturally elegant.

PrimeUnits should remain maintainable.

---

# PERFORMANCE

Search must remain efficient.

Inspect:

* Eloquent relationships
* Query scopes
* indexes
* N+1 queries
* eager loading
* pagination
* filtering strategy

If the search is database-driven, ensure appropriate indexes exist for commonly queried fields.

Do not introduce unnecessary Elasticsearch/Algolia/etc. unless the existing project actually requires it.

---

# IMPORTANT: EXISTING DATA

Before changing taxonomy:

Inspect current records.

Determine whether existing listings already contain:

* category_id
* classification_id
* type
* make
* model
* listing_type
* plan
* seller_type
* rental information

If there is existing production/demo data, DO NOT blindly migrate or rename values.

Create a compatibility strategy.

---

# FABLE 5 UI STANDARD

Use Fable 5 to produce a visually exceptional interface.

Prioritize:

* Strong visual hierarchy
* Premium spacing
* Beautiful cards
* Excellent typography
* Modern micro-interactions
* Clear affordances
* Sophisticated but restrained gradients
* Smooth hover/focus transitions
* High-quality responsive behavior
* Excellent mobile experience

Avoid:

* excessive animations
* huge gradients everywhere
* excessive glassmorphism
* unnecessary cards inside cards
* clutter
* giant filter forms
* generic dashboard styling
* over-engineering

The design should look expensive without being visually noisy.

---

# IMPLEMENTATION PROCESS

Follow this sequence:

1. Inspect project.
2. Inspect current landing page.
3. Inspect search implementation.
4. Inspect Category/Classification models and migrations.
5. Inspect listing model and relationships.
6. Inspect actual existing records/seeds.
7. Identify taxonomy inconsistencies.
8. Propose the clean taxonomy.
9. Implement the minimum necessary backend changes.
10. Redesign the Find a Unit component.
11. Connect it to real search/filter logic.
12. Enhance mobile experience.
13. Test search combinations.
14. Test existing listing flows.
15. Run build/tests.
16. Fix regressions.
17. Review the landing page visually.
18. Refine spacing, typography, interaction and responsiveness.

---

# ACCEPTANCE TESTS

Test scenarios such as:

1. Find all Cars.
2. Cars → SUV.
3. Cars → Toyota → Fortuner.
4. Motorcycles → Scooter → Honda.
5. EV → appropriate EV classification.
6. Trucks → appropriate truck classification.
7. Farm Machines → Tractor.
8. Heavy Equipment → Excavator.
9. Rental search.
10. Price range.
11. Location filtering.
12. Make + model filtering.
13. Multiple filters simultaneously.
14. Clear all filters.
15. Browser back/forward.
16. Mobile search.
17. Empty results.
18. Invalid filter combinations.
19. Existing listings remain searchable.
20. Existing listing creation/editing still works.

---

# IMPORTANT DESIGN DECISION

Do not assume the taxonomy is correct simply because it already exists.

You are explicitly authorized to identify and correct poor category/classification architecture.

But do this carefully.

If the current implementation is already sound, preserve it and focus on improving the UX.

The final PrimeUnits search experience should communicate:

**“Whatever vehicle or equipment you are looking for, PrimeUnits helps you find it quickly.”**

The result should be visually outstanding while remaining technically simple, maintainable, and scalable.

At the end, report:

### AUDIT

What the existing Category/Classification system currently does.

### RECOMMENDATION

What should change and why.

### IMPLEMENTATION

Exactly what you changed.

### UI

What was improved in Find a Unit.

### DATABASE

Any migrations/models/indexes changed.

### TESTING

Commands run and results.

### REMAINING

Any issues or future enhancements that should NOT be implemented yet.
