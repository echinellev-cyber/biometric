<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACI Biometric Attendance System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="apple-touch-icon" href="../images/logo.png">
    <link rel="shortcut icon" href="../images/logo.png">

    <!-- Styles & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100..900&display=swap" rel="stylesheet"> 
    
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

    // Fetch attendance data from database
    $sql = "SELECT 
                se.time_in, 
                se.time_out, 
                se.attendance_status,
                se.date_recorded,
                rs.uid as student_id, 
                rs.student_name, 
                rs.course, 
                rs.year_level,
                ae.event_name,
                ae.date as event_date
            FROM students_events se
            JOIN register_student rs ON se.student_id = rs.id
            JOIN admin_event ae ON se.event_id = ae.event_id
            ORDER BY se.date_recorded DESC";

    $result = $conn->query($sql);
    $attendanceData = array();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $attendanceData[] = $row;
        }
    }
    $conn->close();
    ?>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white,rgb(243, 236, 117));
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
        }

        /* Watermark Background */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 220px;
            right: 0;
            bottom: 0;
            background-image: url('/biometric/images/logo.png');           
            background-size: 100%;
            background-repeat: no-repeat;
            background-position: center;
            opacity: 0.15;
            z-index: -1;
            pointer-events: none;
        }

        /* Sidebar Styles */
        .sidebar {
    width: 250px;
    background-color: #007836;
    color: white;
    min-height: 100vh;
    padding: 20px 0;
    transition: all 0.3s;
    position: fixed; /* ADD THIS */
    height: 100vh; /* ADD THIS */
    overflow-y: auto; /* ADD THIS */
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

        /* Main Content Area */
  .main-content {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    margin-left: 250px; /* ADD THIS */
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

        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        /* Attendance Section */
        .attendance-container {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .attendance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .attendance-title {
            font-size: 24px;
            color: #333;
            font-weight: 600;
        }

        .btn-submit {
            background-color: #007836;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #00612b;
        }

        .table-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            gap: 15px;
            flex-wrap: wrap;
        }

        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            max-width: 400px;
        }

        .search-container label {
            font-weight: 500;
            color: #555;
            white-space: nowrap;
        }

        .search-box {
            padding: 10px 15px;
            border-radius: 25px;
            border: 2px solid #007836;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-box:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 5px rgba(0, 120, 54, 0.3);
        }

        .search-box::placeholder {
            color: #999;
        }

        .clear-search {
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            padding: 5px;
            font-size: 16px;
            transition: color 0.3s;
        }

        .clear-search:hover {
            color: #007836;
        }

        .show-entries {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .show-entries select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .attendance-table th {
            background-color: #007836;
            color: white;
            padding: 12px;
            text-align: left;
        }

        .attendance-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }

        .attendance-table tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .attendance-table tr:hover {
            background-color: #f1f1f1;
        }

        .status-present { color: #28a745; font-weight: bold; }
        .status-late { color: #ffc107; font-weight: bold; }
        .status-absent { color: #dc3545; font-weight: bold; }
        .status-excused { color: #6c757d; font-weight: bold; }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #666;
        }

        .pagination {
            display: flex;
            gap: 5px;
        }

        .pagination button {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background-color: white;
            border-radius: 3px;
            cursor: pointer;
        }

        .pagination button.active {
            background-color: #007836;
            color: white;
        }

        .pagination button:hover:not(:disabled) {
            background-color: #007836;
            color: white;
        }

        .pagination button:disabled {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .search-hint {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            text-align: center;
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
            
            .attendance-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .table-controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-container {
                max-width: 100%;
                width: 100%;
            }
            
            .attendance-table {
                display: block;
                overflow-x: auto;
            }
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background:rgb(137, 172, 133);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background:rgb(69, 156, 65);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
         <ul class="sidebar-menu">
            <li><a href="/biometric/admin/dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="/biometric/admin/admin-management.php" ><i class="fas fa-user-shield"></i> Admin Management</a></li>
            <li><a href="#" style="font-size: 14px"  onclick="launchBiometricApp(); return false;"><i class="fas fa-fingerprint"></i> Fingerprint Management</a></li>
            <li><a href="/biometric/admin/students.php" ><i class="fas fa-users"></i> Student Management</a></li>
            <li><a href="/biometric/admin/events.php" ><i class="fas fa-calendar-alt"></i> Events Management</a></li>            
            <li><a href="/biometric/admin/attendance.php"  class="active"><i class="fas fa-clipboard-list"></i> Attendance Records</a></li>
            <li><a href="/biometric/admin/fines.php"><i class="fas fa-exclamation-circle"></i> Fines Management</a></li>
            <li><a href="/biometric/admin/reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="header">
                <div>
                    <h1>Attendance Management</h1>
                    <div class="breadcrumb">ACI Admin Panel » Attendance Records</div>
                </div>
                <div>
                    <a href="/biometric/login/login.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                 </div>
            </div>

            <div class="attendance-container">
                <div class="attendance-header">
                    <h2 class="attendance-title">Student Attendance Record</h2>
                    <div>
                        <button class="btn-submit" onclick="refreshData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                    </div>
                </div>

                <div class="table-controls">
                    <div class="search-container">
                        <label for="search-input"><i class="fas fa-search"></i></label>
                        <input type="text" id="search-input" class="search-box" 
                               placeholder="Search by student name, ID, course, event, date (YYYY-MM-DD)...">
                        <button class="clear-search" onclick="clearSearch()" title="Clear search">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="show-entries">
                        <span>Show</span>
                        <select id="entries-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>entries</span>
                    </div>
                </div>

                <div class="search-hint">
                    💡 Search by: Student Name • Student ID • Course • Event Name • Date (2025-07-11)
                </div>

                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Event Date</th>
                            <th>Event Name</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-table-body">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>

                <div class="table-footer">
                    <div id="showing-entries">Showing 0 entries</div>
                    <div class="pagination">
                        <button id="prev-btn">Previous</button>
                        <button id="next-btn">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Convert PHP data to JavaScript
        const attendanceData = <?php echo json_encode($attendanceData); ?>;
        
        // Format time function
        function formatTime(dateTimeString) {
            if (!dateTimeString) return 'N/A';
            
            const date = new Date(dateTimeString);
            if (isNaN(date.getTime())) return 'N/A';
            
            return date.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                hour12: true 
            });
        }

        // Format date function
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return 'N/A';
            
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        // Get raw date for filtering (YYYY-MM-DD format)
        function getRawDate(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return '';
            
            return date.toISOString().split('T')[0];
        }

        // Get formatted date for display but also searchable in different formats
        function getSearchableDate(dateString) {
            if (!dateString) return '';
            
            const date = new Date(dateString);
            if (isNaN(date.getTime())) return '';
            
            // Return multiple date formats for searching
            const yyyy_mm_dd = date.toISOString().split('T')[0]; // 2025-07-11
            const mm_dd_yyyy = (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                              date.getDate().toString().padStart(2, '0') + '/' + 
                              date.getFullYear(); // 07/11/2025
            const dd_mm_yyyy = date.getDate().toString().padStart(2, '0') + '/' + 
                              (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                              date.getFullYear(); // 11/07/2025
            
            return `${yyyy_mm_dd} ${mm_dd_yyyy} ${dd_mm_yyyy}`;
        }

        // Get status class
        function getStatusClass(status) {
            switch(status) {
                case 'present': return 'status-present';
                case 'late': return 'status-late';
                case 'absent': return 'status-absent';
                case 'excused': return 'status-excused';
                default: return '';
            }
        }

        // DOM elements
        const entriesSelect = document.getElementById('entries-select');
        const searchInput = document.getElementById('search-input');
        const attendanceTableBody = document.getElementById('attendance-table-body');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const showingEntries = document.getElementById('showing-entries');

        // Pagination variables
        let currentPage = 1;
        let entriesPerPage = 10;
        let filteredData = [...attendanceData];

        // Initialize the table
        function initTable() {
            renderTable();
            updatePagination();
        }

        // Render the table with current data
        function renderTable() {
            attendanceTableBody.innerHTML = '';
            
            // Calculate pagination
            const startIndex = (currentPage - 1) * entriesPerPage;
            const endIndex = startIndex + entriesPerPage;
            const paginatedData = filteredData.slice(startIndex, endIndex);
            
            // Populate table rows
            if (paginatedData.length === 0) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td colspan="9" style="text-align: center; padding: 20px;">
                        No attendance records found matching your search.
                    </td>
                `;
                attendanceTableBody.appendChild(row);
            } else {
                paginatedData.forEach(record => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${formatDate(record.event_date)}</td>
                        <td>${record.event_name || 'N/A'}</td>
                        <td>${record.student_id}</td>
                        <td>${record.student_name}</td>
                        <td>${record.course}</td>
                        <td>${record.year_level}</td>
                        <td>${formatTime(record.time_in)}</td>
                        <td>${formatTime(record.time_out)}</td>
                        <td class="${getStatusClass(record.attendance_status)}">
                            ${record.attendance_status.charAt(0).toUpperCase() + record.attendance_status.slice(1)}
                        </td>
                    `;
                    attendanceTableBody.appendChild(row);
                });
            }
            
            // Update showing entries text
            updateShowingEntriesText();
        }

        function updateShowingEntriesText() {
            const totalEntries = filteredData.length;
            const startIndex = (currentPage - 1) * entriesPerPage;
            const startEntry = totalEntries > 0 ? startIndex + 1 : 0;
            const endEntry = Math.min(startIndex + entriesPerPage, totalEntries);
            showingEntries.textContent = `Showing ${startEntry} to ${endEntry} of ${totalEntries} entries`;
        }

        // Filter data based on search input across all fields
        function filterData() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            
            if (searchTerm === '') {
                filteredData = [...attendanceData];
            } else {
                filteredData = attendanceData.filter(record => {
                    // Create a searchable string with all relevant data
                    const searchableText = `
                        ${record.student_id}
                        ${record.student_name}
                        ${record.course}
                        ${record.year_level}
                        ${record.event_name || ''}
                        ${getSearchableDate(record.event_date)}
                        ${record.attendance_status}
                        ${formatDate(record.event_date).toLowerCase()}
                        ${formatTime(record.time_in).toLowerCase()}
                        ${formatTime(record.time_out).toLowerCase()}
                    `.toLowerCase();

                    return searchableText.includes(searchTerm);
                });
            }
            
            currentPage = 1;
            renderTable();
            updatePagination();
        }

        // Clear search function
        function clearSearch() {
            searchInput.value = '';
            filterData();
            searchInput.focus();
        }

        // Update pagination buttons
        function updatePagination() {
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);
            
            // Disable/enable previous button
            prevBtn.disabled = currentPage === 1;
            
            // Disable/enable next button
            nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        }

        // Refresh data function
        function refreshData() {
            location.reload();
        }

        // Event listeners
        entriesSelect.addEventListener('change', (e) => {
            entriesPerPage = parseInt(e.target.value);
            currentPage = 1;
            renderTable();
            updatePagination();
        });

        searchInput.addEventListener('input', filterData);

        // Add Enter key support for search
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                filterData();
            }
        });

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
                updatePagination();
            }
        });

        nextBtn.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
                updatePagination();
            }
        });

        // Initialize the table on page load
        document.addEventListener('DOMContentLoaded', initTable);
    </script>

    <script>
    function launchBiometricApp() {
        const url = 'biometricapp://open?screen=fingerprint_registration';
        try {
            window.location.href = url;
            setTimeout(() => {
                if (document.visibilityState === 'visible') {
                    // alert('If the app did not open, install/enable the biometric app protocol handler.');
                }
            }, 1500);
        } catch (e) {
            alert('Unable to launch the desktop app.');
        }
    }
    </script>
</body>
</html>