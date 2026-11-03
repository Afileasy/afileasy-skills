# Nuvemshop (Tiendanube)

- **Attribution:** coupon and lead. There's no last-click link attribution for purchases.
- **Market:** Brazil.
- **Connection:** OAuth app install.
- **No code to write.**

**Don't paste the tracking script into the store theme.** The Afileasy storefront app handles referral capture.

## Steps (the user does these)

The user can start from either side:
- **From Afileasy:** Settings → Integrations → Nuvemshop → **Connect with Nuvemshop**. Then authorize the app in the store.
- **From the Nuvemshop app store:** install the Afileasy app. If the user has no Afileasy account yet, this creates one.
  - Afileasy emails a one-time code to confirm the store's email, then finishes signup.
  - A store already connected to another Afileasy account doesn't log anyone in automatically.

After authorizing:
1. Order webhooks (paid, cancelled) are subscribed automatically. There's nothing to configure in Nuvemshop.
2. **Coupons:** create one coupon per affiliate in Afileasy (Coupons → Codes). They're created in the store automatically.
3. **Leads and coupon autofill:** the Afileasy storefront app captures `?ref=CODE`, applies the affiliate's coupon at checkout and records leads, even from abandoned checkouts.

## How attribution works

- **Purchases are attributed by the coupon on the order.** The storefront app's job is to put the affiliate's coupon there for visitors who came through the link.
- Without an affiliate coupon on the order, the sale goes to the customer's existing affiliate. That includes a lead captured earlier by the app.
- A sale from a new customer with no coupon isn't credited to anyone. Tell the user to promote coupons as the main channel on Nuvemshop.

## Common issues

- **Coupon not applied automatically.** Check these in order:
  1. The coupon must exist in the store (coupons created before connecting must be synced from Afileasy).
  2. The shopper's own coupon is never replaced.
  3. The store may refuse the code: exhausted, cart below the minimum, or not combinable.
- **No leads at all.** The storefront app may not be active on that store. Contact Afileasy support; its activation is handled with Nuvemshop.
