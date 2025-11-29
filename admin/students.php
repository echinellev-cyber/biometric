<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | ACI Biometric System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white, rgba(250, 246, 166, 1));
            overflow-x: hidden;
            display: flex;
        }

        /* Watermark Background - FIXED */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 220px;
            right: 0;
            bottom: 0;
            background-image: url('../images/logo.png');
            background-size: 100%;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        /* Sidebar fixed position */
        .sidebar {
            width: 250px;
            background-color: #007836;
            color: white;
            min-height: 100vh;
            padding: 20px 0;
            transition: all 0.3s;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #444;
            margin-bottom: 20px;
        }

        .sidebar-header h3 {
            color: #fff;
            font-size: 20px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ddd;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover {
            background-color: #444;
            color: white;
        }

        .sidebar-menu a.active {
            background: linear-gradient(to right, #FFD700, #FFEA70) !important;
            color: #333;
        }

        .sidebar-menu i {
            margin-right: 10px;
            font-size: 18px;
        }

        /* Main content margin to account for fixed sidebar */
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            margin-left: 250px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            font-weight: 600;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #666;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007836;
        }

        /* Search and Filter Section */
        .search-section {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            align-items: center;
        }

        .search-bar {
            flex: 1;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: #007836;
            box-shadow: 0 0 0 2px rgba(0, 120, 54, 0.2);
        }

        .search-bar i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #333;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .filter-btn:hover {
            background: #f5f5f5;
        }

        .add-student-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #007836;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .add-student-btn:hover {
            background: #00612b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        /* Students Table */
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .students-table th, 
        .students-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .students-table th {
            background-color: #007836;
            color: white;
            font-weight: 500;
        }

        .students-table tr:nth-child(even) {
            background-color: rgba(0, 120, 54, 0.05);
        }

        .students-table tr:hover {
            background-color: rgba(0, 120, 54, 0.1);
        }

        .status-active {
            color: #007836;
            font-weight: 500;
        }

        .status-inactive {
            color: #e74c3c;
            font-weight: 500;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: #007836;
            color: white;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }

        .page-btn {
            padding: 8px 12px;
            border-radius: 4px;
            background: white;
            color: #333;
            text-decoration: none;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .page-btn.active {
            background: #007836;
            color: white;
            border-color: #007836;
        }

        .page-btn:hover:not(.active) {
            background: #f5f5f5;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-header h2 {
            color: #007836;
            font-size: 22px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #007836;
            color: white;
        }

        .btn-primary:hover {
            background-color: #00612b;
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #ddd;
        }

        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }

        /* Delete Confirmation Modal */
        .delete-modal-content {
            text-align: center;
        }

        .delete-modal-content i {
            font-size: 48px;
            color: #e74c3c;
            margin-bottom: 15px;
        }

        /* View Modal */
        .student-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 10px;
        }

        .detail-label {
            font-weight: 500;
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            color: #333;
            font-size: 16px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                min-height: auto;
                padding: 10px 0;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .sidebar-menu {
                display: flex;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .sidebar-menu li {
                margin-bottom: 0;
                flex-shrink: 0;
            }
            
            .sidebar-menu a {
                padding: 10px 15px;
            }

            .search-section {
                flex-direction: column;
                align-items: stretch;
            }

            .students-table {
                display: block;
                overflow-x: auto;
            }
            
            .student-details {
                grid-template-columns: 1fr;
            }
            
            /* Reset body background for mobile */
            body::before {
                position: absolute;
                left: 0;
                background-size: 60%;
            }
        }

        /* Logout Button */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgb(137, 172, 133);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgb(69, 156, 65);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        
        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .toast.success {
            background-color: #007836;
        }
        
        .toast.error {
            background-color: #e74c3c;
        }
        
        .toast.show {
            opacity: 1;
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header" style="display: flex">
            <img src="../images/logo.png" style="height: 50px; margin-right: 10px;">
            <h4 style="margin-top: 15px">ACI Admin Panel</h4>
        </div>
         <ul class="sidebar-menu" >
            <li><a href="/biometric/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/admin/admin-management.php" ><i class="fas fa-user-shield"></i> Admin Management</a></li>
            <li><a href="#" style="font-size: 14px"  onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/biometric/admin/students.php"  class="active"><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php"><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div>
                    <h1>Student Management</h1>
                    <p>Biometric Attendance System</p>
                    <div class="breadcrumb">ACI Admin Panel » Student Management</div>
                </div>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name">Administrator</div>
                        <div class="user-role">Super Admin</div>
                    </div>
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Search and Action Buttons -->
            <div class="search-section">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search students by name, ID, or course..." id="searchInput">
                </div>
                <a href="#" class="filter-btn">
                    <i class="fas fa-filter"></i> Filters
                </a>
                <a href="#" class="add-student-btn" id="addStudentBtn">
                    <i class="fas fa-plus"></i> Add Student
                </a>
            </div>

            <!-- Students Table -->
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Fingerprint</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
           <?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "biometric";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch students from database
$sql = "SELECT id, uid, student_name, course, year_level, fingerprint_data FROM register_student ORDER BY student_name";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        $hasFingerprint = !empty($row["fingerprint_data"]) ? "Registered" : "Not Registered";
        $iconColor = !empty($row["fingerprint_data"]) ? "#007836" : "#e74c3c";
        $iconClass = !empty($row["fingerprint_data"]) ? "fa-check-circle" : "fa-times-circle";
        
        // Format course name properly
        $course = $row["course"];
        
        // CONVERT YEAR LEVEL TO DISPLAY FORMAT
        $yearLevel = $row["year_level"];
        $yearDisplay = convertYearLevelToDisplay($yearLevel);
        
        echo "<tr data-id='" . htmlspecialchars($row["id"]) . "'>
            <td>" . htmlspecialchars($row["uid"]) . "</td>
            <td>" . htmlspecialchars($row["student_name"]) . "</td>
            <td>" . htmlspecialchars($course) . "</td>
            <td>" . htmlspecialchars($yearDisplay) . "</td>
            <td><i class='fas $iconClass' style='color: $iconColor;'></i> $hasFingerprint</td>
            <td>
                <a href='#' class='action-btn view-btn' title='View' data-id='" . htmlspecialchars($row["id"]) . "'><i class='fas fa-eye'></i></a>
                <a href='#' class='action-btn edit-btn' title='Edit' data-id='" . htmlspecialchars($row["id"]) . "'><i class='fas fa-edit'></i></a>
                <a href='#' class='action-btn delete-btn' title='Delete' data-id='" . htmlspecialchars($row["id"]) . "'><i class='fas fa-trash-alt'></i></a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='no-data'>No students registered yet</td></tr>";
}

// Function to convert year level to display format
function convertYearLevelToDisplay($yearLevel) {
    switch ($yearLevel) {
        case '1': return '1st Year';
        case '2': return '2nd Year';
        case '3': return '3rd Year';
        case '4': return '4th Year';
        case '5': return '5th Year';
        default: return $yearLevel; // fallback
    }
}

$conn->close();
?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <a href="#" class="page-btn"><i class="fas fa-angle-left"></i></a>
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">3</a>
                <a href="#" class="page-btn"><i class="fas fa-angle-right"></i></a>
            </div>
        </div>
    </main>

    <!-- View Student Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Student Details</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="student-details" id="studentDetails">
                <!-- Student details will be populated here by JavaScript -->
            </div>
            <div class="form-actions">
                <button class="btn btn-secondary" id="closeViewBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Student Modal -->
    <div id="studentFormModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="formModalTitle">Add Student</h2>
                <button class="close-btn">&times;</button>
            </div>
            <form id="studentForm">
                <input type="hidden" id="studentId" name="studentId">
                <div class="form-group">
                    <label for="studentName">Full Name</label>
                    <input type="text" id="studentName" name="studentName" required>
                </div>
                <div class="form-group">
                    <label for="studentUid">Student ID</label>
                    <input type="text" id="studentUid" name="studentUid" required>
                </div>
              <div class="form-group">
    <label for="course">Course</label>
    <select id="course" name="course" required>
        <option value="">Select Course</option>
        <option value="BS in Information Technology">BS in Information Technology</option>
        <option value="Bachelor in Elementary Education">Bachelor in Elementary Education</option>
        <option value="BS in Secondary Education">BS in Secondary Education</option>
        <option value="BS in Accountancy">BS in Accountancy</option>
        <option value="BS in Business Administration">BS in Business Administration</option>
        <option value="BS in Business Administration - Marketing Management">BS in Business Administration - Marketing Management</option>
        <option value="BS in Business Administration - Financial Management">BS in Business Administration - Financial Management</option>
        <option value="BS in Hospitality Management">BS in Hospitality Management</option>
        <option value="BS in Tourism Management">BS in Tourism Management</option>
        <option value="BS in Nursing">BS in Nursing</option>
        <option value="BS in Civil Engineering">BS in Civil Engineering</option>
        <option value="BS in Electrical Engineering">BS in Electrical Engineering</option>
        <option value="BS in Criminology">BS in Criminology</option>
    </select>
</div>

                <div class="form-group">
                    <label for="yearLevel">Year Level</label>
                    <select id="yearLevel" name="yearLevel" required>
                        <option value="">Select Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelFormBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveStudentBtn">Save Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content delete-modal-content">
            <div class="modal-header">
                <h2>Confirm Deletion</h2>
                <button class="close-btn">&times;</button>
            </div>
            <i class="fas fa-exclamation-triangle"></i>
            <p>Are you sure you want to delete this student?</p>
            <p>This action cannot be undone.</p>
            <div class="form-actions">
                <button class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
// DOM Elements
const viewModal = document.getElementById('viewModal');
const studentFormModal = document.getElementById('studentFormModal');
const deleteModal = document.getElementById('deleteModal');
const studentDetails = document.getElementById('studentDetails');
const studentForm = document.getElementById('studentForm');
const formModalTitle = document.getElementById('formModalTitle');
const studentIdField = document.getElementById('studentId');
const searchInput = document.getElementById('searchInput');
const addStudentBtn = document.getElementById('addStudentBtn');
const toast = document.getElementById('toast');

// Current student to be deleted
let currentStudentToDelete = null;

// Event Listeners
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const studentId = this.getAttribute('data-id');
        viewStudent(studentId);
    });
});

document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const studentId = this.getAttribute('data-id');
        editStudent(studentId);
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => { 
    btn.addEventListener('click', function() {
        const studentId = this.getAttribute('data-id');
        confirmDelete(studentId);
    });
});

addStudentBtn.addEventListener('click', function() {
    openAddStudentForm();
});

// Close modals when clicking on X button
document.querySelectorAll('.close-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        closeAllModals();
    });
});

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    if (event.target === viewModal) viewModal.style.display = 'none';
    if (event.target === studentFormModal) studentFormModal.style.display = 'none';
    if (event.target === deleteModal) deleteModal.style.display = 'none';
});

// Close buttons
document.getElementById('closeViewBtn').addEventListener('click', function() {
    viewModal.style.display = 'none';
});

document.getElementById('cancelFormBtn').addEventListener('click', function() {
    studentFormModal.style.display = 'none';
});

document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
    deleteModal.style.display = 'none';
});

// Confirm delete
document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentStudentToDelete) {
        deleteStudent(currentStudentToDelete);
    }
});

// Form submission
studentForm.addEventListener('submit', function(e) {
    e.preventDefault();
    saveStudent();
});

// Search functionality
searchInput.addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('.students-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

// Functions
function viewStudent(studentId) {
    // Find the student row in the table
    const row = document.querySelector(`tr[data-id="${studentId}"]`);
    if (!row) {
        showToast('Student not found', 'error');
        return;
    }
    
    // Get data from the table cells
    const cells = row.querySelectorAll('td');
    const studentUid = cells[0].textContent;
    const studentName = cells[1].textContent;
    const course = cells[2].textContent;
    const yearLevel = cells[3].textContent;
    
    // Check fingerprint status
    const fingerprintIcon = cells[4].querySelector('i');
    const hasFingerprint = fingerprintIcon.classList.contains('fa-check-circle');
    
    // Populate the view modal
    studentDetails.innerHTML = `
        <div class="detail-item">
            <div class="detail-label">Student ID</div>
            <div class="detail-value">${studentUid}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Full Name</div>
            <div class="detail-value">${studentName}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Course</div>
            <div class="detail-value">${course}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Year Level</div>
            <div class="detail-value">${yearLevel}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Fingerprint Status</div>
            <div class="detail-value">
                <i class='fas ${hasFingerprint ? 'fa-check-circle' : 'fa-times-circle'}' 
                   style='color: ${hasFingerprint ? '#007836' : '#e74c3c'};'></i> 
                ${hasFingerprint ? 'Registered' : 'Not Registered'}
            </div>
        </div>
    `;
    
    viewModal.style.display = 'flex';
}

function editStudent(studentId) {
    // Find the student row in the table
    const row = document.querySelector(`tr[data-id="${studentId}"]`);
    if (!row) {
        showToast('Student not found', 'error');
        return;
    }
    
    // Get data from the table cells
    const cells = row.querySelectorAll('td');
    const studentUid = cells[0].textContent;
    const studentName = cells[1].textContent;
    const course = cells[2].textContent;
    const yearLevel = cells[3].textContent;
    
    // Populate the edit form
    formModalTitle.textContent = 'Edit Student';
    studentIdField.value = studentId;
    document.getElementById('studentName').value = studentName;
    document.getElementById('studentUid').value = studentUid;
    document.getElementById('course').value = course;
    document.getElementById('yearLevel').value = yearLevel;
    
    studentFormModal.style.display = 'flex';
}

function openAddStudentForm() {
    formModalTitle.textContent = 'Add Student';
    studentForm.reset();
    studentIdField.value = '';
    studentFormModal.style.display = 'flex';
}

function confirmDelete(studentId) {
    currentStudentToDelete = studentId;
    deleteModal.style.display = 'flex';
}

function saveStudent() {
    const formData = new FormData(studentForm);
    const studentId = formData.get('studentId');
    const studentName = formData.get('studentName');
    const studentUid = formData.get('studentUid');
    const course = formData.get('course');
    const yearLevel = formData.get('yearLevel');
    
    if (studentId) {
        // Update existing student - send to server
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_student.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Update the table row
                            const row = document.querySelector(`tr[data-id="${studentId}"]`);
                            if (row) {
                                const cells = row.querySelectorAll('td');
                                cells[0].textContent = studentUid;
                                cells[1].textContent = studentName;
                                cells[2].textContent = course;
                                cells[3].textContent = yearLevel;
                            }
                            
                            showToast('Student updated successfully!', 'success');
                            studentFormModal.style.display = 'none';
                        } else {
                            showToast('Error updating student: ' + response.message, 'error');
                        }
                    } catch (e) {
                        showToast('Error parsing server response', 'error');
                    }
                } else {
                    showToast('Error connecting to server', 'error');
                }
            }
        };
        
        xhr.send('studentId=' + encodeURIComponent(studentId) + 
                 '&studentName=' + encodeURIComponent(studentName) + 
                 '&studentUid=' + encodeURIComponent(studentUid) + 
                 '&course=' + encodeURIComponent(course) + 
                 '&yearLevel=' + encodeURIComponent(yearLevel));
    } else {
        // Add new student - send to server
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_student.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        
                        if (response.success) {
                            // Add new row to table
                            const tbody = document.querySelector('.students-table tbody');
                            
                            // Remove "no data" message if it exists
                            const noDataRow = tbody.querySelector('.no-data');
                            if (noDataRow) {
                                noDataRow.remove();
                            }
                            
                            // Create a new row
                            const newRow = document.createElement('tr');
                            newRow.setAttribute('data-id', response.studentId);
                            newRow.innerHTML = `
                                <td>${studentUid}</td>
                                <td>${studentName}</td>
                                <td>${course}</td>
                                <td>${yearLevel}</td>
                                <td><i class='fas fa-times-circle' style='color: #e74c3c;'></i> Not Registered</td>
                                <td>
                                    <a href='#' class='action-btn view-btn' title='View' data-id='${response.studentId}'><i class='fas fa-eye'></i></a>
                                    <a href='#' class='action-btn edit-btn' title='Edit' data-id='${response.studentId}'><i class='fas fa-edit'></i></a>
                                    <a href='#' class='action-btn delete-btn' title='Delete' data-id='${response.studentId}'><i class='fas fa-trash-alt'></i></a>
                                </td>
                            `;
                            
                            tbody.appendChild(newRow);
                            
                            // Add event listeners to the new buttons
                            addEventListenersToRow(newRow);
                            
                            showToast('Student added successfully!', 'success');
                            studentFormModal.style.display = 'none';
                        } else {
                            showToast('Error adding student: ' + response.message, 'error');
                        }
                    } catch (e) {
                        showToast('Error parsing server response', 'error');
                    }
                } else {
                    showToast('Error connecting to server', 'error');
                }
            }
        };
        
        xhr.send('studentName=' + encodeURIComponent(studentName) + 
                 '&studentUid=' + encodeURIComponent(studentUid) + 
                 '&course=' + encodeURIComponent(course) + 
                 '&yearLevel=' + encodeURIComponent(yearLevel));
    }
}

function deleteStudent(studentId) {
    // Send AJAX request to delete from database
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete_student.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (response.success) {
                        // Remove from table
                        const row = document.querySelector(`tr[data-id="${studentId}"]`);
                        if (row) {
                            row.remove();
                            showToast('Student deleted successfully!', 'success');
                            
                            // Check if table is empty and show "no data" message
                            const tbody = document.querySelector('.students-table tbody');
                            const rows = tbody.querySelectorAll('tr');
                            if (rows.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="6" class="no-data">No students registered yet</td></tr>';
                            }
                        }
                        
                        // Close the delete modal after successful deletion
                        deleteModal.style.display = 'none';
                        currentStudentToDelete = null;
                    } else {
                        showToast('Error deleting student: ' + response.message, 'error');
                    }
                } catch (e) {
                    showToast('Error parsing server response', 'error');
                }
            } else {
                showToast('Error connecting to server', 'error');
            }
        }
    };
    
    xhr.send('studentId=' + encodeURIComponent(studentId));
}

function closeAllModals() {
    viewModal.style.display = 'none';
    studentFormModal.style.display = 'none';
    deleteModal.style.display = 'none';
}

function showToast(message, type) {
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

// Helper function to add event listeners to action buttons in a row
function addEventListenersToRow(row) {
    const viewBtn = row.querySelector('.view-btn');
    const editBtn = row.querySelector('.edit-btn');
    const deleteBtn = row.querySelector('.delete-btn');
    
    if (viewBtn) {
        viewBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            viewStudent(studentId);
        });
    }
    
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            editStudent(studentId);
        });
    }
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            const studentId = this.getAttribute('data-id');
            confirmDelete(studentId);
        });
    }
}

// Add event listeners to all existing rows when page loads
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.students-table tbody tr').forEach(row => {
        addEventListenersToRow(row);
    });
});

// Function to launch biometric app
function launchBiometricApp() {
    const url = 'biometricapp://open?screen=fingerprint_registration';
    try {
        window.location.href = url;
        setTimeout(() => {
            if (document.visibilityState === 'visible') {
                // Optional: Add fallback message if needed
            }
        }, 1500);
    } catch (e) {
        alert('Unable to launch the desktop app.');
    }
}
</script>
</body>
</html>