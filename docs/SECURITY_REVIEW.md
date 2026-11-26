# Security review

## Corporate site

Observed controls include prepared PDO queries, CSRF validation, strict-mode sessions, HTTP-only cookies, SameSite Strict cookies, session expiry and rate limiting. Production credentials are intentionally excluded from the repository.

Outstanding work:

- Verify that HTTPS is always enforced before relying on secure cookies.
- Add security headers at the web-server layer, including CSP, HSTS, frame restrictions and a restrictive referrer policy.
- Test upload MIME validation, file-size limits and storage permissions using the deployed PHP configuration.
- Add login throttling tests and audit-log coverage.

## SMS application

The deployed bundle shows separate navigation for head/principal, finance, teacher, parent and student roles. Client-side navigation is not an authorisation boundary; every API endpoint must independently validate the authenticated user, school tenant and permission.

The following could not be verified without the SMS source and database policies:

- Supabase row-level security policies.
- Tenant isolation.
- Password and recovery handling.
- Rate limits and audit logs.
- Financial-operation idempotency and reconciliation.
- Backup and disaster-recovery procedures.

No public shared-password demo should be advertised until a synthetic, isolated, restricted tenant has been tested.
