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
    overflow: auto;
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

/* Registration Container Styles */
.registration-container {
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.3);
    width: 650px;
    max-width: 90%;
    padding: 30px;
    position: relative;
}

.header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 20px;
}

.logo {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.title {
    font-size: 24px;
    color: #007836;
    margin: 0;
}

.subtitle {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
    font-size: 14px;
}

/* Form row for side-by-side inputs */
.form-row {
    display: flex;
    gap: 15px;
    width: 100%;
    margin-bottom: 15px;
}

.form-group {
    flex: 1;
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #555;
    font-weight: 500;
}

.form-group input, .form-group select {
    background-color: #eee;
    border: none;
    margin: 8px 0;
    padding: 12px 15px;
    font-size: 14px;
    border-radius: 8px;
    width: 100%;
    outline: none;
}

.form-group input:focus, .form-group select:focus {
    box-shadow: 0 0 0 2px rgba(0, 120, 54, 0.3);
}

.password-field {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #777;
    background: none;
    border: none;
    font-size: 16px;
}

.password-strength {
    height: 5px;
    margin-top: 5px;
    border-radius: 3px;
    background-color: #eee;
    overflow: hidden;
}

.password-strength-bar {
    height: 100%;
    width: 0;
    transition: width 0.3s, background-color 0.3s;
}

.password-strength-weak {
    background-color: #dc3545;
    width: 33%;
}

.password-strength-medium {
    background-color: #ffc107;
    width: 66%;
}

.password-strength-strong {
    background-color: #28a745;
    width: 100%;
}

.password-hints {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
    line-height: 1.4;
}

/* Required field indicator */
.required {
    color: #dc3545;
}

/* Select input styling */
select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23555' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 16px;
}

/* Input hints */
.input-hint {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
    min-height: 18px;
}

.available {
    color: #28a745;
}

.not-available {
    color: #dc3545;
}

.checking {
    color: #007bff;
}

/* Checkbox styling */
.checkbox-container {
    display: flex;
    align-items: flex-start;
    position: relative;
    padding-left: 30px;
    margin-bottom: 12px;
    cursor: pointer;
    font-size: 14px;
    color: #555;
    user-select: none;
    line-height: 1.5;
}

.checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 20px;
    width: 20px;
    background-color: #eee;
    border-radius: 4px;
}

.checkbox-container:hover input ~ .checkmark {
    background-color: #ddd;
}

.checkbox-container input:checked ~ .checkmark {
    background-color: #007836;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
    display: block;
}

.checkbox-container .checkmark:after {
    left: 7px;
    top: 3px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

/* Submit button */
.submit-btn {
    position: relative;
    margin-top: 10px;
    background: #007836;
    color: #fff;
    font-size: 16px;
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s;
    backdrop-filter: blur(8px);
    box-shadow:
        0 4px 6px rgba(0, 0, 0, 0.1),
        inset 0 1px 1px rgba(255, 255, 255, 0.2);
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.submit-btn:hover {
    background: rgba(0, 120, 54, 0.85);
    border-color: rgba(255, 255, 255, 0.5);
    box-shadow:
        0 6px 12px rgba(0, 0, 0, 0.15),
        inset 0 1px 3px rgba(255, 255, 255, 0.3),
        0 0 10px rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.submit-btn:active {
    transform: translateY(0);
    background: rgba(0, 120, 54, 0.8);
    box-shadow:
        0 2px 4px rgba(0, 0, 0, 0.1),
        inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-loading {
    display: none;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.form-footer {
    margin-top: 20px;
    text-align: center;
    font-size: 14px;
    color: #666;
}

.form-footer a {
    color: #007836;
    text-decoration: none;
    font-weight: 500;
}

.form-footer a:hover {
    text-decoration: underline;
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .registration-container {
        padding: 20px;
        width: 90%;
    }
    
    .header {
        flex-direction: column;
        text-align: center;
    }
    
    .title {
        font-size: 20px;
    }
    
    body::before {
        background-size: 200px;
    }
}
</style>
</head>
<body>
    <div class="registration-container">
        <div class="header">
            <svg class="logo" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="#007836" />
                <text x="50" y="60" font-size="30" text-anchor="middle" fill="white">ACI</text>
            </svg>
            <h1 class="title">Admin Account Registration</h1>
        </div>
        <p class="subtitle">Create a new administrator account for the system</p>
        
        <form id="registerForm">
            <div id="registerError" class="error-message"></div>
            <div id="registerSuccess" class="success-message"></div>
            
            <!-- Full Name beside Email -->
            <div class="form-row">
                <div class="form-group">
                    <label for="fullName">Full Name <span class="required">*</span></label>
                    <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email (optional)">
                </div>
            </div>
            
            <!-- Username beside Role -->
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="regUsername" name="username" placeholder="Choose a username" required minlength="5">
                    <div class="input-hint" id="usernameAvailability"></div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role <span class="required">*</span></label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="student_leader">Student Leader</option>
                        <option value="sas_director">SAS Director</option>
                        <option value="sas_adviser">SAS Adviser</option>
                    </select>
                </div>
            </div>
            
            <!-- Password beside Confirm Password -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <div class="password-field">
                        <input type="password" id="regPassword" name="password" placeholder="Create a strong password" required minlength="8">
                        <button type="button" class="toggle-password" id="toggleRegPassword">👁️</button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                    <div class="password-hints" id="passwordHints">
                        Min. 8 chars with uppercase, lowercase, number, special char
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password <span class="required">*</span></label>
                    <div class="password-field">
                        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm your password" required>
                        <button type="button" class="toggle-password" id="toggleConfirmPassword">👁️</button>
                    </div>
                    <div id="passwordMatch" class="input-hint"></div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" id="agreeTerms" name="agreeTerms" required>
                    <span class="checkmark"></span>
                    I agree to the <a href="#" style="display: inline;">Terms of Service</a> and <a href="#" style="display: inline;">Privacy Policy</a>
                </label>
            </div>
            
            <button type="submit" id="registerBtn" class="submit-btn">
                <span class="btn-text">Register Account</span>
                <span class="btn-loading" style="display: none;">
                    <i class="spinner"></i> Processing...
                </span>
            </button>
            
            <div class="form-footer">
                Already have an account? <a href="login.php" id="showLogin">Login here</a>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const registerForm = document.getElementById('registerForm');
        const regPasswordInput = document.getElementById('regPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const toggleRegPassword = document.getElementById('toggleRegPassword');
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const usernameInput = document.getElementById('regUsername');
        const usernameAvailability = document.getElementById('usernameAvailability');
        const passwordMatch = document.getElementById('passwordMatch');
        
        // Toggle password visibility
        if (toggleRegPassword) {
            toggleRegPassword.addEventListener('click', function() {
                const type = regPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                regPasswordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🔒';
            });
        }
        
        if (toggleConfirmPassword) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🔒';
            });
        }
        
        // Username availability check (debounced)
        let usernameCheckTimeout;
        let currentUsernameCheck = null;
        
        if (usernameInput) {
            usernameInput.addEventListener('input', function() {
                clearTimeout(usernameCheckTimeout);
                const username = this.value.trim();
                
                if (username.length < 5) {
                    usernameAvailability.textContent = 'Username must be at least 5 characters';
                    usernameAvailability.className = 'input-hint not-available';
                    return;
                }
                
                // Cancel any ongoing request
                if (currentUsernameCheck) {
                    currentUsernameCheck.abort();
                }
                
                usernameAvailability.textContent = 'Checking availability...';
                usernameAvailability.className = 'input-hint checking';
                
                usernameCheckTimeout = setTimeout(() => {
                    checkUsernameAvailability(username);
                }, 800);
            });
        }
        
        function checkUsernameAvailability(username) {
            // Create a new AbortController for this request
            currentUsernameCheck = new AbortController();
            
            fetch(`check_username.php?username=${encodeURIComponent(username)}`, {
                signal: currentUsernameCheck.signal
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    usernameAvailability.textContent = 'Username is available';
                    usernameAvailability.className = 'input-hint available';
                } else {
                    usernameAvailability.textContent = data.error || 'Username is already taken';
                    usernameAvailability.className = 'input-hint not-available';
                }
                currentUsernameCheck = null;
            })
            .catch(error => {
                if (error.name === 'AbortError') {
                    // Request was cancelled, do nothing
                    return;
                }
                usernameAvailability.textContent = 'Error checking username availability';
                usernameAvailability.className = 'input-hint not-available';
                currentUsernameCheck = null;
            });
        }
        
        // Password strength indicator
        if (regPasswordInput) {
            regPasswordInput.addEventListener('input', function() {
                const password = this.value;
                checkPasswordStrength(password);
                checkPasswordMatch();
            });
        }
        
        // Confirm password match check
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }
        
        function checkPasswordMatch() {
            const password = regPasswordInput.value;
            const confirm = confirmPasswordInput.value;
            
            if (confirm.length === 0) {
                passwordMatch.textContent = '';
                return;
            }
            
            if (password === confirm) {
                passwordMatch.textContent = 'Passwords match';
                passwordMatch.className = 'input-hint available';
            } else {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.className = 'input-hint not-available';
            }
        }
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let hints = [];
            
            // Check password length
            if (password.length >= 8) strength += 1;
            else hints.push('at least 8 characters');
            
            // Check for mixed case
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            else hints.push('both uppercase and lowercase letters');
            
            // Check for numbers
            if (password.match(/([0-9])/)) strength += 1;
            else hints.push('at least one number');
            
            // Check for special characters
            if (password.match(/([!,@,#,$,%,^,&,*,?,_,~])/)) strength += 1;
            else hints.push('at least one special character');
            
            // Update strength bar
            passwordStrengthBar.className = 'password-strength-bar';
            
            if (strength === 0 && password.length > 0) {
                passwordStrengthBar.classList.add('password-strength-weak');
                document.getElementById('passwordHints').textContent = 'Add: ' + hints.join(', ');
            } else if (strength === 1) {
                passwordStrengthBar.classList.add('password-strength-weak');
                document.getElementById('passwordHints').textContent = 'Weak password. Consider adding: ' + hints.join(', ');
            } else if (strength === 2 || strength === 3) {
                passwordStrengthBar.classList.add('password-strength-medium');
                document.getElementById('passwordHints').textContent = 'Medium strength password. For better security add: ' + hints.join(', ');
            } else if (strength === 4) {
                passwordStrengthBar.classList.add('password-strength-strong');
                document.getElementById('passwordHints').textContent = 'Strong password!';
            } else {
                document.getElementById('passwordHints').textContent = 'Password must contain at least 8 characters with uppercase, lowercase, number, and special character';
            }
        }
        
        // Registration form submission
        if (registerForm) {
            registerForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const fullName = document.getElementById('fullName').value.trim();
                const email = document.getElementById('email').value.trim();
                const username = document.getElementById('regUsername').value.trim();
                const role = document.getElementById('role').value;
                const password = regPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const agreeTerms = document.getElementById('agreeTerms').checked;
                const errorDiv = document.getElementById('registerError');
                const successDiv = document.getElementById('registerSuccess');
                const registerBtn = document.getElementById('registerBtn');
                const btnText = registerBtn.querySelector('.btn-text');
                const btnLoading = registerBtn.querySelector('.btn-loading');

                
                // Clear previous messages
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                
                // Validation
                if (!fullName || !username || !role || !password || !confirmPassword) {
                    errorDiv.textContent = 'Please fill in all required fields';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                if (!agreeTerms) {
                    errorDiv.textContent = 'You must agree to the Terms of Service and Privacy Policy';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                if (password !== confirmPassword) {
                    errorDiv.textContent = 'Passwords do not match';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                if (password.length < 8) {
                    errorDiv.textContent = 'Password must be at least 8 characters long';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Check if username is available
                if (usernameAvailability.classList.contains('not-available')) {
                    errorDiv.textContent = 'Please choose a different username';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // If username is still being checked, wait for it
                if (usernameAvailability.classList.contains('checking')) {
                    errorDiv.textContent = 'Please wait while we check username availability';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Disable button and show loading
                registerBtn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                
                // Prepare form data for submission
                const formData = new FormData();
                formData.append('fullName', fullName);
                formData.append('email', email);
                formData.append('username', username);
                formData.append('role', role);
                formData.append('password', password);
                formData.append('agreeTerms', agreeTerms);
                
                try {
                    // Send registration request to server
                    const response = await fetch('register_admin.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Registration successful
                        successDiv.textContent = result.message || 'Registration successful!';
                        successDiv.style.display = 'block';
                        
                        // Clear form after successful registration
                        setTimeout(() => {
                            registerForm.reset();
                            passwordStrengthBar.style.width = '0';
                            usernameAvailability.textContent = '';
                            passwordMatch.textContent = '';
                            successDiv.style.display = 'none';
                        }, 2000);
                    } else {
                        // Registration failed
                        errorDiv.textContent = result.message || 'Registration failed. Please try again.';
                        errorDiv.style.display = 'block';
                    }
                } catch (error) {
                    errorDiv.textContent = 'Network error. Please try again.';
                    errorDiv.style.display = 'block';
                } finally {
                    // Re-enable button
                    registerBtn.disabled = false;
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>