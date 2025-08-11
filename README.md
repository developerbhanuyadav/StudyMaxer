# Studymaxer - Batch Management System

A simple and responsive batch management system for educational content.

## Features

- **Admin Panel**: Add batches from external API to local storage
- **User Interface**: Clean, mobile-responsive display of enrolled batches
- **JSON Storage**: Batches are stored in `batches.json` file
- **Mobile Responsive**: Optimized for all device sizes

## How to Use

### 1. Access Admin Panel
- Go to `admin/admin.php`
- Login with password: `admin123`

### 2. Add Batches
- Browse available batches from the API
- Select individual batches or use "Select All"
- Click "Add Selected Batches" to save to `batches.json`

### 3. View Enrolled Batches
- Go to `index.php` to see all enrolled batches
- Clean, mobile-responsive interface
- No admin buttons visible to users

## File Structure

```
├── index.php              # Main user interface
├── batches.json           # Storage for enrolled batches
├── admin/
│   └── admin.php         # Admin panel for managing batches
├── enroll_batch.php      # API for enrolling in batches
├── batch.php             # Individual batch view
├── chapter.php           # Chapter management
├── subject.php           # Subject management
└── play.php              # Video player
```

## Mobile Responsive Design

The website is fully optimized for mobile devices with:
- Responsive grid layout
- Touch-friendly buttons
- Optimized font sizes
- Proper spacing for small screens

## Technical Details

- **Storage**: JSON file-based storage (`batches.json`)
- **API**: Fetches batches from external API
- **Responsive**: Bootstrap 5 with custom mobile optimizations
- **Session Management**: PHP sessions for admin authentication

## Setup

1. Ensure PHP is installed
2. Place files in your web server directory
3. Make sure `batches.json` is writable
4. Access via web browser

## Admin Access

- **URL**: `/admin/admin.php`
- **Password**: `admin123`
- **Features**: Add/remove batches, view enrolled batches