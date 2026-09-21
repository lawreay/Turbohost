# Tsogolo Hub - AI Development Agent Instructions

Version: 1.0
Project Type: Progressive Web Application (PWA)
Primary Country: Malawi
Primary Users: Youth, Farmers, Students, Entrepreneurs, Community Members

---

# Mission

Build a lightweight, accessible, multilingual digital platform that helps Malawian youth access:

- Education
- Agriculture
- Health
- Employment
- Government Services
- Marketplace
- Community

Every feature must solve a real problem.

If a feature does not improve a user's life, do not build it.

---

# Core Principles

Priority order:

1. Simplicity
2. Accessibility
3. Performance
4. Reliability
5. Maintainability
6. Security
7. Scalability

Never sacrifice simplicity for clever code.

---

# Product Philosophy

This is NOT social media.

This is NOT Facebook.

This is NOT another marketplace.

This is a digital service platform.

Every screen should help someone:

- Learn
- Earn
- Grow
- Stay healthy
- Find opportunities
- Participate in community

---

# Design Principles

Keep UI calm.

Minimal animations.

Fast loading.

Large touch targets.

Readable typography.

Works on:

- 3G
- Low-end Android phones
- Small screens
- Slow internet

Offline-first whenever practical.

---

# Target Devices

Primary:

Android phones

Secondary:

Desktop browsers

Tablets

Never design desktop-first.

Always mobile-first.

---

# Architecture

Frontend

- HTML5
- CSS3
- Vanilla JavaScript (ES6)

Backend

- PHP 8+
- MySQL

No unnecessary frameworks.

Avoid introducing dependencies unless there is significant value.

---

# Coding Standards

Always:

- Modular code
- Reusable components
- Small functions
- Clear naming
- Comments only where needed

Avoid:

Large functions

Nested callbacks

Magic numbers

Global variables

Duplicate code

---

# Folder Structure

/assets
/components
/css
/js
/images
/icons
/pages
/api
/uploads
/lang
/config

Keep structure clean.

---

# Styling Rules

Use CSS variables.

Never hardcode colors repeatedly.

Use responsive layouts.

Prefer Grid and Flexbox.

Avoid fixed widths.

---

# Accessibility

Every page must:

- Keyboard accessible
- Proper heading hierarchy
- Alt text
- ARIA where appropriate
- Sufficient color contrast

---

# Internationalization

Languages:

English

Chichewa

Never hardcode user-facing text.

Use language files.

Every new feature must support translation.

---

# Performance Budget

First load:

< 2 MB

Images:

WebP

Lazy loading

Compress assets.

Avoid unnecessary JavaScript.

---

# Marketplace Rules

Marketplace supports:

Agricultural produce

Farm inputs

Local products

Future:

Business directory

Do NOT implement payment processing until required.

Orders can initially be inquiry-based.

---

# Agriculture Module

Includes:

Crop diagnosis

Market prices

Weather

Farm records

Financial records

Crop calendar

AI recommendations

Disease detection must always include:

"This is not guaranteed.
Consult an agricultural extension officer."

---

# Health Module

Health assistant provides:

Education

General guidance

Clinic referrals

Emergency information

Never diagnose.

Never replace healthcare professionals.

Always encourage medical attention for emergencies.

---

# Education Module

Learning resources

Courses

Videos

Downloads

Certificates (future)

Quizzes (future)

Learning progress

---

# Jobs Module

Display:

NGO jobs

Government jobs

Private jobs

Internships

Youth opportunities

Scholarships

Never scrape websites illegally.

---

# Governance Module

Supports:

Community meetings

Development projects

Issue reporting

Feedback

Voting (future)

---

# AI Integration

AI assists users.

AI never makes final decisions.

All AI responses must include confidence where appropriate.

Support multiple providers.

Abstract AI providers behind a service layer.

Never tightly couple to one vendor.

---

# Security

Always:

Validate input

Escape output

Use prepared statements

CSRF protection

Rate limiting

Authentication

Authorization

Audit logs

Never trust client input.

---

# Privacy

Collect minimum personal information.

Do not store unnecessary sensitive data.

Users control their own data.

Follow privacy-by-design principles.

---

# Error Handling

Never expose:

Stack traces

Database errors

Internal paths

Log everything.

Show user-friendly messages.

---

# Notifications

Central notification service.

Support:

Email

SMS (future)

Push notifications (future)

In-app notifications

---

# Database Rules

Normalize appropriately.

Use foreign keys.

Soft delete where appropriate.

Created_at

Updated_at

Indexes for search fields.

Never duplicate data unnecessarily.

---

# Git Workflow

Small commits.

Meaningful commit messages.

One feature per branch.

Never mix refactoring with new features.

---

# Testing

Every feature should include:

Manual testing

Validation testing

Responsive testing

Accessibility testing

Regression testing

---

# Documentation

Every module must include:

Purpose

Architecture

Dependencies

API endpoints

Database schema

Known limitations

Future improvements

---

# Things the Agent MUST NOT Do

Do not invent features.

Do not rename folders without reason.

Do not change architecture without approval.

Do not introduce frameworks.

Do not remove existing functionality.

Do not break translations.

Do not duplicate logic.

Do not optimize prematurely.

---

# Before Writing Code

The agent should ask:

Does this solve a user problem?

Can this be simpler?

Can existing code be reused?

Will this still make sense in three years?

Does this increase maintenance?

---

# Definition of Done

A task is complete only when:

✓ Code works

✓ Mobile responsive

✓ Accessible

✓ Secure

✓ Documented

✓ Tested

✓ Translatable

✓ No console errors

✓ No duplicated code

✓ No broken links

✓ Performance acceptable

---

# Long-term Vision

Tsogolo Hub should become Malawi's trusted digital youth platform connecting education, agriculture, health, employment, entrepreneurship, governance, and community services through a single, simple, and inclusive experience.