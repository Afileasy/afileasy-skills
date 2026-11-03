# Zouti

- **Attribution:** link and lead. No coupons.
- **Market:** Brazil.
- **Connection:** a webhook URL generated in Afileasy, plus a signing secret generated in Zouti.

The Zouti checkout accepts no scripts, and its webhook carries no metadata or coupon. The only way to carry the affiliate to the order is the checkout's **UTM parameters**. So the scripts go on the merchant's **sales page**, the page the affiliate link points to, not on the checkout.

## Dashboard steps (the user does these)

1. Afileasy → Settings → Integrations → Zouti → **Generate webhook URL**. The integration stays *Pending* until step 4.
2. In Zouti: **Integrações → Webhooks → Criar Webhook**. Paste the URL from Afileasy.
3. Select the events **Pedido pago**, **Pedido estornado** and **Pedido em chargeback**, then create the webhook.
4. Copy the **signing secret** Zouti shows. In Afileasy, paste it into the field on the integration page and click **Save secret**. The status becomes *Active*.
   - Do this **right away**. Zouti turns the webhook off after 10 rejected deliveries in a row, and Afileasy rejects everything until the secret is saved.

## Code (you do this)

On the sales page (in `<head>` or before `</body>`):

```html
<script src="https://cdn.afileasy.com/analytics/script.min.js" data-afileasy="ORGANIZATION_PUBLIC_KEY"></script>
<!-- Zouti plugin: carries the affiliate from the click to the Zouti checkout -->
<script src="https://cdn.afileasy.com/analytics/script-zouti.min.js"></script>
```

- The plugin adds `utm_term=<Afileasy code>` to every link pointing at `*.zouti.com.br`.
  - It retries for a few seconds, which covers buttons that page builders render late.
  - Zouti returns the UTM with the paid order, and Afileasy credits the affiliate.
- Keep **`utm_term` free** on checkout links. The plugin overwrites it.
- Checkout buttons must be real links (`<a href="https://….zouti.com.br/…">`). A button that navigates through JavaScript isn't rewritten.
  - If the page builder does that, convert it to a link.
  - Or append the code yourself: `Afileasy.getReferral()` → `utm_term`.

## Limits to tell the user

- **Direct checkout links don't work for link attribution.** If an affiliate shares the Zouti checkout URL directly, no script runs.
  - The sale is attributed only if the buyer was already recorded as that affiliate's **lead** with the same email.
  - Affiliates should share the sales page.
- To capture leads on the sales page (e.g. a pre-checkout form), see `leads.md`.

## Verify

1. Open the sales page with `?ref=<affiliate code>`.
2. Hover the buy button. Its URL now ends with `utm_term=<uuid>`.
3. Complete a purchase. In Zouti's webhook logs, deliveries return 200.
4. The sale appears for the affiliate in Afileasy.
