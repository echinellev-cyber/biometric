<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ACI Login Form</title>

<!-- Favicon -->
<link rel="icon" type="image/png" href="../images/logo.png">
<link rel="apple-touch-icon" href="../images/logo.png">
<link rel="shortcut icon" href="../images/logo.png">

<!-- Styles & Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100..900&display=swap" rel="stylesheet">
 <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            position: relative;
            background: radial-gradient(circle, white, #c5c067);
            min-height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Watermark Background */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
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

        /* Initial Circle Container Styles */
        .circle-container {
            position: absolute;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            transition: opacity 0.5s ease;
            z-index: 1000;
            
        }

        .circle {
            background: radial-gradient(circle at center, #ffffff, #e8e8a0);
            border-radius: 50%;
            width: 500px;
            height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: pulse 2s infinite alternate;
           
        }

        .title {
            font-size: 24px;
            color: #007836;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .separator {
            width: 80px;
            height: 2px;
            background-color: #007836;
            margin: 15px auto;
        }

        .company {
            font-size: 16px;
            color: #555;
            line-height: 1.5;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }
            100% {
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            }
        }

        /* Login Container Styles (initially hidden) */
        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 770px;
            max-width: 100%;
            min-height: 480px;
            display: none; /* Initially hidden */
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            width: 50%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            z-index: 2;
        }

        .sign-up {
            left: 0;
            opacity: 0;
            z-index: 1;
        }

        form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }

        span {
            font-size: 14px;
            color: #666;
            display: block;
            margin-bottom: 20px;
        }

        input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

 

        a {
            color: #007836;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
            display: inline-block;
        }

        button {
    background: #007836;
    color: #fff;
    font-size: 14px;
    padding: 12px 45px;
    border: 1px solid rgba(255, 255, 255, 0.3); /* Glass border */
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    backdrop-filter: blur(8px); /* Frosted effect */
    box-shadow:
        0 4px 6px rgba(0, 0, 0, 0.1),
        inset 0 1px 1px rgba(255, 255, 255, 0.2); /* Inner glow */
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

button:hover {
    background: rgba(0, 120, 54, 0.85); /* More opaque */
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow:
        0 6px 12px rgba(0, 0, 0, 0.15),
        inset 0 1px 3px rgba(255, 255, 255, 0.3),
        0 0 10px rgba(255, 255, 255, 0.3); /* White glow */
    transform: translateY(-2px);
    opacity: 1; /* Override default opacity */
}

button:active {
    transform: translateY(0);
    background: rgba(0, 120, 54, 0.8);
    box-shadow:
        0 2px 4px rgba(0, 0, 0, 0.1),
        inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Optional light reflection animation */
button::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.15),
        transparent
    );
    transition: 0.5s;
}

button:hover::after {
    left: 100%;
}

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 100px 0 0 100px;
            z-index: 1000;
        }

        .toggle {
            background: linear-gradient(to right, #007836, #005a29);
            color: #fff;
            height: 100%;
            width: 200%;
            position: relative;
            left: -100%;
            transition: all 0.6s ease-in-out;
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .toggle-btn {
    background-color: rgba(255, 255, 255, 0.1); /* Semi-transparent white */
    border: 1px solid rgba(255, 255, 255, 0.3); /* Frosted glass border */
    color: #fff;
    margin-top: 20px;
    padding: 10px 30px;
    border-radius: 25px; /* Rounded edges */
    backdrop-filter: blur(10px); /* Frosted glass effect */
    box-shadow: 
        0 4px 6px rgba(0, 0, 0, 0.1), /* Subtle shadow */
        inset 0 0 10px rgba(255, 255, 255, 0.2); /* Inner glow */
    transition: all 0.3s ease-in-out;
    cursor: pointer;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

.toggle-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow: 
        0 6px 12px rgba(0, 0, 0, 0.15),
        inset 0 0 15px rgba(255, 255, 255, 0.3),
        0 0 15px rgba(255, 215, 0, 0.4); /* Golden glow */
    transform: translateY(-2px);
}

.toggle-btn:active {
    transform: translateY(0);
    background-color: rgba(255, 255, 255, 0.15);
}

/* Optional: Add a light reflection effect */
.toggle-btn::after {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(
        to bottom right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    transform: rotate(30deg);
    pointer-events: none;
}

        /* Active states */
        .container.active .sign-in {
            transform: translateX(100%);
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 100px 100px 0;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        .logo {
            width: 80px; /* Adjust size as needed */
            height: 80px; /* Adjust size as needed */
            margin-bottom: 15px;
            object-fit: contain;
        }

        .separator {
    height: 2px;
    width: 300px;
    background: linear-gradient(90deg, transparent, #007836, transparent);
    margin: 15px auto;
}

.error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }
        
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            display: none;
        }

        .error {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 5px;
    margin: 15px auto;
    font-size: 14px;
    width: 100%;
    max-width: 400px;
    text-align: center;
}

.admin-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .admin-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }
    
    #adminForm h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }

      /* Shared header styles for both forms */
    .form-header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 15px;
    }
    
    .form-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
    }
    
    #studentForm h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }
    
    /* Student form specific styles */
    #studentForm {
        max-width: 400px;
        margin: 0 auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
    }
    
    #studentForm span {
        display: block;
        text-align: center;
        margin-bottom: 20px;
        color: #666;
    }
    
    #studentForm input {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 12px 15px;
            font-size: 14px;
            border-radius: 8px;
            width: 90%;
            outline: none;
    }
    
    #studentForm a {
        display: block;
        text-align: center;
        margin: 10px 0;
        color: #2a7f62;
        text-decoration: none;
    }
    
    #studentForm button {
    width: 90%;
    padding: 12px;
    background: #007836;
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3); /* Glass-like border */
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    backdrop-filter: blur(8px); /* Frosted glass effect */
    box-shadow:
        0 2px 10px rgba(0, 0, 0, 0.1),
        inset 0 1px 1px rgba(255, 255, 255, 0.2); /* Inner glow */
    transition: all 0.3s ease;
    text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
    position: relative;
    overflow: hidden;
}

#studentForm button:hover {
    background: rgba(42, 127, 98, 0.85); /* Slightly more opaque */
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow:
        0 4px 15px rgba(0, 0, 0, 0.2),
        inset 0 1px 2px rgba(255, 255, 255, 0.3),
        0 0 10px rgba(255, 255, 255, 0.4); /* Enhanced glow */
    transform: translateY(-1px);
}

#studentForm button:active {
    transform: translateY(0);
    background: rgba(42, 127, 98, 0.8);
    box-shadow:
        0 2px 5px rgba(0, 0, 0, 0.1),
        inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

#studentForm button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.1),
        transparent
    );
    transition: 0.5s;
}

#studentForm button:hover::before {
    left: 100%;
}
    
    .error-message {
        color: #d32f2f;
        text-align: center;
        margin-bottom: 15px;
    }
    
    .success-message {
        color: #388e3c;
        text-align: center;
        margin-bottom: 15px;
    }
    </style>
</head>
<body>
<?php 
// Check for error messages in URL parameters
if (isset($_GET['error'])) {
    echo '<div class="error-message" style="display:block; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>


    <!-- Initial Circle Container -->
    <div class="circle-container" id="circleContainer">
        <div class="circle">
        <img src="../images/logo.png"  alt="Aldersgate College Logo" class="logo">            
        <h1 class="title">Aldersgate College Inc.</h1>
            <h2 class="subtitle">ONE TOUCH, ONE RECORD:</h2>
            <div class="separator"></div>
            <p class="company">Biometric Attendance<br>System</p>
        </div>
    </div>

     <!-- Login Container -->
     <div class="container" id="container">
        <!-- Admin Login Form -->
        <div class="form-container sign-in">
        <form id="adminForm" method="POST" action="login_process.php">     
    <div class="admin-header">
        <img src="../images/logo.png" alt="Aldersgate College Logo" class="admin-logo">
        <h1>ACI Admin Login</h1>
    </div>
    <span>Enter Admin Username and Password</span>
    <div id="adminError" class="error-message"></div>
    <div id="adminSuccess" class="success-message"></div>
    <input type="text" id="adminUsername" name="username" placeholder="Username" required>
    <input type="password" id="adminPassword" name="password" placeholder="Password" required>
    <a href="#">Forget Your Password?</a>
    <a href="create-account.php">Don't have an account? Register</a>
    <button type="submit" id="adminLoginBtn">Log In</button>
</form>

</div>
        
        <!-- Student Login Form -->
        <div class="form-container sign-up">
        <form id="studentForm" method="POST" action="student_login_process.php">
    <div class="form-header">
        <img src="../images/logo.png" alt="Aldersgate College Logo" class="form-logo">
        <h1>ACI Student Login</h1>
    </div>
    <span>Enter Student ID and Password</span>
    <div id="studentError" class="error-message"></div>
    <div id="studentSuccess" class="success-message"></div>
    <input type="text" id="studentId" name="student_id" placeholder="Student ID" required>
    <input type="password" id="studentPassword" name="password" placeholder="Password" required>
    <a href="#">Forget Your Password?</a>
    <button type="submit" id="studentLoginBtn">Log In</button>
</form>
                </div>
        
        <!-- Toggle Panel -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Not a Student?</h1>
                    <button class="toggle-btn" id="showAdmin">Login as Admin</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Not System Admin?</h1>
                    <button class="toggle-btn" id="showStudent">Login as Student</button>
                </div>
            </div>
        </div>
    </div>

<script>
    // Auto-hide error messages after 5 seconds
    setTimeout(function() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(function(msg) {
            if (msg.style.display === 'block') {
                msg.style.display = 'none';
            }
        });
    }, 5000);

    // Initial animation sequence
    setTimeout(function() {
        // Fade out circle
        document.getElementById('circleContainer').style.opacity = '0';
        
        setTimeout(function() {
            // Hide circle and show login form
            document.getElementById('circleContainer').style.display = 'none';
            const container = document.getElementById('container');
            container.style.display = 'block';
            
            // Fade in the login form
            setTimeout(function() {
                container.style.opacity = '1';
            }, 10);
            
            // Setup toggle functionality
            const showAdminBtn = document.getElementById('showAdmin');
            const showStudentBtn = document.getElementById('showStudent');
            const adminForm = document.getElementById('adminForm');
            const studentForm = document.getElementById('studentForm');

            showStudentBtn.addEventListener('click', () => {
                container.classList.add('active');
            });

            showAdminBtn.addEventListener('click', () => {
                container.classList.remove('active');
            });

            adminForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const username = document.getElementById('adminUsername').value.trim();
                const password = document.getElementById('adminPassword').value.trim();
                const errorDiv = document.getElementById('adminError');
                const successDiv = document.getElementById('adminSuccess');
                const loginBtn = document.getElementById('adminLoginBtn');
                
                // Clear previous messages
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                // Basic validation
                if (!username || !password) {
                    errorDiv.textContent = 'Please enter both username and password';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Disable button and show loading
                loginBtn.disabled = true;
                loginBtn.textContent = 'Logging in...';
                
                try {
                    const response = await fetch('login_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        // Success - redirect to dashboard
                        window.location.href = '../admin/dashboard.php';
                    } else {
                        errorDiv.textContent = result.message;
                        errorDiv.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    errorDiv.textContent = 'Network error. Please check your connection.';
                    errorDiv.style.display = 'block';
                } finally {
                    // Re-enable button
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Log In';
                }
            });

            // Student login form handler
            studentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const studentId = document.getElementById('studentId').value.trim();
                const password = document.getElementById('studentPassword').value.trim();
                const errorDiv = document.getElementById('studentError');
                const successDiv = document.getElementById('studentSuccess');
                const loginBtn = document.getElementById('studentLoginBtn');
                
                // Clear previous messages
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                // Basic validation
                if (!studentId || !password) {
                    errorDiv.textContent = 'Please enter both Student ID and Password';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Disable button and show loading
                loginBtn.disabled = true;
                loginBtn.textContent = 'Logging in...';
                
                try {
                    const response = await fetch('student_login_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `student_id=${encodeURIComponent(studentId)}&password=${encodeURIComponent(password)}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        // Success - redirect to student dashboard
                        window.location.href = '../students/dashboard.php';
                    } else {
                        errorDiv.textContent = result.message;
                        errorDiv.style.display = 'block';
                    }
                } catch (error) {
                    console.error('Student login error:', error);
                    errorDiv.textContent = 'Network error. Please check your connection.';
                    errorDiv.style.display = 'block';
                } finally {
                    // Re-enable button
                    loginBtn.disabled = false;
                    loginBtn.textContent = 'Log In';
                }
            });
        }, 500);
    }, 1000);
</script>
</body>
</html>