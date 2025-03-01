<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $licenseNumber = $_POST['licenseNumber'];
    $idProof = $_POST['idProof'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'procook');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $checkEmailQuery = "SELECT Email FROM DeliveryPersonnel WHERE Email = ?";
    $checkStmt = $conn->prepare($checkEmailQuery);
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<p style='color: red; text-align: center;'>Error: Email already exists.</p>";
    } else {
        // Insert new rider into the database
        $insertQuery = "INSERT INTO DeliveryPersonnel (FullName, Email, PhoneNumber, Password, LicenseNumber, IDProof) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssssss", $fullName, $email, $phoneNumber, $password, $licenseNumber, $idProof);

        if ($stmt->execute()) {
            echo "<p style='color: green; text-align: center;'>Registration successful! Please wait for verification.</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }

    $checkStmt->close();
    $conn->close();
}
?>