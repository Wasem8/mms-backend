# Volunteer Management Module

## Overview

The Volunteer Management module enables mosques to create volunteer opportunities, manage applications, assign tasks, track hours, and issue certificates. It supports a full workflow from opportunity creation to volunteer recognition.

## Features

### Opportunity Management
- Create, update, and close volunteer opportunities
- Set required volunteer count with automatic available slots calculation
- Auto-close opportunities when all slots are filled
- Status tracking (open / closed)
- Date-bound opportunities with start and end dates

### Application Workflow
- Volunteers can browse open opportunities and apply
- Mosque managers approve or reject applications
- Duplicate application prevention
- Closed opportunity validation

### Task Management
- Managers assign tasks to approved volunteers
- Volunteers mark tasks as completed
- Task status tracking (assigned / completed)

### Time Tracking & Evaluation
- Managers log hours worked by volunteers
- Manager evaluation notes per session
- Total hours calculation per volunteer per opportunity
- Volunteer can view their own logs

### Certificate Issuance
- Automatic certificate generation on sufficient hours
- Duplicate certificate prevention
- Volunteer can view their certificates
- Certificate URL storage for PDF downloads

## Database Schema

### `volunteer_opportunities`
| Column | Type | Description |
|--------|------|-------------|
| `id` | bigIncrements | Primary key |
| `mosque_id` | unsignedBigInteger | FK to mosques |
| `title` | string | Opportunity title |
| `description` | text | Opportunity description |
| `required_volunteers` | unsignedInteger | Number of volunteers needed |
| `start_date` | date | Opportunity start date |
| `end_date` | date | Opportunity end date |
| `status` | string (enum) | `open` / `closed` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |
| `deleted_at` | softDeletes | |

### `volunteer_applications`
| Column | Type | Description |
|--------|------|-------------|
| `id` | bigIncrements | Primary key |
| `opportunity_id` | foreignId | FK to volunteer_opportunities |
| `volunteer_id` | foreignId | FK to users |
| `status` | string (enum) | `pending` / `approved` / `rejected` |
| Unique | | `(opportunity_id, volunteer_id)` |

### `volunteer_tasks`
| Column | Type | Description |
|--------|------|-------------|
| `id` | bigIncrements | Primary key |
| `application_id` | foreignId | FK to volunteer_applications |
| `task_description` | text | Description of the task |
| `status` | string (enum) | `assigned` / `completed` |

### `volunteer_logs`
| Column | Type | Description |
|--------|------|-------------|
| `id` | bigIncrements | Primary key |
| `volunteer_id` | foreignId | FK to users |
| `opportunity_id` | foreignId | FK to volunteer_opportunities |
| `logged_hours` | decimal(5,2) | Hours worked |
| `manager_evaluation` | text | Manager's feedback |
| `notes` | text | Additional notes (nullable) |

### `volunteer_certificates`
| Column | Type | Description |
|--------|------|-------------|
| `id` | bigIncrements | Primary key |
| `volunteer_id` | foreignId | FK to users |
| `opportunity_id` | foreignId | FK to volunteer_opportunities |
| `certificate_url` | string | URL to generated certificate PDF |
| `issued_at` | timestamp | When certificate was issued |
| Unique | | `(volunteer_id, opportunity_id)` |

## API Endpoints

### Manager Routes (role: `mosque_manager`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/volunteer/manager/opportunities` | List all opportunities for manager's mosque |
| `POST` | `/api/v1/volunteer/opportunities` | Create new opportunity |
| `PUT` | `/api/v1/volunteer/opportunities/{id}` | Update opportunity |
| `POST` | `/api/v1/volunteer/opportunities/{id}/close` | Close opportunity |
| `GET` | `/api/v1/volunteer/opportunities/{opportunityId}/applications` | List applications for an opportunity |
| `POST` | `/api/v1/volunteer/applications/{applicationId}/approve` | Approve application |
| `POST` | `/api/v1/volunteer/applications/{applicationId}/reject` | Reject application |
| `POST` | `/api/v1/volunteer/tasks` | Assign task to volunteer |
| `POST` | `/api/v1/volunteer/logs` | Log hours and evaluation |
| `POST` | `/api/v1/volunteer/certificates/{volunteerId}/{opportunityId}` | Issue certificate |
| `GET` | `/api/v1/volunteer/hours/{volunteerId}/{opportunityId}` | Get total hours |

### Volunteer Routes (role: `volunteer`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/volunteer/opportunities` | Browse open opportunities |
| `GET` | `/api/v1/volunteer/opportunities/{id}` | View opportunity details |
| `POST` | `/api/v1/volunteer/opportunities/{opportunityId}/apply` | Apply for an opportunity |
| `GET` | `/api/v1/volunteer/my-applications` | View own applications |
| `GET` | `/api/v1/volunteer/applications/{applicationId}/tasks` | View tasks for an application |
| `POST` | `/api/v1/volunteer/tasks/{taskId}/complete` | Mark task as completed |
| `GET` | `/api/v1/volunteer/my-logs` | View own logged hours |
| `GET` | `/api/v1/volunteer/my-certificates` | View own certificates |

## Business Logic

### Available Slots
`available_slots` is a computed attribute on `VolunteerOpportunity`:
```
available_slots = required_volunteers - approved_applications_count
```
When `available_slots` reaches 0 after approving an application, the opportunity is automatically closed.

### Application Flow
1. Volunteer browses open opportunities (`status = open`, `end_date >= today`)
2. Volunteer submits an application (status: `pending`)
3. Manager approves or rejects the application
4. On approval, `available_slots` is recalculated; if 0, opportunity auto-closes
5. If rejected, the slot remains available

### Task Flow
1. Manager assigns tasks to an approved application
2. Volunteer views their assigned tasks
3. Volunteer marks tasks as completed

### Certificate Flow
1. Manager logs hours for a volunteer on an opportunity
2. Manager issues a certificate (requires `total_hours > 0`)
3. System prevents duplicate certificates for the same volunteer+opportunity
4. Certificate URL is stored for download

## Key Components

### Controllers
| Controller | Purpose |
|------------|---------|
| `VolunteerOpportunityController` | CRUD for opportunities, application management |
| `VolunteerTaskController` | Task assignment and completion |
| `VolunteerEvaluationController` | Logging hours, issuing certificates, viewing logs/certificates |

### Services
| Service | Purpose |
|---------|---------|
| `VolunteerOpportunityService` | Business logic for opportunities and applications |
| `VolunteerTaskService` | Business logic for task management |
| `VolunteerEvaluationService` | Business logic for logs, hours, certificates |

### Repositories
| Interface | Implementation |
|-----------|---------------|
| `VolunteerOpportunityRepositoryInterface` | `EloquentVolunteerOpportunityRepository` |
| `VolunteerApplicationRepositoryInterface` | `EloquentVolunteerApplicationRepository` |
| `VolunteerTaskRepositoryInterface` | `EloquentVolunteerTaskRepository` |
| `VolunteerEvaluationRepositoryInterface` | `EloquentVolunteerEvaluationRepository` |

### DTOs
| DTO | Used By |
|-----|---------|
| `CreateOpportunityDTO` | `CreateOpportunityRequest` → service |
| `UpdateOpportunityDTO` | `UpdateOpportunityRequest` → service |
| `AssignTaskDTO` | `AssignTaskRequest` → service |
| `LogHoursDTO` | `LogHoursRequest` → service |

### Enums
| Enum | Values |
|------|--------|
| `OpportunityStatus` | `open`, `closed` |
| `ApplicationStatus` | `pending`, `approved`, `rejected` |
| `TaskStatus` | `assigned`, `completed` |

### Events
| Event | Fired When | Listener |
|-------|------------|----------|
| `OpportunityCreated` | New opportunity created | — |
| `ApplicationStatusChanged` | Application approved/rejected | `NotifyVolunteerOfApplicationStatus` |
| `CertificateIssued` | Certificate issued | `NotifyVolunteerOfCertificate` |

## Validation Rules

### Create Opportunity
| Field | Rules |
|-------|-------|
| `title` | required, string, max:255 |
| `description` | required, string |
| `required_volunteers` | required, integer, min:1 |
| `start_date` | required, date, after_or_equal:today |
| `end_date` | required, date, after:start_date |

### Update Opportunity
| Field | Rules |
|-------|-------|
| `title` | sometimes, string, max:255 |
| `description` | sometimes, string |
| `required_volunteers` | sometimes, integer, min:1 |
| `start_date` | sometimes, date |
| `end_date` | sometimes, date, after:start_date |

### Assign Task
| Field | Rules |
|-------|-------|
| `application_id` | required, integer, exists:volunteer_applications,id |
| `task_description` | required, string |

### Log Hours
| Field | Rules |
|-------|-------|
| `volunteer_id` | required, integer, exists:users,id |
| `opportunity_id` | required, integer, exists:volunteer_opportunities,id |
| `logged_hours` | required, numeric, min:0.25, max:24 |
| `manager_evaluation` | required, string, max:1000 |
| `notes` | nullable, string, max:500 |

## Authorization

| Role | Permissions |
|------|-------------|
| `super_admin` | Full access via `manage_users` permission |
| `mosque_manager` | Create/update/close opportunities, approve/reject applications, assign tasks, log hours, issue certificates |
| `volunteer` | Browse open opportunities, apply, view/complete own tasks, view own logs and certificates |
| `guest` | No volunteer access |

## Integration Points
- **Mosque Module**: Opportunities are scoped to mosques via `mosque_id`
- **User Module**: Volunteers and managers are `User` models with roles
- **Notification System**: Event listeners ready for Firebase push notifications
- **PDF Generation**: Certificate generation stub ready for DomPDF/mPDF integration

## Future Enhancements
- Complete notification (push/email) integration for application status changes and certificate issuance
- PDF certificate generation with DomPDF or mPDF
- Volunteer dashboard with statistics
- Advanced filtering and search for opportunities
- Recurring volunteer opportunities
- Volunteer rating system
- Shift scheduling
