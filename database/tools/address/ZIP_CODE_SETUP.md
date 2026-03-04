# ZIP Code Setup Guide for Developers

## Quick Start

### Prerequisites
1. MySQL database running
2. PHP 7.4+ installed
3. Laravel project set up

### Option 1: Using PHP Script (Recommended)

**Local Development:**
```bash
cd surelife-main/script
php smart_merge_zips.php
```

**Deployment:**
```bash
cd surelife-main/script
php smart_merge_zipsdeployment.php
```

### Option 2: Using Laravel Seeder

```bash
cd surelife-main
php artisan db:seed --class=CompleteZipCodeSeeder
```

## Database Configuration

### Local (`smart_merge_zips.php`)
Edit these values in the script:
```php
$host = 'localhost';
$dbname = 'surelife';  // Your local DB name
$username = 'root';
$password = '';
```

### Deployment (`smart_merge_zipsdeployment.php`)
```php
$host = '103.38.65.87';
$dbname = 'u928736972_surelife';
$username = 'u928736972_surelife';
$password = 'Surelife@2024';
```

## Troubleshooting

### "Unknown database" error
- Create the database first: `CREATE DATABASE surelife;`
- Or update `$dbname` to match your existing database

### "Table 'tbladdress' doesn't exist"
- Run Laravel migrations: `php artisan migrate`
- Or import the address table structure first

### Cities still missing ZIP codes
- Check if city names in `tbladdress` match `philippine_provinces_and_cities.sql`
- Some cities may have different names (e.g., "CITY OF MANILA" vs "Manila")

## File Structure

```
script/
├── philippine_provinces_and_cities.sql   # Master ZIP code data
├── smart_merge_zips.php                   # Local sync script
├── smart_merge_zipsdeployment.php         # Deployment sync script
└── philippine-provinces-and-cities-sql-0.3/  # Original source
```

## What Each Script Does

1. **smart_merge_zips.php** - Reads SQL file, updates tbladdress with ZIP codes
2. **smart_merge_zipsdeployment.php** - Same as above, for deployment server
3. **CompleteZipCodeSeeder.php** - Laravel seeder that does the same thing

All three read from the same source: `philippine_provinces_and_cities.sql`
