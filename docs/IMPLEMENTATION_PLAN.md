# Optimisation implementation plan

## P1 — corporate website

1. Add a canonical `/schools/` product page using only functionality verified from the deployed SMS bundle.
2. Publish approved pricing from a single source on that page:
   - Starter: US$63/month for 1–300 active students.
   - Growth: US$126/month for 301–800 active students.
   - Enterprise: US$196/month for 801–1,500 active students.
   - Custom quote for 1,501+ active students.
   - Annual prepayment receives a 10% subscription discount.
   - Standard setup and basic remote orientation are included.
   - SMS, assisted migration, extra training/travel and bespoke work are separate.
3. Calculate optional assisted record migration progressively: US$1.00 for records 1–500, US$0.75 for 501–1,000, US$0.50 for 1,001–1,500 and US$0.25 thereafter, with a US$100 minimum when selected.
4. Link `/schools/` from the main navigation and homepage.
5. Add WhatsApp and product-access CTAs without claiming that an unverified public demo exists.
6. Add `SoftwareApplication`, `Offer`, FAQ and breadcrumb structured data.
7. Add `/schools/` to the corporate sitemap.

## P1 — SMS application (separate repository required)

1. Add a public, server-rendered product landing response or appropriate prerendering.
2. Serve valid `/robots.txt` and `/sitemap.xml` resources rather than the SPA shell.
3. Add `noindex` to authenticated and login surfaces.
4. Verify tenant isolation and role checks at the API layer.
5. Build a synthetic, read-only demo tenant before exposing a demo CTA.

## P2

- Replace the product illustration with anonymised real application screenshots.
- Add privacy-conscious analytics after measurement identifiers and consent requirements are confirmed.
- Run Lighthouse, axe and mobile network testing against the deployed pages.
- Add automated HTML/link checks and backend tests to CI.

## Deployment rule

Prepare, test and commit changes first. Production deployment requires explicit approval after review.
