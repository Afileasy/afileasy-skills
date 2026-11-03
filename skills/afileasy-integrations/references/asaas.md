# Asaas

- **Attribution:** link and lead. There are no Afileasy coupons on Asaas.
- **Market:** Brazil.
- **Connection:** API key, plus a webhook the user creates in Asaas.

## Dashboard steps (the user does these)

1. In Asaas: **User menu → Integrations**, generate or copy the **API Key**. A sandbox key (`$aact_hmlg_…`) connects to the Asaas sandbox; a production key connects to production.
2. In Afileasy: Settings → Integrations → Asaas. Paste the key into the form and click **Connect with Asaas**. Afileasy checks the key with Asaas right away.
3. The page now shows **Webhook URL** and **Authentication token**. In Asaas: **User menu → Integrations → Webhooks → Create Webhook**, then fill in:
   - **URL:** the Webhook URL from Afileasy.
   - **Authentication token:** paste exactly the value from Afileasy. **Don't** use Asaas's "Generate token" button. A different token means every event is rejected.
   - **API version:** v3.
   - **Send type:** Sequential (SEQUENTIALLY).
   - **Email:** one of the user's own emails, for failure alerts.
   - **Webhook enabled** and **Sync queue enabled:** on.
   - **Events:** select exactly these.
     - Generate commission: `PAYMENT_RECEIVED`, `PAYMENT_CONFIRMED`
     - Reverse commission (refund): `PAYMENT_REFUNDED`, `PAYMENT_PARTIALLY_REFUNDED`, `PAYMENT_RECEIVED_IN_CASH_UNDONE`
     - Hold commission during chargeback: `PAYMENT_CHARGEBACK_REQUESTED`, `PAYMENT_CHARGEBACK_DISPUTE`, `PAYMENT_AWAITING_CHARGEBACK_REVERSAL`
     - Subscription status: `SUBSCRIPTION_INACTIVATED`, `SUBSCRIPTION_DELETED`, `SUBSCRIPTION_UPDATED`
4. Save the webhook in Asaas.

Asaas has no metadata field. Afileasy reads the code from **`externalReference`**.

## Code (you do this)

1. Install the tracking script on every page (`tracking-script.md`).
2. On the checkout page, read the code in the browser and send it to the backend with the order:

   ```js
   const afileasyRef = window.Afileasy?.getReferral() ?? null;
   // include afileasyRef in the existing request that creates the order
   ```

   Alternatively, add `<input type="hidden" name="afileasyRef" data-afileasy-ref>` to the order form. The script fills it in.
3. In the backend, set `externalReference` to that value on **`POST /v3/payments`** (one-time charges) or **`POST /v3/subscriptions`** (subscriptions):

   ```json
   {
     "customer": "cus_000005219613",
     "billingType": "PIX",
     "value": 150.00,
     "dueDate": "2026-12-01",
     "externalReference": "<value of getReferral()>"
   }
   ```

- **Only a UUID counts.** Many integrations already use `externalReference` for their own order ID.
  - If the code already writes something there, tell the user about the conflict.
  - Afileasy needs that field. The order ID has to move somewhere else, e.g. `description`, or the user's own database keyed by the Asaas payment ID.
  - Don't overwrite it silently.
- **No code:** omit the field, or leave what was there before if the user decides so. The sale then falls back to the customer's existing affiliate, if any.
- **Subscriptions:** set it on the subscription. Each generated payment carries it.

## Verify

1. Visit the campaign site with `?ref=<affiliate code>`, then go through checkout.
2. In Asaas, open the payment and confirm **External reference** contains a UUID.
3. In Asaas → Webhooks → logs, the deliveries to Afileasy return 200.
4. In Afileasy, the sale appears for the affiliate once the payment is received or confirmed.
