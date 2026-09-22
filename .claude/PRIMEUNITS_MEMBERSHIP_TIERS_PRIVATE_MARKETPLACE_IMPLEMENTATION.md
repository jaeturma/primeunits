# PrimeUnits Membership Tiers and Private Marketplace

## Claude CLI Implementation Prompt

You are working inside the existing **PrimeUnits** marketplace project.

Implement a tiered marketplace with **Regular**, **Silver**, and **Gold** membership, separate buyer and seller authorization, optional verified stores, restricted premium listings, privacy controls, upgrade applications, administration, sample data, and automated tests.

Do not begin coding immediately. First inspect the complete repository and document the current:

- Framework and dependency versions
- Authentication and user models
- Role and permission system
- Identity verification process
- Category and listing architecture
- Store or dealer implementation
- Subscription and payment features
- Media and private-file storage
- Search, filtering, messaging, booking, offer, and transaction workflows
- Administrative interface
- Audit logging
- Tests, factories, and seeders
- Frontend design system and mobile behavior

Follow the existing architecture, code style, UI components, authorization conventions, and naming patterns. Extend working features instead of rebuilding them. Do not replace dependencies or rewrite unrelated modules.

If a requested capability already exists, reuse and extend it. If the project lacks payment integration, implement membership plans, applications, entitlements, and safe placeholder/manual activation workflows without inventing a production payment gateway.

---

## 1. Product Principle

PrimeUnits will have three marketplace levels:

1. **PrimeUnits Regular**
2. **PrimeUnits Silver**
3. **PrimeUnits Gold**

The implementation must keep the following concepts separate:

- Membership subscription or plan
- Identity verification
- Buyer access level
- Seller authorization level
- Store status and store access level
- Listing marketplace tier
- Listing visibility level
- Category-specific credentials

Do not implement access using only one `membership_level` column or one role.

A user may legitimately be:

- Gold buyer
- Regular seller
- No store
- Verified identity

Another user may be:

- Regular buyer
- Silver seller
- Owner of a Silver verified store

Model these states independently using the project’s existing entitlement, permission, status, or policy patterns.

Suggested conceptual fields or relationships, to be adapted to the current schema:

```text
membership_plan
membership_status
identity_verification_level
buyer_access_level
seller_access_level
store_status
store_access_level
```

Do not duplicate these fields if equivalent structures already exist.

---

## 2. PrimeUnits Regular

Regular membership is free or entry-level.

Public visitors may browse public listings without an account when the existing application permits it. This preserves discovery, link sharing, marketing, and search visibility.

Users must complete basic verification before they can publish listings, contact protected sellers, submit offers, book rentals, or enter transactions.

### Regular verification

- Valid government-issued ID
- Selfie or liveness verification when supported
- Verified mobile number
- Verified email address
- Address or general location
- Marketplace terms acceptance

Make verification requirements configurable by administrators. Never expose private identity documents publicly.

### Regular categories

- Ordinary motorcycles
- Cars and SUVs
- Electric vehicles
- Vans and trucks
- Farm machines
- Rice combine harvesters
- Agricultural drones
- Heavy equipment
- Equipment rentals
- General mobility and equipment services

Category-specific credentials remain separate. For example, agricultural drone operation may require verified pilot credentials even when the user has a higher membership tier.

---

## 3. PrimeUnits Silver

Silver is the premium marketplace for high-value road vehicles, premium motorcycles, armored vehicles, and recreational personal watercraft.

### Silver categories

- Premium and performance motorcycles
- Big bikes
- Luxury and high-end SUVs
- Sports and performance cars
- Exotic vehicles
- Armored and security vehicles
- Personal watercraft
- Premium collector vehicles
- Premium custom motorcycles

Use the label **Armored & Security Vehicles**, not “Bullet Proofs,” in the professional user interface.

Use **Personal Watercraft (Jet Skis)** or **Personal Watercraft** as the main category. Avoid using a specific manufacturer’s brand as the generic internal category identifier.

### Silver buyer requirements

- Completed Regular verification
- Address verification
- Enhanced selfie or liveness verification where supported
- Active Silver or Gold membership
- No active fraud, security, or serious dispute restriction
- Administrative review when risk rules require it
- Optional proof of financial capacity for a specific confidential listing

### Silver seller requirements

Silver buyer access must not automatically authorize Silver selling.

Require the appropriate combination of:

- Completed identity verification
- Active Silver or Gold membership when configured
- Ownership or brokerage-authority documents
- Registration documents
- VIN, chassis, engine, or hull reference
- Seller declaration
- Service or modification records when applicable
- Armoring documents when applicable
- Listing-level administrative review

Support these independent states:

- Silver Buyer
- Silver Seller
- Silver Buyer and Seller

---

## 4. PrimeUnits Gold

Gold is a private and professionally reviewed marketplace for rare, specialist, aviation, and marine assets.

### Gold categories

- Rare and collector choppers
- Collector motorcycles
- Private aircraft
- Light aircraft
- Turboprop aircraft
- Private jets
- Helicopters
- Sailing yachts
- Motor yachts
- Superyachts
- Large leisure vessels
- Commercial vessels by special approval

Do not classify every chopper as Gold. Support category rules such as:

- Ordinary custom motorcycle: Regular or Silver
- Premium custom build: Silver
- Rare, historically significant, collector, or specially approved chopper: Gold

Do not classify every day boat as Gold. Use:

- Personal watercraft: Silver
- Ordinary day boats and speedboats: Silver
- Premium or collector day boats: Gold when approved
- Yachts and superyachts: Gold

Commercial ships must require special professional or enterprise approval. Do not allow ordinary Gold members to freely publish commercial ships without manual review.

### Gold buyer requirements

- Completed Silver-level verification or equivalent
- Active Gold membership
- Manual Gold-access approval
- Enhanced identity and address verification
- No serious unresolved fraud or dispute restriction
- Confidentiality or NDA acceptance when required
- Optional proof of funds or financial capacity for selected listings
- Seller approval for invitation-only listings

### Gold seller requirements

- Verified identity or verified business
- Active applicable membership when configured
- Ownership or brokerage authority
- Category-specific registration and supporting records
- Maintenance, service, inspection, survey, or operational records where applicable
- Manual seller authorization
- Manual approval of every Gold listing

A seller may submit a listing as a **Gold candidate**, but only PrimeUnits administrators or authorized Gold reviewers can approve its Gold classification.

---

## 5. Optional Store System

A store is optional and independent of buyer membership and seller authorization.

### Individual seller without a store

An approved individual seller may:

- Sell personally owned assets
- Publish a configurable limited number of listings
- Respond to inquiries
- Receive reviews
- Apply separately for Regular, Silver, or Gold seller authorization

### Verified business store

Support verified stores for:

- Dealerships
- Equipment suppliers
- Rental providers
- Independent brokers
- Brokerage companies
- Charter operators
- Fleet or corporate owners
- Manufacturers and distributors

Business verification may include:

- DTI, SEC, CDA, or equivalent registration
- Business permit
- Tax information when required
- Authorized representative
- Verified business address and contacts
- Dealer, broker, charter, or operator credentials where applicable
- Store policies
- Payout or bank-account verification when the platform supports payments

### Store levels

- Regular Store
- Silver Store
- Gold Professional Store
- Gold Enterprise or Commercial Vessel Store when later enabled

A verified store must not automatically receive Silver or Gold authority. It must separately qualify for each store and seller access level.

### Seller capacity declaration

Every premium listing must declare whether the seller is:

- Private owner
- Authorized dealer
- Independent broker
- Brokerage company
- Charter operator
- Fleet or corporate owner
- Manufacturer or distributor

Show this capacity clearly on the public listing without exposing private documents.

---

## 6. Membership Statuses

Support configurable membership statuses:

- Trial
- Pending Payment
- Pending Review
- Active
- Grace Period
- Expired
- Suspended
- Cancelled
- Banned

An expired or cancelled member must lose access to newly protected premium content while retaining lawful access to:

- Their invoices
- Their transaction and booking history
- Existing contractual records
- Dispute records
- Messages needed for an active transaction or dispute, subject to policy

Suspension and banning must follow existing moderation and audit rules.

---

## 7. Upgrade and Downgrade Workflows

Implement upgrade applications separately for buyer, seller, and store access.

### Buyer upgrade

```text
Regular Verified User
  -> Apply for Silver
  -> Complete Enhanced Verification
  -> Silver Buyer Approved
  -> Apply for Gold
  -> Manual Gold Review
  -> Gold Buyer Approved
```

### Seller upgrade

```text
Regular Seller
  -> Apply for Silver Seller Authorization
  -> Submit Ownership/Business Documents
  -> Silver Seller Approved
  -> Apply for Gold Seller Authorization
  -> Specialist Review
  -> Gold Seller Approved
```

### Store upgrade

```text
No Store or Regular Store
  -> Business Verification
  -> Silver Store Application
  -> Silver Store Approved
  -> Gold Professional Application
  -> Specialist Review
  -> Gold Professional Store Approved
```

Every application must support:

- Draft
- Submitted
- Pending Review
- Additional Information Required
- Approved
- Rejected
- Withdrawn
- Suspended after approval
- Expired when applicable

Store reviewer identity, dates, internal notes, applicant-visible notes, rejection reasons, and status history.

---

## 8. Marketplace Mode Switching

Use one application, one account, and one canonical listing system.

Eligible users must be able to switch between:

- PrimeUnits Regular
- PrimeUnits Silver
- PrimeUnits Gold

The switcher must only show modes available to the current user. It must not grant authorization by changing frontend state.

All access must be enforced by server-side policies, query scopes, API resources, and authorization checks.

Remember the user’s most recent permitted marketplace mode. If membership expires or access is suspended, safely fall back to the highest currently authorized mode.

Provide distinct but consistent visual treatment:

- Regular: existing PrimeUnits design
- Silver: refined silver accent
- Gold: restrained premium gold accent

Do not create a completely separate design system or duplicate application routes unnecessarily.

---

## 9. Listing Marketplace Tier and Visibility

Listing marketplace tier and listing visibility are different concepts.

### Marketplace tiers

- Regular
- Silver
- Gold
- Gold Enterprise or Commercial, reserved for future/special approval

### Visibility levels

- Public
- Public Preview
- Silver Exclusive
- Gold Exclusive
- Verified Buyer Only
- Invitation Only

Example: a Gold yacht may have a Public Preview while its complete information remains Gold Exclusive or Invitation Only.

### Public preview

A public preview may show:

- Asset category
- General description
- Selected approved photographs
- Approximate price range or Price on Request
- General location
- Gold or Silver badge

It must hide confidential information such as:

- Owner identity
- Exact storage, hangar, berth, or residence location
- Registration numbers
- Complete VIN, chassis, serial, aircraft, or hull numbers
- Security specifications
- Travel schedules
- Private contact details
- Private documents

### Access requests

Allow qualified users to request access to restricted or invitation-only listings.

Suggested statuses:

- Pending
- Approved
- Rejected
- Revoked
- Expired

Support seller approval, administrator approval, expiry, reason, and audit history.

---

## 10. Category and Value Rules

Do not permanently determine marketplace tier only from a category name. Category provides the default tier, but administrators must be able to configure rules and exceptions.

Examples:

- An ordinary chopper may be Silver, while a rare collector chopper may be Gold.
- A small day boat may be Silver, while a collector or luxury day boat may be Gold.
- An unusually valuable vehicle may require Gold-level confidentiality even when its category normally belongs to Silver.

Create configurable category-access rules with appropriate fields such as:

- Default marketplace tier
- Minimum buyer access
- Minimum seller access
- Manual listing review required
- Public preview allowed
- Verified buyer required
- Ownership documents required
- Category-specific credentials required
- Proof of funds may be requested
- Confidentiality agreement may be required

Do not rely only on a price threshold. Value, rarity, risk, regulatory complexity, and privacy may affect tier assignment.

---

## 11. Category-Specific Verification

Design a configurable requirements framework rather than hardcoding one country’s document names into core logic.

### Premium motorcycles and vehicles

- Ownership or authority to sell
- Registration information
- VIN, chassis, and engine information
- Encumbrance declaration
- Service history when available
- Modification records

### Armored and security vehicles

- Ownership and registration information
- Armoring provider or modification documentation when available
- Non-sensitive protection classification
- Manual listing approval

Never publicly reveal protection weaknesses, detailed security layouts, owner routines, or sensitive defensive specifications.

### Aircraft

- Ownership or brokerage authority
- Registration information
- Aircraft serial information
- Airworthiness and maintenance information
- Total time and cycle information
- Engine and component history
- Manual specialist review

### Yachts and vessels

- Ownership or brokerage authority
- Registration information
- Hull identification or official number
- Length, beam, draft, and tonnage
- Flag or jurisdiction
- Maintenance, inspection, or survey information when applicable
- Manual specialist review

PrimeUnits must clearly distinguish between:

- User submitted
- Document submitted
- PrimeUnits reviewed
- Third-party verified
- Government issued

Do not claim that PrimeUnits guarantees title, authenticity, airworthiness, seaworthiness, legal transfer, or government approval.

---

## 12. Yacht and Vessel Classification

Do not hardcode “small, medium, large, super, and luxury” as if they are all sizes.

Store measurable specifications:

- Length overall
- Beam
- Draft
- Gross tonnage
- Passenger capacity
- Cabin count
- Crew capacity
- Vessel use
- Hull type

Allow administrators to configure market-facing size classifications using measurable criteria.

Treat **luxury** as a market segment or feature, not a physical size.

---

## 13. Premium Inquiry and Transaction Workflow

Create a protected high-value inquiry process that extends existing messaging, offers, or bookings.

Suggested flow:

1. Qualified user requests listing access or submits an inquiry.
2. System validates membership and buyer access.
3. Seller, broker, or reviewer evaluates the request.
4. Buyer accepts confidentiality terms or an NDA when required.
5. Approved confidential information becomes available.
6. Buyer requests a viewing, inspection, sea trial, demonstration, or meeting.
7. Buyer submits an expression of interest or formal offer.
8. Parties record due-diligence and inspection milestones without exposing private documents.
9. Transaction proceeds through appropriate professionals or future PrimeUnits transaction services.

Support inquiry statuses such as:

- Submitted
- Under Review
- Information Requested
- Qualified
- Access Granted
- Viewing Scheduled
- Offer Submitted
- Negotiating
- Due Diligence
- Closed Won
- Closed Lost
- Cancelled

Do not present ordinary chat as sufficient proof of a high-value transaction.

---

## 14. Privacy and Security

Implement strict authorization for all premium information.

Required controls:

- Private storage for identity, ownership, registration, aircraft, and vessel documents
- Signed or controlled document access using existing secure mechanisms
- Complete authorization checks for every document request
- Masked registration and serial information
- Approximate public location
- Exact location revealed only after explicit approval when required
- Watermarked confidential images or documents when supported
- Audit logs for confidential-content access
- Reauthentication for highly sensitive actions when supported
- Access expiry and revocation
- Suspicious-access monitoring hooks
- Rate limiting
- No sensitive data in frontend source, page props, logs, analytics, notifications, or search indexes

Prevent:

- Insecure direct-object references
- Frontend-only access restrictions
- Mass assignment
- Enumeration of private listing IDs
- Leaking restricted listing data through search, related listings, counts, notifications, sitemaps, metadata, APIs, exports, or caches

---

## 15. Administration

Authorized administrators must be able to:

- Manage membership plans and statuses
- Configure Regular, Silver, and Gold access rules
- Review buyer, seller, and store applications separately
- Approve, reject, suspend, revoke, or expire access
- Configure category default tiers and exceptions
- Review Gold candidate listings
- Assign listing marketplace tier and visibility
- Manage invitation-only access
- Configure document requirements
- Review ownership and business submissions
- Manage confidentiality-notice and NDA versions
- View audit logs
- View membership history
- Identify listings whose seller authorization has expired
- Disable noncompliant listings
- Grant emergency access revocation

Use granular permissions consistent with the existing authorization system.

Suggested conceptual permissions, to be renamed as needed:

```text
memberships.manage
membership-applications.review
buyer-access.review
seller-access.review
stores.review
premium-listings.review
gold-listings.review
confidential-documents.view
listing-access.manage
category-tier-rules.manage
```

---

## 16. Membership Pricing and Payments

Make membership price, billing interval, trial period, grace period, currency, and benefits configurable.

Do not invent final prices. Seed clearly labeled demonstration prices only when the project requires sample values.

If payment integration exists:

- Reuse it
- Use idempotent payment handling
- Verify webhooks
- Keep subscription state synchronized safely
- Maintain payment and membership audit history

If no payment integration exists:

- Implement manual or administrator activation for development
- Do not create a fake production payment gateway
- Keep payment-provider integration behind a service interface or future-ready boundary

Payment alone must never automatically grant seller authorization, Gold approval, listing approval, or category-specific credentials.

---

## 17. Search, SEO, and Discovery

Prevent protected listings from leaking through:

- Search APIs
- Autocomplete
- Related listings
- Category counts
- Public profile pages
- Store inventories
- XML sitemaps
- Open Graph tags
- Structured data
- Notifications
- Email previews
- Analytics payloads
- Cached responses

Public Preview listings may be indexed only when administrators allow it. Full confidential details must never be indexed.

Search and filters must respect the current user’s highest permitted marketplace and listing visibility.

---

## 18. Required Demo Seeders

Create safe, realistic, idempotent development/demo seeders using stable slugs, deterministic test emails, and the project’s established demo-password convention.

Do not seed private identity-document images, real registration numbers, real financial documents, or production credentials.

### Membership plans

Seed:

- PrimeUnits Regular: free or entry-level demo plan
- PrimeUnits Silver: demonstration premium plan
- PrimeUnits Gold: demonstration private-marketplace plan

Use clearly labeled demo pricing if required. Do not imply that seeded prices are final business decisions.

### Demo users

Create at least:

1. Regular verified buyer and seller
   - `regular.demo@primeunits.test`
2. Silver verified buyer
   - `silver.buyer@primeunits.test`
3. Silver authorized seller without a store
   - `silver.seller@primeunits.test`
4. Silver store owner
   - `silver.store@primeunits.test`
5. Gold approved buyer
   - `gold.buyer@primeunits.test`
6. Gold candidate awaiting review
   - `gold.pending@primeunits.test`
7. Gold authorized individual seller
   - `gold.seller@primeunits.test`
8. Gold Professional Store owner
   - `gold.store@primeunits.test`
9. Suspended Silver member
   - `silver.suspended@primeunits.test`
10. Expired Gold member
   - `gold.expired@primeunits.test`

### Demo stores

Seed:

- Regular equipment dealership
- Silver premium-motor dealership
- Gold aviation and marine brokerage

Clearly label every business as fictional demo data.

### Demo listings

Seed representative listings:

#### Regular

- Standard motorcycle
- Family SUV
- Rice combine harvester rental
- Agricultural drone service

#### Silver

- Premium big bike
- High-end SUV
- Sports car
- Armored and security vehicle
- Personal watercraft
- Premium custom motorcycle

#### Gold

- Rare collector chopper
- Light private aircraft
- Private jet listing marked Price on Request
- Motor yacht
- Superyacht public preview with confidential details restricted
- Premium day boat approved as Gold exception
- Commercial vessel in pending specialist review, not publicly bookable or purchasable

### Listing visibility examples

Seed at least one listing for each:

- Public
- Public Preview
- Silver Exclusive
- Gold Exclusive
- Verified Buyer Only
- Invitation Only

### Applications and access requests

Seed:

- Approved Silver buyer application
- Approved Silver seller application
- Pending Gold buyer application
- Approved Gold seller application
- Rejected or additional-information-required store application
- Approved invitation-only listing access request
- Pending invitation-only listing access request
- Revoked listing access request

### Confidentiality data

Seed a versioned confidentiality notice or demo NDA acknowledgement without real signatures or identity documents.

### Seeder rules

- Use upserts or equivalent safe idempotent logic.
- Do not depend on unstable numeric IDs.
- Do not duplicate records when rerun.
- Do not run demo seeders automatically in production.
- Use safe placeholder images from the project’s existing demo-image mechanism.
- Document all demo login credentials using the project’s existing demo-password convention.

Suggested seeder organization, adapted to repository conventions:

```text
MembershipPlanSeeder
MembershipDemoUserSeeder
MarketplaceAccessRuleSeeder
PremiumCategorySeeder
PremiumStoreSeeder
PremiumListingSeeder
MembershipApplicationSeeder
RestrictedListingAccessSeeder
PrimeUnitsMembershipDemoSeeder
```

---

## 19. Notifications

Use existing notification channels for:

- Verification submitted
- Upgrade application received
- Additional information requested
- Application approved or rejected
- Membership activated
- Membership nearing expiration
- Membership grace period
- Membership expired
- Seller authorization suspended
- Listing approved for Silver or Gold
- Restricted-listing access requested
- Access approved, rejected, revoked, or expired
- Confidential inquiry received

Never include private documents, full registration identifiers, exact protected locations, or sensitive listing details in notifications.

---

## 20. Automated Tests

Add or update tests covering:

### Membership and verification

- Regular public browsing
- Verification requirement before marketplace participation
- Silver and Gold activation
- Trial, grace, expired, suspended, cancelled, and banned states
- Safe downgrade after expiration
- Retention of historical records after expiration

### Buyer and seller separation

- Gold buyer with only Regular seller authority
- Silver seller without Silver buyer access
- Store access independent from membership
- Payment does not automatically grant seller approval

### Listing visibility

- Public listing visibility
- Public Preview data redaction
- Silver Exclusive authorization
- Gold Exclusive authorization
- Verified Buyer Only authorization
- Invitation Only authorization
- Revoked and expired access
- No leakage through search, related listings, counts, APIs, metadata, or sitemaps

### Category rules

- Default category tier
- Manual tier exception
- Ordinary chopper assigned Silver
- Collector chopper approved Gold
- Ordinary day boat assigned Silver
- Premium day boat approved Gold
- Commercial vessel blocked pending specialist review

### Stores

- Individual seller without store
- Regular Store
- Silver Store
- Gold Professional Store
- Store approval does not automatically grant Gold seller access

### Security

- Private-document access control
- Masked identifiers
- Protected exact location
- Unauthorized ID enumeration
- Confidential-content audit logging
- Suspended member access revocation

### Seeders

- Clean database migration and seeding
- Seeder idempotency
- Correct relationships among users, memberships, stores, listings, applications, and access grants

Run the complete relevant backend and frontend test suites. Fix any regression caused by the implementation.

---

## 21. Acceptance Criteria

The work is complete only when:

1. Regular, Silver, and Gold exist as configurable marketplace memberships.
2. Identity verification, membership, buyer access, seller access, and store access remain independent.
3. A verified Regular user can participate in the general marketplace.
4. Only authorized Silver or Gold users can access protected premium listings.
5. Silver buyer access does not automatically authorize Silver selling.
6. Gold membership payment does not automatically authorize Gold selling.
7. A user can sell without creating a store when policy permits it.
8. A store can qualify separately as Regular, Silver, or Gold Professional.
9. Gold candidate listings require manual approval.
10. Public previews reveal no confidential asset or owner information.
11. Invitation-only access can be approved, revoked, and expired.
12. The marketplace-mode switcher never bypasses server authorization.
13. Search, APIs, notifications, metadata, and caches do not leak protected data.
14. Seeded demo users and listings visibly demonstrate all three levels.
15. Seeders are idempotent and safe from production execution.
16. Automated tests pass.
17. Existing PrimeUnits functions continue working.

---

## 22. Required Implementation Report

After completing the work, provide:

1. Summary of implemented functionality
2. Existing architecture discovered
3. Architecture and security decisions
4. Database migrations and schema changes
5. Models, services, policies, requests, resources, jobs, and notifications added or changed
6. Frontend pages and components added or changed
7. Membership, access, and listing-tier rules
8. Seeder names and demo accounts
9. Demo login instructions using the existing demo-password convention
10. Exact migration, seeding, build, queue, and test commands
11. Automated test results
12. Manual verification checklist
13. Unresolved assumptions or business decisions
14. Recommended next phase

Do not stop after producing a plan or example snippets. Implement the feature in the repository, use safe development or test data, run the relevant migrations and seeders, execute the tests, fix implementation-related failures, and report the actual results.

If a requirement conflicts with the current architecture or creates a security problem, do not silently ignore it. Explain the conflict, implement the safest architecture-compatible alternative, and document the decision in the completion report.
