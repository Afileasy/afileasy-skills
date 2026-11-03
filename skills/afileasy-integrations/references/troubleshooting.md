# Verification and troubleshooting

## Verification checklist

Run these in order. Each step proves the previous one worked. Use a private browser window and an email that has **never** bought before. A known customer is credited to their existing affiliate no matter what, which hides a broken setup.

1. **The integration is active.** Afileasy → Settings → Integrations → provider shows *Connected* / *Active*. Zouti stays *Pending* until the signing secret is saved.
2. **The campaign website URL is set** and matches the store's domain (Campaign → general settings).
3. **The script loads.** On the landing page, `window.Afileasy` exists in the console, with a `version` set.
   - Skip this on WooCommerce, Shopify and Nuvemshop, where the plugin or app loads it.
   - No `[Afileasy]` warning in the console means the `data-afileasy` attribute is present.
4. **The click is registered.**
   1. Open `https://<campaign site>/?ref=<affiliate code>`. The affiliate's code is in their link, in the affiliate portal or in Afileasy's affiliate list.
   2. `Afileasy.getReferral()` returns a UUID, and the `afileasy_ref` cookie exists.
5. **The code reaches the provider.** Open the test payment or order in the provider's dashboard:
   - Stripe: `client_reference_id` / `metadata.event_id`
   - Asaas: *External reference*
   - AbacatePay: `metadata.event_id`
   - Zouti: the checkout URL had `utm_term=<uuid>`
   - Shopify: order *Additional details* shows `afileasy_ref`
6. **The webhook is delivered.** The provider's webhook log shows a 200 response from Afileasy.
7. **The sale is attributed.** Afileasy shows the sale and the commission under the affiliate.

## Symptom → cause → fix

| Symptom | Likely cause | Fix |
|---|---|---|
| `window.Afileasy` is undefined | Script missing on that page, blocked, or the tag lost `data-afileasy` | Add it to the base layout; keep the attribute; check ad-blockers in testing |
| `getReferral()` is `null` right after `?ref=` | The page's domain doesn't match the campaign website URL (click rejected), the URL is empty, or the affiliate code is wrong | Set or fix the campaign website URL (subdomains are fine); copy the code from the affiliate's own link |
| `getReferral()` returns an **older** affiliate | First touch: the cookie from an earlier visit stays | `Afileasy.clear()` or a private window |
| Code is `null` on the checkout page only | Checkout is on another domain, or the script isn't on the checkout page | Load the script there, or pass the code with `Afileasy.appendRef(url)` / your own request |
| The provider shows an order number in the field, not a UUID | Code reads the wrong value or overwrites it | Use `getReferral()` (not `getReferralCode()`), and don't reuse the field for your own ID |
| Webhook log shows **400** | Wrong secret or token | Asaas: token not pasted exactly (or "Generate token" used). AbacatePay: URL missing `?webhookSecret=…`. Zouti: secret not saved in Afileasy or outdated |
| Webhook log shows **404** | URL has the wrong integration token, or the integration was disconnected | Copy the URL again from the Afileasy integration page |
| Zouti webhook got disabled | 10 rejected deliveries in a row (secret saved late) | Save the secret in Afileasy, re-enable the webhook in Zouti |
| No deliveries at all | Webhook not created, wrong events selected, or saved disabled | Re-check the event list in the provider reference |
| Stripe sale missing entirely | One-time charge without a Stripe Customer | `customer_creation: 'always'` on Checkout Sessions, or pass `customer` |
| Sale arrives but goes to the wrong affiliate | The customer already belonged to that affiliate, or used another affiliate's coupon | Expected behavior (see `concepts.md`); test with a fresh email |
| Sale arrives with no commission | The sale is recorded but no commission rule applies to the affiliate's group, or the commission is held for approval | Check the campaign's commission rules and approval settings in Afileasy |
| Coupon not created in the store | No coupon-capable integration was connected when it was created, or it was created before connecting | Sync coupons from Afileasy → Coupons |
| WooCommerce orders not arriving | Connection key replaced, or scheduled actions failing | Paste the current key, then click **Test connection**; check WooCommerce → Status → Scheduled Actions |
| Shopify: no last-click attribution | App embed off, unsaved, not in the published theme, or missing the public key | Enable **Afileasy Tracking** in App embeds with the key |

If every step passes and the sale still doesn't appear, ask the user to contact Afileasy support. Have them share the provider's payment ID and the time of the test.
