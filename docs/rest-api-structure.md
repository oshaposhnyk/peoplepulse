# REST API Structure
## IT Employee Management System - API Specification

**Version:** 1.0  
**Date:** December 7, 2025  
**Status:** Final  
**API Version:** v1  
**Base URL:** `https://api.peoplepulse.com/api/v1`

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Common Patterns](#common-patterns)
4. [Employee Endpoints](#employee-endpoints)
5. [Team Endpoints](#team-endpoints)
6. [Equipment Endpoints](#equipment-endpoints)
7. [Leave Endpoints](#leave-endpoints)
8. [Authentication Endpoints](#authentication-endpoints)
9. [Error Handling](#error-handling)
10. [Rate Limiting](#rate-limiting)

---

## Overview

### API Principles

- **RESTful Design:** Resource-based URLs, standard HTTP methods
- **JSON API:** All requests and responses use JSON
- **Stateless:** Each request contains all necessary information
- **Versioned:** API version in URL (`/api/v1/`)
- **Secured:** Token-based authentication (Laravel Sanctum)
- **Paginated:** List endpoints return paginated results
- **Filterable:** Support for filtering, sorting, searching
- **Documented:** OpenAPI 3.0 specification available

### HTTP Methods

| Method | Usage |
|--------|-------|
| GET | Retrieve resource(s) |
| POST | Create new resource |
| PUT | Update entire resource |
| PATCH | Partially update resource |
| DELETE | Delete resource |

### Response Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created successfully |
| 204 | No Content - Request successful, no content to return |
| 400 | Bad Request - Invalid request data |
| 401 | Unauthorized - Authentication required or failed |
| 403 | Forbidden - Authenticated but not authorized |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation errors |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Server error |

### Content Type

All requests and responses use:
```
Content-Type: application/json
Accept: application/json
```

---

## Authentication

### Token-Based Authentication (Laravel Sanctum)

All API endpoints (except authentication endpoints) require authentication.

**Authentication Header:**
```
Authorization: Bearer {access_token}
```

### Obtaining Token

**POST** `/api/v1/auth/login`

Request:
```json
{
  "email": "john.doe@company.com",
  "password": "SecurePassword123!"
}
```

Response (200):
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "john.doe@company.com",
      "role": "Employee",
      "employee": {
        "id": "EMP-2025-0001",
        "name": "John Doe"
      }
    },
    "token": "1|abc123def456...",
    "tokenType": "Bearer",
    "expiresIn": 28800
  },
  "message": "Login successful"
}
```

### Token Expiration

- Default expiration: 8 hours
- Refresh token before expiry using `/api/v1/auth/refresh`
- Expired tokens return 401 Unauthorized

---

## Common Patterns

### Pagination

All list endpoints support pagination.

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 25, max: 100)

**Request:**
```
GET /api/v1/employees?page=2&per_page=50
```

**Response Structure:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "currentPage": 2,
    "perPage": 50,
    "total": 500,
    "lastPage": 10,
    "from": 51,
    "to": 100
  },
  "links": {
    "first": "/api/v1/employees?page=1",
    "last": "/api/v1/employees?page=10",
    "prev": "/api/v1/employees?page=1",
    "next": "/api/v1/employees?page=3"
  }
}
```

### Filtering

Filter resources using query parameters.

**Request:**
```
GET /api/v1/employees?status=Active&department=Engineering&location=San Francisco HQ
```

### Sorting

Sort resources using `sort` parameter.

**Request:**
```
GET /api/v1/employees?sort=-hire_date,last_name
```

- Prefix with `-` for descending order
- Comma-separated for multiple fields

### Searching

Full-text search using `search` parameter.

**Request:**
```
GET /api/v1/employees?search=john
```

### Including Related Resources

Include related resources using `include` parameter.

**Request:**
```
GET /api/v1/employees/EMP-2025-0001?include=teams,equipment,leave_balance
```

### Field Selection

Select specific fields using `fields` parameter.

**Request:**
```
GET /api/v1/employees?fields=id,name,email,position
```

### Standard Response Format

**Success Response:**
```json
{
  "success": true,
  "data": { /* resource or array */ },
  "message": "Optional success message",
  "meta": { /* pagination, etc. */ },
  "links": { /* pagination links */ }
}
```

**Error Response:**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."],
      "salary": ["The salary must be at least 30000."]
    }
  }
}
```

---

## Employee Endpoints

### List Employees

**GET** `/api/v1/employees`

**Description:** Get paginated list of employees.

**Authorization:** Admin: All employees, Employee: Own record only

**Query Parameters:**
- `page` (int) - Page number
- `per_page` (int) - Items per page
- `status` (string) - Filter by status: Active, Terminated, OnLeave
- `position` (string) - Filter by position
- `department` (string) - Filter by department
- `location` (string) - Filter by office location
- `search` (string) - Search by name, email, or employee ID
- `sort` (string) - Sort fields
- `include` (string) - Include relations: teams, equipment, leave_balance

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "EMP-2025-0001",
      "firstName": "John",
      "lastName": "Doe",
      "email": "john.doe@company.com",
      "phone": "+1-555-0100",
      "position": "Senior Developer",
      "department": "Engineering",
      "location": "San Francisco HQ",
      "status": "Active",
      "hireDate": "2025-01-15",
      "photoUrl": "https://cdn.example.com/photos/emp-001.jpg"
    }
  ],
  "meta": {
    "currentPage": 1,
    "perPage": 25,
    "total": 100,
    "lastPage": 4
  }
}
```

---

### Get Employee

**GET** `/api/v1/employees/{employeeId}`

**Description:** Get detailed information about a specific employee.

**Authorization:** Admin: Any employee, Employee: Own record only

**Path Parameters:**
- `employeeId` (string) - Employee ID (e.g., EMP-2025-0001)

**Query Parameters:**
- `include` (string) - Include relations: teams, equipment, leave_balance, position_history, location_history

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "firstName": "John",
    "lastName": "Doe",
    "middleName": null,
    "email": "john.doe@company.com",
    "phone": "+1-555-0100",
    "dateOfBirth": "1990-05-15",
    "address": {
      "street": "123 Main St",
      "city": "San Francisco",
      "state": "CA",
      "zipCode": "94102",
      "country": "USA"
    },
    "emergencyContact": {
      "name": "Jane Doe",
      "phone": "+1-555-0200",
      "relationship": "Spouse"
    },
    "position": "Senior Developer",
    "department": "Engineering",
    "employmentType": "Full-time",
    "status": "Active",
    "salary": {
      "amount": 95000.00,
      "currency": "USD",
      "frequency": "Annual"
    },
    "location": "San Francisco HQ",
    "remoteWork": {
      "enabled": true,
      "type": "Hybrid",
      "remoteDays": ["Monday", "Wednesday", "Friday"]
    },
    "hireDate": "2025-01-15",
    "startDate": "2025-01-15",
    "photoUrl": "https://cdn.example.com/photos/emp-001.jpg",
    "createdAt": "2025-01-15T09:00:00Z",
    "updatedAt": "2025-06-15T10:00:00Z"
  }
}
```

**Response (404):**
```json
{
  "success": false,
  "error": {
    "code": "NOT_FOUND",
    "message": "Employee not found"
  }
}
```

---

### Create Employee

**POST** `/api/v1/employees`

**Description:** Hire a new employee.

**Authorization:** Admin only

**Request Body:**
```json
{
  "firstName": "John",
  "lastName": "Doe",
  "middleName": null,
  "email": "john.doe@company.com",
  "phone": "+1-555-0100",
  "dateOfBirth": "1990-05-15",
  "address": {
    "street": "123 Main St",
    "city": "San Francisco",
    "state": "CA",
    "zipCode": "94102",
    "country": "USA"
  },
  "position": "Senior Developer",
  "department": "Engineering",
  "employmentType": "Full-time",
  "salary": {
    "amount": 95000.00,
    "currency": "USD",
    "frequency": "Annual"
  },
  "location": "San Francisco HQ",
  "hireDate": "2025-01-15",
  "startDate": "2025-01-15"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "firstName": "John",
    "lastName": "Doe",
    "email": "john.doe@company.com",
    "position": "Senior Developer",
    "status": "Active",
    "hireDate": "2025-01-15"
  },
  "message": "Employee hired successfully"
}
```

**Validation Rules:**
- `firstName`: required, string, max:100
- `lastName`: required, string, max:100
- `email`: required, email, unique:employees
- `phone`: required, string
- `dateOfBirth`: nullable, date, before:18 years ago
- `position`: required, string
- `department`: required, string
- `salary.amount`: required, numeric, min:30000
- `hireDate`: required, date, before_or_equal:today

---

### Update Employee

**PUT** `/api/v1/employees/{employeeId}`

**Description:** Update employee information (non-sensitive fields only).

**Authorization:** Admin: Any employee, Employee: Own record (limited fields)

**Request Body:**
```json
{
  "phone": "+1-555-0101",
  "address": {
    "street": "456 Oak Ave",
    "city": "San Francisco",
    "state": "CA",
    "zipCode": "94103",
    "country": "USA"
  },
  "emergencyContact": {
    "name": "Jane Doe",
    "phone": "+1-555-0201",
    "relationship": "Spouse"
  }
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "phone": "+1-555-0101",
    "updatedAt": "2025-06-15T10:00:00Z"
  },
  "message": "Employee updated successfully"
}
```

---

### Change Position

**POST** `/api/v1/employees/{employeeId}/position`

**Description:** Change employee's position and/or salary.

**Authorization:** Admin only

**Request Body:**
```json
{
  "newPosition": "Lead Developer",
  "newDepartment": "Engineering",
  "newSalary": {
    "amount": 110000.00,
    "currency": "USD"
  },
  "effectiveDate": "2025-06-01",
  "reason": "Annual promotion"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "position": "Lead Developer",
    "salary": {
      "amount": 110000.00,
      "currency": "USD"
    },
    "effectiveDate": "2025-06-01"
  },
  "message": "Position changed successfully"
}
```

---

### Change Location

**POST** `/api/v1/employees/{employeeId}/location`

**Description:** Change employee's office location.

**Authorization:** Admin only

**Request Body:**
```json
{
  "newLocation": "New York Office",
  "effectiveDate": "2025-07-01",
  "reason": "Employee relocation",
  "isTemporary": false
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "location": "New York Office",
    "effectiveDate": "2025-07-01"
  },
  "message": "Location changed successfully"
}
```

---

### Enable Remote Work

**POST** `/api/v1/employees/{employeeId}/remote-work/enable`

**Description:** Enable remote work for employee.

**Authorization:** Admin only

**Request Body:**
```json
{
  "type": "Hybrid",
  "remoteDays": ["Monday", "Wednesday", "Friday"],
  "startDate": "2025-03-01"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "remoteWork": {
      "enabled": true,
      "type": "Hybrid",
      "remoteDays": ["Monday", "Wednesday", "Friday"]
    }
  },
  "message": "Remote work enabled successfully"
}
```

---

### Disable Remote Work

**POST** `/api/v1/employees/{employeeId}/remote-work/disable`

**Description:** Disable remote work for employee.

**Authorization:** Admin only

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "remoteWork": {
      "enabled": false
    }
  },
  "message": "Remote work disabled successfully"
}
```

---

### Terminate Employee

**POST** `/api/v1/employees/{employeeId}/terminate`

**Description:** Terminate employee's employment.

**Authorization:** Admin only

**Request Body:**
```json
{
  "terminationDate": "2025-12-31",
  "lastWorkingDay": "2025-12-31",
  "terminationType": "Resignation",
  "reason": "Personal reasons",
  "exitInterviewDate": "2025-12-28T14:00:00Z"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "EMP-2025-0001",
    "status": "Terminated",
    "terminationDate": "2025-12-31",
    "lastWorkingDay": "2025-12-31"
  },
  "message": "Employee terminated successfully"
}
```

---

### Get Employee History

**GET** `/api/v1/employees/{employeeId}/history`

**Description:** Get employee's position and location history.

**Authorization:** Admin only

**Query Parameters:**
- `type` (string) - Filter by type: position, location, all (default: all)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "positionHistory": [
      {
        "previousPosition": "Developer",
        "newPosition": "Senior Developer",
        "previousSalary": 80000.00,
        "newSalary": 95000.00,
        "effectiveDate": "2025-06-01",
        "reason": "Annual promotion"
      }
    ],
    "locationHistory": [
      {
        "previousLocation": "Austin Office",
        "newLocation": "San Francisco HQ",
        "effectiveDate": "2025-01-15",
        "reason": "Initial assignment"
      }
    ]
  }
}
```

---

## Team Endpoints

### List Teams

**GET** `/api/v1/teams`

**Description:** Get paginated list of teams.

**Authorization:** All authenticated users

**Query Parameters:**
- `page`, `per_page` - Pagination
- `type` (string) - Filter by team type
- `department` (string) - Filter by department
- `search` (string) - Search by name
- `include` (string) - Include: members, parent

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "TEAM-0001",
      "name": "Backend Engineering",
      "type": "Development",
      "department": "Engineering",
      "memberCount": 8,
      "maxSize": 10,
      "teamLead": {
        "id": "EMP-2025-0050",
        "name": "Jane Smith"
      }
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### Get Team

**GET** `/api/v1/teams/{teamId}`

**Description:** Get detailed team information.

**Authorization:** All authenticated users

**Query Parameters:**
- `include` (string) - Include: members, parent, children

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "TEAM-0001",
    "name": "Backend Engineering",
    "description": "Core backend development team",
    "type": "Development",
    "department": "Engineering",
    "maxSize": 10,
    "memberCount": 8,
    "teamLead": {
      "id": "EMP-2025-0050",
      "name": "Jane Smith",
      "position": "Engineering Manager"
    },
    "parentTeam": null,
    "isActive": true,
    "createdAt": "2025-01-01T09:00:00Z"
  }
}
```

---

### Create Team

**POST** `/api/v1/teams`

**Description:** Create a new team.

**Authorization:** Admin only

**Request Body:**
```json
{
  "name": "Backend Engineering",
  "description": "Core backend development team",
  "type": "Development",
  "department": "Engineering",
  "parentTeamId": null,
  "maxSize": 10
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "TEAM-0001",
    "name": "Backend Engineering",
    "type": "Development",
    "memberCount": 0
  },
  "message": "Team created successfully"
}
```

**Validation Rules:**
- `name`: required, string, max:100, unique
- `type`: required, string
- `department`: required, string
- `maxSize`: nullable, integer, min:1

---

### Update Team

**PUT** `/api/v1/teams/{teamId}`

**Description:** Update team information.

**Authorization:** Admin only

**Request Body:**
```json
{
  "name": "Platform Engineering",
  "description": "Updated description",
  "maxSize": 12
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "TEAM-0001",
    "name": "Platform Engineering",
    "maxSize": 12
  },
  "message": "Team updated successfully"
}
```

---

### Assign Member to Team

**POST** `/api/v1/teams/{teamId}/members`

**Description:** Assign an employee to a team.

**Authorization:** Admin only

**Request Body:**
```json
{
  "employeeId": "EMP-2025-0001",
  "role": "Member",
  "allocationPercentage": 100
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "teamId": "TEAM-0001",
    "employeeId": "EMP-2025-0001",
    "role": "Member",
    "allocationPercentage": 100,
    "assignedAt": "2025-01-15T09:00:00Z"
  },
  "message": "Member assigned to team successfully"
}
```

**Validation Rules:**
- `employeeId`: required, exists:employees
- `role`: required, in:Member,TeamLead,TechLead
- `allocationPercentage`: required, integer, between:1,100

---

### Remove Member from Team

**DELETE** `/api/v1/teams/{teamId}/members/{employeeId}`

**Description:** Remove an employee from a team.

**Authorization:** Admin only

**Response (200):**
```json
{
  "success": true,
  "message": "Member removed from team successfully"
}
```

---

### Transfer Employee Between Teams

**POST** `/api/v1/teams/{teamId}/transfer`

**Description:** Transfer employee from one team to another.

**Authorization:** Admin only

**Request Body:**
```json
{
  "employeeId": "EMP-2025-0001",
  "targetTeamId": "TEAM-0002",
  "newRole": "Member",
  "newAllocation": 100,
  "transferDate": "2025-06-01",
  "reason": "Skill realignment"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "employeeId": "EMP-2025-0001",
    "sourceTeamId": "TEAM-0001",
    "targetTeamId": "TEAM-0002",
    "transferDate": "2025-06-01"
  },
  "message": "Employee transferred successfully"
}
```

---

### Change Team Lead

**POST** `/api/v1/teams/{teamId}/lead`

**Description:** Change team lead.

**Authorization:** Admin only

**Request Body:**
```json
{
  "employeeId": "EMP-2025-0051",
  "effectiveDate": "2025-06-01",
  "reason": "Leadership transition"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "teamId": "TEAM-0001",
    "newLeadId": "EMP-2025-0051",
    "effectiveDate": "2025-06-01"
  },
  "message": "Team lead changed successfully"
}
```

---

### Get Team Members

**GET** `/api/v1/teams/{teamId}/members`

**Description:** Get all team members.

**Authorization:** All authenticated users

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "employeeId": "EMP-2025-0001",
      "firstName": "John",
      "lastName": "Doe",
      "position": "Senior Developer",
      "role": "Member",
      "allocationPercentage": 100,
      "assignedAt": "2025-01-15"
    }
  ]
}
```

---

### Disband Team

**DELETE** `/api/v1/teams/{teamId}`

**Description:** Disband a team (must have no members).

**Authorization:** Admin only

**Response (200):**
```json
{
  "success": true,
  "message": "Team disbanded successfully"
}
```

---

## Equipment Endpoints

### List Equipment

**GET** `/api/v1/equipment`

**Description:** Get paginated list of equipment.

**Authorization:** Admin: All equipment, Employee: Assigned equipment only

**Query Parameters:**
- `page`, `per_page` - Pagination
- `type` (string) - Filter by equipment type
- `status` (string) - Filter by status: Available, Assigned, InMaintenance, Decommissioned
- `assignedTo` (string) - Filter by assigned employee ID
- `search` (string) - Search by asset tag, serial number, model
- `include` (string) - Include: currentAssignment, assignmentHistory

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "assetTag": "ASSET-2025-1234",
      "type": "Laptop",
      "brand": "Apple",
      "model": "MacBook Pro 16-inch",
      "serialNumber": "C02XYZ123456",
      "status": "Assigned",
      "condition": "Good",
      "currentAssignee": {
        "id": "EMP-2025-0001",
        "name": "John Doe"
      },
      "assignedAt": "2025-01-15"
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### Get Equipment

**GET** `/api/v1/equipment/{equipmentId}`

**Description:** Get detailed equipment information.

**Authorization:** Admin: Any equipment, Employee: Own assigned equipment only

**Query Parameters:**
- `include` (string) - Include: currentAssignment, assignmentHistory, maintenanceHistory

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "assetTag": "ASSET-2025-1234",
    "serialNumber": "C02XYZ123456",
    "type": "Laptop",
    "brand": "Apple",
    "model": "MacBook Pro 16-inch",
    "specifications": {
      "cpu": "M3 Pro",
      "ram": "32GB",
      "storage": "1TB SSD"
    },
    "purchaseDate": "2025-01-10",
    "purchasePrice": 2999.00,
    "warrantyExpiryDate": "2028-01-10",
    "status": "Assigned",
    "condition": "Good",
    "currentAssignee": {
      "id": "EMP-2025-0001",
      "name": "John Doe"
    },
    "assignedAt": "2025-01-15"
  }
}
```

---

### Add Equipment

**POST** `/api/v1/equipment`

**Description:** Add new equipment to inventory.

**Authorization:** Admin only

**Request Body:**
```json
{
  "type": "Laptop",
  "brand": "Apple",
  "model": "MacBook Pro 16-inch",
  "serialNumber": "C02XYZ123456",
  "specifications": {
    "cpu": "M3 Pro",
    "ram": "32GB",
    "storage": "1TB SSD"
  },
  "purchaseDate": "2025-01-10",
  "purchasePrice": 2999.00,
  "purchaseCurrency": "USD",
  "warrantyExpiryDate": "2028-01-10",
  "condition": "New"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "assetTag": "ASSET-2025-1234",
    "serialNumber": "C02XYZ123456",
    "type": "Laptop",
    "status": "Available"
  },
  "message": "Equipment added successfully"
}
```

**Validation Rules:**
- `type`: required, string
- `brand`: required, string
- `model`: required, string
- `serialNumber`: required, unique
- `purchaseDate`: required, date
- `purchasePrice`: required, numeric, min:0

---

### Issue Equipment

**POST** `/api/v1/equipment/{equipmentId}/issue`

**Description:** Issue equipment to an employee.

**Authorization:** Admin only

**Request Body:**
```json
{
  "employeeId": "EMP-2025-0001",
  "issueDate": "2025-01-15",
  "condition": "New",
  "accessories": ["Charger", "USB-C Cable", "Laptop Bag"]
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "equipmentId": "uuid",
    "assetTag": "ASSET-2025-1234",
    "employeeId": "EMP-2025-0001",
    "issueDate": "2025-01-15",
    "status": "Assigned"
  },
  "message": "Equipment issued successfully"
}
```

---

### Return Equipment

**POST** `/api/v1/equipment/{equipmentId}/return`

**Description:** Process equipment return from employee.

**Authorization:** Admin only

**Request Body:**
```json
{
  "returnDate": "2025-12-31",
  "condition": "Good",
  "accessoriesReturned": ["Charger", "USB-C Cable", "Laptop Bag"],
  "damageReport": null,
  "photos": []
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "equipmentId": "uuid",
    "assetTag": "ASSET-2025-1234",
    "returnDate": "2025-12-31",
    "condition": "Good",
    "status": "Available"
  },
  "message": "Equipment returned successfully"
}
```

---

### Transfer Equipment

**POST** `/api/v1/equipment/{equipmentId}/transfer`

**Description:** Transfer equipment from one employee to another.

**Authorization:** Admin only

**Request Body:**
```json
{
  "toEmployeeId": "EMP-2025-0002",
  "transferDate": "2025-06-15",
  "reason": "Employee terminated, equipment reassigned",
  "condition": "Good",
  "dataWiped": true
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "equipmentId": "uuid",
    "fromEmployeeId": "EMP-2025-0001",
    "toEmployeeId": "EMP-2025-0002",
    "transferDate": "2025-06-15"
  },
  "message": "Equipment transferred successfully"
}
```

---

### Schedule Maintenance

**POST** `/api/v1/equipment/{equipmentId}/maintenance`

**Description:** Schedule equipment maintenance.

**Authorization:** Admin only

**Request Body:**
```json
{
  "maintenanceType": "Repair",
  "description": "Screen replacement",
  "scheduledDate": "2025-03-15",
  "expectedDuration": 3,
  "serviceProvider": "External Vendor",
  "estimatedCost": 250.00
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "maintenanceId": "uuid",
    "equipmentId": "uuid",
    "maintenanceType": "Repair",
    "scheduledDate": "2025-03-15",
    "status": "Scheduled"
  },
  "message": "Maintenance scheduled successfully"
}
```

---

### Get Equipment History

**GET** `/api/v1/equipment/{equipmentId}/history`

**Description:** Get complete equipment history (assignments, maintenance, etc.).

**Authorization:** Admin only

**Response (200):**
```json
{
  "success": true,
  "data": {
    "assignments": [
      {
        "employeeId": "EMP-2025-0001",
        "employeeName": "John Doe",
        "assignedAt": "2025-01-15",
        "returnedAt": null,
        "condition": "New"
      }
    ],
    "maintenance": [
      {
        "type": "Cleaning",
        "date": "2025-02-01",
        "cost": 50.00,
        "status": "Completed"
      }
    ],
    "transfers": []
  }
}
```

---

### Decommission Equipment

**DELETE** `/api/v1/equipment/{equipmentId}`

**Description:** Decommission equipment (remove from active inventory).

**Authorization:** Admin only

**Request Body:**
```json
{
  "reason": "End of Life",
  "disposalMethod": "Recycle",
  "dataWiped": true
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Equipment decommissioned successfully"
}
```

---

## Leave Endpoints

### List Leave Requests

**GET** `/api/v1/leaves`

**Description:** Get paginated list of leave requests.

**Authorization:** Admin: All requests, Employee: Own requests only

**Query Parameters:**
- `page`, `per_page` - Pagination
- `employeeId` (string) - Filter by employee
- `type` (string) - Filter by leave type
- `status` (string) - Filter by status: Pending, Approved, Rejected, Cancelled
- `startDate` (date) - Filter by start date (from)
- `endDate` (date) - Filter by end date (to)
- `include` (string) - Include: employee

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "LEAVE-2025-0001",
      "employeeId": "EMP-2025-0001",
      "employeeName": "John Doe",
      "leaveType": "Vacation",
      "startDate": "2025-07-01",
      "endDate": "2025-07-14",
      "totalDays": 10,
      "status": "Pending",
      "requestedAt": "2025-06-01T10:00:00Z"
    }
  ],
  "meta": { /* pagination */ }
}
```

---

### Get Leave Request

**GET** `/api/v1/leaves/{leaveId}`

**Description:** Get detailed leave request information.

**Authorization:** Admin: Any request, Employee: Own request only

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "LEAVE-2025-0001",
    "employeeId": "EMP-2025-0001",
    "employeeName": "John Doe",
    "leaveType": "Vacation",
    "startDate": "2025-07-01",
    "endDate": "2025-07-14",
    "totalDays": 10,
    "workingDays": 10,
    "reason": "Family vacation",
    "contactDuringLeave": "+1-555-0100",
    "backupPerson": {
      "id": "EMP-2025-0002",
      "name": "Jane Smith"
    },
    "status": "Pending",
    "currentBalance": 24,
    "balanceAfterRequest": 14,
    "requestedAt": "2025-06-01T10:00:00Z"
  }
}
```

---

### Request Leave

**POST** `/api/v1/leaves`

**Description:** Submit a leave request.

**Authorization:** All authenticated users (for own leave)

**Request Body:**
```json
{
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "reason": "Family vacation",
  "contactDuringLeave": "+1-555-0100",
  "backupPersonId": "EMP-2025-0002"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": "LEAVE-2025-0001",
    "leaveType": "Vacation",
    "startDate": "2025-07-01",
    "endDate": "2025-07-14",
    "totalDays": 10,
    "status": "Pending"
  },
  "message": "Leave request submitted successfully"
}
```

**Validation Rules:**
- `leaveType`: required, in:Vacation,Sick,Unpaid,Bereavement,Parental,Personal
- `startDate`: required, date, after_or_equal:today (except sick)
- `endDate`: required, date, after:startDate
- `reason`: nullable, string

**Business Validation:**
- Sufficient leave balance (except sick/bereavement)
- No overlapping approved leave
- Minimum notice period met
- Team capacity constraints met

---

### Approve Leave

**POST** `/api/v1/leaves/{leaveId}/approve`

**Description:** Approve a leave request.

**Authorization:** Admin only

**Request Body:**
```json
{
  "approvalNotes": "Approved, enjoy your vacation!"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "LEAVE-2025-0001",
    "status": "Approved",
    "approvedBy": "EMP-2025-0100",
    "approvedAt": "2025-06-02T09:00:00Z"
  },
  "message": "Leave request approved"
}
```

---

### Reject Leave

**POST** `/api/v1/leaves/{leaveId}/reject`

**Description:** Reject a leave request.

**Authorization:** Admin only

**Request Body:**
```json
{
  "rejectionReason": "Insufficient coverage during that period",
  "suggestedAlternative": "Consider August dates"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "LEAVE-2025-0001",
    "status": "Rejected",
    "rejectedBy": "EMP-2025-0100",
    "rejectedAt": "2025-06-02T09:00:00Z"
  },
  "message": "Leave request rejected"
}
```

**Validation Rules:**
- `rejectionReason`: required, string

---

### Cancel Leave

**POST** `/api/v1/leaves/{leaveId}/cancel`

**Description:** Cancel a leave request.

**Authorization:** Employee: Own request, Admin: Any request

**Request Body:**
```json
{
  "cancellationReason": "Change of plans"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "LEAVE-2025-0001",
    "status": "Cancelled",
    "balanceRestored": 10
  },
  "message": "Leave request cancelled"
}
```

**Business Rules:**
- Cannot cancel completed leave
- Cannot cancel within 24 hours of start date

---

### Get Leave Balance

**GET** `/api/v1/employees/{employeeId}/leave-balance`

**Description:** Get employee's leave balance.

**Authorization:** Admin: Any employee, Employee: Own balance only

**Query Parameters:**
- `year` (int) - Year to get balance for (default: current year)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "year": 2025,
    "balances": [
      {
        "leaveType": "Vacation",
        "opening": 0,
        "accrued": 24,
        "used": 10,
        "pending": 0,
        "available": 14,
        "carriedOver": 0
      },
      {
        "leaveType": "Sick",
        "opening": 0,
        "accrued": 12,
        "used": 2,
        "pending": 0,
        "available": 10,
        "carriedOver": 0
      }
    ],
    "nextAccrualDate": "2025-07-01"
  }
}
```

---

### Get Leave Calendar

**GET** `/api/v1/leaves/calendar`

**Description:** Get team leave calendar.

**Authorization:** All authenticated users

**Query Parameters:**
- `teamId` (string) - Filter by team
- `startDate` (date) - Calendar start date
- `endDate` (date) - Calendar end date
- `leaveType` (string) - Filter by leave type

**Response (200):**
```json
{
  "success": true,
  "data": {
    "startDate": "2025-07-01",
    "endDate": "2025-07-31",
    "leaves": [
      {
        "id": "LEAVE-2025-0001",
        "employeeId": "EMP-2025-0001",
        "employeeName": "John Doe",
        "leaveType": "Vacation",
        "startDate": "2025-07-01",
        "endDate": "2025-07-14",
        "status": "Approved"
      }
    ],
    "holidays": [
      {
        "date": "2025-07-04",
        "name": "Independence Day"
      }
    ]
  }
}
```

---

### Get Leave History

**GET** `/api/v1/employees/{employeeId}/leave-history`

**Description:** Get employee's complete leave history.

**Authorization:** Admin: Any employee, Employee: Own history only

**Query Parameters:**
- `year` (int) - Filter by year
- `type` (string) - Filter by leave type

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "LEAVE-2025-0001",
      "leaveType": "Vacation",
      "startDate": "2025-07-01",
      "endDate": "2025-07-14",
      "totalDays": 10,
      "status": "Approved",
      "requestedAt": "2025-06-01T10:00:00Z",
      "approvedAt": "2025-06-02T09:00:00Z"
    }
  ]
}
```

---

## Authentication Endpoints

### Register

**POST** `/api/v1/auth/register`

**Description:** Register new user account (from registration link).

**Authorization:** None (requires valid registration token)

**Request Body:**
```json
{
  "registrationToken": "token-from-email",
  "password": "SecurePassword123!",
  "passwordConfirmation": "SecurePassword123!"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "john.doe@company.com",
      "role": "Employee"
    },
    "token": "1|abc123def456...",
    "tokenType": "Bearer"
  },
  "message": "Account registered successfully"
}
```

---

### Login

**POST** `/api/v1/auth/login`

**Description:** Authenticate user and receive access token.

**Authorization:** None

**Request Body:**
```json
{
  "email": "john.doe@company.com",
  "password": "SecurePassword123!",
  "rememberMe": false
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": "uuid",
      "email": "john.doe@company.com",
      "role": "Employee",
      "employee": {
        "id": "EMP-2025-0001",
        "name": "John Doe",
        "position": "Senior Developer"
      }
    },
    "token": "1|abc123def456...",
    "tokenType": "Bearer",
    "expiresIn": 28800
  },
  "message": "Login successful"
}
```

**Response (401):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Invalid email or password"
  }
}
```

---

### Logout

**POST** `/api/v1/auth/logout`

**Description:** Logout user and revoke token.

**Authorization:** Bearer token required

**Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

### Refresh Token

**POST** `/api/v1/auth/refresh`

**Description:** Refresh access token before expiry.

**Authorization:** Bearer token required

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "2|xyz789uvw012...",
    "tokenType": "Bearer",
    "expiresIn": 28800
  },
  "message": "Token refreshed successfully"
}
```

---

### Forgot Password

**POST** `/api/v1/auth/password/forgot`

**Description:** Request password reset link.

**Authorization:** None

**Request Body:**
```json
{
  "email": "john.doe@company.com"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

---

### Reset Password

**POST** `/api/v1/auth/password/reset`

**Description:** Reset password using reset token.

**Authorization:** None (requires valid reset token)

**Request Body:**
```json
{
  "email": "john.doe@company.com",
  "token": "reset-token-from-email",
  "password": "NewSecurePassword123!",
  "passwordConfirmation": "NewSecurePassword123!"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

---

### Change Password

**POST** `/api/v1/auth/password/change`

**Description:** Change password while logged in.

**Authorization:** Bearer token required

**Request Body:**
```json
{
  "currentPassword": "OldPassword123!",
  "newPassword": "NewSecurePassword123!",
  "newPasswordConfirmation": "NewSecurePassword123!"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

### Get Current User

**GET** `/api/v1/auth/me`

**Description:** Get currently authenticated user information.

**Authorization:** Bearer token required

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": "uuid",
    "email": "john.doe@company.com",
    "role": "Employee",
    "employee": {
      "id": "EMP-2025-0001",
      "name": "John Doe",
      "position": "Senior Developer",
      "department": "Engineering",
      "photoUrl": "https://cdn.example.com/photos/emp-001.jpg"
    },
    "permissions": ["view_own_profile", "request_leave", "view_team"]
  }
}
```

---

### Get Active Sessions

**GET** `/api/v1/auth/sessions`

**Description:** Get all active sessions for current user.

**Authorization:** Bearer token required

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": "session-id",
      "ipAddress": "192.168.1.100",
      "userAgent": "Mozilla/5.0...",
      "device": "Chrome on macOS",
      "lastActivity": "2025-12-07T10:00:00Z",
      "isCurrent": true
    }
  ]
}
```

---

### Revoke Session

**DELETE** `/api/v1/auth/sessions/{sessionId}`

**Description:** Revoke a specific session.

**Authorization:** Bearer token required

**Response (200):**
```json
{
  "success": true,
  "message": "Session revoked successfully"
}
```

---

## Error Handling

### Standard Error Response

All error responses follow this format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "errors": {
      "field": ["Validation error message"]
    }
  }
}
```

### Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| VALIDATION_ERROR | 422 | Request validation failed |
| AUTHENTICATION_REQUIRED | 401 | User not authenticated |
| INVALID_CREDENTIALS | 401 | Login credentials invalid |
| INSUFFICIENT_PERMISSIONS | 403 | User lacks required permissions |
| RESOURCE_NOT_FOUND | 404 | Requested resource not found |
| DUPLICATE_RESOURCE | 409 | Resource already exists |
| BUSINESS_RULE_VIOLATION | 422 | Business rule violated |
| RATE_LIMIT_EXCEEDED | 429 | Too many requests |
| INTERNAL_SERVER_ERROR | 500 | Unexpected server error |

### Validation Error Example

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "email": [
        "The email field is required.",
        "The email must be a valid email address."
      ],
      "salary.amount": [
        "The salary amount must be at least 30000."
      ]
    }
  }
}
```

### Business Rule Violation Example

```json
{
  "success": false,
  "error": {
    "code": "BUSINESS_RULE_VIOLATION",
    "message": "Insufficient leave balance",
    "details": {
      "required": 10,
      "available": 5,
      "leaveType": "Vacation"
    }
  }
}
```

---

## Rate Limiting

### Rate Limits

| Endpoint Type | Rate Limit |
|--------------|------------|
| Authentication | 5 requests per minute per IP |
| General API | 1000 requests per hour per user |
| Sensitive Operations | 10 requests per minute per user |

### Rate Limit Headers

Responses include rate limit information:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1638360000
```

### Rate Limit Exceeded Response

**Response (429):**
```json
{
  "success": false,
  "error": {
    "code": "RATE_LIMIT_EXCEEDED",
    "message": "Too many requests. Please try again in 60 seconds.",
    "retryAfter": 60
  }
}
```

---

## Summary

### API Statistics

- **Total Endpoints:** 60+
- **Employee Endpoints:** 11
- **Team Endpoints:** 12
- **Equipment Endpoints:** 10
- **Leave Endpoints:** 9
- **Authentication Endpoints:** 10

### Key Features

✅ **RESTful Design** - Resource-based URLs, standard HTTP methods  
✅ **Token Authentication** - Secure Bearer token authentication  
✅ **Pagination** - All list endpoints paginated  
✅ **Filtering & Sorting** - Flexible query parameters  
✅ **Validation** - Comprehensive input validation  
✅ **Error Handling** - Consistent error response format  
✅ **Rate Limiting** - Protection against abuse  
✅ **Versioning** - API version in URL for evolution  

---

**Document Status:** ✅ Complete  
**API Documentation:** OpenAPI 3.0 specification available  
**Postman Collection:** Available for testing

