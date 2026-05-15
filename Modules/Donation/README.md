# Donation System Module

## Overview

The Donation System is a comprehensive module designed to manage donations for mosques within the MMS (Mosque Management System) backend. It supports both cash and in-kind donations, integrates with payment gateways, and includes campaign management functionality.

## Features

### Donation Types
- **Cash Donations**: Monetary donations with support for multiple payment methods
- **Kind Donations**: In-kind donations (items/goods) with descriptions

### Payment Methods
- **Cash**: Direct cash payments (marked as completed immediately)
- **Stripe**: Online payments through Stripe gateway (processed asynchronously)

### Campaign Management
- Create and manage donation campaigns for specific mosques
- Set target amounts and track collected funds
- Campaign status tracking (active, paused, completed, cancelled)
- Associate donations with specific campaigns

### Mosque Integration
- Donations are linked to specific mosques
- Support for mosque-specific needs (mosque_need_id)
- Mosque managers have special permissions

## Database Schema

### Donations Table
- `reference`: Unique donation reference (auto-generated: DON-XXXXXX)
- `mosque_id`: Foreign key to mosques table
- `mosque_need_id`: Optional link to specific mosque needs
- `campaign_id`: Optional link to donation campaigns
- `user_id`: Optional link to authenticated users
- `type`: Enum ('cash', 'kind')
- `amount`: Decimal amount for cash donations
- `item_description`: Description for kind donations
- `donor_name`: Name of the donor (defaults to 'فاعل خير' - anonymous donor)
- `status`: Enum ('pending', 'completed')
- `completed_at`: Timestamp when donation was completed
- `payment_method`: Payment method used ('cash' or 'stripe')

### Campaigns Table
- `mosque_id`: Foreign key to mosques table
- `title`: Campaign title
- `description`: Campaign description
- `target_amount`: Target donation amount
- `collected_amount`: Amount collected so far
- `status`: Enum ('active', 'paused', 'completed', 'cancelled')
- `start_date`: Campaign start date
- `end_date`: Optional end date
- `cover_image`: Optional campaign image

## API Endpoints

### Donations
- `GET /api/v1/donations` - List all donations
- `GET /api/v1/donations/{id}` - Get specific donation
- `POST /api/v1/donations` - Create new donation
- `PUT /api/v1/donations/{id}` - Update donation (authenticated users only)
- `DELETE /api/v1/donations/{id}` - Delete donation (authenticated users only)

### Campaigns
- `GET /api/campaign/` - List all campaigns
- `GET /api/campaign/{id}` - Get specific campaign
- `GET /api/campaign/mosque/{mosqueId}` - Get campaigns for specific mosque
- `POST /api/campaign/` - Create new campaign (mosque managers only)
- `PUT /api/campaign/{id}` - Update campaign (mosque managers only)
- `DELETE /api/campaign/{id}` - Delete campaign (mosque managers only)

## Business Logic

### Donation Creation Flow
1. **Validation**: Request validated based on donation type (cash/kind)
2. **Payment Processing**:
   - For cash donations: Process payment through selected method
   - For kind donations: No payment processing required
3. **Status Assignment**:
   - Cash + Stripe: Status = 'pending' (awaiting webhook confirmation)
   - Cash + Cash: Status = 'completed'
   - Kind: Status = 'pending' until an administrator confirms the donation
4. **Reference Generation**: Auto-generate unique reference if not provided

### Authorization
- Public endpoints: View donations and campaigns
- Authenticated users: Can update/delete their own donations
- Mosque managers: Can manage all donations and campaigns for their mosque

### Payment Strategies
- **Strategy Pattern**: Uses payment strategy pattern for different payment methods
- **CashPayment**: Handles cash payments (immediate completion)
- **StripePayment**: Handles Stripe payments (async processing)

## Key Components

### Controllers
- `DonationController`: Handles donation CRUD operations
- `CampaignController`: Handles campaign CRUD operations

### Services
- `DonationService`: Business logic for donations
- `CampaignService`: Business logic for campaigns

### Models
- `Donation`: Eloquent model for donations
- `Campaign`: Eloquent model for campaigns

### Strategies
- `PaymentStrategyInterface`: Interface for payment strategies
- `CashPayment`: Cash payment implementation
- `StripePayment`: Stripe payment implementation
- `PaymentProcessor`: Strategy context class

### Repositories
- `DonationRepositoryInterface`: Repository pattern for donations
- `CampaignRepositoryInterface`: Repository pattern for campaigns

## Validation Rules

### Cash Donations
- `mosque_id`: Required, must exist in mosques table
- `amount`: Required, numeric, min 5, max 999999
- `payment_method`: Optional, 'cash' or 'stripe'
- `success_url`, `cancel_url`: Required for Stripe payments
- `customer_email`: Optional email for Stripe

### Kind Donations
- `mosque_id`: Required
- `item_description`: Required, string, max 255
- `type`: Must be 'kind'

## Security Features
- Authentication required for modification operations
- Role-based access control (mosque_manager role)
- Input validation and sanitization
- Soft deletes for data integrity

## Integration Points
- **Mosque Module**: Links donations to mosques and mosque needs
- **User Module**: Associates donations with users
- **Payment Gateways**: Stripe integration for online payments
- **API Response**: Standardized API responses using ApiResponse class

## Future Enhancements
- Webhook handling for Stripe payment confirmations
- Recurring donation support
- Donation receipt generation
- Advanced reporting and analytics
- Multi-currency support</content>
<parameter name="filePath">c:\Users\owais\Desktop\mms-backend\Modules\Donation\README.md
