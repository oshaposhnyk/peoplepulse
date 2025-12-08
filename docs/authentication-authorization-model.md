# Authentication & Authorization Model
## IT Employee Management System

**Version:** 1.0  
**Date:** December 7, 2025  
**Status:** Final

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication Strategy](#authentication-strategy)
3. [Authorization Model](#authorization-model)
4. [Role-Based Access Control](#role-based-access-control)
5. [Permission System](#permission-system)
6. [Security Policies](#security-policies)
7. [Session Management](#session-management)
8. [Multi-Factor Authentication](#multi-factor-authentication)
9. [API Security](#api-security)
10. [Implementation Guide](#implementation-guide)

---

## Overview

### Purpose
This document defines the complete authentication and authorization model for the IT Employee Management System, ensuring secure access control and proper permission management.

### Security Principles

1. **Authentication** - Verify user identity
2. **Authorization** - Verify user permissions
3. **Least Privilege** - Grant minimum required permissions
4. **Defense in Depth** - Multiple layers of security
5. **Audit Trail** - Log all authentication and authorization events

### Technology Stack

- **Authentication:** Laravel Sanctum (Token-based)
- **Password Hashing:** bcrypt (cost factor: 12)
- **Session Management:** Database-backed sessions
- **MFA:** TOTP (Time-based One-Time Password)
- **API Security:** Bearer tokens

---

## Authentication Strategy

### Authentication Flow

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       │ 1. POST /api/v1/auth/login
       │    {email, password}
       ↓
┌─────────────────────┐
│  Laravel Backend    │
│                     │
│  2. Validate        │
│     Credentials     │
│                     │
│  3. Check Account   │
│     Status          │
│                     │
│  4. Check MFA       │
│     (if enabled)    │
│                     │
│  5. Generate        │
│     Sanctum Token   │
└──────┬──────────────┘
       │
       │ 6. Return Token
       ↓
┌─────────────┐
│   Client    │
│             │
│ Store Token │
└─────────────┘

Subsequent Requests:
┌─────────────┐
│   Client    │
└──────┬──────┘
       │
       │ Authorization: Bearer {token}
       ↓
┌─────────────────────┐
│  Laravel Backend    │
│                     │
│  1. Validate Token  │
│  2. Load User       │
│  3. Check Perms     │
│  4. Process Request │
└─────────────────────┘
```

### Authentication Methods

#### 1. Email/Password Authentication

**Standard login flow:**
- User submits email and password
- System validates credentials
- Account status checked (active, not locked)
- Failed attempts tracked
- Token generated on success

**Security Features:**
- Password hashing with bcrypt
- Rate limiting (5 attempts per minute)
- Account lockout (5 failed attempts = 30 min lock)
- IP tracking for suspicious activity
- Login attempt logging

#### 2. Password Reset Flow

```
1. User requests password reset
   POST /api/v1/auth/password/forgot
   {email}

2. System generates reset token (1 hour expiry)
   - Token: 64-character random string
   - Stored in database with expiry
   - Email sent to user

3. User clicks link in email
   Opens password reset form

4. User submits new password
   POST /api/v1/auth/password/reset
   {email, token, password, password_confirmation}

5. System validates:
   - Token exists and not expired
   - Email matches token
   - Password meets requirements
   - Password not in history (last 3)

6. Password updated
   - All sessions revoked
   - User must login again
```

#### 3. Registration Flow

```
1. Admin creates employee
   POST /api/v1/employees
   - Employee record created
   - EmployeeHired event fired

2. System creates user account
   - Listen to EmployeeHired event
   - Generate registration token (24 hour expiry)
   - Send registration email

3. Employee receives email
   - Link: /register?token={token}
   - Token single-use

4. Employee sets password
   POST /api/v1/auth/register
   {token, password, password_confirmation}

5. Account activated
   - Password set
   - Email verified
   - Account enabled
   - User can login
```

---

## Authorization Model

### Authorization Architecture

```
┌───────────────────────────────────────────────────┐
│              Authorization Layers                  │
├───────────────────────────────────────────────────┤
│                                                    │
│  1. Authentication Layer                           │
│     ├─ Valid Token?                               │
│     ├─ User Active?                               │
│     └─ Account Not Locked?                        │
│                                                    │
│  2. Role-Based Access Control (RBAC)              │
│     ├─ User Role: Admin or Employee               │
│     └─ Role Permissions                           │
│                                                    │
│  3. Resource-Level Authorization                   │
│     ├─ Laravel Policies                           │
│     ├─ Can user access this resource?             │
│     └─ Ownership checks                           │
│                                                    │
│  4. Field-Level Authorization                      │
│     ├─ Can user see sensitive fields?             │
│     └─ Response filtering                         │
│                                                    │
└───────────────────────────────────────────────────┘
```

### Authorization Flow

```php
// Every API request goes through this flow:

1. Middleware: sanctum:auth
   - Validate token
   - Load authenticated user
   - Check account status

2. Middleware: role:admin (optional)
   - Check if user has required role
   - Reject if role not matched

3. Controller: Policy Check
   - Check if user authorized for action
   - Example: $this->authorize('update', $employee)

4. Service Layer: Business Rules
   - Additional business rule checks
   - Example: Cannot terminate employee with unreturned equipment

5. Response: Field Filtering
   - Filter sensitive fields based on role
   - Example: Salary visible to admin only
```

---

## Role-Based Access Control

### Roles

The system has **2 primary roles**:

#### 1. Administrator (Admin)

**Description:** Full system access with administrative privileges

**Capabilities:**
- ✅ Manage all employees (CRUD, terminate, position/location changes)
- ✅ Manage all teams (CRUD, assign/remove members)
- ✅ Manage all equipment (CRUD, issue, return, transfer)
- ✅ Approve/reject all leave requests
- ✅ Manage user accounts and roles
- ✅ Access all reports and analytics
- ✅ View audit logs
- ✅ Configure system settings

**Assignment:**
- Manually assigned by existing admin
- Typically: HR managers, IT managers, Directors

#### 2. Employee (Regular User)

**Description:** Standard user with limited self-service access

**Capabilities:**
- ✅ View own employee profile
- ✅ Update own profile (limited fields: phone, address, photo)
- ✅ View own teams and team members
- ✅ View own assigned equipment
- ✅ Request leave (vacation, sick)
- ✅ View own leave balance and history
- ✅ Cancel own pending leave requests
- ✅ View team calendar
- ✅ View organizational structure (read-only)

**Restrictions:**
- ❌ Cannot view other employees' sensitive data (salary, personal info)
- ❌ Cannot modify employment data
- ❌ Cannot assign/remove team members
- ❌ Cannot issue/return equipment
- ❌ Cannot approve leave requests

**Assignment:**
- Automatically assigned on employee creation
- Default role for all new users

### Role Comparison Matrix

| Feature | Admin | Employee |
|---------|-------|----------|
| **Employee Management** |
| View all employees | ✅ | ❌ (own only) |
| Create employee | ✅ | ❌ |
| Update employee | ✅ | ❌ (limited) |
| Terminate employee | ✅ | ❌ |
| Change position | ✅ | ❌ |
| Change location | ✅ | ❌ |
| View salary | ✅ | ❌ (own only) |
| **Team Management** |
| View all teams | ✅ | ✅ (basic info) |
| Create team | ✅ | ❌ |
| Update team | ✅ | ❌ |
| Assign member | ✅ | ❌ |
| Remove member | ✅ | ❌ |
| Transfer member | ✅ | ❌ |
| Disband team | ✅ | ❌ |
| **Equipment Management** |
| View all equipment | ✅ | ❌ (assigned only) |
| Add equipment | ✅ | ❌ |
| Issue equipment | ✅ | ❌ |
| Return equipment | ✅ | ❌ |
| Transfer equipment | ✅ | ❌ |
| Schedule maintenance | ✅ | ❌ |
| Decommission | ✅ | ❌ |
| **Leave Management** |
| Request leave | ✅ | ✅ |
| View own leave | ✅ | ✅ |
| Approve leave | ✅ | ❌ |
| Reject leave | ✅ | ❌ |
| View all leave requests | ✅ | ❌ (team only) |
| View leave calendar | ✅ | ✅ (team only) |
| **System** |
| View audit logs | ✅ | ❌ |
| Manage users | ✅ | ❌ |
| Manage roles | ✅ | ❌ |
| View reports | ✅ | ❌ (own only) |
| System settings | ✅ | ❌ |

---

## Permission System

### Granular Permissions

While roles provide broad access control, granular permissions enable fine-tuned authorization.

#### Permission Structure

```php
// Permission format: {domain}.{resource}.{action}

// Examples:
'employee.view.all'        // View all employees
'employee.view.own'        // View own profile
'employee.create'          // Create employees
'employee.update'          // Update employees
'employee.delete'          // Delete employees
'employee.terminate'       // Terminate employees

'team.view.all'            // View all teams
'team.create'              // Create teams
'team.manage_members'      // Assign/remove members

'equipment.view.all'       // View all equipment
'equipment.issue'          // Issue equipment
'equipment.return'         // Return equipment

'leave.approve'            // Approve leave requests
'leave.reject'             // Reject leave requests
'leave.view.all'           // View all leave requests

'audit.view'               // View audit logs
'system.settings'          // Manage system settings
```

#### Permission Assignment by Role

**Admin Permissions:**
```php
[
    // Employee
    'employee.view.all',
    'employee.create',
    'employee.update',
    'employee.delete',
    'employee.terminate',
    'employee.change_position',
    'employee.change_location',
    
    // Team
    'team.view.all',
    'team.create',
    'team.update',
    'team.delete',
    'team.manage_members',
    
    // Equipment
    'equipment.view.all',
    'equipment.create',
    'equipment.update',
    'equipment.delete',
    'equipment.issue',
    'equipment.return',
    'equipment.transfer',
    'equipment.maintain',
    
    // Leave
    'leave.view.all',
    'leave.approve',
    'leave.reject',
    'leave.request',
    'leave.cancel',
    
    // System
    'audit.view',
    'user.manage',
    'system.settings',
    'reports.view.all'
]
```

**Employee Permissions:**
```php
[
    // Employee
    'employee.view.own',
    'employee.update.own.limited',
    
    // Team
    'team.view.own',
    'team.view.members',
    
    // Equipment
    'equipment.view.own',
    
    // Leave
    'leave.request',
    'leave.view.own',
    'leave.cancel.own',
    'leave.view.team_calendar',
    
    // System
    'profile.update.own'
]
```

### Permission Checking

#### In Controllers (via Policies)

```php
// EmployeeController.php
public function update(Request $request, Employee $employee)
{
    // Policy automatically checks if user can update this employee
    $this->authorize('update', $employee);
    
    // Continue with update...
}
```

#### In Policies

```php
// EmployeePolicy.php
class EmployeePolicy
{
    public function update(User $user, Employee $employee): bool
    {
        // Admin can update any employee
        if ($user->isAdmin()) {
            return true;
        }
        
        // Employee can only update own record (limited fields)
        return $user->employee_id === $employee->id;
    }
    
    public function viewSalary(User $user, Employee $employee): bool
    {
        // Admin can view any salary
        if ($user->isAdmin()) {
            return true;
        }
        
        // Employee can only view own salary
        return $user->employee_id === $employee->id;
    }
}
```

#### Using Gates

```php
// In middleware or controller
if (Gate::allows('manage-equipment')) {
    // User can manage equipment
}

// In Blade/Vue (for UI)
@can('approve-leave')
    <button>Approve</button>
@endcan
```

---

## Security Policies

### Password Policy

**Requirements:**
- Minimum length: 8 characters
- Maximum length: 64 characters
- Must contain:
  - At least 1 uppercase letter (A-Z)
  - At least 1 lowercase letter (a-z)
  - At least 1 number (0-9)
  - At least 1 special character (!@#$%^&*)
- Cannot be common password (checked against blacklist)
- Cannot be same as username or email
- Cannot reuse last 3 passwords

**Password Expiry (Optional):**
- Admin accounts: 90 days (recommended)
- Employee accounts: No expiry (or 180 days)
- Warning 7 days before expiry
- Forced change on expiry

**Password Storage:**
- Hashed using bcrypt
- Cost factor: 12
- Never stored in plain text
- Never logged or exposed in responses

### Account Lockout Policy

**Failed Login Attempts:**
- Track failed attempts by email/IP
- Lockout after 5 consecutive failures
- Lockout duration: 30 minutes
- Reset counter on successful login

**Unlock Methods:**
1. **Automatic:** After 30 minutes
2. **Password Reset:** Via password reset flow
3. **Admin Unlock:** Admin can manually unlock

**Lockout Notifications:**
- Email sent to user on lockout
- Admin notified of repeated lockouts (potential attack)
- Security team alerted on mass lockouts

### Session Policy

**Session Configuration:**
- Session lifetime: 8 hours (configurable)
- Idle timeout: 2 hours of inactivity
- Absolute timeout: 8 hours regardless of activity
- Maximum concurrent sessions: 5 per user

**Session Security:**
- Sessions tied to IP address (optional, can be disabled for mobile)
- User agent validation
- Session hijacking detection
- Secure session cookies (httpOnly, secure, sameSite)

**Session Management:**
- Users can view all active sessions
- Users can revoke specific sessions
- Password change revokes all sessions
- Logout revokes current session only (option for all)

### Token Policy

**Sanctum Token Configuration:**
- Token expiry: 8 hours (28800 seconds)
- Refresh before expiry using `/auth/refresh`
- Tokens tied to specific device/client
- Token abilities can be scoped (optional)

**Token Security:**
- Generated using cryptographically secure random
- Stored hashed in database
- Never logged
- Revoked on logout
- All tokens revoked on password change

**Token Rotation:**
- Refresh endpoint generates new token
- Old token invalidated
- Prevents token replay attacks

---

## Session Management

### Session Lifecycle

```
1. Login
   ├─ Validate credentials
   ├─ Check MFA (if enabled)
   ├─ Create session record
   ├─ Generate Sanctum token
   └─ Return token to client

2. Active Session
   ├─ Track last activity
   ├─ Validate on each request
   ├─ Refresh activity timestamp
   └─ Check for timeout

3. Session Timeout
   ├─ Idle timeout: 2 hours no activity
   ├─ Absolute timeout: 8 hours
   └─ Revoke token

4. Logout
   ├─ Revoke current token
   ├─ Delete session record
   └─ Clear client-side token

5. Force Logout Events
   ├─ Password changed
   ├─ Account locked
   ├─ Account disabled
   ├─ Role changed
   └─ Admin forced logout
```

### Session Database Schema

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    
    -- Additional fields
    device_type VARCHAR(50) NULL,
    device_os VARCHAR(50) NULL,
    device_browser VARCHAR(50) NULL,
    login_time TIMESTAMP NOT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity)
);
```

### Concurrent Session Management

**Multiple Devices:**
- Users can login from multiple devices
- Each device gets unique session/token
- Maximum 5 concurrent sessions
- Oldest session removed when limit exceeded

**Session Tracking:**
```json
{
  "sessions": [
    {
      "id": "session-1",
      "device": "Chrome on macOS",
      "ipAddress": "192.168.1.100",
      "loginTime": "2025-12-07T08:00:00Z",
      "lastActivity": "2025-12-07T10:00:00Z",
      "isCurrent": true
    },
    {
      "id": "session-2",
      "device": "Safari on iPhone",
      "ipAddress": "10.0.0.50",
      "loginTime": "2025-12-06T18:00:00Z",
      "lastActivity": "2025-12-07T09:30:00Z",
      "isCurrent": false
    }
  ]
}
```

**Session Actions:**
- View all active sessions: `GET /api/v1/auth/sessions`
- Revoke specific session: `DELETE /api/v1/auth/sessions/{id}`
- Revoke all sessions: `POST /api/v1/auth/logout-all`

---

## Multi-Factor Authentication

### MFA Configuration

**MFA Methods:**
1. **TOTP (Time-based One-Time Password)** - Primary method
   - Google Authenticator
   - Authy
   - Microsoft Authenticator
   - Any TOTP-compatible app

2. **Backup Codes** - Recovery method
   - 10 single-use codes generated on MFA setup
   - Can be regenerated anytime
   - Use when TOTP unavailable

**MFA Policy:**
- **Optional for Employees** (can be enabled by user)
- **Mandatory for Admins** (enforced by system)
- Grace period: 7 days for admins to enable MFA

### MFA Setup Flow

```
1. User enables MFA
   POST /api/v1/auth/mfa/enable

2. System generates secret
   - 32-character base32 secret
   - QR code generated
   - Manual entry code provided

3. User scans QR code
   - Add to authenticator app
   - Account: "PeoplePulse - john.doe@company.com"

4. User verifies setup
   POST /api/v1/auth/mfa/verify
   {code: "123456"}

5. System validates code
   - Checks TOTP code
   - Generates 10 backup codes
   - Enables MFA for account

6. User saves backup codes
   - Download backup codes
   - Store securely
```

### MFA Login Flow

```
1. User submits credentials
   POST /api/v1/auth/login
   {email, password}

2. System validates credentials
   - Check email/password
   - Check account status

3. If MFA enabled
   - Generate temporary token (5 min expiry)
   - Return: {mfaRequired: true, tempToken}

4. User submits MFA code
   POST /api/v1/auth/mfa/verify-login
   {tempToken, code}

5. System validates TOTP code
   - Check code within time window (30s)
   - Allow 1 time-step tolerance
   - Check if code already used (replay protection)

6. Generate session token
   - Create full access token
   - Return token to client

Alternative: Using Backup Code
   POST /api/v1/auth/mfa/verify-login
   {tempToken, backupCode}
   - Validate backup code
   - Mark backup code as used
   - Generate session token
```

### MFA Recovery

**Lost Device:**
1. User uses backup code to login
2. User can disable MFA (with backup code)
3. User can re-enable with new device

**Lost Backup Codes:**
1. Login with existing TOTP
2. Regenerate backup codes
3. Old codes invalidated

**Admin Recovery:**
- Admin can disable MFA for user
- Requires approval from senior admin
- Logged in audit trail
- User notified via email

### MFA Database Schema

```sql
-- In users table
ALTER TABLE users ADD COLUMN mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users ADD COLUMN mfa_secret VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN mfa_backup_codes JSON NULL;
ALTER TABLE users ADD COLUMN mfa_enabled_at TIMESTAMP NULL;
```

---

## API Security

### Token-Based Authentication

**Sanctum Token Flow:**
```
Client                          Server
  │                               │
  │  POST /auth/login             │
  │  {email, password}            │
  ├──────────────────────────────>│
  │                               │
  │                               │ Validate credentials
  │                               │ Generate token
  │                               │
  │  {token: "1|abc..."}         │
  │<──────────────────────────────┤
  │                               │
  │  Store token locally          │
  │                               │
  │  GET /employees               │
  │  Authorization: Bearer 1|abc..│
  ├──────────────────────────────>│
  │                               │
  │                               │ Validate token
  │                               │ Load user
  │                               │ Check permissions
  │                               │
  │  {data: [...]}               │
  │<──────────────────────────────┤
```

**Token Storage (Client-Side):**
- **Web:** localStorage or httpOnly cookie (preferred)
- **Mobile:** Secure storage (Keychain/Keystore)
- **Never:** Regular cookies without httpOnly/secure

### Rate Limiting

**Rate Limit Configuration:**

| Endpoint Type | Rate Limit | Window |
|--------------|------------|--------|
| Auth endpoints (login, register) | 5 requests | per minute per IP |
| Password reset | 3 requests | per hour per email |
| General API | 1000 requests | per hour per user |
| Sensitive operations | 10 requests | per minute per user |

**Rate Limit Headers:**
```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1638360000
```

**Rate Limit Exceeded Response (429):**
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

### CORS Configuration

**Allowed Origins:**
- Development: `http://localhost:3000`, `http://localhost:5173`
- Staging: `https://staging.peoplepulse.com`
- Production: `https://app.peoplepulse.com`

**CORS Headers:**
```
Access-Control-Allow-Origin: https://app.peoplepulse.com
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With
Access-Control-Allow-Credentials: true
Access-Control-Max-Age: 86400
```

### Security Headers

**Response Headers:**
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

---

## Implementation Guide

### Laravel Implementation

#### 1. User Model

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    
    protected $fillable = [
        'employee_id',
        'email',
        'password',
        'role',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];
    
    protected $casts = [
        'mfa_enabled' => 'boolean',
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
        'mfa_backup_codes' => 'array',
        'permissions' => 'array',
        'password_history' => 'array',
    ];
    
    // Role checking
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }
    
    public function isEmployee(): bool
    {
        return $this->role === 'Employee';
    }
    
    // Permission checking
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true; // Admin has all permissions
        }
        
        return in_array($permission, $this->permissions ?? []);
    }
    
    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
```

#### 2. Authentication Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        // Check if user exists
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password'
                ]
            ], 401);
        }
        
        // Check if account is locked
        if ($user->is_locked) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCOUNT_LOCKED',
                    'message' => 'Account is locked. Please try again later.'
                ]
            ], 403);
        }
        
        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            // Record failed attempt
            $this->recordFailedLogin($user);
            
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password'
                ]
            ], 401);
        }
        
        // Check if MFA is enabled
        if ($user->mfa_enabled) {
            $tempToken = $this->generateTempToken($user);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'mfaRequired' => true,
                    'tempToken' => $tempToken
                ]
            ]);
        }
        
        // Generate access token
        $token = $user->createToken('access-token')->plainTextToken;
        
        // Reset failed login attempts
        $user->update([
            'failed_login_attempts' => 0,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip()
        ]);
        
        // Log successful login
        event(new UserLoggedIn($user));
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'employee' => $user->employee
                ],
                'token' => $token,
                'tokenType' => 'Bearer',
                'expiresIn' => config('sanctum.expiration')
            ],
            'message' => 'Login successful'
        ]);
    }
    
    private function recordFailedLogin(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        
        $user->update([
            'failed_login_attempts' => $attempts
        ]);
        
        // Lock account after 5 failed attempts
        if ($attempts >= 5) {
            $user->update([
                'is_locked' => true,
                'locked_until' => now()->addMinutes(30)
            ]);
            
            event(new AccountLocked($user));
        }
    }
}
```

#### 3. Policies

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        // Admin can view all employees
        return $user->isAdmin();
    }
    
    public function view(User $user, Employee $employee): bool
    {
        // Admin can view any employee
        if ($user->isAdmin()) {
            return true;
        }
        
        // Employee can view own profile
        return $user->employee_id === $employee->id;
    }
    
    public function create(User $user): bool
    {
        // Only admin can create employees
        return $user->isAdmin();
    }
    
    public function update(User $user, Employee $employee): bool
    {
        // Admin can update any employee
        if ($user->isAdmin()) {
            return true;
        }
        
        // Employee can update own profile (limited fields)
        return $user->employee_id === $employee->id;
    }
    
    public function delete(User $user, Employee $employee): bool
    {
        // Only admin can delete employees
        return $user->isAdmin();
    }
    
    public function terminate(User $user, Employee $employee): bool
    {
        // Only admin can terminate employees
        return $user->isAdmin();
    }
    
    public function viewSalary(User $user, Employee $employee): bool
    {
        // Admin can view any salary
        if ($user->isAdmin()) {
            return true;
        }
        
        // Employee can view own salary
        return $user->employee_id === $employee->id;
    }
}
```

#### 4. Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'AUTHENTICATION_REQUIRED',
                    'message' => 'Authentication required'
                ]
            ], 401);
        }
        
        if ($user->role !== $role) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_PERMISSIONS',
                    'message' => 'You do not have permission to access this resource'
                ]
            ], 403);
        }
        
        return $next($request);
    }
}
```

---

## Summary

### Authentication Features

✅ **Email/Password Authentication** - Standard login flow  
✅ **Token-Based API Auth** - Laravel Sanctum  
✅ **Password Reset** - Secure reset flow with tokens  
✅ **Account Lockout** - After 5 failed attempts  
✅ **Session Management** - Multi-device support  
✅ **MFA Support** - TOTP with backup codes  
✅ **Password Policy** - Strong password requirements  

### Authorization Features

✅ **Role-Based Access Control** - 2 roles (Admin, Employee)  
✅ **Granular Permissions** - Fine-tuned access control  
✅ **Resource-Level Auth** - Laravel Policies  
✅ **Field-Level Auth** - Response filtering  
✅ **Audit Trail** - All auth events logged  

### Security Measures

✅ **Password Hashing** - bcrypt cost factor 12  
✅ **Rate Limiting** - Protection against brute force  
✅ **Token Security** - Secure generation and storage  
✅ **CORS Configuration** - Proper origin control  
✅ **Security Headers** - XSS, CSP, HSTS protection  

---

**Document Status:** ✅ Complete  
**Next Step:** Design queue architecture (Task 1.9)

