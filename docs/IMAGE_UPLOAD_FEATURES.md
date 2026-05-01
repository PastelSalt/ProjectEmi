# Image Upload Features Implementation

## Summary
This implementation adds profile picture upload for workers and employers, and optional job area image upload for job postings.

## Database Changes

### Modified: `database/schema.sql`
Added `job_image` column to `job_posts` table:
```sql
job_image VARCHAR(255),
```

## Configuration Changes

### Modified: `config/config.php`
- Added `JOB_IMAGES_DIR` constant for job image storage
- Added job images directory to auto-creation list
- Added `mbstring` extension check at startup

## Features Implemented

### 1. Profile Picture Upload

**For Workers (`dashboard-worker.php`):**
- Added profile picture display in profile panel
- Added upload form with file selection
- Handles JPEG, PNG, WebP (max 2MB)
- Automatically deletes old profile picture when uploading new one
- Displays fallback letter avatar if no picture uploaded

**For Employers (`dashboard-employer.php`):**
- Added employer profile panel with picture display
- Same upload functionality as workers
- Added `upload_profile_picture` action handler

**In Navigation (`includes/header.php`):**
- Shows small circular profile picture in top navigation
- Links to appropriate dashboard
- Shows fallback letter avatar if no picture

**In Messages (`messages.php`):**
- Shows profile pictures in conversation list
- Shows profile picture in chat header
- Updated SQL queries to fetch profile_picture

### 2. Job Area Image Upload

**For Job Posting (`post-job.php`):**
- Added optional "Job Area Image" section to form
- Supports JPEG, PNG, WebP (max 5MB)
- Added file upload processing logic
- Added `enctype="multipart/form-data"` to form
- Updated SQL insert to include `job_image` field

**In Job Details (`job-details.php`):**
- Displays job image prominently if uploaded
- Image displays full-width with max-height of 400px
- Uses object-fit: cover for proper display

## File Storage Structure

```
uploads/
├── profiles/     # Profile pictures (2MB max)
├── jobs/         # Job area images (5MB max)
├── documents/
├── posts/
└── resumes/
```

## Security Features

1. **File Type Validation:** Only allows image/jpeg, image/png, image/webp
2. **File Size Limits:** 2MB for profile pics, 5MB for job images
3. **Unique Filenames:** Uses `uniqid()` with user ID to prevent overwrites
4. **Path Sanitization:** All file paths use `htmlspecialchars()` when displayed
5. **Old File Cleanup:** Automatically deletes old profile pictures when uploading new ones

## Testing Instructions

### Profile Pictures
1. Go to Worker or Employer Dashboard
2. Click "Choose File" in the profile panel
3. Select an image (JPEG, PNG, or WebP, max 2MB)
4. Click "Update Photo"
5. Verify image appears in:
   - Dashboard profile panel
   - Top navigation bar
   - Messages conversation list

### Job Area Images
1. Go to "Post a Job" page
2. Fill in job details
3. Scroll to "Job Area Image (Optional)" section
4. Upload an image (max 5MB)
5. Post the job
6. View job details page to see the image displayed

## Environment Requirements

- PHP `mbstring` extension (now checked at startup)
- Write permissions for `uploads/profiles/` and `uploads/jobs/` directories
- GD or ImageMagick recommended (for future image processing features)

## Future Enhancements

Potential improvements that could be added:
1. Image cropping/resizing to standard dimensions
2. Multiple job images (gallery)
3. Profile picture preview before upload
4. Watermarking on job images
5. Lazy loading for images
6. WebP conversion for better compression
