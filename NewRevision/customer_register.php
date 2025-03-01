<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $address = $_POST['address'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'procook_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $checkEmailQuery = "SELECT Email FROM Customers WHERE Email = ?";
    $checkStmt = $conn->prepare($checkEmailQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<p style='color: red; text-align: center;'>Error: Email already exists.</p>";
    } else {
        // Insert new customer into the database
        $insertQuery = "INSERT INTO Customers (Name, Email, Phone, Password, Address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssss", $name, $email, $phone, $password, $address);

        if ($stmt->execute()) {
            echo "<p style='color: green; text-align: center;'>Registration successful!</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>
