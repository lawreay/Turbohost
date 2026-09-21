# LGBAY

LGBAY is a static storefront for faith-inspired clothing and accessories.

## Run the site locally

Open the `LGBAY.html` file with a local static server. The checkout flow sends an order summary to WhatsApp and does not collect card numbers, PINs, or passwords.

## Order backend

```powershell
Set-Location backend
npm.cmd install
$env:FRONTEND_ORIGIN = "http://localhost:5500"
npm.cmd start
```

The backend validates product names, quantities, prices, customer fields, and payment methods server-side. It deliberately does not accept or store card details, CVVs, PINs, or passwords. Configure a real payment provider separately before enabling online card or mobile-money collection in production.

## Contacts

- WhatsApp: https://wa.me/265988721157
- Instagram: https://www.instagram.com/l.g.b.a.y/
