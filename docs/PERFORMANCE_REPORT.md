# Performance report

No Lighthouse or field Core Web Vitals measurements were executed in this audit, so no performance score is claimed.

## Repository observations

- Fonts are locally hosted as WOFF2 and preloaded on key pages.
- Service imagery uses WebP.
- Below-fold images on portfolio pages use lazy loading.
- The corporate site avoids a frontend framework and heavy animation dependencies.
- The SMS application is a React SPA with separate React and Supabase chunks.

## Recommended verification targets

- LCP at or below 2.5 seconds.
- INP at or below 200 milliseconds.
- CLS at or below 0.1.
- Test on a mid-range mobile viewport using throttled 4G.

The new school page uses one WebP hero illustration, local fonts, no new JavaScript and lazy loading for the below-fold visual.
