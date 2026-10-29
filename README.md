# Vitaw Technologies

Official website source for [vitaw.co.zw](https://vitaw.co.zw/).

The original homepage structure and six-image technology slideshow are retained. The site includes the official logo and icons, service pages, website portfolio, About and leadership, privacy policy, terms of use, and shared navigation. Vitaw operates globally, with headquarters in Zimbabwe.

## Technology

Static HTML, first-party CSS and JavaScript, with PHP 8.2+ and PDO MySQL for enquiries and the administration portal. Public portfolio and service pages remain readable without a database connection. No frontend package manager is required.

## Preview

For static pages:

```sh
python -m http.server 8080 --directory public
```

For PHP routes:

```sh
php -S 127.0.0.1:8080 -t public tools/router.php
```

Database functions require a private configuration and imported schema. Inquiry submissions store records; email delivery is not implemented.

## Hostinger deployment

Deploy the contents of `public/` into the domain's `public_html/`. Place `src/`, private `storage/`, and `config.php` in the parent directory, outside the web root. Import `schema.sql` for a fresh installation. Configure an existing deployment without reimporting or replacing its database.

Copy `config.example.php` to the private `config.php` and set database values, administrator email, a PHP `password_hash` hash, and storage path. Never commit actual credentials or replace production configuration during a routine content deployment. Require HTTPS and PHP extensions PDO MySQL and fileinfo. Keep uploaded enquiry documents outside the web root.

The repository deliberately excludes credentials, enquiry uploads, production database data and authentication sessions. Existing production configuration must be preserved.

## Editing and building

Edit HTML and PHP templates under `public/`. CSS sources are `src/fonts.css`, `public/style.css` and `public/slideshow.css`; JavaScript sources are `public/app.js` and `public/slideshow.js`.

```sh
python tools/build-assets.py
```

The build writes fingerprinted assets and updates references in HTML and public PHP entry/listing templates. Commit the new bundles and updated templates together. The initial HTML contains the portfolio and service content so it is crawlable without JavaScript.

## Search and content

- Canonical origin: `https://vitaw.co.zw/`.
- `robots.txt` points to `sitemap.xml`; update that static sitemap when adding pages.
- School management software is described as a service scoped with each institution; individual modules, pricing and delivery require agreement.
- The seven website projects are presented as Vitaw Technologies portfolio work.
- Leadership: Whisper Kudzai Vito, Co-founder and CEO; Takunda Vito, CTO.
- Capability images are illustrative, AI-generated visuals, not photographs of delivered client projects.

## Administration limits

The authenticated PHP administration portal supports enquiries and database content. Static service and portfolio URLs take precedence over matching CMS URLs. New CMS content is not automatically added to the static sitemap. Advanced media management, rich-text editing and email notifications are outside the current implementation.

## Validation

The public sitemap contains 20 canonical URLs. Before deployment, check page navigation, legal links, hero controls and mobile layouts. Google accepted the sitemap earlier in the project; discovery and ranking are controlled by search providers. No ranking or performance score is guaranteed.
