# Test Accounts Reference Guide

## Quick Login Reference

| Account | Role ID | Role Name | Username | Password | Access Key |
|---------|---------|-----------|----------|----------|------------|
| **Admin** | 1 | Administrator | `SLCadmin` | password123 | a8821dd1f |
| **Level 2** | 2 | Main Branch Staff | `SLClevel2` | password123 | a8821dd1f |
| Level 2 Sales | 11 | New Sales Manager | `SLClevel2sales` | password123 | a8821dd1f |
| Level 2 Old Sales | 16 | Old Sales Manager | `SLClevel2oldsales` | password123 | a8821dd1f |
| Level 2 Approver | 20 | Approver | `SLClevel2approver` | password123 | a8821dd1f |
| Level 3 FSM | 3 | FSM | `SLClevel3fsm` | password123 | a8821dd1f |
| Level 3 FSD | 8 | FSD | `SLClevel3fsd` | password123 | a8821dd1f |
| Level 3 IFSD | 9 | IFSD | `SLClevel3ifsd` | password123 | a8821dd1f |
| Level 3 Collection | 12 | Collection Manager | `SLClevel3collection` | password123 | a8821dd1f |
| Level 3 HR | 17 | Human Resource | `SLClevel3hr` | password123 | a8821dd1f |
| Level 4 FSGA | 4 | FSGA | `SLClevel4fsga` | password123 | a8821dd1f |
| Level 4 Auditor | 13 | Auditor | `SLClevel4auditor` | password123 | a8821dd1f |
| Level 4 Accounting | 18 | Accounting | `SLClevel4accounting` | password123 | a8821dd1f |
| Level 5 Old FSA | 5 | Old Scheme FSA | `SLClevel5oldfsa` | password123 | a8821dd1f |
| Level 5 New FSA | 6 | New Scheme FSA | `SLClevel5newfsa` | password123 | a8821dd1f |
| Level 5 Verifier | 14 | Verifier | `SLClevel5verifier` | password123 | a8821dd1f |
| Level 6 Cashier | 10 | Cashier | `SLClevel6cashier` | password123 | a8821dd1f |
| Level 6 Collector | 15 | Collector | `SLClevel6collector` | password123 | a8821dd1f |

## Login Instructions

1. Navigate to `/login`
2. Enter username with **SLC** prefix (e.g., `SLCadmin`, `SLClevel2`)
3. Enter password: `password123`
4. Enter Access Key: `a8821dd1f`
5. Click Login

## Account Levels Explained

### Level 1 - Administrator
- **Account**: SLCadmin
- **Role**: Full system access
- **Permissions**: All features and settings

### Level 2 - Management
- **Accounts**: SLClevel2, SLClevel2sales, SLClevel2oldsales, SLClevel2approver
- **Roles**: Main Branch Staff, Sales Managers, Approver
- **Permissions**: Management operations, approvals, reports

### Level 3 - Supervisors
- **Accounts**: SLClevel3fsm, SLClevel3fsd, SLClevel3ifsd, SLClevel3collection, SLClevel3hr
- **Roles**: FSM, FSD, IFSD, Collection Manager, HR
- **Permissions**: Department supervision, team management

### Level 4 - Specialists
- **Accounts**: SLClevel4fsga, SLClevel4auditor, SLClevel4accounting
- **Roles**: FSGA, Auditor, Accounting
- **Permissions**: Specialized operations, audits, financial records

### Level 5 - Field Agents
- **Accounts**: SLClevel5oldfsa, SLClevel5newfsa, SLClevel5verifier
- **Roles**: Field Sales Agents (Old/New), Verifier
- **Permissions**: Field operations, document verification

### Level 6+ - Operational
- **Accounts**: SLClevel6cashier, SLClevel6collector
- **Roles**: Cashier, Collector
- **Permissions**: Cash handling, field collections

## Seeder Files

### AllLevelsTestSeeder
Creates all accounts above with AccessKey support.

```bash
php artisan db:seed --class=AllLevelsTestSeeder
```

### AdminAndLevel2Seeder  
Creates only admin and level 2 accounts.

```bash
php artisan db:seed --class=AdminAndLevel2Seeder
```

### TestUsersSeeder
Pre-existing seeder with 16+ accounts (no AccessKey).

```bash
php artisan db:seed --class=TestUsersSeeder
```

## Database Information

### User Credentials Location
**Table**: `tbluser`
- `UserName`: Database username (without SLC prefix)
- `Password`: SHA1 hash of `password123`
- `RoleId`: Role identifier (1, 2, 3, etc.)
- `AccessKey`: `a8821dd1f`

### Staff Records Location
**Table**: `tblstaff`
- Links user accounts to staff information
- Required for login to work

## Important Notes

- ⚠️ **SLC prefix is REQUIRED** for all staff logins
- ⚠️ System removes SLC prefix during validation
- ⚠️ Access Key `a8821dd1f` is required for all accounts
- ⚠️ All accounts use password: `password123`
- ⚠️ Change passwords after first login in production

## Testing Checklist

- [ ] Login with SLCadmin (Level 1)
- [ ] Login with SLClevel2 (Level 2)
- [ ] Login with SLClevel3fsm (Level 3)
- [ ] Login with SLClevel4fsga (Level 4)
- [ ] Login with SLClevel5oldfsa (Level 5)
- [ ] Login with SLClevel6cashier (Level 6)
- [ ] Verify role-specific menus
- [ ] Test modal functionality
- [ ] Check permissions by level
