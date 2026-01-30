# 🚀 Google Drive Remote File Upload API

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![Google Drive API](https://img.shields.io/badge/Google%20Drive-API%20v3-yellow)
![Status](https://img.shields.io/badge/status-production--ready-success)

**A high-performance PHP application that enables seamless remote file uploads to Google Drive with real-time progress tracking, intelligent caching, and optimized transfer speeds.**

Perfect for content creators, webmasters, and businesses who need to automatically backup or transfer files from remote URLs directly to Google Drive without manual downloading.

---

## 📋 Table of Contents

1. [Features](#-features)
2. [What This Does (Simple Explanation)](#-what-this-does-simple-explanation)
3. [Who Is This For?](#-who-is-this-for)
4. [Live Demo](#-live-demo)
5. [Requirements](#-requirements)
6. [Installation Guide](#-installation-guide-step-by-step)
7. [Google Cloud Setup](#-google-cloud-setup-detailed-guide)
8. [Configuration](#-configuration)
9. [How to Use](#-how-to-use)
10. [Troubleshooting](#-troubleshooting)
11. [Performance Optimization](#-performance-optimization)
12. [Security Best Practices](#-security-best-practices)
13. [FAQ](#-faq)
14. [Support](#-support)
15. [License](#-license)

---

## ✨ Features

### Core Functionality
- 🌐 **Remote File Upload** - Upload files from any URL directly to Google Drive
- ⚡ **High-Speed Transfer** - Optimized for fast downloads (up to 55 MB/s) and uploads
- 📊 **Real-Time Progress** - Live progress bars with speed metrics and ETA
- 💾 **Intelligent Caching** - Automatically stores files locally to avoid re-downloading
- 🔄 **Resumable Uploads** - Large file support with automatic resumption
- 🔐 **OAuth 2.0 Security** - Secure Google authentication
- 🎯 **Public Link Generation** - Automatically generates shareable Google Drive links
- 📱 **Responsive UI** - Beautiful, mobile-friendly interface

### Technical Highlights
- Dynamic chunk sizing for optimal upload performance
- HTTP/2 support for faster connections
- Memory-optimized for large files (up to 6GB RAM usage)
- No timeout limitations for huge files
- Real-time streaming progress updates
- File existence checking to prevent duplicates

---

## 🎯 What This Does (Simple Explanation)

**Imagine this scenario:**

You have a large file (like a video, archive, or software installer) hosted somewhere on the internet, and you want to save it to your Google Drive. Normally, you would:

1. Download the file to your computer (takes time)
2. Upload it to Google Drive (takes time again)
3. Delete the local copy to free space

**This tool does ALL of that automatically:**

Just give it the file's URL, and it will:
- ✅ Download the file directly to your server
- ✅ Upload it to your Google Drive
- ✅ Give you a shareable Google Drive link
- ✅ Keep a cached copy for future uploads (optional)

**All with beautiful progress bars showing download/upload speed!**

---

## 👥 Who Is This For?

- **Content Creators** - Backup videos, images, and media to Google Drive automatically
- **Website Owners** - Transfer files between servers via Google Drive
- **Digital Marketers** - Archive campaign assets and media files
- **Developers** - Integrate file transfer capabilities into your applications
- **Businesses** - Automate file backup workflows
- **Students** - Backup educational resources and materials
- **Anyone** - Who needs to transfer files from the web to Google Drive without downloading locally

---

## 🎬 Live Demo

**Try it yourself:**
```
http://your-domain.com/index.php?url=https://example.com/file.zip
```

*(Replace with your actual domain after installation)*

---

## 📦 Requirements

### Server Requirements
- **PHP**: Version 7.4 or higher
- **Composer**: PHP dependency manager
- **cURL**: For file downloads (usually pre-installed)
- **OpenSSL**: For secure connections
- **Memory**: At least 512MB available (6GB recommended for large files)
- **Storage**: Sufficient space for temporary file caching

### Google Requirements
- **Google Account** (Free Gmail account works)
- **Google Cloud Console** access (Free)
- **Google Drive API** enabled (Free)

### Knowledge Requirements
- **None!** This guide is written for complete beginners
- Basic ability to upload files to your web server
- Ability to follow step-by-step instructions

---

## 📥 Installation Guide (Step by Step)

### Step 1: Download the Project

**Option A: Using Git (Recommended)**
```bash
cd /path/to/your/website
git clone https://github.com/4kamruzzaman/google-drive-remote-file-upload-api.git
cd google-drive-remote-file-upload-api
```

**Option B: Manual Download**
1. Download the ZIP file from GitHub
2. Extract it to your website directory
3. Rename the folder to `google-drive-uploader` (or any name you prefer)

---

### Step 2: Install Dependencies

Open your terminal or SSH into your server and run:

```bash
cd /path/to/google-drive-uploader
composer install
```

**What this does:** Downloads all required Google API libraries and dependencies.

**Don't have Composer?** Install it first:
```bash
# For Linux/Mac
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# For Windows
# Download from: https://getcomposer.org/download/
```

---

### Step 3: Create Storage Directory

This creates a folder to store downloaded files temporarily:

```bash
mkdir -p ../unpub/files
chmod 755 ../unpub/files
```

**For Windows servers:**
```bash
mkdir ..\unpub\files
```

---

### Step 4: Set Up Google Cloud Project

This is the most important step! Follow carefully:

#### 4.1 Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Select a Project"** at the top
3. Click **"NEW PROJECT"**
4. Enter project name: `Drive Uploader` (or any name)
5. Click **"CREATE"**
6. Wait 10-30 seconds for project creation

#### 4.2 Enable Google Drive API

1. In the search bar at top, type: `Google Drive API`
2. Click on **"Google Drive API"**
3. Click the blue **"ENABLE"** button
4. Wait for it to enable (5-10 seconds)

#### 4.3 Configure OAuth Consent Screen

1. In the left menu, click **"OAuth consent screen"**
2. Select **"External"** (unless you have Google Workspace)
3. Click **"CREATE"**
4. Fill in the required fields:
   - **App name**: `Drive File Uploader`
   - **User support email**: Your email
   - **Developer contact email**: Your email
5. Click **"SAVE AND CONTINUE"**
6. On "Scopes" page, click **"ADD OR REMOVE SCOPES"**
7. Find and check: `../auth/drive.file`
8. Click **"UPDATE"** then **"SAVE AND CONTINUE"**
9. On "Test users" page, click **"ADD USERS"**
10. Enter your Gmail address
11. Click **"ADD"** then **"SAVE AND CONTINUE"**
12. Review and click **"BACK TO DASHBOARD"**

#### 4.4 Create OAuth Credentials

1. In the left menu, click **"Credentials"**
2. Click **"+ CREATE CREDENTIALS"** at the top
3. Select **"OAuth client ID"**
4. Choose **Application type**: "Web application"
5. **Name**: `Drive Uploader Client`
6. Under **"Authorized redirect URIs"**:
   - Click **"+ ADD URI"**
   - Enter: `http://your-domain.com` (replace with YOUR actual domain)
   - For localhost testing: `http://localhost:8000`
7. Click **"CREATE"**
8. A popup appears with your credentials - **DON'T CLOSE IT YET!**

#### 4.5 Download Credentials

1. In the popup, click **"DOWNLOAD JSON"**
2. Save the file (it will be named something like `client_secret_xxx.json`)
3. **Rename** it to: `credentials.json`
4. **Upload/Replace** this file in your project folder
   - It should be at: `google-drive-uploader/credentials.json`

---

### Step 5: Configure Your Domain

Open `index.php` and find line ~103:

**Change it to YOUR domain:**
```php
$redirect_uri = 'http://your-actual-domain.com';
```

⚠️ **Important:** This MUST match the redirect URI you entered in Google Cloud Console!

---

### Step 6: Set Permissions

Make sure your files have the right permissions:

```bash
# Linux/Mac
chmod 644 index.php
chmod 644 credentials.json
chmod 755 ../unpub/files

# The script will create token.json automatically
# But ensure the directory is writable
chmod 755 .
```

---

### Step 7: Test the Installation

#### First-Time Authorization

1. Open your browser and go to:
   ```
   http://your-domain.com/index.php?url=https://example.com/sample.pdf
   ```

2. You'll see: **"One-Time Setup Required"**
3. Click **"Authorize with Google"**
4. Choose your Google account
5. You may see a warning: "Google hasn't verified this app"
   - Click **"Advanced"**
   - Click **"Go to Drive File Uploader (unsafe)"**
   - This is safe because it's YOUR app
6. Click **"Allow"** to grant permissions
7. You'll be redirected back and the upload will start!

#### Test Upload

Try uploading a small test file:
```
http://your-domain.com/index.php?url=https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf
```

You should see:
- ✅ Real-time download progress
- ✅ Upload progress to Google Drive
- ✅ A shareable Google Drive link at the end

---

## ⚙️ Configuration

### Basic Settings

Edit `index.php` to customize:

#### Storage Location
```php
define('LOCAL_FILES_DIR', __DIR__ . '/../unpub/files/');
```
Change this to where you want files cached.

#### Memory Limit
```php
ini_set('memory_limit', '6G');
```
Adjust based on your server capacity (512M minimum, 6G recommended).

#### Chunk Size for Uploads
```php
// For files < 100MB
$chunkSizeBytes = 8 * 1024 * 1024; // 8MB

// For files 100MB - 1GB
$chunkSizeBytes = 32 * 1024 * 1024; // 32MB

// For files > 1GB
$chunkSizeBytes = 128 * 1024 * 1024; // 128MB
```

### Advanced Configuration

#### Disable File Caching
If you don't want to keep files locally, modify the `finally` block at the end of `index.php`:

```php
} finally {
    // Delete the file after upload
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}
```

#### Add File Type Restrictions
Add this before the download function:

```php
$allowed_extensions = ['pdf', 'zip', 'mp4', 'jpg', 'png'];
$file_ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

if (!in_array(strtolower($file_ext), $allowed_extensions)) {
    die("Error: File type not allowed");
}
```

---

## 🎮 How to Use

### Basic Usage

Simply add `?url=` parameter with your file URL:

```
http://your-domain.com/index.php?url=FILE_URL_HERE
```

### Examples

**Upload a PDF:**
```
http://your-domain.com/index.php?url=https://example.com/document.pdf
```

**Upload a video:**
```
http://your-domain.com/index.php?url=https://example.com/video.mp4
```

**Upload a large archive:**
```
http://your-domain.com/index.php?url=https://example.com/backup.zip
```

### Integration in Your Website

#### HTML Link
```html
<a href="http://your-domain.com/index.php?url=https://example.com/file.zip">
    Upload to Google Drive
</a>
```

#### HTML Form
```html
<form action="http://your-domain.com/index.php" method="GET">
    <input type="url" name="url" placeholder="Enter file URL" required>
    <button type="submit">Upload to Drive</button>
</form>
```

#### JavaScript Integration
```javascript
function uploadToDrive(fileUrl) {
    window.location.href = `http://your-domain.com/index.php?url=${encodeURIComponent(fileUrl)}`;
}

// Usage
uploadToDrive('https://example.com/file.pdf');
```

#### PHP Integration
```php
$fileUrl = 'https://example.com/file.zip';
$uploadUrl = 'http://your-domain.com/index.php?url=' . urlencode($fileUrl);
header('Location: ' . $uploadUrl);
```

---

## 🔧 Troubleshooting

### Problem: "One-Time Setup Required" appears every time

**Solution:**
- Check if `token.json` is being created in your project folder
- Ensure the directory is writable: `chmod 755 .`
- Verify your redirect URI matches in both Google Cloud Console and `index.php`

---

### Problem: "Invalid credentials" error

**Solution:**
1. Delete `credentials.json`
2. Go back to Google Cloud Console
3. Download credentials again
4. Make sure you renamed it to `credentials.json` exactly
5. Re-upload to your server

---

### Problem: Download is very slow

**Solution:**
- Check your server's internet speed
- Verify the remote file server isn't rate-limiting
- Try increasing cURL buffer: `CURLOPT_BUFFERSIZE => 2097152` (2MB)

---

### Problem: Upload fails for large files

**Solution:**
1. Increase PHP limits in `php.ini`:
   ```ini
   max_execution_time = 0
   max_input_time = 0
   memory_limit = 6G
   ```
2. Restart your web server
3. Check available disk space

---

### Problem: "Could not create local files directory"

**Solution:**
```bash
# Create directory manually
mkdir -p ../unpub/files

# Set proper permissions
chmod 755 ../unpub/files
chown www-data:www-data ../unpub/files  # Linux Apache
# OR
chown nginx:nginx ../unpub/files  # Linux Nginx
```

---

### Problem: Progress bar not updating

**Solution:**
- Disable output buffering in `php.ini`:
  ```ini
  output_buffering = Off
  zlib.output_compression = Off
  ```
- If using Nginx, add to your config:
  ```nginx
  proxy_buffering off;
  fastcgi_buffering off;
  ```
- Restart web server

---

### Problem: "This app hasn't been verified by Google"

**Solution:**
- This is normal for personal projects
- Click "Advanced" → "Go to [App Name] (unsafe)"
- It's safe because YOU created the app
- To remove warning: Submit app for verification (only needed for public apps)

---

## 🚀 Performance Optimization

### For Shared Hosting

```php
ini_set('memory_limit', '512M');  // Lower limit
$chunkSizeBytes = 4 * 1024 * 1024; // 4MB chunks
```

### For VPS/Dedicated Server

```php
ini_set('memory_limit', '6G');     // High limit
$chunkSizeBytes = 128 * 1024 * 1024; // 128MB chunks
```

### Enable Faster PHP

If available, use PHP-FPM:
```bash
sudo apt install php-fpm
sudo systemctl enable php8.1-fpm
```

### Use HTTP/2

Already enabled in the code via:
```php
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0
```

---

## 🔐 Security Best Practices

### 1. Protect Credentials

Add to `.gitignore`:
```
credentials.json
token.json
/vendor/
```

### 2. Restrict Access

Add authentication to `index.php`:
```php
$valid_token = 'your-secret-token-here';
if (!isset($_GET['token']) || $_GET['token'] !== $valid_token) {
    die('Unauthorized');
}
```

Usage:
```
http://your-domain.com/index.php?token=your-secret-token-here&url=FILE_URL
```

### 3. Whitelist Domains

```php
$allowed_domains = ['example.com', 'trusted-site.com'];
$url_host = parse_url($url, PHP_URL_HOST);

if (!in_array($url_host, $allowed_domains)) {
    die('Domain not allowed');
}
```

### 4. Rate Limiting

```php
session_start();
$max_uploads = 10; // Per hour
$time_window = 3600; // 1 hour in seconds

if (!isset($_SESSION['upload_count'])) {
    $_SESSION['upload_count'] = 0;
    $_SESSION['upload_time'] = time();
}

if (time() - $_SESSION['upload_time'] > $time_window) {
    $_SESSION['upload_count'] = 0;
    $_SESSION['upload_time'] = time();
}

if ($_SESSION['upload_count'] >= $max_uploads) {
    die('Rate limit exceeded. Try again later.');
}

$_SESSION['upload_count']++;
```

### 5. File Size Limits

```php
$max_file_size = 5 * 1024 * 1024 * 1024; // 5GB

$headers = get_headers($url, true);
$content_length = isset($headers['Content-Length']) ? $headers['Content-Length'] : 0;

if ($content_length > $max_file_size) {
    die('File too large (max 5GB)');
}
```

---

## ❓ FAQ

### Q: Is this free to use?
**A:** Yes! Google Drive API has a free tier that's more than enough for personal use.

### Q: How much storage do I get on Google Drive?
**A:** Free Gmail accounts get 15GB. You can purchase more if needed.

### Q: Can I upload files larger than 15GB?
**A:** Yes, the script supports it, but you'll need enough Google Drive storage.

### Q: Do I need coding knowledge?
**A:** No! Just follow this guide step by step.

### Q: Can I use this on shared hosting?
**A:** Yes, but you may need to adjust memory limits and chunk sizes.

### Q: Is my data secure?
**A:** Yes. OAuth 2.0 is Google's secure authentication. No passwords are stored.

### Q: Can multiple people use my installation?
**A:** Yes, but all files will go to YOUR Google Drive. Consider adding authentication.

### Q: What happens if the upload fails midway?
**A:** Google's resumable upload will try to continue from where it stopped.

### Q: Can I customize the interface?
**A:** Absolutely! Edit the HTML/CSS in `index.php` to match your design.

### Q: Does it work with private/authenticated URLs?
**A:** Not by default. You'd need to add authentication headers to the cURL request.

---

## 📞 Support

### Getting Help

1. **Check this README** - Most questions are answered here
2. **GitHub Issues** - Report bugs or request features
3. **Stack Overflow** - Tag your question with `google-drive-api` and `php`

### Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

### Bug Reports

When reporting bugs, include:
- PHP version (`php -v`)
- Error messages from browser console
- Steps to reproduce the issue
- Server environment details

---

## 📄 License

This project is licensed under the **MIT License**.

```
MIT License

Copyright (c) 2026 [Your Name]

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 🙏 Acknowledgments

- Google Drive API Team
- PHP Composer Community
- All contributors and users

---

## 📊 Project Stats

![Stars](https://img.shields.io/github/stars/4kamruzzaman/google-drive-remote-file-upload-api?style=social)
![Forks](https://img.shields.io/github/forks/4kamruzzaman/google-drive-remote-file-upload-api?style=social)
![Issues](https://img.shields.io/github/issues/4kamruzzaman/google-drive-remote-file-upload-api)

---

## 🗺️ Roadmap

- [ ] Multi-file upload support
- [ ] Folder organization in Drive
- [ ] Progress webhook notifications
- [ ] API endpoint for programmatic access
- [ ] Docker container support
- [ ] Admin dashboard
- [ ] Upload history/logs
- [ ] Scheduled uploads
- [ ] File conversion support
- [ ] Team Drive support

---

## 💝 Support the Project

If this project helps you, consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting features
- 🔀 Contributing code
- 📢 Sharing with others

---

**Made with ❤️ for developers who automate everything**

🔗 [GitHub Repository](https://github.com/4kamruzzaman/google-drive-remote-file-upload-api) | 📧 [Report Issues](https://github.com/4kamruzzaman/google-drive-remote-file-upload-api/issues) | 📖 [Documentation](https://github.com/4kamruzzaman/google-drive-remote-file-upload-api/wiki)
