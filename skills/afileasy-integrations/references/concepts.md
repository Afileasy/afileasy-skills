# Afileasy concepts for integrators

## Vocabulary

| Term | What it is |
|---|---|
| Organization | The merchant's Afileasy account. Holds campaigns, integrations and the **public key**. |
| Public key | The organization's identifier in the tracking script (`data-afileasy="…"`). Public by design and safe in HTML. Shown on every integration page. |
| Campaign | An affiliate program. It has a **website URL**, a cookie duration (default 30 days), commission rules and a portal where affiliates sign up. |
| Affiliate / promoter | Someone who promotes a campaign. Each has a referral code and, optionally, coupons. |
| Referral link | The campaign's site with the affiliate code: `https://store.com/?ref=CODE`. The script also accepts `afi`, `aff` and `via` as the parameter name. |
| Afileasy code (event id) | A UUID created when a visitor arrives through a referral link. `window.Afileasy.getReferral()` returns it. This is the value you stamp on charges. |
| Coupon | A discount code tied to one affiliate. Created in Afileasy (Coupons → Codes) and pushed to the store or gateway. |
| Lead | A referred visitor who signed up without buying. Counts in the funnel and can trigger a "lead bonus" commission rule. |
| Integration | A connection between the organization and a store or gateway. Lives in Settings → Integrations. |

## How a sale gets attributed

1. An affiliate shares `https://store.com/?ref=CODE`.
2. The tracking script on `store.com` sends the click to Afileasy. Afileasy accepts it only if the page is on the campaign's website domain or a subdomain of it. It returns the Afileasy code, which the script stores in the `afileasy_ref` cookie. If the affiliate has a coupon, the script also stores it in `afileasy_coupon`.
3. At checkout, the code reaches the order in one of two ways:
   - the store plugin or app carries it there (WooCommerce, Shopify, Nuvemshop, Zouti scripts);
   - your code stamps it on the charge (Stripe, Asaas, AbacatePay).
4. The provider sends a webhook when the payment is confirmed. Afileasy matches the buyer to a known customer by the provider's customer ID or email, then:
   - **New customer:** the **Afileasy code** on the charge decides (it must be a UUID from this organization). Without one, the affiliate's **coupon** decides. Without either, the payment isn't recorded: there's nobody to credit.
   - **Known customer** (an earlier attributed purchase, or a lead with the same email): the sale goes to the affiliate who already owns the customer, and a new code on the charge doesn't change that. A **coupon from another affiliate** is the exception: it credits the sale to the coupon's owner and transfers the customer to them. Past sales stay with whoever earned them.
5. A commission is created by the campaign's rules. A refund reverses it in proportion; a chargeback holds it until resolved.

## Attribution methods per integration

- **Coupon.** The customer uses the affiliate's coupon. Needs no tracking script on store platforms.
- **Link.** The referral link plus the tracking script plus the code reaching the order.
- **Lead.** The customer signed up earlier through the affiliate. Later purchases with the same email are credited to that affiliate.

## Availability

- **Global:** Stripe, WooCommerce, Shopify.
- **Brazil only:** Asaas, AbacatePay, Zouti, Nuvemshop. Pagar.me is also Brazil-only and marked "Coming soon": it can't be connected yet.
- **Not supported:** anything else, including Hotmart, Kiwify, Eduzz, Mercado Pago, PayPal, Paddle and Lemon Squeezy. There is no merchant API for pushing sales manually.

## Account

- Sign up at `https://app.afileasy.com`. A free plan exists, limited to a few active affiliates.
- Installing the Afileasy app from the Nuvemshop app store also creates the account.
- Integration pages are visible only to organization admins.
