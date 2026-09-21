import crypto from "node:crypto";
import express from "express";
import cors from "cors";
import helmet from "helmet";
import rateLimit from "express-rate-limit";

const app = express();
const port = Number(process.env.PORT || 3000);
const allowedOrigin = process.env.FRONTEND_ORIGIN || "http://localhost:5500";
const paymentMethods = new Set(["Mobile money via WhatsApp", "Cash on delivery"]);
const catalog = new Map([
  ["Jesus is King Tee", 28000],
  ["Oversized Hoodie", 25000],
  ["Grace Bangle", 7000],
  ["Cross Necklace", 7000],
  ["Faith Bracelet", 5000],
  ["Faith Cap", 22000],
  ["Christian Ring", 4000],
  ["LGBAY T-shirt", 25000],
  ["LGBAY Tracksuit", 50000],
  ["Faith Rings", 3000],
  ["Classic Faith Cap", 18000],
  ["Verse T-shirt", 30000],
  ["White LGBAY Tracksuit", 50000],
]);

app.disable("x-powered-by");
app.use(helmet());
app.use(cors({ origin: allowedOrigin, methods: ["GET", "POST"], allowedHeaders: ["Content-Type"] }));
app.use(express.json({ limit: "10kb" }));
app.use(rateLimit({ windowMs: 15 * 60 * 1000, limit: 30, standardHeaders: "draft-7", legacyHeaders: false }));

app.get("/api/health", (_request, response) => response.json({ status: "ok" }));

app.post("/api/orders", (request, response) => {
  const body = request.body && typeof request.body === "object" ? request.body : {};
  if (Object.keys(body).some((key) => ["cardNumber", "cvv", "pin", "password"].includes(key))) {
    return response.status(400).json({ message: "Sensitive payment details are not accepted." });
  }

  const customer = body.customer && typeof body.customer === "object" ? body.customer : {};
  const name = cleanText(customer.name, 100);
  const phone = cleanText(customer.phone, 30);
  const address = cleanText(customer.address, 300);
  const payment = cleanText(body.payment, 60);
  const items = Array.isArray(body.items) ? body.items : [];

  if (!name || !phone || !address || !paymentMethods.has(payment) || !items.length || items.length > 20) {
    return response.status(400).json({ message: "Please provide valid customer, payment, and order details." });
  }

  const validatedItems = [];
  for (const item of items) {
    const title = cleanText(item?.title, 120);
    const quantity = Number(item?.quantity);
    const price = catalog.get(title);
    if (!price || !Number.isInteger(quantity) || quantity < 1 || quantity > 10) {
      return response.status(400).json({ message: "The order contains an invalid product or quantity." });
    }
    validatedItems.push({ title, quantity, price });
  }

  const subtotal = validatedItems.reduce((total, item) => total + item.price * item.quantity, 0);
  const shipping = 2500;
  const orderId = crypto.randomUUID();

  return response.status(201).json({
    orderId,
    items: validatedItems,
    subtotal,
    shipping,
    total: subtotal + shipping,
    message: "Order details validated. Confirm payment through the selected safe channel.",
  });
});

app.use((_request, response) => response.status(404).json({ message: "Not found." }));
app.listen(port, () => console.log(`LGBAY order backend listening on port ${port}`));

function cleanText(value, maxLength) {
  return typeof value === "string" ? value.trim().slice(0, maxLength) : "";
}
