<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if all required fields are set in the $_POST array
    if (isset($_POST['fullName'], $_POST['restaurantName'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['address'])) {
        // Get form data
        $fullName = $_POST['fullName'];
        $restaurantName = $_POST['restaurantName'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
        $address = $_POST['address'];

        // Database connection
        $conn = new mysqli('localhost', 'root', '', 'procook'); // Replace with your database name
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind the SQL statement
        $stmt = $conn->prepare("INSERT INTO BusinessProfile (FullName, RestaurantName, Email, PhoneNumber, Password, Address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $fullName, $restaurantName, $email, $phone, $password, $address);

        // Execute the statement
        if ($stmt->execute()) {
            echo "<p style='color: green; text-align: center;'>Registration successful!</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error: " . $stmt->error . "</p>";
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        echo "<p style='color: red; text-align: center;'>All fields are required!</p>";
    }
}
?>