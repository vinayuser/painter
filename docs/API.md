# Paint Store API Documentation

Base URL: `https://painter.rainbowstonerealestate.com/api/v1`

Authentication: `Authorization: Bearer {token}`

**Website login:** Admin only at `/admin`  
**API login:** Customers, painters, and delivery agents use separate API endpoints.

---

## Auth (OTP via 2Factor.in)

All API users authenticate with mobile OTP. Set `TWOFACTOR_API_KEY` in `.env`. Without it, dev mode accepts OTP `123456`.

| Method | Endpoint | Role |
|--------|----------|------|
| POST | `/auth/register` | Customer self-register → sends OTP |
| POST | `/auth/staff/register` | Painter self-register → sends OTP |
| POST | `/auth/login` | Customer login → sends OTP |
| POST | `/auth/staff/login` | Painter / Delivery login → sends OTP (`role` required) |
| POST | `/auth/verify-otp` | Verify OTP → returns JWT |
| POST | `/auth/resend-otp` | Resend OTP |
| GET | `/auth/me` | Any authenticated user |
| POST | `/auth/logout` | Any |
| POST | `/auth/refresh` | Any |

**Verify OTP body:** `{ "phone": "9876543210", "otp": "123456", "session_id": "...", "role": "customer", "fcm_token": "optional-device-token" }`

On successful verify, `fcm_token` is saved and the device is subscribed to the role topic:

| Role | FCM Topic |
|------|-----------|
| Customer | `paint_store_customers` |
| Vendor | `paint_store_vendors` |
| Painter | `paint_store_painters` |
| Delivery | `paint_store_delivery_agents` |
| Admin | `paint_store_admins` |

Also: `POST /auth/fcm-token` `{ "fcm_token": "..." }` to refresh token anytime.

### In-app notifications (database inbox)

Every push event is also saved in `app_notifications` for the target user.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | My notifications (paginated). Query: `unread=1`, `type=`, `per_page=` |
| GET | `/notifications/unread-count` | `{ data: { unread_count } }` for badge |
| POST | `/notifications/{id}/read` | Mark one as read |
| POST | `/notifications/read-all` | Mark all as read |

Works for **customer, painter, delivery_agent, vendor** JWT tokens.

**Web panels:** Admin `/admin/notifications`, Vendor `/vendor/notifications`, Delivery `/delivery/notifications`

### Push notifications
Lifecycle events push to FCM (and log to `app_notifications`):
- Order placed → vendor + admins (+ Chrome if subscribed)
- Order packed → customer + admins
- Delivery assigned → agent + customer
- Delivery accepted / out for delivery / delivered → relevant parties
- Painter booking created / accepted / rejected / started / completed

Admin panel → **Notifications** can broadcast to any global role channel.

**Verify OTP response includes:**
- `access_token` — JWT bearer token
- `expires_in` — token lifetime in **seconds** (default: 30 days = 2592000)

### Token lifecycle (important for mobile apps)

JWT tokens **expire by design**. After expiry, protected APIs return `401`.

| Setting | Default | Meaning |
|---------|---------|---------|
| `JWT_TTL` | 43200 min (30 days) | Access token validity |
| `JWT_REFRESH_TTL` | 86400 min (60 days) | How long refresh is allowed after login |

**When token expires:**
1. Call `POST /auth/refresh` with the old token in `Authorization: Bearer {token}` — returns a new token (works even if token is expired, within refresh window)
2. If refresh also fails (`session_expired`), user must login again via OTP

**Mobile app should:**
- Store `access_token` and `expires_in` from login/refresh response
- Refresh token proactively before expiry, or on any `401` response
- Replace stored token with the new one from `/auth/refresh`

**Common causes of "invalid token" errors:**
- Token expired and app did not call `/auth/refresh`
- App still using an old token after refresh (old token is blacklisted)
- Server `JWT_SECRET` was changed (all tokens become invalid)
- Missing `Authorization: Bearer ` header

| Method | Endpoint | Auth required |
|--------|----------|---------------|
| POST | `/auth/refresh` | Bearer token (can be expired) |
| POST | `/auth/logout` | Valid bearer token |
| GET | `/auth/me` | Valid bearer token |

**Painter register body:** `{ "name", "phone", "experience_years", "cost_per_hour", "aadhar_number", "specialization?", "email?" }`

**Profile fields (painter):** `experience_years`, `experience_text`, `cost_per_hour`, `aadhar_number`, `specialization`, `is_verified`

**Profile fields (delivery):** `license_number`, `vehicle_number`

---

## Customer APIs (`/customer/*`)

### Products (also public)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | List all products (search, filter, paginate). Optional `featured=1` |
| GET | `/products/featured` | Featured products only (public, paginated) |
| GET | `/products/{id}` | Product detail |
| GET | `/categories` | List categories with product count |
| GET | `/categories/{id}/products` | Products by category |

### Delivery Addresses
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customer/addresses` | List saved delivery addresses |
| POST | `/customer/addresses` | Add a new address |
| GET | `/customer/addresses/{id}` | Get address by ID |
| PUT | `/customer/addresses/{id}` | Update address |
| DELETE | `/customer/addresses/{id}` | Delete address |
| POST | `/customer/addresses/{id}/default` | Set as default address |

**Add address body:**
```json
{
  "label": "Home",
  "recipient_name": "John Doe",
  "phone": "9876543210",
  "address_line1": "123 Main Street",
  "address_line2": "Apt 4B",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "is_default": true
}
```

The first address is automatically set as default. Only one address can be default at a time.

### Cart & Checkout

**Single-vendor cart:** A cart may only contain products from **one vendor** (or platform-owned products with no vendor). Adding a product from a different vendor returns **409** with `error: "different_vendor"`. Send `"replace": true` on add-to-cart to clear the cart and add the new product.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customer/cart` | Cart items + summary (includes `vendor` / `vendor_label`) |
| POST | `/customer/cart` | Add product `{ product_id, quantity, replace? }` |
| POST | `/customer/cart/{productId}/increment` | Increase qty by 1 |
| POST | `/customer/cart/{productId}/decrement` | Decrease qty by 1 (removes at 0) |
| PUT | `/customer/cart/{productId}` | Set quantity `{ quantity }` |
| DELETE | `/customer/cart/{productId}` | Remove item |
| GET | `/customer/cart/checkout` | Checkout preview with vendor, totals, and saved addresses |

**Add to cart (different vendor — without replace):**
```json
HTTP 409
{
  "message": "Your cart contains items from Sharma Paint Supplies. Clear the cart or send replace=true to add from Berger Paints.",
  "error": "different_vendor",
  "cart_vendor": { "id": 5, "name": "...", "business_name": "...", "display_name": "..." },
  "product_vendor": { "id": 6, "name": "...", "business_name": "...", "display_name": "..." }
}
```

**Add to cart (replace existing vendor):**
```json
{
  "product_id": 4,
  "quantity": 1,
  "replace": true
}
```

### Orders & Payment

Checkout creates **one order** for the entire cart (single vendor).

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/customer/orders` | Place order from cart (use `address_id` or `shipping_address`) |
| GET | `/customer/orders` | Order history (paginated) |
| GET | `/customer/orders/{id}` | Order detail by ID |
| POST | `/customer/orders/{id}/pay` | Initiate Razorpay payment |
| POST | `/customer/payments/verify` | Verify Razorpay payment |

### Painters & Bookings
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customer/painters` | List painters with portfolio |
| GET | `/customer/painters/{id}` | Painter profile + recent works |
| GET | `/customer/bookings?filter=` | My bookings |
| POST | `/customer/bookings` | Book painter (see below) |
| GET | `/customer/bookings/{id}` | Booking detail |

**Book painter:**
```json
{
  "painter_id": 3,
  "booking_date": "2026-06-25",
  "booking_time": "10:00",
  "address": "123 Main St",
  "notes": "Living room painting"
}
```
Multipart: `reference_images[]` (optional)

### Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/customer/profile` | Get profile |
| POST | `/customer/profile` | Update profile (multipart for avatar) |

---

## Painter APIs (`/painter/*`)

### Bookings
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/painter/bookings?filter=new` | New bookings (accept/reject) |
| GET | `/painter/bookings?filter=upcoming` | Upcoming accepted jobs |
| GET | `/painter/bookings?filter=completed` | Completed jobs |
| GET | `/painter/bookings/{id}` | Booking detail |
| POST | `/painter/bookings/{id}/accept` | Accept booking |
| POST | `/painter/bookings/{id}/reject` | Reject booking |
| POST | `/painter/bookings/{id}/start` | Mark in progress |
| POST | `/painter/bookings/{id}/before-images` | Upload **before** work photos (starts job if accepted) |
| POST | `/painter/bookings/{id}/after-images` | Upload **after** photos & complete booking |
| POST | `/painter/bookings/{id}/complete` | Upload before + after together & complete |

**Recommended flow:** `accept` → `start` (optional if using before-images) → `before-images` → `after-images`

**Upload before images (multipart):**
- `before_images[]` (required, 1–5 images)
- `work_notes` (optional)

**Upload after images & complete (multipart):**
- `after_images[]` (required, 1–5 images)
- `completion_notes` (optional)

**Complete work in one call (multipart):**
- `before_images[]` (optional if already uploaded)
- `after_images[]` (required)
- `completion_notes` (optional)

### Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/painter/profile` | Get profile + portfolio |
| POST | `/painter/profile` | Update profile, avatar, portfolio images |

---

## Delivery Agent APIs (`/delivery/*`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/delivery/orders?filter=pending` | Assigned orders with address |
| GET | `/delivery/orders?filter=completed` | Delivered orders |
| GET | `/delivery/orders/{id}` | Order detail with address |
| POST | `/delivery/orders/{id}/accept` | Accept delivery |
| PATCH | `/delivery/orders/{id}/status` | Update status (picked_up, out_for_delivery) |
| POST | `/delivery/orders/{id}/complete` | Upload proof photo & mark delivered |
| GET | `/delivery/profile` | Get profile |
| POST | `/delivery/profile` | Update profile |

**Complete delivery (multipart):**
- `delivery_proof` (required image)

---

## Sample Credentials

| Role | Phone | Login |
|------|-------|-------|
| Admin | — | Website `/admin` (admin@paintstore.com / password) |
| Customer | 9876543210 | `POST /auth/login` → verify-otp |
| Painter | 9876543211 | `POST /auth/staff/login` (role: painter) → verify-otp |
| Delivery | 9876543212 | `POST /auth/staff/login` (role: delivery_agent) → verify-otp |

Dev OTP (no API key): `123456`

---

## Order Flow
Cart → Checkout preview → Place order → Pay (Razorpay) → Verify → Track delivery

## Booking Flow
Browse painters → Book with date → Painter accepts → Work → Upload photos → Completed
