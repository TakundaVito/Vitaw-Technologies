# Optimisation implementation plan

## P1 — corporate website

1. Add a canonical `/schools/` product page using only functionality verified from the deployed SMS bundle.
2. Publish approved pricing from a single source on that page:
   - US$250 one-time onboarding.
   - US$70/month for up to 500 enrolled learners.
   - US$130/month for 501–1,000 enrolled learners.
   - Custom quote above 1,000 learners or for multi-school groups.
   - Optional training quoted separately.
3. Link `/schools/` from the main navigation and homepage.
4. Add WhatsApp and product-access CTAs without claiming that an unverified public demo exists.
5. Add `SoftwareApplication`, `Offer`, FAQ and breadcrumb structured data.
6. Add `/schools/` to the corporate sitemap.

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
