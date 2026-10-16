# UIU Connect — The Smart Campus OS

**UIU Connect** is a full-stack academic and campus social platform built exclusively for **United International University (UIU)**. It replaces scattered social media groups and manual notice boards with a unified ecosystem connecting **Students**, **Faculty**, **Alumni**, and **Administrators**.

From AI-powered OCR trimester routine synchronization and a categorized course materials repository to faculty counseling slot booking, an encrypted real-time messenger, an emergency blood bank, and a moderated campus marketplace, UIU Connect centralizes daily university life into a single web application.


## Key Features by Module

### 1. Centralized Social Feed & Campus Announcements (`dashboard.php`)
[assets/screenshots/landing.png]
- **Rich Post Composer**: Publish text updates, upload photos/videos/documents (`jpg`, `png`, `gif`, `mp4`, `webm`, `pdf`, `doc`, `docx`, `zip`), and launch interactive multi-option **Campus Polls**.
- **Granular Privacy Controls**: Set post visibility to `Public`, `Connections`, or `Only Me`, with full post editing and an auditable **Edit History** log.
- **Interactive Engagement**: Real-time AJAX likes, nested two-level comment threads with comment likes, post sharing, saved post collections, and community spam reporting.
- **Official Broadcasts**: Faculty and Administrators can publish highlighted **Campus Announcements** that automatically trigger real-time notifications across all users.
- **Live Global Search**: Instant debounced search across campus users, public posts, and approved marketplace listings from the top navigation bar.


### 2. AI/OCR Trimester Routine Sync & Academic Tracker (`dashboard.php`, `faculty_tasks.php`)
[assets/screenshots/dashboard.png]
- **Client-Side OCR Routine Scanner**: Students upload their ELMS routine image (`PNG`/`JPG`), and **Tesseract.js v5** automatically extracts UIU course codes (`CSE`, `EEE`, `BBA`, `ECO`, `MAT`, `ENG`, `PHY`, `CHE`, `ACT`, `INT`) and enrolls the student into matching courses.
- **Smart Daily Timeline**: Displays **Today's Classes** and **Upcoming (Tomorrow)** schedules with room numbers and start/end times for both students and faculty.
- **Faculty Task & Assignment Manager**: Faculty members create course-specific assignments with deadlines and downloadable resource attachments (`uploads/tasks/`), which appear automatically in enrolled students' pending assignment widgets.


### 3. Course Materials Hub (`courses.php`)
[assets/screenshots/ocr_sync.png]
- **Categorized Study Repository**: Browse and search courses by code or title and filter materials across five tabs: **Mid Questions**, **Final Questions**, **CT Questions**, **Notes**, and **Books**.
- **Direct Peer & Faculty Sharing**: Upload and download study files (`pdf`, `doc`, `docx`, `ppt`, `pptx`, `zip`, `rar`, `txt`, `jpg`, `png`) with one-click spam/fake reporting.

### 4. Faculty Counseling & Appointment System (`counseling.php`)
- **Faculty Slot Management**: Faculty members define recurring weekly counseling hours by day of the week and start/end times.
- **14-Day Dynamic Booking Engine**: Students select a faculty member, view dynamically generated available time slots for the upcoming 14 days, and submit appointment requests with their consultation reason.
- **Approval Workflow & Alerts**: Faculty can **Approve** or **Reject** appointments with instant status notifications delivered to the student.

### 5. Skills, Developer Portfolio & Collaborator Finder (`skills.php`)
- **Student Skill Tags**: Students curate technical and academic skills on their profile.
- **Project Showcase**: Publish software and hardware projects with cover images (`uploads/projects/`), tech stack tags, GitHub repository links, and live demo URLs, complete with community project likes.
- **Smart Collaborator Search**: Search for peers by skill tag or project tech stack and initiate direct messages with one click.

### 6. End-to-End Encrypted Messenger (`messages.php`)
- **AES-256-CBC Encrypted Chat**: Direct messages between campus members are encrypted server-side using OpenSSL `AES-256-CBC` with dynamically generated Initialization Vectors (IV) encoded alongside the payload.
- **Real-Time Polling & Read Receipts**: Live chat window updates every 3 seconds with unread message badges and contact search.

### 7. Smart Campus Blood Bank (`blood_bank.php`)
- **Emergency Blood Requests**: Post urgent blood requirements specifying patient name, blood group, bags needed, hospital name, urgency level (`Normal`, `Urgent`, `Critical`), needed date, and direct phone contact.
- **Donor Directory**: Register as a campus blood donor, track last donation dates, filter available donors by blood group (`A+`, `A-`, `B+`, `B-`, `O+`, `O-`, `AB+`, `AB-`), and mark emergency requests as fulfilled.

### 8. Moderated Campus Marketplace (`marketplace.php`)
- **Buy, Sell & Exchange**: List textbooks, electronics, stationery, and campus essentials with condition badges (`Brand New`, `Like New`, `Used`) and MIME-verified product images (`uploads/marketplace/`).
- **Admin Pre-Approval & Reporting**: Newly listed items enter a `pending` state until approved by an administrator, with community reporting and instant seller contact via the internal messenger.

### 9. Clubs & Societies Hub (`clubs.php`)
- **Campus Organizations Directory**: Filter clubs by category (`Technology`, `Cultural`, `Sports`, `Business`), view live member counts, and join or leave clubs via AJAX.
- **Upcoming Club Events**: Sidebar calendar showcasing upcoming club workshops, tournaments, and campus events.

### 10. Jobs & Alumni Network (`alumni.php`)
- **Career & Internship Board**: Browse and filter job postings (`Full-Time`, `Internship`, `Part-Time`, `Contract`) submitted by alumni and faculty, moderated via admin approval.
- **Alumni & Mentorship Directory**: Graduating students can transition their account to an **Alumni** profile with company, designation, LinkedIn URL, and mentor availability badge.

### 11. Dedicated Admin Portal (`admin/index.php`, `admin/admin_dashboard.php`)
[assets/screenshots/admin.png]
- **System Overview & Analytics**: Real-time counters for total users, active blood requests, pending job posts, pending marketplace items, and reported posts.
- **Course & Faculty Management**: Create, edit, and delete courses, set class schedules/rooms, and assign or unassign faculty instructors.
- **Content Moderation Queues**: Approve or reject pending marketplace listings and job postings, review and remove reported posts, manage blood requests, create/delete campus clubs, manage user roles (`Student`, `Faculty`, `Admin`), and broadcast campus-wide announcements.


## Technology Stack

| Layer | Technologies Used |
| :--- | :--- |
| **Backend** | PHP 8.2+ (Session authentication, MySQLi prepared statements, REST-style AJAX endpoints) |
| **Database** | MariaDB 11.8+ / MySQL (`utf8mb4` character set, relational foreign keys with cascade rules) |
| **Frontend** | HTML5, Custom CSS3 Design System (CSS Variables, Flexbox, CSS Grid), Vanilla JavaScript (ES6+, Fetch API) |
| **AI / OCR Engine** | [Tesseract.js v5](https://www.google.com/search?q=https%3A%2F%2Fcdn.jsdelivr.net%2Fnpm%2Ftesseract.js%405%2Fdist%2Ftesseract.min.js) (Client-side Optical Character Recognition for routine images) |
| **Security & Crypto** | PHP `password_hash()` / `password_verify()` (Bcrypt), OpenSSL `AES-256-CBC` with dynamic IVs (`messages.php`), `finfo_file()` MIME verification |
| **Icons & Typography** | [FontAwesome 6.4.0](https://www.google.com/search?q=https%3A%2F%2Fcdnjs.cloudflare.com%2Fajax%2Flibs%2Ffont-awesome%2F6.4.0%2Fcss%2Fall.min.css), Google Fonts (`Inter`) |

## Project Structure

| Path | Description |
| :--- | :--- |
| `index.php` | Public landing page showcasing platform statistics, core features, and student/faculty reviews |
| `login.php` | Unified login portal for Students, Faculty, and Admins |
| `register.php` | Account registration page for Students and Faculty |
| `dashboard.php` | Main campus feed, post/poll composer, OCR routine sync modal, class schedule, and task widgets |
| `courses.php` | Course Materials Hub with course search, category tabs, and file uploads |
| `counseling.php` | Faculty counseling slot manager and student 14-day appointment booking system |
| `faculty_tasks.php` | Faculty-only portal to assign course tasks, deadlines, and attachment files |
| `skills.php` | Student skill manager, project portfolio showcase, and collaborator search |
| `marketplace.php` | Campus buy/sell marketplace with category filters and admin moderation queue |
| `blood_bank.php` | Emergency blood request board and filterable blood donor directory |
| `clubs.php` | University clubs directory, one-click membership toggle, and upcoming events widget |
| `alumni.php` | Alumni directory, mentorship registration, and moderated job/internship board |
| `messages.php` | Real-time AES-256-CBC encrypted direct messaging interface |
| `notifications.php` | Centralized notification center for likes, comments, connections, announcements, and appointments |
| `profile.php` | User profile page with bio, academic info, connections, skills, and personal post timeline |
| `admin/index.php` | Dedicated dark-themed login portal for System Administrators |
| `admin/admin_dashboard.php` | Comprehensive administrative control panel for courses, users, approvals, clubs, and reports |
| `auth/logout.php` | Session termination and redirect handler |
| `includes/db_connect.php` | MySQLi database connection and session initialization |
| `includes/header.php` | Sticky top navigation bar with live global search, message/notification badges, and Tesseract.js loader |
| `includes/sidebar.php` | Role-aware left navigation sidebar |
| `database/uiu_connect.sql` | Complete MariaDB/MySQL relational database schema and seed data |
| `assets/` | Modular stylesheets (`style.css`, `dashboard.css`, `index.css`, `courses.css`, `counseling.css`, `marketplace.css`, `blood_bank.css`, `clubs.css`, `alumni.css`, `skills.css`, `messages.css`, `profile.css`), `main.js`, and static images |
| `uploads/` | User-uploaded media storage (`marketplace/`, `materials/`, `profiles/`, `projects/`, `tasks/`) |

## Database Schema Overview

All database tables and relationships are pre-configured in `database/uiu_connect.sql`:

| Domain | Tables |
| :--- | :--- |
| **Users & Networking** | `users`, `connections`, `user_skills`, `alumni_profiles`, `notifications`, `messages` |
| **Social Feed & Polls** | `posts`, `post_edit_history`, `likes`, `comments`, `comment_likes`, `poll_votes`, `saved_posts` |
| **Academics & Counseling** | `courses`, `enrollments`, `course_materials`, `tasks`, `faculty_slots`, `appointments` |
| **Campus Life & Career** | `clubs`, `club_members`, `club_events`, `projects`, `project_likes`, `jobs`, `marketplace` |
| **Emergency Services** | `blood_donors`, `blood_requests`, `blood_bank` |

## Installation & Local Setup

### Prerequisites
- **XAMPP**, **WAMP**, **Laragon**, or a standard **LAMP/LEMP** stack
- **PHP** 8.0 or higher (developed on PHP 8.2.12 with `mysqli`, `openssl`, and `fileinfo` extensions enabled)
- **MariaDB** 10.4+ or **MySQL** 8.0+
- Active internet connection on the client browser (required to load FontAwesome icons and the Tesseract.js CDN for OCR routine scanning)

### Step 1: Clone or Extract the Project
Clone the repository into your web server's document root (such as `C:\xampp\htdocs\` on Windows or `/var/www/html/` on Linux):

```bash
git clone https://github.com/mahmudul286/UIU-Connect.git "UIU Connect"
```

### Step 2: Import the Database (`uiu_connect.sql`)
1. Start **Apache** and **MySQL** from your XAMPP Control Panel.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Create a new database named `uiu_connect`.
4. Select the `uiu_connect` database, click the **Import** tab, choose `database/uiu_connect.sql` from the project folder, and click **Import**.

### Step 3: Verify Database Connection
By default, `includes/db_connect.php` is configured for standard local XAMPP credentials (`host = 'localhost'`, `user = 'root'`, `pass = ''`, `dbname = 'uiu_connect'`). Update `includes/db_connect.php` only if your local MySQL server uses a custom username or password.

### Step 4: Ensure Upload Directories Are Writable
Make sure your web server has write permissions for the `uploads/` directory and its subdirectories (`uploads/marketplace/`, `uploads/materials/`, `uploads/profiles/`, `uploads/projects/`, `uploads/tasks/`).

### Step 5: Run the Application
- **Main Campus Portal (Students, Faculty, Alumni)**:
  `http://localhost/UIU%20Connect/`
- **Admin Portal**:
  `http://localhost/UIU%20Connect/admin/`

## Security & Architecture Highlights

- **Password Security**: All user passwords are hashed using PHP's native `password_hash(..., PASSWORD_DEFAULT)` (Bcrypt) and verified via `password_verify()`.
- **Encrypted Direct Messaging**: Private messages in `messages.php` are encrypted before database insertion using `openssl_encrypt()` (`AES-256-CBC`) with a cryptographically random 16-byte Initialization Vector (`openssl_random_pseudo_bytes`) prepended to the ciphertext and Base64-encoded.
- **Upload Validation**: File uploads enforce strict extension allowlists and MIME-type inspection (`finfo_file`) with randomized filenames (`bin2hex(random_bytes(...))`) to prevent path traversal and arbitrary script execution.
- **Role-Based Access Control (RBAC)**: Server-side session checks isolate Student, Faculty, Alumni, and Admin workflows across all pages and AJAX handlers.

## Author

- **Mahmudul Hasan** ([@mahmudul286](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2Fmahmudul286))
- **Repository**: [https://github.com/mahmudul286/UIU-Connect](https://www.google.com/search?q=https%3A%2F%2Fgithub.com%2Fmahmudul286%2FUIU-Connect)
