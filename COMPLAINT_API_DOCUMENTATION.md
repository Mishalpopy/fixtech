# Fixtek Complaint Management API Documentation

## Overview
This API provides comprehensive complaint management functionality for customers, partners, and administrators. The system allows customers to raise complaints, admins to manage and assign them, and partners to update their assigned complaints.

## Base URL
```
http://localhost:8000/api
```

## Authentication
All protected endpoints require Bearer token authentication:
```
Authorization: Bearer {token}
```

## API Endpoints

### Customer APIs

#### Authentication
- **POST** `/v1/customer/register` - Register new customer
- **POST** `/v1/customer/login` - Customer login
- **POST** `/v1/customer/logout` - Customer logout
- **GET** `/v1/customer/profile` - Get customer profile

#### Complaints
- **GET** `/v1/customer/complaints` - Get all customer complaints
- **POST** `/v1/customer/complaints` - Create new complaint
- **GET** `/v1/customer/complaints/{id}` - Get complaint details
- **PUT** `/v1/customer/complaints/{id}` - Update complaint
- **DELETE** `/v1/customer/complaints/{id}` - Delete complaint
- **GET** `/v1/customer/complaints/{ticket}/attachments/{attachment}/download` - Download attachment

### Partner APIs

#### Authentication
- **POST** `/v1/partner/register` - Register new partner
- **POST** `/v1/partner/login` - Partner login
- **POST** `/v1/partner/logout` - Partner logout
- **GET** `/v1/partner/profile` - Get partner profile

#### Assigned Complaints
- **GET** `/v1/partner/complaints` - Get assigned complaints
- **GET** `/v1/partner/complaints/{id}` - Get complaint details
- **POST** `/v1/partner/complaints/{id}/update-status` - Update complaint status
- **GET** `/v1/partner/complaints/{ticket}/attachments/{attachment}/download` - Download attachment

### Admin APIs

#### Complaint Management
- **GET** `/v1/admin/complaints` - Get all complaints
- **POST** `/v1/admin/complaints` - Create new complaint
- **GET** `/v1/admin/complaints/{id}` - Get complaint details
- **PUT** `/v1/admin/complaints/{id}` - Update complaint
- **DELETE** `/v1/admin/complaints/{id}` - Delete complaint
- **POST** `/v1/admin/complaints/{id}/assign` - Assign complaint to partner
- **POST** `/v1/admin/complaints/{id}/update-status` - Update complaint status
- **GET** `/v1/admin/complaints/{ticket}/attachments/{attachment}/download` - Download attachment

#### Helper APIs
- **GET** `/v1/admin/customers` - Get all customers
- **GET** `/v1/admin/partners` - Get all partners

## Request/Response Examples

### Customer - Create Complaint

**Request:**
```http
POST /v1/customer/complaints
Authorization: Bearer {customer_token}
Content-Type: multipart/form-data

title: "Plumbing Issue in Kitchen"
description: "The kitchen sink is leaking and water is pooling under the cabinet."
category: "plumbing"
priority: "high"
attachments[]: [file1.jpg, file2.pdf]
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "ticket_number": "TKT-000001",
        "customer_id": 1,
        "title": "Plumbing Issue in Kitchen",
        "description": "The kitchen sink is leaking and water is pooling under the cabinet.",
        "category": "plumbing",
        "priority": "high",
        "status": "open",
        "created_at": "2025-10-22T22:30:00.000000Z",
        "attachments": [
            {
                "id": 1,
                "original_name": "file1.jpg",
                "file_type": "image/jpeg",
                "file_size": 1024000,
                "formatted_file_size": "1.00 MB"
            }
        ]
    },
    "message": "Complaint submitted successfully! Complaint Number: TKT-000001"
}
```

### Partner - Update Status

**Request:**
```http
POST /v1/partner/complaints/1/update-status
Authorization: Bearer {partner_token}
Content-Type: application/json

{
    "status": "in_progress",
    "partner_notes": "Started working on the issue. Found the leak source and will fix it today."
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "ticket_number": "TKT-000001",
        "status": "in_progress",
        "partner_notes": "Started working on the issue. Found the leak source and will fix it today.",
        "updated_at": "2025-10-22T22:35:00.000000Z"
    },
    "message": "Complaint status updated successfully"
}
```

### Admin - Assign Complaint

**Request:**
```http
POST /v1/admin/complaints/1/assign
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "assigned_partner_id": 1,
    "admin_notes": "Assigned to ABC Plumbing Services for immediate attention."
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "ticket_number": "TKT-000001",
        "status": "assigned",
        "assigned_partner_id": 1,
        "assigned_by": 1,
        "assigned_at": "2025-10-22T22:40:00.000000Z",
        "admin_notes": "Assigned to ABC Plumbing Services for immediate attention.",
        "assignedPartner": {
            "id": 1,
            "name": "ABC Plumbing Services",
            "email": "info@abcplumbing.com"
        }
    },
    "message": "Complaint assigned successfully"
}
```

## Query Parameters

### Filtering and Pagination
All list endpoints support the following query parameters:

- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 10)
- `status` - Filter by status (open, assigned, in_progress, resolved, closed, cancelled)
- `priority` - Filter by priority (low, medium, high, urgent)
- `category` - Filter by category (plumbing, electrical, hvac, appliance, general, other)
- `search` - Search in title, description, or ticket number

### Admin-specific filters:
- `customer_id` - Filter by customer
- `assigned_partner_id` - Filter by assigned partner

## Status Workflow

```
open → assigned → in_progress → resolved → closed
```

- **open** - Initial status when complaint is created
- **assigned** - Admin has assigned to a partner
- **in_progress** - Partner is working on the complaint
- **resolved** - Partner has resolved the complaint
- **closed** - Admin has closed the complaint
- **cancelled** - Complaint was cancelled

## File Upload

### Supported File Types
- Images: jpg, jpeg, png
- Documents: pdf, doc, docx
- Maximum file size: 10MB per file
- Maximum files per complaint: 5

### Upload Format
Use `multipart/form-data` with the field name `attachments[]` for multiple files.

## Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "You are not authorized to view this complaint."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "category": ["The selected category is invalid."]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Complaint not found."
}
```

## Rate Limiting
- 60 requests per minute per user
- File uploads are limited to 10MB per file

## Testing with Postman

1. Import the `Fixtek_Complaint_API_Collection.postman_collection.json` file
2. Import the `Fixtek_API_Environment.postman_environment.json` file
3. Set the `base_url` variable to your API endpoint
4. Run the authentication requests to get tokens
5. Use the tokens in subsequent requests

## Health Check
```http
GET /health
```

Returns API status and timestamp.

## Notes
- All timestamps are in UTC
- File downloads are secure and require authentication
- Partners can only see complaints assigned to them
- Customers can only see their own complaints
- Admins can see and manage all complaints
