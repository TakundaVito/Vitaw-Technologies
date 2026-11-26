# VITAW Technologies optimisation audit

Date: 26 November 2026

## Scope inspected

- Corporate website repository in this checkout.
- Public runtime responses from `sms.vitaw.co.zw`, including the deployed JavaScript route map.
- Public SEO endpoints on the SMS host.

The SMS application source repository and production database were not available in this checkout. Security findings for that application are therefore limited to observable production behaviour and bundle metadata.

## Existing implementation

- Static HTML, first-party CSS and JavaScript corporate site with a small PHP/PDO MySQL backend for enquiries and administration.
- Responsive corporate homepage, services, portfolio, insights, About, privacy and terms pages.
- School-related service pages and an article already target several education-software search terms.
- Corporate sitemap and robots file exist.
- Enquiry backend includes CSRF protection, strict session cookies, session timeout, prepared PDO queries and database-backed rate limiting.
- SMS production bundle exposes role-aware routes for overview, students, attendance, marks, results, fees, approvals, activity, messages, homework, staff, settings, parent and student views.

## High-priority gaps

1. There is no canonical `/schools/` product landing page. School information is fragmented across two service URLs.
2. The corporate navigation does not provide a direct school-product path.
3. School pricing is absent, increasing sales friction.
4. The SMS host returns the single-page application shell for `/robots.txt` and `/sitemap.xml`; those are not valid SEO resources.
5. The SMS page title is the generic `School Management System`, with no useful public landing-page metadata visible in the initial HTML.
6. No verified public demo environment was established. `/demo` currently returns the same application shell, but its access controls and data isolation could not be verified.
7. No analytics identifiers are present in the inspected corporate source.
8. No automated test suite or CI workflow is present in this checkout.

## Risks

- The public SMS JavaScript reveals route names, which is normal for an SPA, but server-side authorisation must protect every corresponding API operation.
- A shared public demo must not be advertised until synthetic data, restricted permissions, tenant isolation and reset behaviour are verified.
- Existing generated service imagery is illustrative and must not be presented as a real product screenshot.
- Several untracked deployment folders and archives exist in the worktree. They are preserved and excluded from these changes.

## Recommended priority

- **P0:** verify SMS API authorisation, tenant isolation, demo safety and production backup procedures in the SMS repository.
- **P1:** launch `/schools/`, publish transparent pricing, add direct school CTAs, consolidate duplicate school URLs, and correct SMS SEO endpoints.
- **P2:** add consent-aware analytics, real anonymised screenshots, accessibility testing and performance measurement.
- **P3:** assess offline workflows and payment reconciliation from the actual SMS source.
