# Service payments

Public page: https://vitaw.co.zw/pay/ (Services → Pay for a service).

Activation requires a Paynow merchant integration enabled for USD. Copy
`paynow.example.php` to `paynow.php` beside the existing `config.php`, outside
`public_html`, and enter the integration ID and key privately. Never put the key
in HTML, JavaScript, Git, chat or a public folder. Restrict file permissions.

Return URL: https://vitaw.co.zw/pay/
Result URL: https://vitaw.co.zw/api/payments.php?action=notify
The server adds a unique reference to each return URL.

The page stays disabled without configuration. PHP requires cURL, sessions and
the site's existing database. `service_payments` is created on first configured
request, with service, agreed USD amount, email, project reference, gateway
reference and status. These records are currently database-only; no payment
administration screen or automated invoice email is included.

Outbound requests and incoming responses use Paynow's SHA-512 signature.
Callbacks are signed and then independently polled before recording status.
Returning to the site does not count as payment. Paid, Awaiting Delivery and
Delivered are the successful payment states. Amount and reference must match.
Never fulfil work based only on a customer screenshot or redirect.

Before enabling live use, test with Paynow test credentials: successful payment,
cancelled payment, pending payment, callback retry and status refresh. Verify
the USD settlement account with Paynow. No live transaction has been tested.

Checks completed during implementation: PHP and JS syntax, official Paynow
inbound hash test vector, tampered signature rejection, gateway URL allowlist,
unconfigured API response, and live page/navigation verification.
