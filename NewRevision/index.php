<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Service Enterprise Management System</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="indexStyles.css">
</head>
<body>

    <!-- Header -->
    <header>
        <div class="logo">ProCook</div>
        <nav>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#menu">Menu</a></li>
                <li><a href="#apply">Apply</a></li>
                <li><a href="#reviews">Reviews</a></li>
                <li><a href="#social-contact">Contact</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <a href="#" class="btn" onclick="openPopup('customerLoginPopup')">Customer Login</a>
            <a href="#" class="btn" onclick="openPopup('businessLoginPopup')">Business Login</a>
            <a href="#" class="btn" onclick="openPopup('riderLoginPopup')">Rider Login</a>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="section hero">
        <h1>Welcome to Our Food Service Enterprise Management System</h1>
        <p>Order food, manage your restaurant, or deliver meals seamlessly with our platform.</p>
        <a href="#menu" class="btn">Explore Menu</a>
    </section>

    <!-- About Section -->
    <section id="about" class="section about">
        <h2>About Us</h2>
        <p>We are a group of students who founded this company in 2025 to help local restaurants gain recognition. Our goal is to start within barangay areas, allowing small restaurants to reach a wider audience.</p>
        <p>Our platform provides a complete ecosystem for customers, business owners, and delivery drivers to connect and thrive.</p>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="section menu">
        <h2>Our Menu</h2>
        <p>Discover delicious meals from top local restaurants.</p>
        <div class="menu-grid">
            <!-- Filipino Local Dishes -->
            <div class="menu-item">
                <img src="Goto.jpg" alt="Goto">
                <h3>Goto</h3>
                <p>A comforting Filipino rice porridge with beef tripe, garlic, and ginger.</p>
                <span>₱80.00</span>
            </div>
            <div class="menu-item">
                <img src="Arroz Caldo.jpg" alt="Arroz Caldo">
                <h3>Arroz Caldo</h3>
                <p>A savory chicken rice porridge with saffron and toasted garlic.</p>
                <span>₱75.00</span>
            </div>
            <!-- Add more dishes as needed -->
        </div>
    </section>

    <!-- Apply Section -->
    <section id="apply" class="section apply">
        <h2>Join Our Platform</h2>
        <div class="apply-container">
            <div class="apply-card">
                <h3>For Business Owners</h3>
                <p>Expand your restaurant's reach within your barangay and beyond. Manage your restaurant online with ease:</p>
                <ul>
                    <li>Add and update your menu items.</li>
                    <li>Track and manage your inventory.</li>
                    <li>Monitor sales and customer feedback.</li>
                    <li>Access a dedicated dashboard for analytics and insights.</li>
                </ul>
                <a href="restaurant_register.php" class="btn">Sign Up as Business Owner</a>
            </div>
            <div class="apply-card">
                <h3>For Delivery Drivers</h3>
                <p>Join our team and earn a competitive income while serving your community:</p>
                <ul>
                    <li>Flexible working hours to suit your schedule.</li>
                    <li>Competitive pay rates and incentives.</li>
                    <li>Training and support to ensure your success.</li>
                    <li>Opportunities for career growth and advancement.</li>
                </ul>
                <a href="delivery_register.php" class="btn">Sign Up as Delivery Driver</a>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="section">
        <h2>Customer Reviews</h2>
        <div class="review-grid">
            <div class="review-card">
                <img src="Profile.jpg" alt="Customer" class="id-picture">
                <p>"Great service! The food was delicious and arrived on time."</p>
                <span>⭐⭐⭐⭐⭐</span>
                <p>- Juan Dela Cruz</p>
            </div>
            <div class="review-card">
                <img src="Profile.jpg" alt="Customer" class="id-picture">
                <p>"I love the variety of restaurants available."</p>
                <span>⭐⭐⭐⭐</span>
                <p>- Maria Santos</p>
            </div>
            <!-- Add more reviews as needed -->
        </div>
    </section>

    <!-- Combined Social and Contact Section -->
    <section id="social-contact" class="section contact">
        <div class="social-contact-container">
            <!-- Social Section -->
            <div class="social-section">
                <h2>Follow Us</h2>
                <div class="social-icons">
                    <a href="https://www.facebook.com" target="_blank">Facebook</a>
                    <a href="https://www.instagram.com" target="_blank">Instagram</a>
                    <a href="https://www.twitter.com" target="_blank">Twitter</a>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <h2>Contact Us</h2>
                <p>Email: support@foodenterprise.com</p>
                <p>Phone: +63 900 123 4567</p>
                <p>Address: Barangay 123, Your City</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 Food Service Enterprise. All rights reserved.</p>
    </footer>

    <!-- Popup for Customer Login -->
    <div id="customerLoginPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('customerLoginPopup')">&times;</span>
            <h2>Customer Login</h2>
            <form action="customer_login.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <button type="submit" name="login">Sign In</button>
            </form>
            <div class="forgot-password">
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
            <div class="register-link">
                <p>Don't have an account? <a href="#" onclick="toggleForms('customerLoginPopup', 'customerRegisterPopup')">Sign up here</a></p>
            </div>
        </div>
    </div>

    <!-- Popup for Business Login -->
    <div id="businessLoginPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('businessLoginPopup')">&times;</span>
            <h2>Business Login</h2>
            <form action="business_login.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <button type="submit" name="login">Sign In</button>
            </form>
            <div class="forgot-password">
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
            <div class="login-link">
                <p>Already have an account? <a href="#" onclick="toggleForms('businessLoginPopup', 'businessRegisterPopup')">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Popup for Rider Login -->
    <div id="riderLoginPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('riderLoginPopup')">&times;</span>
            <h2>Rider Login</h2>
            <form action="rider_login.php" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember Me</label>
                </div>
                <button type="submit" name="login">Sign In</button>
            </form>
            <div class="forgot-password">
                <p><a href="forgot_password.php">Forgot Password?</a></p>
            </div>
            <div class="login-link">
                <p>Already have an account? <a href="#" onclick="toggleForms('riderLoginPopup', 'riderRegisterPopup')">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Popup for Customer Registration -->
    <div id="customerRegisterPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('customerRegisterPopup')">&times;</span>
            <h2>Customer Registration</h2>
            <form action="customer_register.php" method="POST">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="address">Address:</label>
                <textarea id="address" name="address" required></textarea>
                <div class="checkbox-container">
                    <input type="checkbox" id="register-check" name="rememberMe">
                    <label for="register-check">Remember Me</label>
                </div>
                <div class="terms-link">
                    <label><a href="#">Terms & Conditions</a></label>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <div class="login-link">
                <p>Already have an account? <a href="#" onclick="toggleForms('customerRegisterPopup', 'customerLoginPopup')">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Popup for Business Registration -->
    <div id="businessRegisterPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('businessRegisterPopup')">&times;</span>
            <h2>Business Registration</h2>
            <form action="business_register.php" method="POST">
                <label for="fullName">Full Name:</label>
                <input type="text" id="fullName" name="fullName" required>
                <label for="restaurantName">Restaurant Name:</label>
                <input type="text" id="restaurantName" name="restaurantName" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="address">Address:</label>
                <textarea id="address" name="address" required></textarea>
                <div class="checkbox-container">
                    <input type="checkbox" id="register-check" name="rememberMe">
                    <label for="register-check">Remember Me</label>
                </div>
                <div class="terms-link">
                    <label><a href="#">Terms & Conditions</a></label>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <div class="login-link">
                <p>Already have an account? <a href="#" onclick="toggleForms('businessRegisterPopup', 'businessLoginPopup')">Sign in here</a></p>
            </div>
        </div>
    </div>

    <!-- Popup for Rider Registration -->
    <div id="riderRegisterPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('riderRegisterPopup')">&times;</span>
            <h2>Delivery Rider Registration</h2>
            <form action="rider_register.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber">Phone:</label>
                        <input type="text" id="phoneNumber" name="phoneNumber" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="licenseNumber">License Number:</label>
                        <input type="text" id="licenseNumber" name="licenseNumber" required>
                    </div>
                    <div class="form-group">
                        <label for="idProof">ID Proof:</label>
                        <input type="file" id="idProof" name="idProof" accept="image/*, .pdf" required>
                    </div>
                </div>
                <div class="checkbox-container">
                    <input type="checkbox" id="register-check" name="rememberMe">
                    <label for="register-check">Remember Me</label>
                </div>
                <div class="terms-link">
                    <label><a href="#">Terms & Conditions</a></label>
                </div>
                <button type="submit" name="register">Register</button>
            </form>
            <div class="login-link">
                <p>Already have an account? <a href="#" onclick="toggleForms('riderRegisterPopup', 'riderLoginPopup')">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script>
        // Function to open the popup
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'flex';
        }

        // Function to close the popup
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        // Function to toggle between login and registration forms
        function toggleForms(hideId, showId) {
            closePopup(hideId);
            openPopup(showId);
        }

        // Close popup when clicking outside of it
        window.onclick = function(event) {
            if (event.target.classList.contains('popup')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>