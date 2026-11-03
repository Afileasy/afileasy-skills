# Shopify

- **Attribution:** coupon, link (last click via the theme extension), lead.
- **Market:** global.
- **Connection:** OAuth app install, plus the Afileasy **app embed** in the theme.
- **No code to write.**

**Don't paste the tracking script into `theme.liquid`.** The app embed loads it, and a second copy causes duplicate clicks.

## Steps (the user does these)

1. In Afileasy: Settings → Integrations → Shopify. Enter the store domain (`my-store.myshopify.com`; the bare handle also works) and click **Connect with Shopify**.
2. Authorize the Afileasy app in Shopify. Order and refund webhooks are set up automatically.
3. **Link attribution and leads:** in Shopify admin, go to **Online Store → Themes → Customize → App embeds**. Enable **Afileasy Tracking** and fill in:
   - **Public key** (*Chave pública Afileasy*): copy it from the Afileasy Shopify integration page. Without it, the embed loads nothing.
   - **Capture leads** (*Registrar leads*): on by default. It records referred visitors who sign up, subscribe to the newsletter or use the contact form.
   - **Script URL:** leave the default. Change it only if Afileasy support asks.
   - Save the theme.
4. **Coupons:** create one coupon per affiliate in Afileasy (Coupons → Codes). They're created as Shopify discount codes automatically.

## How attribution works

- **Coupon.** A customer who uses the affiliate's discount code credits that affiliate.
  - Visitors who arrive through the affiliate's link get the code applied automatically: the embed sends them once through `/discount/<code>` and back.
- **Last click.** The embed copies the referral cookie into a cart attribute named `afileasy_ref`. Shopify saves it on the order, and Afileasy reads it when the order is paid.
- **Custom flows.** If the user creates orders outside the Online Store (draft orders, a custom storefront or headless checkout), replicate the `afileasy_ref` attribute with the value of `window.Afileasy.getReferral()`. The Afileasy script must be loaded on that storefront.

## Common issues

- **No link attribution.** Check these in order:
  1. Is the app embed enabled and saved, in the **published** theme?
  2. Is the public key filled in?
  3. Does the campaign's website URL match the store's domain (custom domain or `myshopify.com`)?
- **Coupon not applied.** Coupons created before connecting must be synced from Afileasy. Shopify also can't apply a code the store doesn't have.
- **The theme editor doesn't redirect for coupons.** That's on purpose. Test on the live storefront.
