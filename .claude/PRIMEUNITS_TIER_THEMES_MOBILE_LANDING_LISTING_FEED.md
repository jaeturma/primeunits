# PrimeUnits Tier Themes and Mobile Landing Listing Feed

## Claude CLI Implementation Prompt

You are working inside the existing **PrimeUnits** marketplace project.

Implement membership-aware visual themes for Regular, Silver, and Gold users, audit and improve the public landing page for mobile responsiveness, and upgrade the landing-page listing section to load listings in batches of 12 with controlled Featured, Sponsored, and Advertisement placements.

This work extends the existing PrimeUnits membership-tier architecture. Do not rebuild the membership system, authentication, category system, listing model, subscription system, or frontend design system.

Before changing code, inspect and document:

- Framework and package versions
- Frontend stack and design system
- CSS strategy, theme variables, Tailwind configuration, component library, or equivalent
- Existing PrimeUnits green palette and branding
- Authentication and membership entitlement source
- Active marketplace-mode switching
- Landing-page routes and components
- Listing cards and listing query services
- Featured or promoted listing fields
- Advertisement implementation, if any
- Pagination or cursor-pagination conventions
- Image-loading and responsive-image behavior
- Existing tests

Follow the repository's established patterns. Do not replace working components or introduce a parallel theme system when one already exists.

---

## 1. Design Objective

PrimeUnits must retain its existing green identity across all membership levels.

Membership themes must blend green with the appropriate premium accent:

- **Regular:** existing PrimeUnits Green
- **Silver:** PrimeUnits Green + Silver
- **Gold:** PrimeUnits Green + Gold

Do not replace the entire interface with gray for Silver or yellow for Gold. Green remains the primary brand color. Silver and Gold provide premium accents, surfaces, borders, badges, focus treatments, and selected calls to action.

The result should feel like one PrimeUnits product with three related experiences, not three separate websites.

---

## 2. Theme Source and Authorization

Determine the theme from the user's currently active and authorized marketplace mode:

- `regular`
- `silver`
- `gold`

Use the current membership and marketplace-mode architecture already present in the repository.

Important rules:

- Theme selection must not grant marketplace access.
- Frontend state must never override server authorization.
- If a membership expires, is suspended, or becomes inaccessible, fall back to the highest currently authorized mode.
- Guests and users without premium access use the Regular green theme.
- Persist the last authorized marketplace mode using the project's existing user-preference or session mechanism.
- Prevent a user from forcing Silver or Gold access by editing local storage, a cookie, query parameter, CSS class, or client-side state.

If the project has a marketplace switcher, integrate the theme into that switcher. Do not create a second competing switcher.

---

## 3. Semantic Theme Tokens

Implement the themes through semantic design tokens rather than scattered hardcoded colors.

Adapt token names to the project's existing convention. Suggested concepts:

```text
--brand-primary
--brand-primary-hover
--brand-accent
--brand-accent-soft
--brand-accent-strong
--brand-surface
--brand-surface-muted
--brand-border
--brand-ring
--brand-badge-background
--brand-badge-foreground
--brand-gradient-start
--brand-gradient-end
```

If Tailwind is used, expose equivalent CSS variables or semantic utilities so existing components can consume the active theme without duplicating component definitions.

Do not create separate copies of every button, card, navigation bar, and badge for each tier.

### Regular theme

Retain the existing PrimeUnits green palette and current visual hierarchy.

### Silver theme

Blend the existing PrimeUnits green with restrained silver accents:

- Silver borders and highlights
- Cool neutral premium surfaces
- Silver membership badge
- Subtle green-to-silver gradients where appropriate
- Existing green for trust, confirmation, primary navigation, and core brand recognition

Avoid low-contrast light gray text and excessive metallic effects.

### Gold theme

Blend the existing PrimeUnits green with restrained gold accents:

- Gold borders and highlights
- Warm premium surfaces
- Gold membership badge
- Subtle green-to-gold gradients where appropriate
- Existing green for brand recognition and primary structural elements

Avoid bright yellow, excessive shine, animated glitter, fake metallic textures, or styling that reduces readability.

---

## 4. Components That Must Respond to Theme

Apply semantic theming consistently to existing components where appropriate:

- Main navigation
- Marketplace-mode switcher
- Membership badge
- Primary and secondary buttons
- Links and focus rings
- Search controls
- Filter chips
- Category cards
- Listing cards
- Price highlights
- Pagination or Load More controls
- Empty states
- Modal and drawer accents
- Toasts and alerts where membership context matters
- Footer highlights

Do not recolor warning, error, success, and informational status meanings in a way that harms comprehension.

Ensure that Featured, Sponsored, Advertisement, Verified, Silver, and Gold badges remain visually distinguishable.

---

## 5. Theme Accessibility

For every theme:

- Meet WCAG AA contrast for normal text and interactive controls.
- Preserve visible keyboard focus.
- Do not communicate membership or promotion type using color alone.
- Use text labels and accessible names.
- Respect reduced-motion preferences.
- Ensure hover, active, disabled, selected, loading, and focus-visible states remain clear.
- Test badges and text over listing images and gradients.

If the existing application supports dark mode, integrate Regular, Silver, and Gold with that system instead of disabling it. If dark mode does not exist, do not add it unless necessary for this task.

---

## 6. Mobile-Responsive Landing Page Audit

Audit the complete public landing page and authenticated landing experience at these approximate widths:

- 320px
- 360px
- 375px
- 390px
- 430px
- 768px
- 1024px
- 1280px and above

Correct issues involving:

- Horizontal overflow
- Oversized hero content
- Cropped text
- Navigation wrapping
- Mobile menu behavior
- Search-bar layout
- Category scrolling or grids
- Listing-card width and image ratio
- Long listing titles
- Long prices and currency formatting
- Badges overlapping content
- Touch-target size
- Filter and sort controls
- Footer stacking
- Layout shifts during image loading
- Load More button width and spacing

Preserve the existing landing-page content unless a layout change is necessary.

Use responsive images, appropriate aspect ratios, lazy loading below the fold, fixed image containers, skeletons or existing loading placeholders, and graceful fallbacks for missing images.

Do not hide essential functionality on mobile.

---

## 7. Landing Listing Section

Upgrade the existing landing-page listing area. Keep the existing section name when appropriate, or use a clear title such as **Featured Listings** or **Discover Listings** based on the current design.

Load listings in batches of exactly **12 visible feed positions** whenever enough eligible content exists.

Each batch may contain:

- Up to 1 Featured listing
- Up to 1 Sponsored listing
- Up to 1 Advertisement placement
- Remaining positions filled by eligible organic listings

This means one batch of 12 can contain a maximum of three promoted placements: one Featured, one Sponsored, and one Advertisement. Do not show more than one of the same promotion type in a single 12-position batch.

When one promotion type has no eligible active item, fill that position with an organic listing rather than leaving an empty space.

Advertisements must be clearly identified and must not masquerade as ordinary user listings.

---

## 8. Promotion Definitions

Use the current database fields and promotion system when available. Extend them safely only if required.

### Featured listing

A valid marketplace listing selected or purchased for enhanced visibility.

Display a clear **Featured** badge.

### Sponsored listing

A valid marketplace listing promoted through a paid or approved campaign.

Display a clear **Sponsored** badge.

### Advertisement

A separate advertisement creative, campaign, or partner placement. It may link to an internal campaign page or an approved external destination according to platform policy.

Display a clear **Ad** or **Advertisement** label.

Do not treat an advertisement as a normal asset listing unless the existing advertising model explicitly links it to a listing.

### Organic listing

An eligible listing returned by the normal discovery ranking without Featured or Sponsored placement in that batch.

---

## 9. Feed Composition Rules

Build a deterministic server-side feed-composition service or extend the existing query service.

Requirements:

1. Return 12 positions per successful batch when enough content exists.
2. Include no more than one Featured listing per batch.
3. Include no more than one Sponsored listing per batch.
4. Include no more than one Advertisement per batch.
5. Fill unused promotional positions with organic listings.
6. Never duplicate a listing or advertisement within the current loaded feed.
7. Do not repeat promoted content on every Load More request.
8. Rotate eligible promoted content fairly using the existing campaign priority, schedule, budget, weight, or rotation logic.
9. Respect listing publication, moderation, membership-tier, marketplace-mode, geographic, category, and visibility rules.
10. Do not leak Silver, Gold, Verified Buyer, or Invitation Only listings to unauthorized users.
11. Exclude expired, sold, archived, rejected, suspended, private, or otherwise ineligible listings.
12. Respect campaign start and end dates.
13. Record impressions only when the card is actually rendered or meets the application's existing viewability standard.
14. Prevent refresh abuse from inflating impression counts.

Use cursor pagination where practical, especially if the existing project already supports it. Avoid offset pagination when it could cause duplicates or skipped records as listing order changes.

Return a continuation cursor or equivalent token that preserves feed composition safely without trusting client-supplied promotion decisions.

---

## 10. Placement Strategy Within 12 Positions

Do not place Featured, Sponsored, and Advertisement cards consecutively.

Use server-selected positions or a deterministic layout that distributes them through the batch. For example, positions may be chosen from safe placement ranges while remaining consistent during hydration and pagination.

Recommended principles:

- First card should normally be an actual listing, not an advertisement.
- Avoid adjacent promoted cards.
- Avoid always putting the same promotion type in the same position.
- Preserve a natural browsing experience.
- Maintain stable ordering after the batch has been delivered.
- Ensure the layout works in one-column, two-column, three-column, and four-column grids.

Do not hardcode placement decisions exclusively in the browser.

---

## 11. Load 12 More Behavior

Add a clear button beneath the listing grid:

**Load 12 More**

On activation:

- Request the next authorized batch.
- Append new cards to the current section.
- Do not replace already loaded cards.
- Do not duplicate existing listings or ads.
- Preserve scroll position.
- Show an accessible loading state.
- Disable repeated clicks while a request is running.
- Handle retryable errors without clearing existing results.
- Announce appended results to assistive technology using an appropriate live region.
- Hide or disable the button when no more eligible content exists.
- If fewer than 12 eligible items remain, append all remaining items and then hide the button.

Use the project's existing request, Inertia partial reload, API, state-management, or query-library conventions.

Avoid infinite scroll for this task. The explicit button gives the user control and matches the requested behavior.

---

## 12. Responsive Listing Grid

Use a responsive grid consistent with the existing design. A reasonable target, adjusted to the project, is:

- Small mobile: 1 column
- Large mobile or small tablet: 2 columns when card width remains readable
- Tablet or laptop: 3 columns
- Large desktop: 4 columns

Do not force two columns on narrow devices if prices, badges, and titles become unreadable.

Listing cards must support:

- Stable image aspect ratio
- Responsive image sizing
- Truncated or line-clamped titles
- Readable price and unit
- Location
- Marketplace tier when relevant
- Featured, Sponsored, or Advertisement label
- Seller or store verification badge when already supported
- Keyboard navigation
- Entire-card or explicit-link semantics consistent with the existing application

Advertisement cards must fit the grid without deceiving users into thinking they are ordinary listings.

---

## 13. Theme and Listing Interaction

Promotion badges must retain their meaning across all membership themes.

Suggested semantic distinctions:

- Featured: premium emphasis using the active theme accent
- Sponsored: clearly labeled neutral or campaign accent
- Advertisement: explicit Ad label and visually distinct card treatment
- Silver listing: silver membership/tier badge
- Gold listing: gold membership/tier badge

Do not allow the Gold theme to make every listing appear Gold-certified. Theme indicates the user's current marketplace experience; listing badges indicate the listing's actual classification.

Similarly, a Silver user viewing an organic listing must not see it incorrectly labeled as Sponsored or Silver-certified.

---

## 14. Backend Data and Administrative Controls

Reuse existing promotion fields where possible. If the project lacks them, design a maintainable model supporting:

- Promotion type: Featured, Sponsored, Advertisement
- Listing or advertisement relationship
- Campaign owner
- Marketplace tier eligibility
- Start and end dates
- Active status
- Priority or weight
- Impression limit
- Click limit when used
- Budget fields only if the project already supports advertising billing
- Review status
- Destination URL policy for advertisements
- Audit timestamps

Administrators must be able to:

- Mark or schedule Featured listings
- Approve and schedule Sponsored listings
- Create or approve Advertisement placements
- Select eligible marketplace modes
- Set campaign dates
- Activate, pause, expire, or cancel campaigns
- Review impressions and clicks when analytics already exists or is added safely
- Preview badges and card presentation

Do not invent production billing logic if no advertising-payment system exists. Implement safe manual administration and a future-ready service boundary.

---

## 15. Advertisement Safety

For external advertisements:

- Validate URLs.
- Allow only approved schemes.
- Apply the project's safe-link behavior.
- Prevent `javascript:` and unsafe redirects.
- Mark external navigation appropriately.
- Add `rel="sponsored"` and other safe relationship attributes when applicable.
- Do not expose private user targeting information.
- Do not allow arbitrary executable HTML or scripts in ad content.
- Sanitize all advertisement copy.

Keep Sponsored listings and third-party Advertisements distinguishable.

---

## 16. Performance

Optimize the landing feed without premature complexity:

- Avoid N+1 queries.
- Eager load only required relationships.
- Select only fields needed by the listing-card resource.
- Use indexes for eligibility, tier, visibility, status, promotion type, and campaign dates where justified.
- Use responsive image derivatives when available.
- Lazy load below-the-fold images.
- Prevent cumulative layout shift.
- Do not send confidential fields in page props or API responses.
- Cache only when cache keys include the necessary marketplace and authorization context.
- Do not share user-specific Gold or Silver feed caches with unauthorized users.

---

## 17. Analytics

If the project already has analytics, extend it for:

- Listing impression
- Featured impression
- Sponsored impression
- Advertisement impression
- Card click
- Load 12 More request
- Membership theme/mode in aggregate

Do not count server delivery alone as a visual impression unless that is the existing documented standard.

Avoid collecting unnecessary personal information. Do not leak protected listing IDs or confidential content to third-party analytics.

---

## 18. Required Demo Seeders

Create or extend safe, idempotent development seeders so the interface can be reviewed immediately.

Seed enough eligible content to demonstrate at least three full 12-position batches.

### Demo users

- Regular active user
- Silver active user
- Gold active user
- Expired Silver user to confirm Regular fallback
- Suspended Gold user to confirm safe fallback

Reuse existing membership demo users when they already exist.

### Demo listings

Seed at least 36 eligible public or appropriately authorized listings across existing categories. Include realistic demo images using the project's established mechanism.

Include enough records to demonstrate:

- Organic listings
- Featured listings
- Sponsored listings
- Regular listings
- Silver listings
- Gold public previews
- Sold or expired listing excluded from the feed
- Suspended listing excluded from the feed

### Demo advertisements

Seed at least three clearly fictional approved advertisements with:

- Safe title
- Image or visual placeholder
- Short copy
- Approved destination
- Active schedule
- Target marketplace mode
- Ad label

Do not use real brands without permission.

### Seeder requirements

- Use stable slugs or deterministic keys.
- Use upserts or equivalent idempotent logic.
- Do not duplicate data when rerun.
- Do not run demo data in production.
- Document the command to run only these seeders.

Suggested names, adapted to repository conventions:

```text
TierThemeDemoUserSeeder
LandingListingFeedSeeder
PromotionCampaignSeeder
LandingAdvertisementSeeder
PrimeUnitsLandingExperienceSeeder
```

---

## 19. Automated Tests

Add or update tests for:

### Theme selection

- Guest receives Regular green theme
- Regular user receives Regular green theme
- Silver active mode receives Green + Silver theme
- Gold active mode receives Green + Gold theme
- Unauthorized client-side theme selection does not grant access
- Expired Silver falls back safely
- Suspended Gold falls back safely
- Last authorized mode persists

### Feed composition

- First request returns 12 positions when sufficient content exists
- Each batch has no more than one Featured listing
- Each batch has no more than one Sponsored listing
- Each batch has no more than one Advertisement
- Missing promotion type is replaced by organic content
- Promoted cards are not adjacent when avoidable
- No duplicate listing or advertisement across appended batches
- Expired and suspended content is excluded
- Unauthorized Silver and Gold content is excluded
- Public Preview response contains only permitted fields
- Invitation Only content is not leaked
- Cursor or continuation token cannot be manipulated to bypass access

### Load More

- Load 12 More appends rather than replaces
- Button prevents concurrent duplicate requests
- End of results is handled correctly
- Fewer than 12 remaining results are appended correctly
- Errors preserve existing content and permit retry

### Advertisement safety

- Unsafe URL is rejected
- Unsafe markup or script is rejected or sanitized
- Advertisement is clearly labeled
- Sponsored relationship attributes are applied when appropriate

### Responsive frontend

Use the project's existing component, browser, or end-to-end testing tools to verify:

- No horizontal overflow at target mobile widths
- Mobile navigation works
- Listing cards remain readable
- Promotion badges do not overlap
- Load More remains accessible
- Keyboard focus is visible
- Theme contrast remains acceptable

Run the relevant backend, frontend, type-checking, linting, build, and automated test commands. Fix regressions introduced by this work.

---

## 20. Acceptance Criteria

The work is complete only when:

1. PrimeUnits green remains the base brand color in all themes.
2. Silver mode visibly and consistently blends green with silver.
3. Gold mode visibly and consistently blends green with gold.
4. Theme selection follows the active authorized marketplace mode.
5. Theme changes never grant membership or listing access.
6. The complete landing page works without horizontal overflow at supported mobile widths.
7. Landing listing cards are readable and accessible on mobile.
8. Initial feed returns up to 12 authorized positions.
9. Each 12-position batch contains no more than one Featured, one Sponsored, and one Advertisement placement.
10. Missing promotion types are replaced with organic listings.
11. Load 12 More appends the next unique batch.
12. No listing or advertisement is duplicated in the loaded feed.
13. Restricted Silver and Gold listing data never leaks to unauthorized users.
14. Featured, Sponsored, and Advertisement cards are clearly labeled.
15. Seeded data demonstrates at least three batches.
16. Seeders are idempotent and protected from production execution.
17. Automated tests pass.
18. Existing marketplace functionality remains operational.

---

## 21. Required Completion Report

After implementation, provide:

1. Existing frontend and theme architecture discovered
2. Summary of completed changes
3. Theme token and membership-mode decisions
4. Landing-page responsive improvements
5. Feed-composition algorithm
6. Files created or modified
7. Migrations, models, services, policies, resources, and administration changes
8. Seeder names and commands
9. Demo accounts and existing demo-password convention
10. Exact build, test, lint, type-check, migration, and seeding commands run
11. Test results
12. Mobile-width verification results
13. Screens or routes for manual review
14. Assumptions and unresolved decisions
15. Recommended next phase

Do not stop after preparing a plan or sample code. Implement the work in the repository, run the relevant migrations and seeders in a safe development or test environment, execute all applicable checks, fix implementation-related failures, and report the actual results.

If the current project already implements any requirement differently, preserve the working architecture and implement the safest compatible solution. Document any justified deviation in the completion report.
