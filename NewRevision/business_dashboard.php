<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['BusinessID'])) {
    header("Location: business_login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'procook');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$businessID = $_SESSION['BusinessID'];

// Fetch business owner details
$stmt = $conn->prepare("SELECT FullName, Email, PhoneNumber, ProfilePicture, Address, BusinessVerificationStatus, BusinessRegistrationDocuments, RestaurantName, RestaurantAddress, RestaurantContactInfo, RestaurantDescription FROM BusinessProfile WHERE BusinessID = ?");
$stmt->bind_param("i", $businessID);
$stmt->execute();
$stmt->bind_result($fullName, $email, $phoneNumber, $profilePicture, $address, $businessVerificationStatus, $businessRegistrationDocuments, $restaurantName, $restaurantAddress, $restaurantContactInfo, $restaurantDescription);
$stmt->fetch();
$stmt->close();

// Fetch menu items for the logged-in business
$menuStmt = $conn->prepare("SELECT MenuID, FoodName, FoodImage, Price, Description, Category, Availability, Stocks, PreparationTime, SpecialTags FROM menu WHERE BusinessID = ?");
$menuStmt->bind_param("i", $businessID);
$menuStmt->execute();
$menuStmt->bind_result($menuID, $foodName, $foodImage, $price, $description, $category, $availability, $stocks, $preparationTime, $specialTags);

// Store menu items in an array
$menuItems = [];
while ($menuStmt->fetch()) {
    $menuItems[] = [
        'MenuID' => $menuID,
        'FoodName' => $foodName,
        'FoodImage' => $foodImage,
        'Price' => $price,
        'Description' => $description,
        'Category' => $category,
        'Availability' => $availability,
        'Stocks' => $stocks,
        'PreparationTime' => $preparationTime,
        'SpecialTags' => $specialTags
    ];
}
$menuStmt->close();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // Validate current password
    $stmt = $conn->prepare("SELECT Password FROM BusinessProfile WHERE BusinessID = ?");
    $stmt->bind_param("i", $businessID);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($currentPassword, $hashedPassword)) {
        if ($newPassword === $confirmNewPassword) {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE BusinessProfile SET Password = ? WHERE BusinessID = ?");
            $updateStmt->bind_param("si", $newPasswordHash, $businessID);

            if ($updateStmt->execute()) {
                $_SESSION['successMessage'] = "Password updated successfully!";
            } else {
                $_SESSION['errorMessage'] = "Error updating password: " . $conn->error;
            }
            $updateStmt->close();
        } else {
            $_SESSION['errorMessage'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['errorMessage'] = "Current password is incorrect.";
    }
    header("Location: business_dashboard.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $phoneNumber = $_POST['phoneNumber'];
    $address = $_POST['address'];
    $restaurantName = $_POST['restaurantName'];
    $restaurantAddress = $_POST['restaurantAddress'];
    $restaurantContactInfo = $_POST['restaurantContactInfo'];
    $restaurantDescription = $_POST['restaurantDescription'];

    // Handle file uploads
    $profilePicture = $_FILES['profilePicture']['name'];
    $businessRegistrationDocuments = $_FILES['businessRegistrationDocuments']['name'];

    if (!empty($_FILES['profilePicture']['tmp_name'])) {
        move_uploaded_file($_FILES['profilePicture']['tmp_name'], "uploads/" . $profilePicture);
    }
    if (!empty($_FILES['businessRegistrationDocuments']['tmp_name'])) {
        move_uploaded_file($_FILES['businessRegistrationDocuments']['tmp_name'], "uploads/" . $businessRegistrationDocuments);
    }

    // Update profile
    $updateStmt = $conn->prepare("UPDATE BusinessProfile SET FullName = ?, Email = ?, PhoneNumber = ?, ProfilePicture = ?, Address = ?, BusinessRegistrationDocuments = ?, RestaurantName = ?, RestaurantAddress = ?, RestaurantContactInfo = ?, RestaurantDescription = ? WHERE BusinessID = ?");
    $updateStmt->bind_param("sssssssssssi", $fullName, $email, $phoneNumber, $profilePicture, $address, $businessRegistrationDocuments, $restaurantName, $restaurantAddress, $restaurantContactInfo, $restaurantDescription, $businessID);

    if ($updateStmt->execute()) {
        $_SESSION['successMessage'] = "Profile updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error updating profile: " . $conn->error;
    }
    $updateStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Handle adding new menu item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_menu_item'])) {
    $foodName = $_POST['FoodName'];
    $price = $_POST['Price'];
    $category = $_POST['Category'];
    $stocks = $_POST['Stocks'];
    $availability = ($stocks > 0) ? 'Available' : 'Not Available';
    $preparationTime = $_POST['PreparationTime'];
    $description = $_POST['Description'];
    $specialTags = !empty($_POST['SpecialTags']) ? $_POST['SpecialTags'] : null;
    $foodImage = $_FILES['FoodImage']['name'];

    if (!empty($_FILES['FoodImage']['tmp_name'])) {
        move_uploaded_file($_FILES['FoodImage']['tmp_name'], "uploads/" . $foodImage);
    }

    $insertStmt = $conn->prepare("INSERT INTO menu (BusinessID, FoodName, FoodImage, Price, Description, Category, Availability, Stocks, PreparationTime, SpecialTags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param("issssssiis", $businessID, $foodName, $foodImage, $price, $description, $category, $availability, $stocks, $preparationTime, $specialTags);

    if ($insertStmt->execute()) {
        $_SESSION['successMessage'] = "Menu item added successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error adding menu item: " . $conn->error;
    }
    $insertStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Handle updating menu item availability
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_availability'])) {
    $menuID = $_POST['menuID'];
    $availability = $_POST['availability'];

    $updateStmt = $conn->prepare("UPDATE menu SET Availability = ? WHERE MenuID = ?");
    $updateStmt->bind_param("si", $availability, $menuID);

    if ($updateStmt->execute()) {
        $_SESSION['successMessage'] = "Availability updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error updating availability: " . $conn->error;
    }
    $updateStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Handle updating stocks
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stocks'])) {
    $menuID = $_POST['menuID'];
    $stocks = $_POST['stocks'];
    $availability = ($stocks > 0) ? 'Available' : 'Not Available';

    $updateStmt = $conn->prepare("UPDATE menu SET Stocks = ?, Availability = ? WHERE MenuID = ?");
    $updateStmt->bind_param("isi", $stocks, $availability, $menuID);

    if ($updateStmt->execute()) {
        $_SESSION['successMessage'] = "Stocks updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error updating stocks: " . $conn->error;
    }
    $updateStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Handle updating price
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    $menuID = $_POST['menuID'];
    $price = $_POST['price'];

    $updateStmt = $conn->prepare("UPDATE menu SET Price = ? WHERE MenuID = ?");
    $updateStmt->bind_param("di", $price, $menuID);

    if ($updateStmt->execute()) {
        $_SESSION['successMessage'] = "Price updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error updating price: " . $conn->error;
    }
    $updateStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Handle deleting menu item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_item'])) {
    $menuID = $_POST['menuID'];

    $deleteStmt = $conn->prepare("DELETE FROM menu WHERE MenuID = ?");
    $deleteStmt->bind_param("i", $menuID);

    if ($deleteStmt->execute()) {
        $_SESSION['successMessage'] = "Menu item deleted successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error deleting menu item: " . $conn->error;
    }
    $deleteStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Fetch orders from the Checkout table
$orderQuery = "SELECT OrderID, CustomerID, TotalPrice, Status, DeliveryOption, PaymentMethod, OrderTimestamp, EstimatedDeliveryTime, Distance, DeliveryFee FROM Checkout WHERE BusinessID = ? ORDER BY OrderTimestamp DESC";
$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("i", $businessID);
$orderStmt->execute();
$orderStmt->bind_result($orderID, $customerID, $totalPrice, $status, $deliveryOption, $paymentMethod, $orderTimestamp, $estimatedDeliveryTime, $distance, $deliveryFee);

$orders = [];
while ($orderStmt->fetch()) {
    $orders[] = [
        'OrderID' => $orderID,
        'CustomerID' => $customerID,
        'TotalPrice' => $totalPrice,
        'Status' => $status,
        'DeliveryOption' => $deliveryOption,
        'PaymentMethod' => $paymentMethod,
        'OrderTimestamp' => $orderTimestamp,
        'EstimatedDeliveryTime' => $estimatedDeliveryTime,
        'Distance' => $distance,
        'DeliveryFee' => $deliveryFee
    ];
}
$orderStmt->close();

// Fetch customer details for each order
foreach ($orders as &$order) {
    $customerQuery = "SELECT Name, Address, ProfilePicture FROM Customers WHERE CustomerID = ?";
    $customerStmt = $conn->prepare($customerQuery);
    $customerStmt->bind_param("i", $order['CustomerID']);
    $customerStmt->execute();
    $customerStmt->bind_result($customerName, $customerAddress, $customerProfilePicture);
    $customerStmt->fetch();
    $customerStmt->close();

    $order['CustomerName'] = $customerName;
    $order['CustomerAddress'] = $customerAddress;
    $order['CustomerProfilePicture'] = $customerProfilePicture;
}

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $orderID = $_POST['orderID'];
    $newStatus = $_POST['update_status'];

    $updateStmt = $conn->prepare("UPDATE Checkout SET Status = ? WHERE OrderID = ?");
    $updateStmt->bind_param("si", $newStatus, $orderID);

    if ($updateStmt->execute()) {
        $_SESSION['successMessage'] = "Order status updated successfully!";
    } else {
        $_SESSION['errorMessage'] = "Error updating order status: " . $conn->error;
    }

    $updateStmt->close();
    header("Location: business_dashboard.php");
    exit();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProCook - Business Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="businessStyles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2>ProCook</h2>
            <ul>
                <li><a href="#" id="dashboard-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" id="menu-link"><i class="fas fa-utensils"></i> Menu Management</a></li>
                <li><a href="#" id="promotions-link"><i class="fas fa-tag"></i> Promotions</a></li>
                <li><a href="#" id="addmenu-link"><i class="fas fa-plus"></i> Add Menu</a></li>
                <li><a href="#" id="orders-link"><i class="fas fa-book"></i> Orders</a></li>
                <li><a href="#" id="profile-link"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="#" id="notifications-link"><i class="fas fa-bell"></i> Notifications</a></li>
            </ul>
        </div>
        <button class="logout-button" onclick="window.location.href='logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Dashboard Content -->
        <div id="dashboard" class="content-section active">
            <h1>Dashboard</h1>
            <p>Welcome to your dashboard, <?php echo htmlspecialchars($fullName); ?>!</p>
        </div>

        <!-- Menu Content -->
        <div id="menu" class="content-section">
            <h1>Menu</h1>
            <?php if (!empty($menuItems)): ?>
                <div class="menu-grid">
                    <?php foreach ($menuItems as $item): ?>
                        <div class="menu-item" data-category="<?php echo htmlspecialchars($item['Category']); ?>">
                            <!-- Left Section: Image and Basic Details -->
                            <div class="menu-item-left">
                                <div class="menu-item-image">
                                    <?php if (!empty($item['FoodImage'])): ?>
                                        <img src="uploads/<?php echo htmlspecialchars($item['FoodImage']); ?>" alt="<?php echo htmlspecialchars($item['FoodName']); ?>">
                                    <?php else: ?>
                                        <img src="placeholder.jpg" alt="No Image">
                                    <?php endif; ?>
                                </div>
                                <div class="menu-item-details">
                                    <h3><?php echo htmlspecialchars($item['FoodName']); ?></h3>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($item['Category']); ?></p>
                                    <p><strong>Price:</strong> ₱<?php echo htmlspecialchars($item['Price']); ?></p>
                                    <p><strong>Stocks:</strong> <?php echo htmlspecialchars($item['Stocks']); ?></p>
                                    <p><strong>Availability:</strong> <?php echo htmlspecialchars($item['Availability']); ?></p>
                                </div>
                            </div>

                            <!-- Right Section: Description, Special Tags, and Forms -->
                            <div class="menu-item-right">
                                <p><strong>Preparation Time:</strong> <?php echo htmlspecialchars($item['PreparationTime']); ?> mins</p>
                                <p><strong>Description:</strong> <?php echo htmlspecialchars($item['Description']); ?></p>
                                <p><strong>Special Tags:</strong> <?php echo htmlspecialchars($item['SpecialTags']); ?></p>

                                <!-- Update Stocks Form -->
                                <form method="POST" action="" class="update-stocks-form">
                                    <input type="hidden" name="menuID" value="<?php echo $item['MenuID']; ?>">
                                    <label for="stocks">Update Stocks:</label>
                                    <input type="number" name="stocks" value="<?php echo htmlspecialchars($item['Stocks']); ?>" min="0" required>
                                    <button type="submit" name="update_stocks">Update Stocks</button>
                                </form>

                                <!-- Update Price Form -->
                                <form method="POST" action="" class="update-price-form">
                                    <input type="hidden" name="menuID" value="<?php echo $item['MenuID']; ?>">
                                    <label for="price">Update Price:</label>
                                    <input type="number" name="price" value="<?php echo htmlspecialchars($item['Price']); ?>" min="0" step="0.01" required>
                                    <button type="submit" name="update_price">Update Price</button>
                                </form>

                                                                <!-- Delete Menu Item Form -->
                                                                <form method="POST" action="" class="delete-item-form">
                                    <input type="hidden" name="menuID" value="<?php echo $item['MenuID']; ?>">
                                    <button type="submit" name="delete_item" onclick="return confirm('Are you sure you want to delete this item?');">Delete Item</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No menu items found.</p>
            <?php endif; ?>
        </div>

        <!-- Add Menu Content -->
        <div id="addmenu" class="content-section">
            <h1>Add New Menu Item</h1>
            <div class="addmenu-container">
                <?php if (isset($_SESSION['successMessage'])): ?>
                    <p style="color: green;"><?php echo $_SESSION['successMessage']; unset($_SESSION['successMessage']); ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['errorMessage'])): ?>
                    <p style="color: red;"><?php echo $_SESSION['errorMessage']; unset($_SESSION['errorMessage']); ?></p>
                <?php endif; ?>
                <div class="addmenu-form-container">
                    <form method="POST" action="" enctype="multipart/form-data" class="addmenu-form">
                        <!-- Food Name and Price -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="FoodName">Food Name</label>
                                <input type="text" id="FoodName" name="FoodName" placeholder="Enter food name" required>
                            </div>
                            <div class="form-group">
                                <label for="Price">Price</label>
                                <input type="number" id="Price" name="Price" placeholder="Enter price" required>
                            </div>
                        </div>

                        <!-- Category and Stocks -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Category">Category</label>
                                <input type="text" id="Category" name="Category" placeholder="Enter category" required>
                            </div>
                            <div class="form-group">
                                <label for="Stocks">Stocks</label>
                                <input type="number" id="Stocks" name="Stocks" placeholder="Enter stock count" required oninput="updateAvailability()">
                            </div>
                        </div>

                        <!-- Availability and Preparation Time -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="Availability">Availability</label>
                                <select id="Availability" name="Availability" required>
                                    <option value="Available">Available</option>
                                    <option value="Not Available">Not Available</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="PreparationTime">Preparation Time (mins)</label>
                                <input type="number" id="PreparationTime" name="PreparationTime" placeholder="Enter preparation time" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="Description">Description</label>
                            <textarea id="Description" name="Description" placeholder="Enter a brief description" required></textarea>
                        </div>

                        <!-- Special Tags -->
                        <div class="form-group">
                            <label for="SpecialTags">Special Tags</label>
                            <input type="text" id="SpecialTags" name="SpecialTags" placeholder="Enter special tags (optional)">
                        </div>

                        <!-- Food Image Upload -->
                        <div class="form-group">
                            <label for="FoodImage">Food Image</label>
                            <div class="file-upload">
                                <input type="file" id="FoodImage" name="FoodImage" onchange="updateFileName(this)">
                                <label for="FoodImage" class="file-upload-label">
                                    <i class="fas fa-upload"></i> Choose File
                                </label>
                                <span class="file-upload-text" id="file-upload-text">No file chosen</span>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button type="submit" name="add_menu_item" class="btn-submit">
                                <i class="fas fa-plus"></i> Add Menu Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Orders Content -->
        <div id="orders" class="content-section">
            <h1>Orders</h1>
            <?php if (!empty($orders)): ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-item">
                            <div class="order-header">
                                <img src="uploads/<?php echo htmlspecialchars($order['CustomerProfilePicture']); ?>" alt="Customer Profile Picture" class="customer-profile-picture">
                                <div class="customer-info">
                                    <h3><?php echo htmlspecialchars($order['CustomerName']); ?></h3>
                                    <p><?php echo htmlspecialchars($order['CustomerAddress']); ?></p>
                                </div>
                            </div>
                            <div class="order-details">
                                <p><strong>Total Price:</strong> ₱<?php echo htmlspecialchars($order['TotalPrice']); ?></p>
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['Status']); ?></p>
                                <p><strong>Delivery Option:</strong> <?php echo htmlspecialchars($order['DeliveryOption']); ?></p>
                                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['PaymentMethod']); ?></p>
                                <p><strong>Order Timestamp:</strong> <?php echo htmlspecialchars($order['OrderTimestamp']); ?></p>
                                <p><strong>Estimated Delivery Time:</strong> <?php echo htmlspecialchars($order['EstimatedDeliveryTime']); ?> minutes</p>
                                <p><strong>Distance:</strong> <?php echo htmlspecialchars($order['Distance']); ?> km</p>
                                <p><strong>Delivery Fee:</strong> ₱<?php echo htmlspecialchars($order['DeliveryFee']); ?></p>
                            </div>
                            <div class="order-actions">
                                <form method="POST" action="">
                                    <input type="hidden" name="orderID" value="<?php echo $order['OrderID']; ?>">
                                    <?php if ($order['Status'] === 'Pending'): ?>
                                        <button type="submit" name="update_status" value="Confirmed" class="btn-action">Confirm Order</button>
                                    <?php endif; ?>
                                    <?php if ($order['Status'] === 'Confirmed'): ?>
                                        <button type="submit" name="update_status" value="Waiting for a Courier" class="btn-action">Waiting for a Courier</button>
                                    <?php endif; ?>
                                    <?php if ($order['Status'] === 'Pending'): ?>
                                        <button type="submit" name="update_status" value="Rejected" class="btn-action btn-cancel">Reject Order</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>

        <!-- Profile Content -->
        <div id="profile" class="content-section">
            <h1>Business Profile</h1>
            <div class="profile-content">
                <div class="profile-form-container">
                    <!-- Left Column: Personal Information and Restaurant Information -->
                    <div class="profile-form-left">
                        <!-- Personal Information -->
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="profile-form-section">
                                <h2>Personal Information</h2>
                                <div class="form-group">
                                    <label for="fullName">Full Name:</label>
                                    <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email:</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number:</label>
                                    <input type="text" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($phoneNumber); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="address">Address:</label>
                                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="profilePicture">Profile Picture:</label>
                                    <input type="file" id="profilePicture" name="profilePicture">
                                </div>
                            </div>

                            <!-- Restaurant Information -->
                            <div class="profile-form-section">
                                <h2>Restaurant Information</h2>
                                <div class="form-group">
                                    <label for="restaurantName">Restaurant Name:</label>
                                    <input type="text" id="restaurantName" name="restaurantName" value="<?php echo htmlspecialchars($restaurantName); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="restaurantAddress">Restaurant Address:</label>
                                    <input type="text" id="restaurantAddress" name="restaurantAddress" value="<?php echo htmlspecialchars($restaurantAddress); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="restaurantContactInfo">Restaurant Contact Info:</label>
                                    <input type="text" id="restaurantContactInfo" name="restaurantContactInfo" value="<?php echo htmlspecialchars($restaurantContactInfo); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="businessRegistrationDocuments">Business Registration Documents:</label>
                                    <input type="file" id="businessRegistrationDocuments" name="businessRegistrationDocuments">
                                </div>
                                <div class="form-group">
                                    <label for="restaurantDescription">Restaurant Description:</label>
                                    <textarea id="restaurantDescription" name="restaurantDescription" required><?php echo htmlspecialchars($restaurantDescription); ?></textarea>
                                </div>
                            </div>

                            <!-- Update Profile Button -->
                            <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                        </form>
                    </div>

                    <!-- Right Column: Change Password -->
                    <div class="profile-form-right">
                        <form method="POST" action="">
                            <div class="profile-form-section">
                                <h2>Change Password</h2>
                                <div class="form-group">
                                    <label for="currentPassword">Current Password:</label>
                                    <input type="password" id="currentPassword" name="currentPassword">
                                </div>
                                <div class="form-group">
                                    <label for="newPassword">New Password:</label>
                                    <input type="password" id="newPassword" name="newPassword">
                                </div>
                                <div class="form-group">
                                    <label for="confirmNewPassword">Confirm New Password:</label>
                                    <input type="password" id="confirmNewPassword" name="confirmNewPassword">
                                </div>
                                <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Toggling Content Sections -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const links = document.querySelectorAll('.sidebar ul li a');
            const contentSections = document.querySelectorAll('.content-section');

            // Function to show the selected content section
            function showContentSection(target) {
                contentSections.forEach(section => {
                    if (section.id === target) {
                        section.classList.add('active');
                    } else {
                        section.classList.remove('active');
                    }
                });
            }

            // Add click event listeners to sidebar links
            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();

                    // Remove active class from all links
                    links.forEach(l => l.classList.remove('active'));

                    // Add active class to the clicked link
                    this.classList.add('active');

                    // Show the corresponding content section
                    const target = this.getAttribute('id').replace('-link', '');
                    showContentSection(target);
                });
            });

            // Show the dashboard by default
            document.getElementById('dashboard-link').click();
        });

        // Function to update availability based on stocks
        function updateAvailability() {
            const stocks = document.getElementById('Stocks').value;
            const availability = document.getElementById('Availability');
            if (stocks > 0) {
                availability.value = 'Available';
            } else {
                availability.value = 'Not Available';
            }
        }

        // Function to update the file name display
        function updateFileName(input) {
            const fileName = input.files[0] ? input.files[0].name : "No file chosen";
            document.getElementById("file-upload-text").textContent = fileName;
        }
    </script>
</body>
</html>
