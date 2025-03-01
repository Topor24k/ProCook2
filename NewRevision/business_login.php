<?php
session_start(); // Start a session to manage user login state

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if email and password are set in the $_POST array
    if (isset($_POST['email'], $_POST['password'])) {
        // Get form data
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'procook'); // Use your database credentials
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and execute the SQL statement to fetch user data
        $stmt = $conn->prepare("SELECT BusinessID, Password FROM BusinessProfile WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if a user with the provided email exists
        if ($stmt->num_rows > 0) {
            // Bind the result to variables
            $stmt->bind_result($businessID, $hashedPassword);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Password is correct, set session variables
                $_SESSION['BusinessID'] = $businessID;
                $_SESSION['Email'] = $email;

                // Redirect to the dashboard or another page
                header("Location: business_dashboard.php"); // Replace with your dashboard page
                exit();
            } else {
                // Password is incorrect
                echo "<p style='color: red; text-align: center;'>Invalid email or password.</p>";
            }
        } else {
            // No user found with the provided email
            echo "<p style='color: red; text-align: center;'>Invalid email or password.</p>";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        // Required fields are missing
        echo "<p style='color: red; text-align: center;'>Email and password are required!</p>";
    }
}
?>