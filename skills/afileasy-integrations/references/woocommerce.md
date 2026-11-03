# WooCommerce

- **Attribution:** coupon, link, lead.
- **Market:** global.
- **Connection:** the **Afileasy for WooCommerce** plugin, plus a connection key.
- **No code to write.** The plugin injects the tracking script, saves the affiliate code on each order, reports coupons, captures leads on account signup and sends paid or refunded orders to Afileasy.

**Don't add the tracking script to the theme manually.** The plugin already loads it.

## Steps (the user does these)

1. In WordPress: **Plugins → Add New**, install and activate **Afileasy for WooCommerce**. If it doesn't show up in the search, ask Afileasy support for the plugin package.
2. In Afileasy: Settings → Integrations → WooCommerce → **Generate connection key**.
3. Copy the **connection key** (it starts with `afl_`) and paste it in WordPress under **WooCommerce → Afileasy**.
4. Click **Test connection** in the plugin.

- Treat the connection key like a password.
- If the user disconnects and reconnects in Afileasy, they have to generate and paste a **new** key.

## What happens automatically

- A visitor arrives with `?ref=CODE`; the script stores the referral in a cookie.
- At checkout (classic or block checkout), the plugin saves the Afileasy code on the order.
- Coupons created in Afileasy become real WooCommerce coupons. The affiliate's coupon is applied to the cart automatically for visitors who came through their link.
- Paid and refunded orders are sent to Afileasy in the background (Action Scheduler), so checkout never waits.
- Customer accounts created by referred visitors (My Account or checkout) are recorded as leads.

## Optional tweaks (only if the user asks)

- **Capture leads from other forms** (newsletter, contact): add `data-afileasy-lead` to the `<form>` in the theme. See `leads.md`.
- **Turn off automatic lead capture:**

  ```php
  add_filter( 'afileasy_capture_leads', '__return_false' );
  ```

## Common issues

- **Affiliate coupon not applied.** Check these in order:
  1. The coupon has to exist in the store. Coupons created in Afileasy before connecting must be synced: Afileasy → Coupons → select → sync with integrations.
  2. Autofill is skipped when the shopper already applied their own coupon or removed the affiliate's.
  3. Autofill is also skipped when the cart doesn't meet the coupon's rules.
- **Coupon sync stopped.** The plugin creates a WooCommerce REST API key on connection (WooCommerce → Settings → Advanced → REST API). If that key was revoked, coupon sync stops; everything else keeps working. Reconnect to restore it.
- **Orders not arriving.**
  1. Check WooCommerce → Status → Scheduled Actions for failed Afileasy actions.
  2. Click **Test connection** again.
  3. If the integration was reconnected in Afileasy, paste the new key.
