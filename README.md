# 📱 TechBridge

**A web-based e-commerce platform for affordable device rentals**

TechBridge is a full-stack web platform that lets low-income students and workers rent smartphones, laptops, and tablets instead of being priced out of ownership — turning "I can't afford a laptop" into "I can rent one this semester."

---

## 💡 Why This Exists

Access to a personal device is now a prerequisite for education and employment — not a luxury. Yet outright ownership remains financially out of reach for many students and entry-level workers. TechBridge closes that gap with a structured, transparent device rental system: renters get flexible, duration-based access; suppliers manage stock and fulfillment; and admins oversee the entire pipeline — all under one platform.

---

## ✨ Key Features

| For Renters | For Admins | For Suppliers |
|---|---|---|
| 🔍 Browse & search devices by spec, category, price | 🗂️ Full CRUD over devices, users, and orders | 📦 Manage inventory quantities in real time |
| 🛒 Persistent cart with live subtotal/tax/shipping calc | ✅ Review & approve/reject rental requests | 🚚 Track and update order fulfillment status |
| 📄 Rental requests scoped to duration limits (School/Tertiary/Working) | 📊 Dashboard with system-wide metrics | 📉 Low-inventory alerts on dashboard |
| 👤 Self-service profile & password management | 🧾 Generate PDF inventory reports | 🔐 Access scoped only to assigned orders |

---

## 🏗️ Architecture Highlights

TechBridge isn't just three dashboards bolted together — the design is intentional:

- **One `users` table, one role discriminator.** Renter, Admin, and Supplier all share a single authentication path — no duplicated login logic, no drift between roles.
- **Session-gated RBAC on every protected page.** A reusable guard clause checks session + role before any content renders, redirecting mismatches to *their own* dashboard rather than leaking a generic "access denied."
- **Request-group architecture.** Multi-device rental requests and procurement orders are grouped under a single ID, so approving or updating status affects the whole transaction atomically — not device-by-device.
- **Validated at every layer.** Prepared SQL statements, server-side input validation, and duration-limit enforcement mean invalid data never reaches the database in the first place.

Full system design — UML Sequence, Use Case, Activity, and Flowchart diagrams, plus the ERD — is documented in [`/docs`](./docs) for anyone who wants to see the *why* behind the code, not just the *what*.

---

## 🧪 Tested, Not Just Built

| Layer | Cases | Result |
|---|---|---|
| Unit | 8 | ✅ 100% Pass |
| Integration | 8 | ✅ 100% Pass |
| Functional | 8 | ✅ 100% Pass |

24/24 test cases passed across calculation logic, cross-module data flow, and full end-to-end user journeys — including negative-path testing (rejected malformed input, blocked unauthorized access).

---

## 🛠️ Tech Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Architecture:** Role-based, session-driven MVC-style structure

---

## 🚀 Getting Started

```bash
# Clone the repo
git clone https://github.com/<your-username>/TechBridge.git

# Import the database schema
mysql -u root -p techbridge < database/techbridge.sql

# Configure your database connection
# edit config/config.php with your credentials

# Serve locally (e.g. via XAMPP/WAMP)
# place project in htdocs and visit:
http://localhost/TechBridge/loginaccount.php
```

---

## 📸 Screenshots

> *(Add 2–3 of your best UI screenshots here — Login, Renter Dashboard, and Admin Dashboard make the strongest first impression.)*

---

## 📚 Project Context

TechBridge was developed as a Final Year Project (CP2), covering the full software development lifecycle — requirements analysis, system design, implementation, and testing — with an emphasis on accessible, role-appropriate design for underserved communities.

---

## 📄 License

This project is submitted for academic purposes. Contact the author for reuse permissions.
