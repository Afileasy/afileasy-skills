# Stripe

Attribution: coupon, link, lead. Market: global. Connection: OAuth (Stripe Connect). **No webhook to configure.** Afileasy receives the connected account's events on its own.

## Dashboard steps (the user does these)

1. Afileasy → Settings → Integrations → Stripe → **Connect with Stripe**.
2. Log in to the existing Stripe account (or create one) and authorize the platform.
3. The user is redirected back and the integration shows as connected.

Disconnecting stops commissions for new payments and stops refund reversals. The Stripe account itself stays intact and can be reconnected.

## Tracking script

Install the core script on every page (`tracking-script.md`). The page also offers a second, **optional** script, `script-stripe.min.js`:
- **Use it** when the site sells through **Payment Links** (`buy.stripe.com/...`) or **Buy Buttons** (`<stripe-buy-button>`). It adds `client_reference_id` to those links and buttons by itself, so the user writes no code.
- **Skip it** when the backend creates Checkout Sessions or PaymentIntents. Stamp the code yourself (below).

## Where Afileasy reads the code

| Stripe object / event | Afileasy code | Coupon |
|---|---|---|
| Checkout Session (`checkout.session.completed`) | `client_reference_id`, else `metadata.event_id` | `metadata.coupon_code` |
| Customer (`customer.created`) | `metadata.event_id` | `metadata.coupon_code` |
| Charge (`charge.succeeded`, one-time) | `metadata.event_id` | `metadata.coupon_code` |
| Invoice (`invoice.paid`, subscriptions) | `metadata.event_id` on the invoice, else on the parent subscription | same cascade |

- **Subscriptions:** stamp the code once, at creation, on the subscription's metadata. Every renewal invoice is attributed through it.
- **Charges without a Stripe Customer are ignored.** Afileasy records a one-time charge only when it's tied to a Customer.
  - In Checkout Session `mode: 'payment'`, Stripe doesn't create a Customer by default. Set `customer_creation: 'always'`, or pass an existing `customer`.
  - Subscription mode always has a Customer.
  - Payment Links behave like `payment` mode: make sure the link is set to create a Customer.
- **Coupons:** Afileasy creates each affiliate coupon in the connected Stripe account as a promotion code, so customers can type it at checkout. For the sale to be attributed **by coupon**, also send the code in `metadata.coupon_code`.
  - Typical approach: pre-fill your own coupon field with `window.Afileasy.getCoupon()`.
  - Then pass the applied code to the session and to `metadata.coupon_code`.
  - If you don't send it, attribution relies on the Afileasy code or on the customer's existing owner.

## Code: Checkout Session (most common)

Frontend, on the checkout page:

```js
const afileasyRef = window.Afileasy?.getReferral() ?? null;
await fetch('/api/checkout', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ priceId, afileasyRef }),
});
```

Backend (Node example; the fields are the same in every Stripe SDK):

```js
const { priceId, afileasyRef } = req.body;
const ref = afileasyRef || undefined;

const session = await stripe.checkout.sessions.create({
  mode: 'payment',                    // or 'subscription'
  line_items: [{ price: priceId, quantity: 1 }],
  customer_creation: 'always',        // payment mode only; omit in subscription mode
  client_reference_id: ref,
  metadata: ref ? { event_id: ref } : undefined,
  payment_intent_data: ref ? { metadata: { event_id: ref } } : undefined, // payment mode only
  // subscription_data: ref ? { metadata: { event_id: ref } } : undefined, // subscription mode
  success_url, cancel_url,
});
```

Don't send an empty string or a placeholder. When there's no code, leave the fields out.

## Code: PaymentIntent / Elements (custom checkout)

Stamp `metadata.event_id` on the **Customer** when you create it, and on the **PaymentIntent**:

```js
const customer = await stripe.customers.create({ email, metadata: ref ? { event_id: ref } : {} });
const pi = await stripe.paymentIntents.create({
  amount, currency, customer: customer.id,
  metadata: ref ? { event_id: ref } : {},
});
```

For subscriptions created through the API, put it in `stripe.subscriptions.create({ ..., metadata: { event_id: ref } })`.

## Verify

1. Visit the campaign site with `?ref=<affiliate code>` and confirm `window.Afileasy.getReferral()` returns a UUID in the console.
2. Make a real low-value purchase with a fresh email. Test-mode payments may not reach Afileasy, so don't read a missing test sale as a broken integration.
3. In Stripe, open the payment. The session or payment shows `client_reference_id` / `metadata.event_id` with that UUID.
4. In Afileasy, the sale appears under the affiliate.
