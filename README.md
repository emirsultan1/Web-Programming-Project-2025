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


Milestone 5 – Frontend MVC Structure & Integration

Deadline: December 2025

Milestone 5 focused on improving the frontend architecture and ensuring a clean separation of concerns between the user interface, application logic, and backend communication. The main objective was to implement an MVC-like structure on the frontend, where API communication is strictly isolated from UI code.

✔ What Was Completed
Frontend MVC-like Architecture (Service Layer on Frontend)

To meet the milestone requirement, the frontend was refactored to follow a clear MVC-style separation:

Service Layer (API communication only):

assets/js/services/http.js

assets/js/services/AuthService.js

assets/js/services/AccountService.js

assets/js/services/OrderService.js

These files are the only place where HTTP requests (fetch/AJAX) are made.
They encapsulate all backend communication and return clean data to the rest of the application.

Controller Layer (logic & coordination):

assets/js/forms.js

This file:

Listens to form submissions (login, register, checkout, profile actions)

Performs basic logic and flow control

Calls Service methods

Contains no direct API calls

View / UI Layer:

assets/js/main.js

This file:

Handles UI behavior only (page switching, DOM updates, event listeners)

Does not contain any fetch or backend logic

Example Flow (Checkout)
User clicks checkout
→ main.js (UI event)
→ forms.js (controller logic)
→ OrderService.js (API request)
→ backend REST API


This structure ensures:

Clear separation of responsibilities

Easy maintenance and scalability

Full compliance with the “frontend MVC” requirement

✔ Core Functionality Verified

Login and registration work correctly

Session-based authentication using PHP sessions

Orders can be created and retrieved

User profile page loads account data and order history

Backend logic from Milestones 2–4 remains unchanged and stable

✔ Backend Validation

Backend validation already exists and was reused:

Required field checks

Email format validation

Password length validation

This ensures data integrity even if frontend validation is bypassed.

⚠ Client-side Validation (Partial)

Basic client-side validation could be added in forms.js (e.g., empty fields, password length, password confirmation).
This was considered optional and not critical for the core milestone objectives.

❌ Deployment (Intentionally Not Submitted)

Deployment to a production environment (Railway/Docker/MySQL cloud) was attempted but intentionally not included in the final submission.

Due to time constraints and the risk of destabilizing an otherwise working system close to the deadline, the decision was made to submit Milestone 5 without deployment.

The project runs correctly in a local development environment, and all architectural and functional requirements were met.

I am satisfied with 3/5 points for this milestone, prioritizing correctness, clarity, and maintainability over partial or unstable deployment.