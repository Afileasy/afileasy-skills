# AbacatePay

- **Attribution:** coupon, link, lead.
- **Market:** Brazil.
- **Connection:** API key (v2), plus a webhook the user creates in AbacatePay.

## Dashboard steps (the user does these)

1. In AbacatePay: **Integrate → API Keys**, copy the API key. **It must be a v2 key.**
2. In Afileasy: Settings → Integrations → AbacatePay. Paste the key and click **Connect with AbacatePay**.
3. The page now shows the **Webhook URL**, which ends in `?webhookSecret=…`, and the **Webhook secret**. In AbacatePay: **Integrate → Webhooks → Create Webhook**.
   - **URL:** paste the full URL, **including everything from `?` onward**. The secret travels only in the URL. Without it, events are rejected.
   - **Secret field**, if the form has one: paste the same secret value.
   - **Events:** select exactly these.
     - Generate commission: `checkout.completed`, `transparent.completed`, `subscription.completed`, `subscription.renewed`
     - Reverse commission: `checkout.refunded`, `transparent.refunded`, `checkout.disputed`, `transparent.disputed`
     - Subscription status: `subscription.trial_started`, `subscription.cancelled`
4. Save the webhook.

## Code (you do this)

1. Install the tracking script on every page (`tracking-script.md`).
2. On the checkout page, read `window.Afileasy?.getReferral()` in the browser and send it to the backend with the order. A hidden `<input data-afileasy-ref>` works too.
3. In the backend, set **`metadata.event_id`** when calling **`POST /v2/checkouts/create`** or **`POST /v2/transparents/create`**:

   ```json
   {
     "method": "PIX",
     "data": {
       "amount": 15000,
       "metadata": { "event_id": "<value of getReferral()>" }
     }
   }
   ```

   This is the shape for a transparent charge. For a checkout, add `metadata` the same way to the checkout body you already send.

- **Fallback carrier:** if `metadata` can't be used, Afileasy also reads the charge's `externalId`. Prefer `metadata.event_id`.
- **Only a UUID counts.** An order number in these fields is ignored.
- **Coupons:** affiliate coupons created in Afileasy are created in AbacatePay too. To credit a sale by coupon, send the applied code in `metadata.coupon_code`. `window.Afileasy.getCoupon()` gives the affiliate's coupon to pre-fill.
- **Subscriptions:** send the code on the checkout that starts the subscription. Renewal webhooks don't repeat metadata; renewals inherit the customer's affiliate automatically.
- **Amounts** in AbacatePay are in cents (`15000` = R$ 150,00). This only matters when you're writing the request.

## Verify

1. Visit with `?ref=<affiliate code>` and pay a small amount.
2. In AbacatePay's webhook logs, deliveries return 200. A **400** means the `?webhookSecret=` part is missing or wrong.
3. The sale appears for the affiliate in Afileasy.
