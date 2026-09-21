# Tsogolo Hub - System Architecture

Version: 1.0

---

# Overview

Tsogolo Hub is a modular Progressive Web Application (PWA) designed to improve access to essential services for youth and rural communities in Malawi.

The platform combines multiple services into a single digital ecosystem while keeping every module independent.

Primary goals:

- Education
- Agriculture
- Health
- Employment
- Governance
- Entrepreneurship
- Community Development

The architecture prioritizes:

- Simplicity
- Performance
- Maintainability
- Accessibility
- Offline capability
- Modular development

---

# High-Level Architecture

```
                    Internet
                        │
                HTTPS Requests
                        │
             Apache / Nginx Server
                        │
                 PHP Application
                        │
        ┌───────────────┼────────────────┐
        │               │                │
    Authentication   Business Logic   REST APIs
        │               │                │
        └───────────────┼────────────────┘
                        │
                  Service Layer
                        │
      ┌──────────┬────────────┬────────────┐
      │          │            │            │
 Agriculture  Education   Marketplace   Health
      │          │            │            │
      └──────────┼────────────┼────────────┘
                 │
           Shared Components
                 │
             MySQL Database
```

---

# Technology Stack

## Frontend

HTML5

CSS3

Vanilla JavaScript (ES6)

Progressive Web App

Service Worker

Manifest.json

---

## Backend

PHP 8+

MySQL 8+

REST API

JWT or Session Authentication

---

## Storage

MySQL

Local Browser Storage

IndexedDB (Offline)

File Storage

---

# Module Architecture

Every module is independent.

Each module contains its own:

```
Module

Controllers

Views

Services

Repositories

Routes

Assets

Documentation

Tests
```

Modules communicate only through shared services.

---

# Proposed Folder Structure

```
project/

│
├── app/
│   ├── Controllers/
│   ├── Services/
│   ├── Repositories/
│   ├── Models/
│   ├── Middleware/
│   ├── Helpers/
│   ├── Validation/
│   └── Core/
│
├── modules/
│   ├── agriculture/
│   ├── marketplace/
│   ├── education/
│   ├── health/
│   ├── jobs/
│   ├── governance/
│   ├── community/
│   ├── ai/
│   ├── notifications/
│   └── profile/
│
├── api/
│
├── assets/
│   ├── css/
│   ├── js/
│   ├── icons/
│   ├── images/
│   └── fonts/
│
├── lang/
│
├── uploads/
│
├── storage/
│
├── config/
│
├── database/
│
├── docs/
│
└── tests/
```

---

# Layered Architecture

```
Presentation Layer

↓

Controllers

↓

Services

↓

Repositories

↓

Database
```

Controllers never access the database directly.

Repositories contain database logic.

Services contain business rules.

Views contain presentation only.

---

# Shared Services

Shared services include:

Authentication

Authorization

Notifications

Localization

Logging

Search

AI Gateway

File Upload

Payments (Future)

Analytics

Caching

Weather

Maps

---

# Authentication

Authentication service handles:

Registration

Login

Logout

Password Reset

Session Management

Role Management

Permissions

---

# User Roles

Guest

Citizen

Farmer

Student

Teacher

Employer

Health Worker

Government Officer

Administrator

Super Administrator

Future roles can be added without modifying existing modules.

---

# Module Details

## Agriculture

Features

Crop Diagnosis

Farm Records

Income Tracking

Expense Tracking

Crop Calendar

Weather

Market Prices

Extension Resources

AI Assistant

Future

Satellite Monitoring

IoT Sensors

---

## Marketplace

Features

Products

Categories

Search

Seller Profiles

Buyer Profiles

Orders

Wishlist

Reviews

Future

Payments

Delivery Tracking

Escrow

---

## Education

Features

Courses

Videos

Articles

Downloads

Assignments

Progress Tracking

Certificates

Future

Live Classes

Discussion Forums

---

## Health

Features

Health Articles

Symptom Guidance

Mental Health

Clinic Directory

Emergency Numbers

AI Health Assistant

Future

Telemedicine

Appointments

Medical Records

---

## Jobs

Features

Job Listings

Internships

Volunteer Opportunities

Scholarships

Youth Programs

Saved Jobs

Applications

---

## Governance

Features

Development Projects

Community Meetings

Issue Reporting

Announcements

Public Documents

Feedback

Future

Voting

Petitions

Budget Tracking

---

## Community

Features

Events

Organizations

Youth Clubs

Forums

Local Groups

Volunteer Activities

---

# AI Architecture

```
User

↓

AI Gateway

↓

Provider Adapter

↓

AI Provider

↓

Response Validator

↓

Application
```

The AI Gateway abstracts provider-specific APIs.

Supported providers may include:

OpenAI

Google Gemini

Claude

OpenRouter

Local Models

The application should never depend directly on a single provider.

---

# Database Architecture

```
Users

↓

Profiles

↓

Modules

↓

Transactions

↓

Audit Logs
```

Every module owns its own tables.

Shared tables include:

users

roles

permissions

notifications

audit_logs

settings

languages

files

---

# API Design

RESTful endpoints

Example

```
GET

/api/products

GET

/api/jobs

POST

/api/login

POST

/api/orders

PUT

/api/profile

DELETE

/api/cart/item
```

JSON only.

---

# Internationalization

Languages stored inside

```
/lang

en/

ch/

future/
```

Every string must use language keys.

No hardcoded user-facing text.

---

# Offline Support

Service Worker

Offline cache

IndexedDB

Queued requests

Automatic synchronization

Offline pages

---

# Notifications

Central notification service

Channels

Email

SMS (Future)

Push Notifications

In-App Notifications

---

# Logging

Application Logs

Authentication Logs

AI Logs

Marketplace Logs

Security Logs

API Logs

Audit Logs

---

# Security

Input Validation

Prepared Statements

Password Hashing

Rate Limiting

CSRF Protection

XSS Prevention

CSP Headers

Role-Based Access Control

Audit Trails

---

# Performance

Lazy Loading

Image Compression

Code Splitting

Browser Caching

Database Indexes

API Pagination

Asset Minification

---

# Accessibility

WCAG AA

Keyboard Navigation

High Contrast

Screen Readers

Accessible Forms

Large Touch Targets

---

# Scalability

Designed for:

Multiple Districts

Multiple Languages

Thousands of Users

Additional Modules

Future Mobile App

Cloud Deployment

---

# Future Integrations

Weather APIs

SMS Gateway

Payment Gateway

Maps

Open Data APIs

Government APIs

Learning Platforms

AI Vision APIs

---

# Development Principles

Every feature must:

- Solve a real user problem.
- Be modular.
- Be documented.
- Be testable.
- Be reusable.
- Support localization.
- Work on mobile first.
- Degrade gracefully on slow networks.

---

# Guiding Principle

The platform is not a collection of unrelated pages.

It is a unified digital ecosystem where independent modules share common services, design language, authentication, and infrastructure while remaining loosely coupled and easy to maintain.