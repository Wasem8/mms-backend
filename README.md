# MMS Backend

**MMS (Mosque Management System)** is a modular Laravel backend designed to manage mosque operations and related community services in a structured, scalable, and maintainable way.

It centralizes mosque administration, education, donations, volunteer workflows, complaints, maintenance requests, invitations, notifications, and role-based dashboards within a single API-driven platform.

## Project Highlights

- Modular architecture for isolated feature development
- JWT-based authentication and role-based authorization
- Swagger API documentation
- Donation and campaign management
- Education workflows for students, teachers, attendance, and evaluations
- Volunteer opportunity, application, task, and certificate management
- Complaint and maintenance tracking
- Internal notifications and invitation handling

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel |
| Architecture | Laravel Modules (`nwidart/laravel-modules`) |
| Authentication | JWT (`tymon/jwt-auth`) |
| API Docs | Swagger (`darkaonline/l5-swagger`) |
| Payments | Stripe |
| Notifications | Firebase |
| Reporting | mPDF |
| Database | PostgreSQL |

## Core Modules

- `User`
- `Mosque`
- `Education`
- `Donation`
- `Volunteer`
- `Complaint`
- `MaintenanceRequest`
- `Invitation`
- `Common`
- `Dashboard`

## Key Functional Areas

### Mosque Management
- Mosques, facilities, needs, and spaces
- Nearby, featured, search, and city-based discovery

### Education Management
- Halaqat, students, teachers, attendance, excuses, and evaluations
- Role-aware access for parents, teachers, and supervisors

### Donations
- Cash and online donation flows
- Donation campaigns and mosque-scoped reporting
- Stripe webhook handling

### Volunteer Management
- Opportunity creation and application workflow
- Task assignment and completion
- Hour logging and certificate issuance

### Complaints and Maintenance
- Guest and member complaint submission
- Maintenance request tracking and administration
- Recent activity and statistics endpoints

### Invitations and Notifications
- User invitation flow
- Read/unread notification management

## Requirements

- PHP 8.4+
- Composer
- Node.js + npm
- PostgreSQL
- Required PHP extensions for your environment

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

## Local Development

```bash
php artisan serve
npm run dev
```


## Useful Composer Commands

| Command | Purpose |
|---|---|
| `composer run setup` | Install dependencies, prepare `.env`, run migrations, and build assets |
| `composer run dev` | Start the full local development stack |
| `composer test` | Run the application test suite |

## API Overview

The API is organized by domain. Common endpoints include:

### Authentication

- `POST /api/auth/login`
- `POST /api/auth/register-parent`
- `POST /api/auth/forgot-password`
- `POST /api/auth/reset-password`
- `GET /api/auth/me`
- `POST /api/auth/logout`
- `POST /api/auth/refresh`

### Mosques

- `GET /api/mosques`
- `GET /api/mosques/{mosque}`
- `GET /api/mosques/search`
- `GET /api/mosques/nearby`
- `GET /api/mosques/featured`

### Education

- `GET /api/education/students`
- `GET /api/education/halaqat`
- `POST /api/education/attendance`
- `POST /api/education/evaluations`

### Donations

- `GET /api/donations`
- `POST /api/donations/online`
- `GET /api/campaigns`
- `POST /api/stripe/webhook`

### Volunteer

- `GET /api/volunteer/opportunities`
- `POST /api/volunteer/opportunities/{opportunityId}/apply`
- `POST /api/volunteer/tasks/{taskId}/complete`

### Complaints and Maintenance

- `POST /api/complaints/guest`
- `GET /api/complaints/track/{complaintNumber}`
- `GET /api/maintenance/track/{maintenance_number}`

### Invitations and Notifications

- `POST /api/invitations/send`
- `GET /api/common/notifications`

## Roles and Permissions

The system uses role-based access control with roles such as:

- `super_admin`
- `mosque_manager`
- `teacher`
- `parent`
- `volunteer`
- `halaqa_supervisor`

## Documentation

Swagger documentation is available after the application is running and the API documentation is configured.

## Repository Structure

```text
app/
Modules/
routes/
database/
resources/
storage/
tests/
```

## Notes

- The project follows a modular structure to keep features independent and maintainable.
- Most business logic resides inside `Modules/`.
- Many endpoints are protected by `auth:api` and `role:*` middleware, depending on the operation.

## License

This project is licensed under the MIT License.
