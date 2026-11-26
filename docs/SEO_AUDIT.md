# SEO audit

## Corporate site

Strengths:

- Canonical URLs, descriptive titles, meta descriptions, structured data and an XML sitemap exist.
- Service and insight pages provide crawlable HTML rather than relying only on JavaScript.

Improvements implemented in this change:

- Canonical `/schools/` product route.
- Zimbabwe-focused school-management title, description and H1.
- SoftwareApplication, Offer, FAQ and breadcrumb structured data.
- Internal school-product navigation and sitemap inclusion.

Recommended follow-up:

- Redirect duplicate school-service pages to `/schools/` only after validating inbound links and Search Console coverage.
- Add anonymised real product screenshots with descriptive alt text.
- Submit the updated corporate sitemap after production deployment.

## SMS host

Observed issues on 26 November 2026:

- Initial HTML title is the generic `School Management System`.
- `/robots.txt` returns the SPA HTML shell.
- `/sitemap.xml` returns the SPA HTML shell.

The SMS host needs its own valid sitemap. A sitemap on `vitaw.co.zw` should not be used as a substitute for the separate `sms.vitaw.co.zw` host.
