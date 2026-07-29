# Diatile Ndhlovu website

A lightweight, responsive portfolio website built with plain HTML, CSS and JavaScript.

## Preview

Serve this folder over HTTP rather than opening `index.html` from disk:

```
python -m http.server 8000
```

Then visit `http://localhost:8000`.

Do not preview by double-clicking `index.html`. A page opened that way has the origin `null`, and browsers refuse to load cross-origin iframes into it, so the LinkedIn posts render as "www.linkedin.com refused to connect" even though nothing is wrong with the markup. Serving over HTTP gives the page a real origin and the embeds load normally, as they do on the live site.

## Images

Both images are real; there are no placeholders left.

- `diatile-speaking.png` — the hero portrait, taken at an Eskom Expo event.
- `are-you-awake.png` — the book cover, sourced from [diatile.co.za](https://diatile.co.za/).

To swap either one, drop the replacement into `assets/img` and update its `src` in `index.html`. Always set the `width` and `height` attributes to the image's real pixel dimensions so the browser can reserve the right space and the page doesn't jump while loading. A vertical portrait of roughly 4:5 suits the hero column best.

The navigation uses a text wordmark because the existing logo carries the previous surname, Mokhoathi. A new Diatile Ndhlovu logo can replace the `.brand` content in `index.html` when one is available.

## The LinkedIn section

The "Latest from LinkedIn" section holds three featured posts. LinkedIn has no official embed for a whole personal profile feed, so these are added individually. To swap one out:

1. Open the post on LinkedIn on desktop.
2. Click the three-dot menu in the top-right corner of the post and choose **Embed this post**.
3. Copy the `<iframe>` code LinkedIn provides.
4. In `index.html`, find the `#updates` section and replace one of the existing iframes with it.

Keep the surrounding `<div class="update-slot">` wrapper — the stylesheet uses it to force each embed to fill its card and resize correctly on mobile.

Only **public** posts offer the embed option. If the three-dot menu has no "Embed this post" entry, the post's audience is set to Connections, or it is an article or newsletter rather than a standard feed post. Deleting a post on LinkedIn will break its embed here, so swap it for another one.

Remember that these only render over HTTP — see the Preview section above.

To make the feed update on its own instead, a third-party aggregator such as Elfsight or EmbedSocial can be dropped into the same `.update-slot` wrappers. Those are paid services and most support LinkedIn Company Pages more reliably than personal profiles.

## Buy the book (PayFast)

The book CTA links to `buy-book.php`, which signs a R50 PayFast checkout server-side and redirects the buyer.

Upload these files with the site (PHP must be enabled on the host — cPanel/LiteSpeed is fine):

- `buy-book.php` — checkout entry point
- `config.payfast.php` — Merchant ID, key, passphrase, price (blocked from direct web access by `.htaccess`)
- `payfast-notify.php` — Instant Transaction Notification endpoint
- `payment-success.html` / `payment-cancel.html` — return pages
- `.htaccess`

To change the price, edit `amount` in `config.payfast.php`. Keep `sandbox` set to `false` for live payments.

In the PayFast dashboard, confirm:

1. The passphrase matches `config.payfast.php`
2. Your domain / staging subdomain is allowed if PayFast has domain restrictions
3. ITN / notify URL can reach `https://your-domain/payfast-notify.php`

Local `python -m http.server` cannot run PHP. Test checkout on the live or staging host.

## Content updates

All page content is in `index.html`. Colours, type, spacing and responsive breakpoints are in `assets/css/styles.css`. The design tokens at the top of that file are the fastest way to adjust the visual theme.

`index.html` is written in plain ASCII, using HTML entities such as `&mdash;` and `&rsquo;` for typographic characters. This keeps the page safe from encoding problems no matter which editor or server handles it, so please keep using entities rather than pasting curly quotes and dashes directly.

## Deploy

Upload the full project folder to the subdomain document root, including `index.html`, `assets/`, the PayFast PHP files, return pages, and `.htaccess`. No build step is required.
