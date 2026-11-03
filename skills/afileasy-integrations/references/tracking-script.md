# Tracking script

Required for Stripe, Asaas, AbacatePay and Zouti. **Don't install it manually on WooCommerce, Shopify or Nuvemshop.** Their plugin or app already loads it, and a second copy causes duplicate clicks.

## Install

Put it on **every page** of the campaign's website, in `<head>` or just before `</body>`. It has to load on the landing page (to catch `?ref=`) and on the checkout page (so `getReferral()` returns the code).

```html
<script src="https://cdn.afileasy.com/analytics/script.min.js" data-afileasy="ORGANIZATION_PUBLIC_KEY"></script>
```

- The Afileasy dashboard shows this exact line with the public key filled in (Settings → Integrations → provider → "Script installation"). Prefer copying it from there. If the dashboard's `src` differs from the one above, the dashboard wins.
- Keep the `src` exactly as given. It updates automatically. Don't download and self-host the file: self-hosted copies never get fixes and break API resolution.
- Optional `data-api-url` overrides the API base. Leave it out unless Afileasy support tells the user otherwise.

Add-ons load **after** the core script:

```html
<!-- Stripe Payment Links / Buy Buttons: stamps client_reference_id automatically -->
<script src="https://cdn.afileasy.com/analytics/script-stripe.min.js"></script>

<!-- Zouti: carries the affiliate to the Zouti checkout as utm_term -->
<script src="https://cdn.afileasy.com/analytics/script-zouti.min.js"></script>
```

### Framework placement

| Stack | Where |
|---|---|
| Next.js (App Router) | `app/layout.tsx` with `<Script src="…" data-afileasy="…" strategy="afterInteractive" />` from `next/script` |
| Next.js (Pages Router) | `pages/_document.tsx` `<Head>`, or `next/script` in `_app.tsx` |
| React SPA (Vite, CRA) | `index.html` `<head>` |
| Vue / Nuxt | `index.html`, or the `app.head.script` entry in `nuxt.config` with the `data-afileasy` attribute |
| Laravel / Blade, Rails, Django | the base layout template |
| WordPress (no WooCommerce) | theme header, or a header-scripts plugin |
| Landing page builders | the "custom code in head" setting |

The tag must keep the `data-afileasy` attribute. The script finds itself by that attribute, so don't strip it in a wrapper component.

## Browser API (`window.Afileasy`)

Available after the `afileasy:ready` event fires on `document`. In the usual case, a checkout page that loads after the landing page, it's ready long before the user clicks "buy".

| Member | Returns | Use |
|---|---|---|
| `getReferral()` | UUID or `null` | **The value to stamp on charges.** |
| `getReferralCode()` | string or `null` | The raw `?ref=` value in the current URL. Don't send it as attribution. |
| `getCoupon()` | string or `null` | The affiliate's coupon, when one was resolved. Use it to pre-fill your coupon field. |
| `appendRef(url)` | string | Adds `afileasy_ref=<uuid>` to a URL. |
| `applyHiddenFields()` | void | Fills every `<input data-afileasy-ref>`. It runs on its own; call it again after rendering a form dynamically. |
| `trackLead({ email, name? })` | Promise | Records a lead. See `leads.md`. |
| `clear()` | void | Removes the tracking cookies. Useful in testing. |
| `version` | string | The script version. |

Events on `document`:
- `afileasy:ready`, with `detail: { eventId }`
- `afileasy:lead`, with `detail: { customerId, email }`

Safe read pattern for checkout code:

```js
function getAfileasyRef() {
  return (window.Afileasy && window.Afileasy.getReferral()) || null;
}
```

If the code runs very early (e.g. at page load), wait for the script first:

```js
document.addEventListener('afileasy:ready', (e) => { /* e.detail.eventId */ });
```

In TypeScript, declare the global instead of using `any` everywhere:

```ts
declare global {
  interface Window { Afileasy?: { getReferral(): string | null; getCoupon(): string | null; trackLead(d: { email: string; name?: string }): Promise<unknown> } }
}
```

## Cookies and behavior

- `afileasy_ref` holds the Afileasy code. `afileasy_coupon` holds the affiliate's coupon, when there is one.
- Both are first-party cookies on the site's domain, so the site and its subdomains share them.
- Lifetime is the campaign's cookie duration (default 30 days).
- **First touch:** once `afileasy_ref` is set, later `?ref=` visits don't replace it. To test a different affiliate, call `Afileasy.clear()` or use a private window.
- The script never applies the coupon by itself. Store plugins and apps do that. On a custom checkout, read `getCoupon()` and pre-fill the field yourself.

## Checkout on another domain

The cookie doesn't cross to a different registrable domain. A subdomain of the campaign site is fine.

If the checkout lives elsewhere:
- **Zouti:** the Zouti add-on handles it.
- **Stripe Payment Links:** the Stripe add-on handles it.
- **Anything else:** pass the code yourself. Either add it to the link with `Afileasy.appendRef(url)`, or read it on the origin page and send it in the request that creates the charge.
