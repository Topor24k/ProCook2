<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli('localhost', 'root', '', 'procook'); // Fixed typo in database name
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT CustomerID, Password FROM Customers WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($customerID, $hashedPassword);

    if ($stmt->fetch() && password_verify($password, $hashedPassword)) {
        $_SESSION['customerID'] = $customerID;
        header("Location: customer_dashboard.php");
    } else {
        echo "<p style='color: red; text-align: center;'>Invalid email or password.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>