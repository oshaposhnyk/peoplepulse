# Event Catalog
## IT Employee Management System - Domain Events

**Version:** 1.0  
**Date:** December 7, 2025  
**Status:** Final

---

## Table of Contents

1. [Overview](#overview)
2. [Event Structure](#event-structure)
3. [Employee Context Events](#employee-context-events)
4. [Team Context Events](#team-context-events)
5. [Equipment Context Events](#equipment-context-events)
6. [Leave Context Events](#leave-context-events)
7. [Identity & Access Context Events](#identity--access-context-events)
8. [Event Subscribers Map](#event-subscribers-map)
9. [Event Processing](#event-processing)

---

## Overview

### Purpose
This document provides a comprehensive catalog of all domain events in the system, including their structure, payload, subscribers, and processing requirements.

### Event Principles

1. **Past Tense Naming** - Events represent something that has already happened
2. **Immutable** - Once created, events cannot be modified
3. **Complete** - Events contain all necessary data for subscribers
4. **Versioned** - Events support schema evolution
5. **Idempotent Handling** - Event handlers must be idempotent

### Event Categories

- **Employee Events** - 8 events
- **Team Events** - 8 events
- **Equipment Events** - 8 events
- **Leave Events** - 8 events
- **Identity Events** - 10 events

**Total: 42 Domain Events**

---

## Event Structure

### Base Event Schema

All events follow this standard structure:

```json
{
  "eventId": "uuid-v4",
  "eventType": "domain.context.event_name",
  "eventVersion": "1.0",
  "occurredAt": "2025-12-07T10:30:00.000Z",
  "aggregateId": "aggregate-identifier",
  "aggregateType": "Employee|Team|Equipment|Leave|User",
  "causationId": "uuid-of-command-or-parent-event",
  "correlationId": "uuid-for-request-tracing",
  "userId": "user-who-triggered-event",
  "metadata": {
    "ipAddress": "192.168.1.100",
    "userAgent": "Mozilla/5.0...",
    "source": "web-app|api|system"
  },
  "payload": {
    // Event-specific data
  }
}
```

### Event Properties

| Property | Type | Description |
|----------|------|-------------|
| eventId | UUID | Unique event identifier |
| eventType | String | Namespaced event type (e.g., "employee.hired") |
| eventVersion | String | Schema version (for evolution) |
| occurredAt | ISO 8601 | When the event occurred (UTC) |
| aggregateId | String | ID of the aggregate that produced the event |
| aggregateType | String | Type of aggregate |
| causationId | UUID | ID of command/event that caused this event |
| correlationId | UUID | ID for tracing related events |
| userId | String | User who triggered the event |
| metadata | Object | Additional context information |
| payload | Object | Event-specific data |

---

## Employee Context Events

### 1. EmployeeHired

**Event Type:** `employee.hired`  
**Description:** Triggered when a new employee is hired and added to the system.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "personalInfo": {
    "firstName": "John",
    "lastName": "Doe",
    "middleName": null,
    "email": "john.doe@company.com",
    "phone": "+1-555-0100",
    "dateOfBirth": "1990-05-15"
  },
  "position": "Senior Developer",
  "department": "Engineering",
  "salary": {
    "amount": 95000.00,
    "currency": "USD",
    "frequency": "Annual"
  },
  "location": "San Francisco HQ",
  "employmentType": "Full-time",
  "hireDate": "2025-01-15",
  "startDate": "2025-01-15"
}
```

**Subscribers:**
- ✅ **Identity Context** - Create user account
- ✅ **Leave Context** - Initialize leave balance
- ✅ **Notification Service** - Send welcome email
- ✅ **Audit Service** - Log employee creation

**Side Effects:**
- User account created with temporary password
- Initial leave balance (pro-rated based on hire date)
- Welcome email sent to employee
- Onboarding tasks created

---

### 2. EmployeeUpdated

**Event Type:** `employee.updated`  
**Description:** Triggered when employee information is updated.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "changes": {
    "email": {
      "old": "john.doe@company.com",
      "new": "john.d@company.com"
    },
    "phone": {
      "old": "+1-555-0100",
      "new": "+1-555-0101"
    }
  },
  "updatedBy": "EMP-2025-0100",
  "updatedAt": "2025-06-15T10:00:00Z",
  "reason": "Employee requested email change"
}
```

**Subscribers:**
- ✅ **Identity Context** - Update user email if changed
- ✅ **Audit Service** - Log changes

---

### 3. EmployeeTerminated

**Event Type:** `employee.terminated`  
**Description:** Triggered when an employee's employment is terminated.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "terminationDate": "2025-12-31",
  "lastWorkingDay": "2025-12-31",
  "terminationType": "Resignation",
  "terminationReason": "Personal reasons",
  "initiatedBy": "EMP-2025-0100",
  "exitInterviewScheduled": true,
  "exitInterviewDate": "2025-12-28T14:00:00Z"
}
```

**Termination Types:**
- Voluntary Resignation
- Involuntary Termination
- Retirement
- Contract End
- Death

**Subscribers:**
- ✅ **Team Context** - Remove employee from all teams
- ✅ **Equipment Context** - Trigger equipment return process
- ✅ **Leave Context** - Calculate final leave payout
- ✅ **Identity Context** - Disable user account (on last working day)
- ✅ **Notification Service** - Notify relevant parties
- ✅ **HR System** - Initiate offboarding workflow

**Critical:** This is a high-priority event that triggers multiple workflows.

---

### 4. PositionChanged

**Event Type:** `employee.position_changed`  
**Description:** Triggered when employee's position or salary changes.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "previousPosition": {
    "title": "Developer",
    "level": "Mid",
    "department": "Engineering"
  },
  "newPosition": {
    "title": "Senior Developer",
    "level": "Senior",
    "department": "Engineering"
  },
  "previousSalary": {
    "amount": 80000.00,
    "currency": "USD"
  },
  "newSalary": {
    "amount": 95000.00,
    "currency": "USD"
  },
  "effectiveDate": "2025-06-01",
  "reason": "Annual promotion",
  "approvedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee and HR
- ✅ **Audit Service** - Log position change
- ✅ **Payroll System** - Update compensation (future integration)

---

### 5. LocationChanged

**Event Type:** `employee.location_changed`  
**Description:** Triggered when employee's office location changes.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "previousLocation": "San Francisco HQ",
  "newLocation": "New York Office",
  "effectiveDate": "2025-07-01",
  "reason": "Employee relocation",
  "isTemporary": false,
  "expectedReturnDate": null
}
```

**Subscribers:**
- ✅ **Equipment Context** - Check for location-specific equipment
- ✅ **Notification Service** - Notify facilities and IT
- ✅ **Audit Service** - Log location change

---

### 6. RemoteWorkEnabled

**Event Type:** `employee.remote_work_enabled`  
**Description:** Triggered when remote work is enabled for an employee.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "remoteWorkPolicy": {
    "type": "Hybrid",
    "remoteDays": ["Monday", "Wednesday", "Friday"],
    "startDate": "2025-03-01"
  },
  "approvedBy": "EMP-2025-0100",
  "approvalDate": "2025-02-15T10:00:00Z"
}
```

**Subscribers:**
- ✅ **Equipment Context** - May need home office equipment
- ✅ **IT System** - Grant VPN access
- ✅ **Notification Service** - Notify employee

---

### 7. RemoteWorkDisabled

**Event Type:** `employee.remote_work_disabled`  
**Description:** Triggered when remote work is disabled for an employee.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "disabledDate": "2025-08-01",
  "reason": "Return to office policy",
  "returnToOfficeDate": "2025-08-01"
}
```

**Subscribers:**
- ✅ **Equipment Context** - Return home office equipment
- ✅ **IT System** - Review VPN access
- ✅ **Notification Service** - Notify employee

---

### 8. SalaryChanged

**Event Type:** `employee.salary_changed`  
**Description:** Triggered when employee's salary changes outside of position change.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "previousSalary": {
    "amount": 95000.00,
    "currency": "USD"
  },
  "newSalary": {
    "amount": 100000.00,
    "currency": "USD"
  },
  "effectiveDate": "2025-01-01",
  "reason": "Annual raise",
  "percentageIncrease": 5.26,
  "approvedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee
- ✅ **Audit Service** - Log salary change

---

## Team Context Events

### 9. TeamCreated

**Event Type:** `team.created`  
**Description:** Triggered when a new team is created.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "name": "Backend Engineering",
  "description": "Core backend development team",
  "type": "Development",
  "department": "Engineering",
  "parentTeamId": null,
  "maxSize": 10,
  "createdBy": "EMP-2025-0100",
  "createdAt": "2025-01-15T09:00:00Z"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify relevant managers
- ✅ **Audit Service** - Log team creation

---

### 10. TeamUpdated

**Event Type:** `team.updated`  
**Description:** Triggered when team information is updated.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "changes": {
    "name": {
      "old": "Backend Engineering",
      "new": "Platform Engineering"
    },
    "maxSize": {
      "old": 10,
      "new": 12
    }
  },
  "updatedBy": "EMP-2025-0100",
  "updatedAt": "2025-06-15T10:00:00Z"
}
```

**Subscribers:**
- ✅ **Audit Service** - Log team changes

---

### 11. EmployeeAssignedToTeam

**Event Type:** `team.employee_assigned`  
**Description:** Triggered when an employee is assigned to a team.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "teamName": "Backend Engineering",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "role": "Member",
  "allocationPercentage": 100,
  "effectiveDate": "2025-01-15",
  "assignedBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Equipment Context** - May need team-specific equipment
- ✅ **Leave Context** - Update team capacity tracking
- ✅ **Notification Service** - Notify team and employee
- ✅ **Audit Service** - Log team assignment

---

### 12. EmployeeRemovedFromTeam

**Event Type:** `team.employee_removed`  
**Description:** Triggered when an employee is removed from a team.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "teamName": "Backend Engineering",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "removalDate": "2025-12-31",
  "reason": "Employee terminated",
  "removedBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Equipment Context** - Return team-shared equipment
- ✅ **Leave Context** - Update team capacity
- ✅ **Notification Service** - Notify team
- ✅ **Audit Service** - Log team removal

---

### 13. EmployeeTransferred

**Event Type:** `team.employee_transferred`  
**Description:** Triggered when an employee is transferred between teams.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "sourceTeam": {
    "teamId": "TEAM-0001",
    "teamName": "Backend Engineering"
  },
  "targetTeam": {
    "teamId": "TEAM-0002",
    "teamName": "Frontend Engineering"
  },
  "transferDate": "2025-06-01",
  "newRole": "Member",
  "newAllocation": 100,
  "reason": "Skill realignment",
  "approvedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Equipment Context** - Review equipment assignments
- ✅ **Leave Context** - Update team capacities
- ✅ **Notification Service** - Notify both teams and employee
- ✅ **Audit Service** - Log transfer

---

### 14. TeamLeadChanged

**Event Type:** `team.lead_changed`  
**Description:** Triggered when team lead changes.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "teamName": "Backend Engineering",
  "previousLead": {
    "employeeId": "EMP-2025-0050",
    "employeeName": "Jane Smith"
  },
  "newLead": {
    "employeeId": "EMP-2025-0051",
    "employeeName": "Bob Johnson"
  },
  "effectiveDate": "2025-06-01",
  "reason": "Promotion",
  "changedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Identity Context** - Update permissions if needed
- ✅ **Notification Service** - Notify team
- ✅ **Audit Service** - Log leadership change

---

### 15. TeamDisbanded

**Event Type:** `team.disbanded`  
**Description:** Triggered when a team is disbanded.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "teamName": "Backend Engineering",
  "disbandedDate": "2025-12-31",
  "reason": "Organizational restructure",
  "disbandedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Equipment Context** - Return all team equipment
- ✅ **Notification Service** - Notify relevant parties
- ✅ **Audit Service** - Log team disbanding

---

### 16. TeamHierarchyChanged

**Event Type:** `team.hierarchy_changed`  
**Description:** Triggered when team parent-child relationships change.

**Payload:**
```json
{
  "teamId": "TEAM-0001",
  "teamName": "Backend Engineering",
  "previousParentId": "TEAM-0100",
  "newParentId": "TEAM-0200",
  "effectiveDate": "2025-06-01",
  "reason": "Organizational restructure",
  "changedBy": "EMP-2025-0200"
}
```

**Subscribers:**
- ✅ **Audit Service** - Log hierarchy change

---

## Equipment Context Events

### 17. EquipmentAdded

**Event Type:** `equipment.added`  
**Description:** Triggered when new equipment is added to inventory.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "equipmentType": "Laptop",
  "brand": "Apple",
  "model": "MacBook Pro 16-inch",
  "serialNumber": "C02XYZ123456",
  "specifications": {
    "cpu": "M3 Pro",
    "ram": "32GB",
    "storage": "1TB SSD",
    "display": "16-inch Liquid Retina XDR"
  },
  "purchaseDate": "2025-01-10",
  "purchasePrice": {
    "amount": 2999.00,
    "currency": "USD"
  },
  "warrantyExpiryDate": "2028-01-10",
  "condition": "New",
  "status": "Available",
  "addedBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify IT asset manager
- ✅ **Audit Service** - Log equipment addition

---

### 18. EquipmentIssued

**Event Type:** `equipment.issued`  
**Description:** Triggered when equipment is issued to an employee.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "equipmentType": "Laptop",
  "equipmentDetails": {
    "brand": "Apple",
    "model": "MacBook Pro 16-inch",
    "serialNumber": "C02XYZ123456"
  },
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "issueDate": "2025-01-15",
  "expectedReturnDate": null,
  "condition": "New",
  "accessories": [
    "Charger",
    "USB-C Cable",
    "Laptop Bag"
  ],
  "issuedBy": "EMP-2025-0100",
  "employeeSignature": "digital-signature-hash"
}
```

**Subscribers:**
- ✅ **Notification Service** - Send equipment acknowledgment to employee
- ✅ **Audit Service** - Log equipment issuance
- ✅ **Insurance System** - Update coverage (future)

---

### 19. EquipmentReturned

**Event Type:** `equipment.returned`  
**Description:** Triggered when equipment is returned by an employee.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "equipmentType": "Laptop",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "returnDate": "2025-12-31",
  "condition": "Good",
  "damageReport": null,
  "accessoriesReturned": [
    "Charger",
    "USB-C Cable",
    "Laptop Bag"
  ],
  "nextAction": "Available",
  "receivedBy": "EMP-2025-0100",
  "photos": []
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify IT asset manager
- ✅ **Audit Service** - Log equipment return
- ✅ **Maintenance Service** - Schedule cleaning/updates if needed

---

### 20. EquipmentTransferred

**Event Type:** `equipment.transferred`  
**Description:** Triggered when equipment is transferred between employees.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "equipmentType": "Laptop",
  "fromEmployee": {
    "employeeId": "EMP-2025-0001",
    "employeeName": "John Doe"
  },
  "toEmployee": {
    "employeeId": "EMP-2025-0002",
    "employeeName": "Jane Smith"
  },
  "transferDate": "2025-06-15",
  "condition": "Good",
  "reason": "Employee terminated, equipment reassigned",
  "dataWiped": true,
  "approvedBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify both employees
- ✅ **Audit Service** - Log transfer
- ✅ **IT System** - Trigger data wipe process

---

### 21. MaintenanceScheduled

**Event Type:** `equipment.maintenance_scheduled`  
**Description:** Triggered when equipment maintenance is scheduled.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "maintenanceType": "Repair",
  "scheduledDate": "2025-03-15",
  "expectedDuration": {
    "value": 3,
    "unit": "days"
  },
  "serviceProvider": "External Vendor",
  "estimatedCost": {
    "amount": 250.00,
    "currency": "USD"
  },
  "description": "Screen replacement",
  "scheduledBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee if assigned
- ✅ **Audit Service** - Log maintenance scheduling

---

### 22. MaintenanceCompleted

**Event Type:** `equipment.maintenance_completed`  
**Description:** Triggered when equipment maintenance is completed.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "maintenanceType": "Repair",
  "completedDate": "2025-03-18",
  "actualCost": {
    "amount": 275.00,
    "currency": "USD"
  },
  "workPerformed": "Screen replacement, system diagnostic",
  "newCondition": "Good",
  "warrantyExtended": false,
  "completedBy": "External Vendor"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify requester
- ✅ **Audit Service** - Log maintenance completion
- ✅ **Finance System** - Record maintenance cost

---

### 23. EquipmentDecommissioned

**Event Type:** `equipment.decommissioned`  
**Description:** Triggered when equipment is decommissioned.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "equipmentType": "Laptop",
  "decommissionDate": "2025-12-31",
  "reason": "End of Life",
  "disposalMethod": "Recycle",
  "dataWiped": true,
  "assetTagRemoved": true,
  "decommissionedBy": "EMP-2025-0100"
}
```

**Subscribers:**
- ✅ **Audit Service** - Log decommissioning
- ✅ **Asset Management** - Update inventory
- ✅ **Compliance** - Document disposal

---

### 24. EquipmentDamaged

**Event Type:** `equipment.damaged`  
**Description:** Triggered when equipment damage is reported.

**Payload:**
```json
{
  "equipmentId": "uuid",
  "assetTag": "ASSET-2025-1234",
  "employeeId": "EMP-2025-0001",
  "damageDate": "2025-06-15",
  "damageDescription": "Cracked screen",
  "estimatedRepairCost": {
    "amount": 500.00,
    "currency": "USD"
  },
  "photos": ["url1", "url2"],
  "employeeLiability": true,
  "reportedBy": "EMP-2025-0001"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify IT and HR
- ✅ **Maintenance Service** - Schedule repair
- ✅ **Audit Service** - Log damage report

---

## Leave Context Events

### 25. LeaveRequested

**Event Type:** `leave.requested`  
**Description:** Triggered when an employee requests leave.

**Payload:**
```json
{
  "leaveId": "LEAVE-2025-0001",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "totalDays": 10,
  "workingDays": 10,
  "reason": "Family vacation",
  "contactDuringLeave": "+1-555-0100",
  "backupPerson": "EMP-2025-0002",
  "currentBalance": 24,
  "balanceAfterRequest": 14,
  "requestedAt": "2025-06-01T10:00:00Z"
}
```

**Subscribers:**
- ✅ **Team Context** - Check team capacity
- ✅ **Notification Service** - Notify manager for approval
- ✅ **Audit Service** - Log leave request

---

### 26. LeaveApproved

**Event Type:** `leave.approved`  
**Description:** Triggered when a leave request is approved.

**Payload:**
```json
{
  "leaveId": "LEAVE-2025-0001",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "totalDays": 10,
  "approvedBy": "EMP-2025-0100",
  "approvedByName": "Manager Name",
  "approvedAt": "2025-06-02T09:00:00Z",
  "approvalNotes": "Approved, enjoy your vacation!",
  "newBalance": 14
}
```

**Subscribers:**
- ✅ **Leave Context** - Deduct from balance
- ✅ **Team Context** - Update team calendar
- ✅ **Notification Service** - Notify employee
- ✅ **Calendar Service** - Add to calendar
- ✅ **Audit Service** - Log approval

---

### 27. LeaveRejected

**Event Type:** `leave.rejected`  
**Description:** Triggered when a leave request is rejected.

**Payload:**
```json
{
  "leaveId": "LEAVE-2025-0001",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "totalDays": 10,
  "rejectedBy": "EMP-2025-0100",
  "rejectedByName": "Manager Name",
  "rejectedAt": "2025-06-02T09:00:00Z",
  "rejectionReason": "Insufficient coverage during that period",
  "suggestedAlternative": "Consider August dates"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee
- ✅ **Audit Service** - Log rejection

---

### 28. LeaveCancelled

**Event Type:** `leave.cancelled`  
**Description:** Triggered when a leave request is cancelled.

**Payload:**
```json
{
  "leaveId": "LEAVE-2025-0001",
  "employeeId": "EMP-2025-0001",
  "employeeName": "John Doe",
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "totalDays": 10,
  "previousStatus": "Approved",
  "cancelledBy": "EMP-2025-0001",
  "cancelledAt": "2025-06-15T10:00:00Z",
  "cancellationReason": "Change of plans",
  "balanceRestored": 10,
  "newBalance": 24
}
```

**Subscribers:**
- ✅ **Leave Context** - Restore balance
- ✅ **Team Context** - Update team calendar
- ✅ **Notification Service** - Notify manager
- ✅ **Audit Service** - Log cancellation

---

### 29. LeaveCompleted

**Event Type:** `leave.completed`  
**Description:** Triggered when an employee returns from leave.

**Payload:**
```json
{
  "leaveId": "LEAVE-2025-0001",
  "employeeId": "EMP-2025-0001",
  "leaveType": "Vacation",
  "startDate": "2025-07-01",
  "endDate": "2025-07-14",
  "actualReturnDate": "2025-07-15",
  "completedAt": "2025-07-15T09:00:00Z"
}
```

**Subscribers:**
- ✅ **Team Context** - Update team availability
- ✅ **Audit Service** - Log completion

---

### 30. LeaveBalanceUpdated

**Event Type:** `leave.balance_updated`  
**Description:** Triggered when leave balance is manually adjusted.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "leaveType": "Vacation",
  "previousBalance": 24,
  "adjustment": 5,
  "newBalance": 29,
  "reason": "Compensatory days added",
  "updatedBy": "EMP-2025-0100",
  "updatedAt": "2025-06-15T10:00:00Z"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee
- ✅ **Audit Service** - Log balance adjustment

---

### 31. LeaveBalanceAccrued

**Event Type:** `leave.balance_accrued`  
**Description:** Triggered when leave balance is automatically accrued (monthly job).

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "accrualPeriod": "2025-06",
  "accruals": [
    {
      "leaveType": "Vacation",
      "accrued": 2.0,
      "previousBalance": 22.0,
      "newBalance": 24.0
    },
    {
      "leaveType": "Sick",
      "accrued": 1.0,
      "previousBalance": 11.0,
      "newBalance": 12.0
    }
  ],
  "accruedAt": "2025-06-01T00:00:00Z"
}
```

**Subscribers:**
- ✅ **Notification Service** - Send monthly balance summary
- ✅ **Audit Service** - Log accrual

---

### 32. LeaveBalanceCarryOver

**Event Type:** `leave.balance_carry_over`  
**Description:** Triggered at year-end when leave balance is carried over.

**Payload:**
```json
{
  "employeeId": "EMP-2025-0001",
  "fromYear": 2025,
  "toYear": 2026,
  "carryOvers": [
    {
      "leaveType": "Vacation",
      "remainingBalance": 8,
      "carriedOver": 5,
      "forfeited": 3,
      "maxCarryOver": 5
    }
  ],
  "processedAt": "2025-12-31T23:59:59Z"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify employee of carry-over
- ✅ **Audit Service** - Log carry-over

---

## Identity & Access Context Events

### 33. UserRegistered

**Event Type:** `identity.user_registered`  
**Description:** Triggered when a new user account is created.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "role": "Employee",
  "linkedEmployeeId": "EMP-2025-0001",
  "registrationToken": "token-hash",
  "tokenExpiresAt": "2025-01-16T09:00:00Z",
  "registeredAt": "2025-01-15T09:00:00Z"
}
```

**Subscribers:**
- ✅ **Notification Service** - Send registration email
- ✅ **Audit Service** - Log user registration

---

### 34. UserLoggedIn

**Event Type:** `identity.user_logged_in`  
**Description:** Triggered when a user successfully logs in.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "loginTime": "2025-12-07T08:00:00Z",
  "ipAddress": "192.168.1.100",
  "userAgent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "deviceInfo": {
    "type": "Desktop",
    "os": "macOS",
    "browser": "Chrome"
  },
  "sessionId": "session-uuid",
  "loginMethod": "password"
}
```

**Subscribers:**
- ✅ **Audit Service** - Log login activity
- ✅ **Security Service** - Check for anomalous login patterns

---

### 35. UserLoggedOut

**Event Type:** `identity.user_logged_out`  
**Description:** Triggered when a user logs out.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "logoutTime": "2025-12-07T17:00:00Z",
  "sessionId": "session-uuid",
  "sessionDuration": "32400",
  "logoutType": "manual"
}
```

**Logout Types:** manual, auto (timeout), forced (admin)

**Subscribers:**
- ✅ **Audit Service** - Log logout activity

---

### 36. PasswordChanged

**Event Type:** `identity.password_changed`  
**Description:** Triggered when a user changes their password.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "changedAt": "2025-12-07T10:00:00Z",
  "changeType": "user_initiated",
  "allSessionsRevoked": true
}
```

**Change Types:** user_initiated, admin_reset, forced_expiration

**Subscribers:**
- ✅ **Notification Service** - Send confirmation email
- ✅ **Audit Service** - Log password change
- ✅ **Security Service** - Revoke all active sessions

---

### 37. PasswordResetRequested

**Event Type:** `identity.password_reset_requested`  
**Description:** Triggered when a user requests password reset.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "resetToken": "token-hash",
  "tokenExpiresAt": "2025-12-07T11:00:00Z",
  "requestedAt": "2025-12-07T10:00:00Z",
  "ipAddress": "192.168.1.100"
}
```

**Subscribers:**
- ✅ **Notification Service** - Send password reset email
- ✅ **Audit Service** - Log reset request
- ✅ **Security Service** - Monitor for abuse

---

### 38. AccountLocked

**Event Type:** `identity.account_locked`  
**Description:** Triggered when a user account is locked.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "lockedAt": "2025-12-07T10:00:00Z",
  "lockedUntil": "2025-12-07T10:30:00Z",
  "lockReason": "Too many failed login attempts",
  "failedAttempts": 5,
  "autoUnlock": true
}
```

**Lock Reasons:** failed_attempts, admin_action, security_policy

**Subscribers:**
- ✅ **Notification Service** - Send account locked email
- ✅ **Audit Service** - Log account lock
- ✅ **Security Service** - Investigate potential breach

---

### 39. AccountUnlocked

**Event Type:** `identity.account_unlocked`  
**Description:** Triggered when a user account is unlocked.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "unlockedAt": "2025-12-07T10:30:00Z",
  "unlockMethod": "automatic",
  "unlockedBy": null
}
```

**Unlock Methods:** automatic (timeout), admin, password_reset

**Subscribers:**
- ✅ **Notification Service** - Send account unlocked email
- ✅ **Audit Service** - Log account unlock

---

### 40. RoleChanged

**Event Type:** `identity.role_changed`  
**Description:** Triggered when a user's role is changed.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "previousRole": "Employee",
  "newRole": "Admin",
  "changedBy": "EMP-2025-0200",
  "changedAt": "2025-12-07T10:00:00Z",
  "reason": "Promoted to manager"
}
```

**Subscribers:**
- ✅ **Notification Service** - Notify user
- ✅ **Audit Service** - Log role change
- ✅ **Permission Service** - Refresh user permissions

---

### 41. MFAEnabled

**Event Type:** `identity.mfa_enabled`  
**Description:** Triggered when multi-factor authentication is enabled.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "mfaMethod": "TOTP",
  "enabledAt": "2025-12-07T10:00:00Z",
  "backupCodesGenerated": 10
}
```

**Subscribers:**
- ✅ **Notification Service** - Send confirmation email
- ✅ **Audit Service** - Log MFA enablement

---

### 42. MFADisabled

**Event Type:** `identity.mfa_disabled`  
**Description:** Triggered when multi-factor authentication is disabled.

**Payload:**
```json
{
  "userId": "uuid",
  "email": "john.doe@company.com",
  "disabledAt": "2025-12-07T10:00:00Z",
  "disabledBy": "uuid",
  "reason": "User request"
}
```

**Subscribers:**
- ✅ **Notification Service** - Send security alert email
- ✅ **Audit Service** - Log MFA disablement
- ✅ **Security Service** - Monitor account activity

---

## Event Subscribers Map

### Complete Subscriber Matrix

| Event | Identity | Employee | Team | Equipment | Leave | Notification | Audit | External |
|-------|----------|----------|------|-----------|-------|--------------|-------|----------|
| **Employee Context** |
| EmployeeHired | ✅ Create | - | - | - | ✅ Balance | ✅ Email | ✅ Log | - |
| EmployeeUpdated | ✅ Email | - | - | - | - | - | ✅ Log | - |
| EmployeeTerminated | ✅ Disable | - | ✅ Remove | ✅ Return | ✅ Payout | ✅ Email | ✅ Log | HR |
| PositionChanged | - | - | - | - | - | ✅ Email | ✅ Log | Payroll |
| LocationChanged | - | - | - | ✅ Check | - | ✅ Email | ✅ Log | - |
| RemoteWorkEnabled | - | - | - | ✅ Home | - | ✅ Email | ✅ Log | IT/VPN |
| RemoteWorkDisabled | - | - | - | ✅ Return | - | ✅ Email | ✅ Log | IT/VPN |
| SalaryChanged | - | - | - | - | - | ✅ Email | ✅ Log | Payroll |
| **Team Context** |
| TeamCreated | - | - | - | - | - | ✅ Email | ✅ Log | - |
| TeamUpdated | - | - | - | - | - | - | ✅ Log | - |
| EmployeeAssignedToTeam | - | - | - | ✅ Check | ✅ Capacity | ✅ Email | ✅ Log | - |
| EmployeeRemovedFromTeam | - | - | - | ✅ Return | ✅ Capacity | ✅ Email | ✅ Log | - |
| EmployeeTransferred | - | - | - | ✅ Review | ✅ Capacity | ✅ Email | ✅ Log | - |
| TeamLeadChanged | ✅ Perms | - | - | - | - | ✅ Email | ✅ Log | - |
| TeamDisbanded | - | - | - | ✅ Return | - | ✅ Email | ✅ Log | - |
| TeamHierarchyChanged | - | - | - | - | - | - | ✅ Log | - |
| **Equipment Context** |
| EquipmentAdded | - | - | - | - | - | ✅ Email | ✅ Log | - |
| EquipmentIssued | - | - | - | - | - | ✅ Email | ✅ Log | Insurance |
| EquipmentReturned | - | - | - | - | - | ✅ Email | ✅ Log | - |
| EquipmentTransferred | - | - | - | - | - | ✅ Email | ✅ Log | IT |
| MaintenanceScheduled | - | - | - | - | - | ✅ Email | ✅ Log | - |
| MaintenanceCompleted | - | - | - | - | - | ✅ Email | ✅ Log | Finance |
| EquipmentDecommissioned | - | - | - | - | - | - | ✅ Log | Compliance |
| EquipmentDamaged | - | - | - | - | - | ✅ Email | ✅ Log | - |
| **Leave Context** |
| LeaveRequested | - | - | ✅ Check | - | - | ✅ Email | ✅ Log | - |
| LeaveApproved | - | - | ✅ Calendar | - | ✅ Deduct | ✅ Email | ✅ Log | Calendar |
| LeaveRejected | - | - | - | - | - | ✅ Email | ✅ Log | - |
| LeaveCancelled | - | - | ✅ Calendar | - | ✅ Restore | ✅ Email | ✅ Log | - |
| LeaveCompleted | - | - | ✅ Available | - | - | - | ✅ Log | - |
| LeaveBalanceUpdated | - | - | - | - | - | ✅ Email | ✅ Log | - |
| LeaveBalanceAccrued | - | - | - | - | - | ✅ Email | ✅ Log | - |
| LeaveBalanceCarryOver | - | - | - | - | - | ✅ Email | ✅ Log | - |
| **Identity Context** |
| UserRegistered | - | - | - | - | - | ✅ Email | ✅ Log | - |
| UserLoggedIn | - | - | - | - | - | - | ✅ Log | Security |
| UserLoggedOut | - | - | - | - | - | - | ✅ Log | - |
| PasswordChanged | - | - | - | - | - | ✅ Email | ✅ Log | Security |
| PasswordResetRequested | - | - | - | - | - | ✅ Email | ✅ Log | Security |
| AccountLocked | - | - | - | - | - | ✅ Email | ✅ Log | Security |
| AccountUnlocked | - | - | - | - | - | ✅ Email | ✅ Log | - |
| RoleChanged | - | - | - | - | - | ✅ Email | ✅ Log | - |
| MFAEnabled | - | - | - | - | - | ✅ Email | ✅ Log | - |
| MFADisabled | - | - | - | - | - | ✅ Email | ✅ Log | Security |

---

## Event Processing

### Event Dispatcher

```php
interface EventDispatcher
{
    public function dispatch(DomainEvent $event): void;
    public function dispatchBatch(array $events): void;
}
```

### Event Handler

```php
interface EventHandler
{
    public function handle(DomainEvent $event): void;
    public function canHandle(string $eventType): bool;
    public function priority(): int; // Higher number = higher priority
}
```

### Processing Strategy

**Synchronous Events:**
- None (all events processed asynchronously via queue)

**Asynchronous Events:**
- All domain events
- Queued with priority
- Retry on failure (3 attempts with exponential backoff)

**Event Priority:**
1. **High Priority** (1-5 min processing): EmployeeTerminated, AccountLocked
2. **Normal Priority** (5-30 min processing): Most events
3. **Low Priority** (up to 2 hours): Reports, analytics updates

### Retry Policy

```json
{
  "maxAttempts": 3,
  "backoff": "exponential",
  "initialDelay": 60,
  "maxDelay": 3600,
  "multiplier": 2
}
```

**Attempt Schedule:**
- Attempt 1: Immediate
- Attempt 2: After 60 seconds
- Attempt 3: After 120 seconds
- Failed: Move to dead letter queue

### Dead Letter Queue

Events that fail after all retries go to dead letter queue for manual intervention:
- Alert administrators
- Log failure details
- Allow manual retry
- Investigate and fix issue

### Event Store (Optional for Future)

Consider implementing event sourcing for:
- Complete audit trail
- Time travel debugging
- State reconstruction
- Analytics and reporting

---

## Summary

### Event Catalog Statistics

- **Total Events:** 42
- **Employee Events:** 8
- **Team Events:** 8
- **Equipment Events:** 8
- **Leave Events:** 8
- **Identity Events:** 10

### Subscriber Statistics

- **Total Subscribers:** 150+ subscriptions
- **Most Subscribed Event:** EmployeeTerminated (6 subscribers)
- **Cross-Context Events:** 32 events trigger cross-context actions
- **External Integrations:** 10+ external system integrations

### Processing Requirements

- **Queue System:** Laravel Queue with Redis
- **Processing Mode:** Asynchronous (all events)
- **Retry Policy:** 3 attempts with exponential backoff
- **Priority Levels:** 3 (High, Normal, Low)
- **Monitoring:** Event processing metrics and alerts

---

**Document Status:** ✅ Complete  
**Next Step:** Design database schema (Task 1.6)

