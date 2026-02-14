# University E-Clearance System

A complete, production-ready Electronic Clearance System designed to automate the manual clearance process for university graduates. Built with **Vanilla JS (Frontend)** and **Core PHP (Backend)**.

## 🚀 Key Features

*   **Role-Based Access Control**: Secure panels for Students, Departments, Accounts, and Admins.
*   **Automated Workflow**: Clearance requests are automatically routed to all relevant departments.
*   **Real-time Tracking**: Students can view their clearance progress via a dynamic status timeline.
*   **Accounts Integration**: Dedicated module for Fee verification (University, Hostel, Transcript, Degree fees).
*   **Mobile Responsive**: Fully responsive UI built with Bootstrap 5 and custom polished CSS.
*   **Secure Authentication**: Session-based login with hashed passwords.

## 🛠 Technology Stack

*   **Frontend**: HTML5, CSS3 (Custom + Bootstrap 5), Vanilla JavaScript
*   **Backend**: PHP 8.x (MVC Structure), PDO
*   **Database**: MySQL
*   **Server**: Apache (XAMPP/WAMP)

## 📦 Installation & Setup

1.  **Deploy Code**:
    *   Place the `EClearence` folder in your server's root directory (e.g., `htdocs` for XAMPP).
    *   Path: `/Applications/XAMPP/xamppfiles/htdocs/EClearence`

2.  **Database Setup**:
    *   Start **Apache** and **MySQL** in XAMPP.
    *   Import the schema logic (already handled by the code, but you can manually import `database/schema.sql` if needed).
    *   Run the seeder logic by visiting: `http://localhost/EClearence/backend/seed.php` (Only needs to be run once).

3.  **Launch**:
    *   Open your browser and navigate to: `http://localhost/EClearence/frontend/index.html`

## 🔑 Login Credentials

The system comes pre-seeded with the following accounts for testing all workflows:

### 🎓 Student Panel
*   **Email**: `student@university.edu`
*   **Password**: `student123`
*   *Access*: Apply for clearance, view status, download certificate.

### 🏛 Department Panels (Approvers)
| Department | Email | Password |
| :--- | :--- | :--- |
| **Library** | `library@university.edu` | `password` |
| **Accounts Section** | `accounts@university.edu` | `password` |
| **Head of Dept (HoD)** | `hod@university.edu` | `password` |
| **Director Academics** | `academics@university.edu` | `password` |
| **Admissions** | `admissions@university.edu` | `password` |
| **Exam / Controller** | `exam@university.edu` | `password` |
| **Hostel Manager** | `hostel@university.edu` | `password` |
| **ICT / IT Center** | `ict@university.edu` | `password` |
| **Cafeteria** | `cafeteria@university.edu` | `password` |
| **CDC** | `cdc@university.edu` | `password` |
| **Chief Proctor** | `proctor@university.edu` | `password` |

### 🛡 Admin Panel
*   **Email**: `admin@university.edu`
*   **Password**: `admin123`
*   *Access*: Manage users, monitor system status.

## 📝 User Guide

1.  **For Students**: Login -> Click "Apply Now" -> Select Purpose (e.g., Degree) -> Submit. Your progress bar will appear.
2.  **For Departments**: Login -> View Pending Requests -> Click "Approve" or "Reject" (with remarks).
3.  **For Accounts**: Login -> Click "Check Dues" -> Mark fees as Paid -> Approve.
4.  **Completion**: Once all steps are Green (Approved), the "Download Certificate" button appears on the Student Dashboard.

## 📄 License
University Internal Use Only.
