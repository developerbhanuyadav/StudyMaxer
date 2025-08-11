# Studymaxer - Educational Platform

A comprehensive educational platform for managing and accessing study batches, lectures, and course materials.

## Features

### 🏠 Home Page (index.php)
- Displays enrolled batches with beautiful cards
- "Enroll Now" and "Explore" buttons for each batch
- Responsive design with modern UI
- Navigation to admin panel

### 🔐 Admin Panel (admin/admin.php)
- Password-protected admin access (default: `admin123`)
- Fetches batches from API: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000`
- Select individual batches or use "Select All" functionality
- Add multiple batches at once
- Beautiful grid layout with batch information

### 📚 Batch Details (batch.php)
- Shows batch information and preview image
- "Let's Study" button for quick access
- **Today's Classes Section**: Horizontal scrolling display of today's lectures
- **Subjects Section**: Horizontal scrolling display of available subjects
- Filters out PDF content, shows only video lectures
- Click on lectures to start watching

### 📖 Subject Page (subject.php)
- Displays subject information and image
- Shows all chapters/topics for the selected subject
- Statistics for videos, notes, and exercises
- Click on chapters to access lectures

### 📝 Chapter Page (chapter.php)
- Tabbed interface with:
  - **Lectures Tab**: All video lectures for the chapter
  - **Notes Tab**: Coming soon
  - **DPP Tab**: Coming soon
- Lecture cards with duration, status, and date
- Click on lectures to start watching

### 🎥 Video Player (play.php)
- Handles encrypted video URLs
- Responsive video player
- Error handling and loading states
- Keyboard shortcuts (ESC to go back)

## API Integration

The application integrates with several APIs:

1. **Batches API**: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000`
2. **Today's Schedule**: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{batch_id}/todays-schedule`
3. **Subjects**: `https://api.tejtimes.live/api/pw/details/subject.php?batch_id={batch_id}`
4. **Topics/Chapters**: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{batch_id}/subject/{subject_id}/topics`
5. **Lecture Content**: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{batch_id}/subject/{subject_id}/topic/{topic_id}/all-contents?type=vidoes`
6. **Video URL**: `https://pwxavengers-proxy.pw-avengers.workers.dev/api/url?batch_id={batch_id}&schedule_id={schedule_id}`

## File Structure

```
studymaxer/
├── index.php              # Home page with enrolled batches
├── batch.php              # Individual batch details page
├── subject.php            # Subject page with chapters
├── chapter.php            # Chapter page with lectures
├── play.php               # Video player page
├── admin/
│   └── admin.php          # Admin panel for batch management
└── README.md              # This file
```

## Setup Instructions

1. **Upload Files**: Upload all files to your web server
2. **Access Home Page**: Navigate to `index.php`
3. **Admin Access**: Go to `admin/admin.php` and use password: `admin123`
4. **Add Batches**: In admin panel, select and add batches
5. **Start Learning**: Return to home page and explore your enrolled batches

## Features in Detail

### Batch Management
- Fetch batches from external API
- Select multiple batches at once
- Store enrolled batches in localStorage
- Prevent duplicate enrollments

### Lecture Navigation
- Today's classes with live status indicators
- Subject-wise chapter organization
- Lecture filtering (videos only, no PDFs)
- Duration and completion status display

### Video Playback
- Encrypted video URL handling
- Responsive video player
- Error handling and fallbacks
- Loading states and user feedback

### User Experience
- Consistent header across all pages
- Responsive design for all devices
- Smooth animations and transitions
- Intuitive navigation with back buttons
- Loading states and error messages

## Customization

### Change Admin Password
Edit the `ADMIN_PASSWORD` constant in `admin/admin.php`:
```javascript
const ADMIN_PASSWORD = 'your_new_password';
```

### Modify API Endpoints
Update the API URLs in respective files if needed.

### Styling
All styling is done with Bootstrap 5 and custom CSS. Modify the `<style>` sections in each file to customize the appearance.

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Mobile browsers

## Dependencies

- Bootstrap 5.3.0
- Font Awesome 6.0.0
- Modern JavaScript (ES6+)

## Security Notes

- Admin password is stored in client-side JavaScript (consider server-side authentication for production)
- Video URLs are encrypted and handled securely
- All API calls use HTTPS

## Support

For issues or questions:
1. Check browser console for errors
2. Verify API endpoints are accessible
3. Ensure all files are properly uploaded
4. Check file permissions on server

---

**Studymaxer** - Empowering education through technology! 🎓