---
name: afileasy-integrations
description: Connect a store, checkout or payment gateway to Afileasy (affiliate/referral program platform) so affiliate sales, coupons and leads are tracked and commissioned. Use this whenever the user mentions Afileasy together with installing, integrating, connecting or troubleshooting — e.g. adding the Afileasy tracking script, stamping the affiliate/referral code on a Stripe, Asaas, AbacatePay or Zouti charge, connecting WooCommerce, Shopify or Nuvemshop, setting up an Afileasy webhook, capturing leads, or figuring out why a sale was not credited to an affiliate. Also use it when the user asks which platforms Afileasy supports or what they need to do in the Afileasy dashboard.
---

# Afileasy integrations

Afileasy runs affiliate programs: an organization creates campaigns, affiliates share links (`https://store.com/?ref=CODE`) and coupons, and every paid sale that reaches Afileasy through a connected integration becomes a commission for the right affiliate. Refunds and chargebacks reverse or hold it.

Your job is to get the user's integration working end to end. Two people do the work:
- **The user** does what happens in dashboards: the Afileasy dashboard (`https://app.afileasy.com`) and the third-party dashboard (Stripe, Asaas, Shopify admin…). You can't log into those. Give the exact steps, then wait for the user to confirm.
- **You** do what happens in their code: install the tracking script, stamp the affiliate code on the checkout or charge, and add lead capture.

## Ground rules

- **Reply in the user's language.** Most Afileasy users are Brazilian, so default to pt-BR when they write in Portuguese. Dashboard labels exist in pt-BR, en and es. Quote the label in the user's language when you know it, otherwise use the English one from these references.
- **Only state what these references say.** Afileasy validates field names and event lists strictly. A wrong field silently loses attribution and nothing errors, so a guessed field is worse than none. If a provider or field isn't documented here, say so. Don't improvise.
- **Keep secrets out of code and chat.** Gateway API keys, webhook tokens and secrets, and the WooCommerce `afl_…` connection key go straight from one dashboard into the other. Never ask the user to paste them to you, and never commit them. The organization **public key** (`data-afileasy="…"`) is not a secret: it's meant to sit in page HTML.
- **Copy snippets from the dashboard.** Each integration page in Afileasy (Settings → Integrations → provider) shows the install snippet with the organization's public key already filled in, plus the webhook URL and credentials. Ask the user to copy those. If they only give you the public key, build the snippet from `references/tracking-script.md`.

## Workflow

### 1. Find out what they sell with
Detect it from the repo before asking:
- `stripe` in package.json, composer.json, requirements or Gemfile, or `buy.stripe.com` links or `<stripe-buy-button>` → **Stripe**
- calls to `api.asaas.com` / `/v3/payments`, or an Asaas SDK → **Asaas**
- calls to `api.abacatepay.com` / `/v2/checkouts/create` / `/v2/transparents/create` → **AbacatePay**
- links to `*.zouti.com.br` → **Zouti**
- `wp-content/` with WooCommerce → **WooCommerce**
- a Shopify theme (`layout/theme.liquid`, `sections/*.liquid`) or a `*.myshopify.com` store → **Shopify**
- a Nuvemshop / Tiendanube store → **Nuvemshop**

If the repo doesn't settle it, ask one short question: where do your customers pay?

### 2. Check it's supported, then read only that reference

| Provider | How it connects | Coupon | Link | Lead | Code you write | Market | Reference |
|---|---|---|---|---|---|---|---|
| Stripe | OAuth (Stripe Connect) | ✓ | ✓ | ✓ | script + stamp the code on the Checkout Session / charge | global | `references/stripe.md` |
| Asaas | API key + webhook (URL + token) | – | ✓ | ✓ | script + `externalReference` | Brazil | `references/asaas.md` |
| AbacatePay | API key (v2) + webhook (URL with secret) | ✓ | ✓ | ✓ | script + `metadata.event_id` | Brazil | `references/abacatepay.md` |
| Zouti | webhook URL + signing secret | – | ✓ | ✓ | two scripts on the sales page, no backend code | Brazil | `references/zouti.md` |
| WooCommerce | plugin + connection key | ✓ | ✓ | ✓ | none | global | `references/woocommerce.md` |
| Shopify | OAuth + theme app embed | ✓ | ✓ | ✓ | none (paste the public key in the app embed) | global | `references/shopify.md` |
| Nuvemshop | OAuth app install | ✓ | lead only | ✓ | none | Brazil | `references/nuvemshop.md` |

- **Pagar.me** shows as "Coming soon". It can't be connected yet.
- **Not supported:** Hotmart, Kiwify, Eduzz, Mercado Pago, PayPal, Paddle, Lemon Squeezy, and any other provider not in the table.
  - Say so plainly.
  - If the user can take payment through a supported gateway instead, point that out.
  - Don't invent a "generic webhook" or "REST API" path. Afileasy has no merchant-facing API for posting sales.
- **Brazil-only** providers appear only for organizations operating in Brazil.

Read `references/concepts.md` first if the user is new to Afileasy or asks how attribution works. Read `references/tracking-script.md` for every provider that needs the script (Stripe, Asaas, AbacatePay, Zouti). Read `references/leads.md` when they want to reward or track sign-ups.

### 3. Dashboard steps (the user does these)
Give the numbered steps from the provider reference. Wait for the user to finish. Then carry on with the code.

The webhook URL and token appear in Afileasy only **after** the account is connected. So the order is: connect in Afileasy first, then create the webhook in the provider.

### 4. Code changes (you do these)
For gateways where your server creates the charge (Stripe API, Asaas, AbacatePay), the pattern is always the same:
1. The tracking script is on every page of the site. It reads `?ref=` and stores the affiliate code in a first-party cookie.
2. On the checkout page, **in the browser**, read `window.Afileasy.getReferral()`. Or add `<input type="hidden" data-afileasy-ref>` to the order form, which the script fills in.
3. Send that value to your backend with the order.
4. The backend writes it into the provider-specific field when it creates the charge.

`window.Afileasy` exists only in the browser. Never call it in server code; that's the most common mistake. The value is a UUID, or `null` when the visit came from no affiliate. Pass it through untouched. Don't substitute an order ID, an affiliate code or a placeholder: anything that isn't the Afileasy UUID is ignored.

Make the change small and local. Find where the checkout or charge is created, add the field there, and thread the value from the frontend through the existing request. Don't restructure the user's checkout.

### 5. Verify
Walk the user through the checks in `references/troubleshooting.md` → "Verification checklist". If something fails, use the symptom table in the same file.

## Concepts you'll need in every conversation

- **Campaign website URL.** Each campaign has a site URL in its general settings.
  - A click counts only when the landing page is on that domain or one of its subdomains (`www.` is ignored).
  - If the URL is empty or points to another domain, every click is rejected and no sale is attributed through links.
  - Check this first whenever link attribution "doesn't work".
- **Who gets the sale.**
  - **New customer:** the Afileasy code on the order decides. If there's no code, the coupon decides. If there's neither, Afileasy doesn't record the sale at all.
  - **Known customer** (an earlier attributed purchase, or a lead with the same email): the sale stays with that customer's affiliate whatever code is on the order. Only a coupon from another affiliate moves the sale, and the customer, to the coupon's owner.
  - A broken code stamp can hide behind this, because it still "works" for known customers. Test with a **fresh customer email**.
- **First touch in the browser.** The script keeps the first affiliate cookie it sees (30 days by default; configurable per campaign). A later `?ref=` doesn't replace it.
- **Coupons.** Create them in Afileasy (Coupons → Codes), one per affiliate. Afileasy creates them as real discount codes in Stripe, AbacatePay, WooCommerce, Shopify and Nuvemshop. Coupons can be created only once at least one coupon-capable integration is connected.

## When things are out of scope

- **Managing affiliates, campaigns, commissions or payouts** isn't integration work. Point the user to the Afileasy dashboard. If an Afileasy MCP server is connected in their agent, its tools cover campaigns, affiliates, coupons and stats.
- **Afileasy can't be reached or the dashboard shows errors.** Tell the user to contact Afileasy support. Don't debug Afileasy's servers.
