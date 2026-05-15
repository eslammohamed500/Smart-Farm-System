# 🌱 GREEN — Smart Community Farm System

> A web-based platform for managing community farming activities — renting agricultural plots, borrowing tools, and organizing volunteer work.

Built as a **Software Engineering course project** covering the full SE lifecycle: requirements engineering, UML modeling, database design, and implementation using the MVC pattern.

---

## 📋 Project Overview

GREEN is a system that bridges the gap between **landowners, farmers, and the community**. It simplifies urban farming management through a structured, role-based web application.

**Key Actors:**
- 🧑‍🌾 **Farmer** — rents plots, borrows tools, participates in volunteer shifts and seed bank
- 🛠️ **Admin** — manages users, inventory, approvals, and monitors all farm operations

---

## 🗂️ Project Phases

### Phase 1 — Requirements & Analysis
| Artifact | Description |
|----------|-------------|
| Use Case Diagrams | 28 use cases across 5 modules |
| Activity Diagrams | Workflow modeling for key scenarios |
| Use Case Descriptions | Full textual description of each use case |
| System Architecture | High-level architecture overview |

**Modules covered:**
- Plot Management (Rent, View, Renew)
- Resource Management (Borrow Tool, Return Tool, Seed Bank)
- Community Services (Mentorship, Volunteer Shifts, Pest Reporting)
- Product Module
- Security (Authentication / Login)

### Phase 2 — Design & Modeling
| Artifact | Description |
|----------|-------------|
| SRS Document | Software Requirements Specification |
| Class Diagrams (v2, v3) | Full OOP class structure |
| Sequence Diagrams | Interaction flows for major use cases |
| Communication Diagrams | Object collaboration modeling |
| Package Diagrams | MVC package structure for classes & use cases |
| ERD | Entity Relationship Diagram (database schema) |
| Design Patterns | MVC, Observer, State |
| Complexity & Testing | Cyclomatic complexity & test cases |
| SQL Schema | Ready-to-use MySQL database |

---

## 🏗️ Architecture

The system follows the **MVC (Model-View-Controller)** pattern:

```
src/
├── controllers/    ← Business logic (FarmerController, AdminController, VolunteerController...)
├── models/         ← Data layer (DB queries & entities)
└── views/          ← UI layer (PHP pages rendered to the user)
```

---

## 🎨 Design Patterns

### 1. MVC Pattern
Separates UI, business logic, and data. The `VolunteerController` handles all shift logic before returning results to the view — keeping `volunteer.php` clean and maintainable.

### 2. Observer Pattern
Used for weather & status notifications. When a volunteer submits a weather report (the **Subject**), the admin dashboard (the **Observer**) is automatically notified — enabling faster emergency response.

### 3. State Pattern
Manages the shift lifecycle. The system checks shift status before rendering UI actions — preventing invalid operations like joining a closed shift or cancelling a shift that was never joined.

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP (MVC Architecture) |
| Database | MySQL |
| UML Modeling | Visual Paradigm |
| Diagrams | Use Case, Activity, Class, Sequence, Communication, Package |

---

## 📁 Repository Structure

```
GREEN-Smart-Farm-System/
│
├── README.md
│
├── docs/
│   ├── phase1/
│   │   ├── use-cases/          ← Use case diagram screenshots
│   │   ├── activity-diagrams/  ← Activity diagram screenshots
│   │   └── usecase-description.pptx
│   │
│   └── phase2/
│       ├── class-diagrams/     ← Class diagram v2 & v3
│       ├── communication-diagrams/
│       ├── package-diagrams/
│       ├── sequence-diagrams.pdf
│       ├── ERD.png
│       ├── SRS.pdf
│       ├── design-patterns.md
│       └── complexity-testing.pdf
│
├── database/
│   └── green.sql               ← Full MySQL schema
│
└── src/                        ← PHP source code
    ├── controllers/
    ├── models/
    └── views/
```

---

## 🚀 Getting Started

```bash
# 1. Clone the repo
git clone https://github.com/YOUR_USERNAME/GREEN-Smart-Farm-System.git

# 2. Import the database
mysql -u root -p < database/green.sql

# 3. Configure DB connection in src/models/DBController.php

# 4. Serve with any PHP server (XAMPP, WAMP, or built-in):
php -S localhost:8000 -t src/
```

---

## 👥 Team

| Name | Role |
|------|------|
| — | Add your name here |
| — | Add teammate names |

---

## 📄 License

This project was developed for academic purposes as part of a Software Engineering course.
