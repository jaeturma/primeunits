You are a senior Laravel + React (Inertia.js) engineer.

Your task is to implement a complete Role-Based Access Control (RBAC) module for a Laravel 13 + React (Inertia) web application called "PrimeUnits".

---

# 🎯 GOAL

Build a fully working RBAC system with:

- Roles: superadmin, admin, manager, coordinator, seller, buyer
- Permissions: dynamic and assignable to roles
- Users can have multiple roles
- Roles can have multiple permissions

The system must be scalable and production-ready.

---

# ⚙️ BACKEND REQUIREMENTS (Laravel)

## 1. Database Structure

Create migrations for:

- roles
- permissions
- role_user (pivot)
- permission_role (pivot)

Fields:

roles:
- id
- name (unique)
- label

permissions:
- id
- name (unique)
- label

---

## 2. Models

Create models:

- Role
- Permission

Relationships:

User:
- roles() → belongsToMany
- permissions() → derived from roles

Role:
- permissions() → belongsToMany

---

## 3. Helper Methods (User Model)

Implement:

- hasRole($role)
- hasPermission($permission)

---

## 4. Seeder

Create a seeder that:

- inserts all roles:
  superadmin, admin, manager, coordinator, seller, buyer

- inserts permissions:
  manage_users
  manage_roles
  verify_sellers
  approve_listings
  manage_listings
  view_reports

- assign all permissions to superadmin

---

## 5. Middleware

Create middleware:

CheckRole

- accepts multiple roles
- blocks unauthorized users (403)

---

## 6. Routes

Create protected admin routes:

/adm/*

Only accessible to:
- superadmin
- admin

Example:
- /adm/dashboard
- /adm/users

---

## ⚛️ FRONTEND REQUIREMENTS (React + Inertia)

## 1. Global Auth Data

Modify HandleInertiaRequests:

Return:
- user
- roles[]
- permissions[]

---

## 2. React Usage

Implement role-based UI:

Example:
- show admin menu if role includes 'admin'
- restrict pages if no permission

---

## 📁 STRUCTURE RULES

- Follow Laravel conventions
- Use clean architecture (no messy logic in controllers)
- Keep code readable and modular
- Do not use external packages (build from scratch)

---

## 🚫 CONSTRAINTS

- Do NOT use Spatie or third-party RBAC packages
- Do NOT hardcode permissions in controllers
- Must be reusable and extendable

---

## ✅ OUTPUT REQUIREMENTS

Generate:

1. All migrations
2. All models
3. Seeder
4. Middleware
5. Example controller (UserController index)
6. Routes setup
7. HandleInertiaRequests update
8. Example React page (User list or Dashboard)

All code must be complete and ready to run.

---

# ⚡ EXECUTION RULES

- Do NOT stop at explanation — generate working code
- Make reasonable assumptions if needed
- Ensure all parts connect properly
- Follow best practices for Laravel 13 + Inertia

---

# END