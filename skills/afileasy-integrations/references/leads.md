# Lead capture (optional)

A lead is a referred visitor who signed up **without buying** (account, trial, waitlist, newsletter). Recording leads does two things. It shows the top of the funnel to the merchant and affiliates. It also fires a "lead bonus" commission rule right away, if the campaign has one.

When the same email later buys, the lead becomes a customer of that affiliate. That purchase is credited even with no code on the charge: it's the "existing owner" fallback.

Leads are recorded only for **referred** visits. A visitor with no Afileasy code is silently ignored, so it's safe to run on every signup.

WooCommerce, Shopify and Nuvemshop capture leads on store signups by themselves. Use the options below only for custom sites, or for extra forms like a newsletter.

## Option 1: declarative form (recommended)

```html
<form data-afileasy-lead action="/signup" method="post">
  <input type="email" name="email" required />
  <input type="text" name="name" />
  <button type="submit">Create account</button>
</form>
```

- On submit, the script sends the first `input[type="email"]` and, if present, `input[name="name"]`.
- To point at other fields, mark them with `data-afileasy-email` / `data-afileasy-name`.
- The submission is never blocked or delayed.

## Option 2: JavaScript call

For signups that don't go through a classic form (SPA, OAuth, modal):

```js
window.Afileasy?.trackLead({ email: 'customer@example.com', name: 'Customer name' });
```

## Option 3: server call

Read the code in the browser with `window.Afileasy.getReferral()` and send it to your backend with the signup. Then POST it. Only the public key is needed; there's no secret.

```bash
curl -X POST https://api.afileasy.com/v1/track/lead \
  -H 'Content-Type: application/json' \
  -d '{
    "public_key": "ORGANIZATION_PUBLIC_KEY",
    "code": "<value of getReferral()>",
    "email": "customer@example.com",
    "name": "Customer name"
  }'
```

- `code` may be the Afileasy code (UUID) or the affiliate's referral code.
- If there's no code, don't call it.
- Recording is idempotent by email.
