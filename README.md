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

## 🚀 Getting Started (How to Run the Website)

**1. Download and Extract the Project**

Download the project as a ZIP file and extract the TechBridge folder into: C:\xampp\htdocs\

The final directory should be: C:\xampp\htdocs\TechBridge

**2. Launch XAMPP**

Open the XAMPP Control Panel and start:

Apache
MySQL

Make sure both services are running as shown below. (Url to download Xampp Control Panel: https://www.apachefriends.org/download.html)

<img width="457" height="162" alt="image" src="https://github.com/user-attachments/assets/30b6a2e8-fe09-467a-8e9a-3360c27fa836" />


**3. Set Up the Database**

Open phpMyAdmin by visiting: http://localhost/phpmyadmin

Create the required database, name the database as "**techbridge**", then import the provided "**techbridge.sql**" file within the repository to create the database entities and records.

**4. Launch the Application**

Open your web browser and visit: http://localhost/TechBridge/

**5. Create an Account or Log In**

New users can create an account by selecting the Register / Create Account option.

Existing users can log in using their registered account credentials.

**Requirements:**
XAMPP
Web browser
Apache
MySQL

---

## 📸 Screenshots

Snapshot of Device List Page (User's View)
<img width="1920" height="1020" alt="Screenshot 2026-08-03 195832" src="https://github.com/user-attachments/assets/9cfe3ec6-8ce3-4a0d-924c-238d886ef1ed" />


Snapshot of Checkout Page (User's View)
<img width="1303" height="712" alt="Screenshot 2026-08-03 200255" src="https://github.com/user-attachments/assets/4e20258b-0adb-40a2-b70f-13308fe7b77a" />


Snapshot of View Device Details Page (User's View)
<img width="826" height="791" alt="Screenshot 2026-08-03 200006" src="https://github.com/user-attachments/assets/f8e079d4-0715-4733-8df0-d5605018e7fe" />

---

## 📚 Project Context

TechBridge was developed as a Final Year Project (CP2), covering the full software development lifecycle — requirements analysis, system design, implementation, and testing — with an emphasis on accessible, role-appropriate design for underserved communities.

---

## 📄 License

This project is submitted for academic purposes. Contact the author for reuse permissions.
