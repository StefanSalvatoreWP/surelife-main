# Changelog

All notable changes to Surelife Care and Services will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Auto-select for O.R. Series Code when creating new clients - faster data entry
- Loan banner now shows status info when request already submitted - clearer user experience

## [1.7.1] - 2026-02-27

### Fixed
- Client View - Selected tab now stays active after performing actions
- Payment History - Void payments now display correctly when selected
- Statement of Accounts - Void payments no longer show in payment history
- Phone number formatting and validation improved
- Client Update Form - Telephone field now accepts numbers only
- Client Update Form - Status dropdown and form validation improved
- OR Selection - Fixed issue showing wrong region's Official Receipts
- Client Create - Fixed submit validation and numeric field handling

### Changed
- Login form now has eye icon to show/hide password
- Telephone field label changed from "Home No." to "Telephone"
- Payment method UI improved with better currency formatting
- Client Create page has improved layout and validation

## [1.7.0] - 2026-02-25

### Added
- **Activity Log** - Tracks all system actions so admins can see who did what
- **Loan Admin View** - Admins can now manage and process loan requests
- **Loan Client View** - Eligible clients can submit loan requests directly
- **Completed Memorial** - Mark memorial plans as served when completed
- Address Seeder - Complete Philippine address data with zip codes

### Changed
- Certificate of Full Payment now includes "Not valid without seal" text
- Migrated modals to new Swift modal system for better user experience
- Improved UI alignment and spacing across multiple pages

### Fixed
- Beneficiaries section spacing improved
- Payment page centering fixed
- Spot cash approval table width issues resolved

## [1.6.0] - 2025-01-15

### Added
- Client home view with loan request capability
- Statement of Accounts (SOA) printing
- Certificate of Full Payment (COFP) printing

### Changed
- Improved client information display
- Enhanced payment tracking and history

## [1.5.0] - 2024-12-01

### Added
- Branch management system
- Region-based access control
- Contract and Official Receipt tracking

### Changed
- Improved client registration workflow
- Enhanced package selection process

## [1.0.0] - 2023-01-01

### Added
- Initial release of Surelife Care and Services system
- Client management (create, update, verify, approve)
- Package and payment term management
- Payment recording and tracking
- Document generation (contracts, receipts)
- User authentication and authorization
- Branch and region management
