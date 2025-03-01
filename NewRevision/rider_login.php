<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'procook');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Fetch rider details
    $sql = "SELECT * FROM DeliveryPersonnel WHERE Email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $rider = $result->fetch_assoc();
        if (password_verify($password, $rider['Password'])) {
            // Login successful
            $_SESSION['RiderID'] = $rider['RiderID'];
            $_SESSION['FullName'] = $rider['FullName'];
            header("Location: rider_dashboard.php");
            exit();
        } else {
            $_SESSION['errorMessage'] = "Invalid password.";
        }
    } else {
        $_SESSION['errorMessage'] = "Rider not found.";
    }
}
?>