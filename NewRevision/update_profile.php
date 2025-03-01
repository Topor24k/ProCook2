<?php
session_start();

if (!isset($_SESSION['customerID'])) {
    header("Location: customer_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'procook_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $customerID = $_SESSION['customerID'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $bio = $_POST['bio'];
    $preferences = $_POST['preferences'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE Customer SET Name = ?, Email = ?, Phone = ?, Address = ?, Gender = ?, Age = ?, Bio = ?, Preferences = ?, Password = ? WHERE CustomerID = ?");
        $stmt->bind_param("sssssisssi", $name, $email, $phone, $address, $gender, $age, $bio, $preferences, $password, $customerID);
    } else {
        $stmt = $conn->prepare("UPDATE Customer SET Name = ?, Email = ?, Phone = ?, Address = ?, Gender = ?, Age = ?, Bio = ?, Preferences = ? WHERE CustomerID = ?");
        $stmt->bind_param("sssssissi", $name, $email, $phone, $address, $gender, $age, $bio, $preferences, $customerID);
    }

    if ($stmt->execute()) {
        header("Location: customer_dashboard.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>