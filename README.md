Clothing Brand Web Application

Course: Web Programming
Author: [Your Full Name]
Date: November 2025

This project is a web-based clothing store developed in multiple milestones. It uses PHP, MySQL, JavaScript, HTML, and CSS, following a REST-style structure that separates the backend and frontend. Each milestone adds new features and functionality to eventually build a complete web application.

Milestone 1 – Initial Setup and Frontend Development

Deadline: October 20, 2025

This first part of the project focused on building the foundation — setting up the structure of the application and preparing all the static frontend pages. The goal was to create the layout and user interface before connecting it to the backend.

I divided the project into two main folders: one for the frontend and one for the backend. The frontend was designed as a Single Page Application (SPA), allowing smooth navigation without reloading pages.

Completed tasks:

Created the project structure with separate frontend and backend directories.

Added folders for CSS, JavaScript, HTML views, and static assets.

Designed all required static pages (Login, Register, Dashboard, Products, Profile, etc.).

Used Bootstrap to make the site responsive and consistent in style.

Created a draft ERD with seven entities: Users, Categories, Products, Orders, Order_Items, Reviews, and Payments.

This milestone was mainly focused on design, structure, and navigation — there was no backend logic yet.

Milestone 2 – Backend Setup and CRUD Operations

Deadline: November 3, 2025

The second milestone focused on setting up the backend and connecting the system to a MySQL database. I implemented full CRUD (Create, Read, Update, Delete) functionality for all entities using PHP Data Access Objects (DAO) with PDO for database communication.

Technologies used:

PHP 8 with PDO for database connection

MySQL 8 for relational database management

REST-style backend (FlightPHP will be used in the next milestone)

PHP test scripts to verify that all DAOs work properly

Project structure:

backend/db/db.php → handles database connection

backend/dao/ → contains all DAO classes (BaseDAO, UsersDAO, CategoriesDAO, ProductsDAO, OrdersDAO, OrderItemsDAO, PaymentsDAO, ReviewsDAO)

backend/tests/run_all_smoke.php → runs automated tests for all CRUD operations

database/schema.sql → complete MySQL schema with all entities

frontend/ → static frontend files from Milestone 1

Database setup:
To create the database, I ran this command in the terminal:
mysql -u root -p < database/schema.sql

This creates a database called clothing_store with seven tables and a few sample records for testing.

Environment configuration:
Database credentials are stored locally in backend/.env (this file is ignored by Git for security).

DB_HOST=127.0.0.1  
DB_NAME=clothing_store  
DB_USER=root  
DB_PASS=your_password_here

Running the tests

To verify that everything works correctly:

Open the terminal and go to the tests folder:
cd backend/tests

Run the main test file:
php run_all_smoke.php

You should see an output similar to:

== Smoke test starting ==
✅ Users: create
✅ Products: CRUD verified
✅ Orders, OrderItems, Payments, Reviews: verified
== Done. Passed: 30, Failed: 0 ==


These tests confirm that the database connection works, all CRUD operations are functional, relationships between tables are correct, and unique constraints (like one review per user per product) are enforced.


Milestone 3 – REST API, Services & OpenAPI Docs

Deadline: November 16, 2025

Milestone 3 focused on converting the backend into a complete REST API using FlightPHP, adding full business logic, a small admin presentation layer, and generating official API documentation.

✔ What was completed

Full REST API for all 7 entities (Users, Categories, Products, Orders, Order Items, Reviews, Payments)
→ Implemented using FlightPHP routes.

Service Layer
→ Each entity has a dedicated Service class with validation and business logic (order totals, payment status updates, review constraints, etc.).

Presentation Layer (Admin)
→ Simple HTML pages rendered with FlightPHP (e.g., /admin/products) to satisfy the requirement.

OpenAPI Documentation (Swagger)
→ Added openapi.yaml and a Swagger UI page at /docs/ for visual API documentation.

✔ Deliverables met

CRUD endpoints for every entity

Modular backend (DAO + Service + Route)

Basic admin page

Full OpenAPI 3.0 documentation


Milestone 5 – Frontend Integration & Authentication

Deadline: December 2025

Milestone 5 focused on connecting the existing frontend (from Milestone 1) with the REST API developed in Milestones 2 and 3. The main objective was to make the application fully interactive by enabling real data exchange between the client and the server, while keeping the original project structure unchanged.

What was implemented
Frontend ↔ Backend Integration

Implemented a centralized JavaScript HTTP service using fetch to communicate with the backend API.

All API calls are handled through reusable service files, avoiding direct fetch calls inside UI code.

API base URL is configurable via environment, meta tag, or local development fallback.

Proper error handling and response normalization are implemented to ensure stable frontend behavior.

Authentication (Session-Based)

Implemented full authentication flow using server-side PHP sessions:

User registration (POST /api/register)

User login (POST /api/login)

User logout (POST /api/logout)

Sessions are created securely on login with session regeneration to prevent fixation attacks.

Frontend automatically sends session cookies using credentials: "include".

Authentication state is checked using a dedicated endpoint (GET /api/me).

Protected Routes

Added protected backend routes that require authentication using middleware.

Example protected endpoint (/api/secret) demonstrates access control enforcement.

Orders and user-related endpoints rely on session authentication and are inaccessible when logged out.

Frontend Services Architecture

Implemented dedicated frontend service files:

AuthService – handles login, registration, and logout

AccountService – retrieves the currently authenticated user

OrderService – retrieves user orders and places new orders

Implemented a controller-style file (forms.js) that:

Binds UI forms to backend services

Manages authentication state

Updates the UI dynamically based on login status

Handles SPA navigation events

User Experience

Login state persists across page reloads using server sessions.

Auth-related UI elements update dynamically (login/register vs. account/logout).

User account page displays profile information and order history when authenticated.

What was not completed

Deployment was not performed.

The application is fully functional in a local development environment but was not deployed to a production server.

This limitation was acknowledged as an accepted deduction for this milestone.

Summary

Milestone 5 successfully integrates the frontend with the backend REST API using a clean service-based JavaScript architecture and secure session-based authentication. The application now supports real user interaction, protected routes, and dynamic data loading. Deployment was not completed.