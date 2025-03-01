<?php
session_start();

if (!isset($_SESSION['customerID'])) {
    header("Location: customer_login.php");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'procook');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch customer details
$customerID = $_SESSION['customerID'];
$sql = "SELECT * FROM Customers WHERE CustomerID = $customerID";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    echo "Customer not found.";
    exit();
}

// Function to calculate distance using Google Maps Distance Matrix API
function calculateDistance($origin, $destination, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&origins=$origin&destinations=$destination&key=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data['status'] === 'OK') {
        return $data['rows'][0]['elements'][0]['distance']['value'] / 1000; // Convert meters to kilometers
    } else {
        return null; // Handle API error
    }
}

// Example usage for distance calculation
$apiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your Google Maps API key
$restaurantAddress = urlencode('Restaurant Address, City, Country'); // Replace with restaurant address
$customerAddress = urlencode($customer['Address']); // Use customer's address from the database

$distance = calculateDistance($restaurantAddress, $customerAddress, $apiKey);

// Base fees and rates
$slowBaseFee = 20; // ₱20 for Slow Delivery
$fastBaseFee = 30; // ₱30 for Fast Delivery
$slowRatePerKm = 4; // ₱4 per km for Slow Delivery
$fastRatePerKm = 5; // ₱5 per km for Fast Delivery

// Calculate delivery fees
$slowDeliveryFee = $slowBaseFee + ($distance * $slowRatePerKm);
$fastDeliveryFee = $fastBaseFee + ($distance * $fastRatePerKm);

// Handle form submission for updating profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $name = $conn->real_escape_string($_POST['name']);
        $phone = $conn->real_escape_string($_POST['phone']);
        $address = $conn->real_escape_string($_POST['address']);
        $gender = $conn->real_escape_string($_POST['gender']);
        $age = (int)$_POST['age'];
        $bio = $conn->real_escape_string($_POST['bio']);
        $foodPreferences = $conn->real_escape_string($_POST['foodPreferences']);

        // Handle profile picture upload
        $profilePicture = $customer['ProfilePicture']; // Default to existing picture
        if ($_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Create directory if it doesn't exist
            }
            $uploadFile = $uploadDir . basename($_FILES['profilePicture']['name']);
            if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadFile)) {
                $profilePicture = $uploadFile;
            }
        }

        $updateSql = "UPDATE Customers SET
                      Name = '$name',
                      Phone = '$phone',
                      Address = '$address',
                      ProfilePicture = '$profilePicture',
                      Gender = '$gender',
                      Age = $age,
                      Bio = '$bio',
                      FoodPreferences = '$foodPreferences'
                      WHERE CustomerID = $customerID";

        if ($conn->query($updateSql) === TRUE) {
            // Update session data
            $_SESSION['ProfilePicture'] = $profilePicture;
            // Redirect to prevent form resubmission
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Error updating profile: " . addslashes($conn->error) . "');</script>";
        }
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmNewPassword = $_POST['confirmNewPassword'];

        if (password_verify($currentPassword, $customer['Password'])) {
            if ($newPassword === $confirmNewPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordSql = "UPDATE Customers SET Password = '$hashedPassword' WHERE CustomerID = $customerID";

                if ($conn->query($updatePasswordSql)) {
                    // Redirect to prevent form resubmission
                    header("Location: customer_dashboard.php");
                    exit();
                } else {
                    echo "<script>alert('Error changing password: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('New passwords do not match.');</script>";
            }
        } else {
            echo "<script>alert('Current password is incorrect.');</script>";
        }
    }

    // Handle adding items to the cart
    if (isset($_POST['add_to_cart'])) {
        $menuID = (int)$_POST['menu_id'];
        $quantity = (int)$_POST['quantity'];

        $itemQuery = "SELECT * FROM Menu WHERE MenuID = $menuID";
        $itemResult = $conn->query($itemQuery);
        $item = $itemResult->fetch_assoc();

        if ($item['Availability'] === 'Available' && $item['Stocks'] >= $quantity) {
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$menuID])) {
                $_SESSION['cart'][$menuID] += $quantity;
            } else {
                $_SESSION['cart'][$menuID] = $quantity;
            }

            // Redirect to prevent form resubmission
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Item is out of stock or not available.');</script>";
        }
    }

    // Handle removing items from the cart
    if (isset($_POST['remove_from_cart'])) {
        $menuID = (int)$_POST['menu_id'];
        if (isset($_SESSION['cart'][$menuID])) {
            unset($_SESSION['cart'][$menuID]);
            // Redirect to prevent form resubmission
            header("Location: customer_dashboard.php");
            exit();
        }
    }

    // Handle checkout
    if (isset($_POST['checkout'])) {
        // Fetch cart items
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $totalPrice = 0;
            $restaurantID = null;

            // Calculate total price and get restaurant ID
            foreach ($_SESSION['cart'] as $menuID => $quantity) {
                $itemQuery = "SELECT * FROM Menu WHERE MenuID = $menuID";
                $itemResult = $conn->query($itemQuery);
                $item = $itemResult->fetch_assoc();

                $totalPrice += $item['Price'] * $quantity;
                $restaurantID = $item['BusinessID']; // Assume all items are from the same restaurant
            }

            // Get delivery option and payment method from the form
            $deliveryOption = $conn->real_escape_string($_POST['delivery_option']); // Fast or Slow
            $paymentMethod = $conn->real_escape_string($_POST['payment_method']); // COD or Online

            // Calculate delivery fee based on the selected option
            $deliveryFee = ($deliveryOption === 'Fast') ? $fastDeliveryFee : $slowDeliveryFee;

            // Calculate estimated delivery time (example: 1 hour for Fast, 2 hours for Slow)
            $estimatedDeliveryTime = ($deliveryOption === 'Fast') ? date('Y-m-d H:i:s', strtotime('+1 hour')) : date('Y-m-d H:i:s', strtotime('+2 hours'));

            // Calculate distance using Google Maps API
            $distance = calculateDistance($restaurantAddress, $customerAddress, $apiKey);

            // If distance calculation fails, set a default value
            if ($distance === null || $distance <= 0) {
                $distance = 0.0; // Default distance in case of failure
            }

            // Insert order into Checkout table
            $insertQuery = "INSERT INTO Checkout (CustomerID, BusinessID, TotalPrice, Status, DeliveryOption, PaymentMethod, OrderTimestamp, EstimatedDeliveryTime, Distance, DeliveryFee)
                            VALUES ($customerID, $restaurantID, $totalPrice, 'Pending', '$deliveryOption', '$paymentMethod', NOW(), '$estimatedDeliveryTime', $distance, $deliveryFee)";

            // Execute the query
            if ($conn->query($insertQuery) === TRUE) {
                $orderID = $conn->insert_id; // Get the auto-generated OrderID

                // Update stock and availability
                foreach ($_SESSION['cart'] as $menuID => $quantity) {
                    $itemQuery = "SELECT * FROM Menu WHERE MenuID = $menuID";
                    $itemResult = $conn->query($itemQuery);
                    $item = $itemResult->fetch_assoc();

                    if ($item['Stocks'] >= $quantity) {
                        $newStock = $item['Stocks'] - $quantity;
                        $updateStockQuery = "UPDATE Menu SET Stocks = $newStock WHERE MenuID = $menuID";
                        $conn->query($updateStockQuery);

                        if ($newStock == 0) {
                            $updateAvailabilityQuery = "UPDATE Menu SET Availability = 'Not Available' WHERE MenuID = $menuID";
                            $conn->query($updateAvailabilityQuery);
                        }
                    } else {
                        echo "<script>alert('Item " . $item['FoodName'] . " is out of stock.');</script>";
                        unset($_SESSION['cart'][$menuID]);
                    }
                }

                // Clear the cart
                unset($_SESSION['cart']);

                // Redirect to prevent form resubmission
                header("Location: customer_dashboard.php");
                exit();
            } else {
                echo "<script>alert('Error placing order: " . addslashes($conn->error) . "');</script>";
            }
        } else {
            echo "<script>alert('Your cart is empty.');</script>";
        }
    }

    // Handle adding items to favorites
    if (isset($_POST['add_to_favorites'])) {
        $menuID = (int)$_POST['menu_id'];
        $insertFavoriteQuery = "INSERT INTO Favorites (CustomerID, MenuID) VALUES ($customerID, $menuID)";
        if ($conn->query($insertFavoriteQuery)) {
            // Redirect to prevent form resubmission
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Error adding to favorites: " . addslashes($conn->error) . "');</script>";
        }
    }

    // Handle removing items from favorites
    if (isset($_POST['remove_from_favorites'])) {
        $menuID = (int)$_POST['menu_id'];
        $deleteFavoriteQuery = "DELETE FROM Favorites WHERE CustomerID = $customerID AND MenuID = $menuID";
        if ($conn->query($deleteFavoriteQuery)) {
            // Redirect to prevent form resubmission
            header("Location: customer_dashboard.php");
            exit();
        } else {
            echo "<script>alert('Error removing from favorites: " . addslashes($conn->error) . "');</script>";
        }
    }

    // Handle order cancellation
    if (isset($_POST['cancel_order'])) {
        $orderID = (int)$_POST['orderID'];

        // Check if the order is still cancellable (Pending or Confirmed status)
        $statusQuery = "SELECT Status FROM Checkout WHERE OrderID = $orderID AND CustomerID = $customerID";
        $statusResult = $conn->query($statusQuery);
        $status = $statusResult->fetch_assoc()['Status'];

        if ($status === 'Pending' || $status === 'Confirmed') {
            // Update the order status to "Cancelled"
            $cancelQuery = "UPDATE Checkout SET Status = 'Cancelled' WHERE OrderID = $orderID";
            if ($conn->query($cancelQuery)) {
                $_SESSION['successMessage'] = "Order #$orderID has been cancelled.";
            } else {
                $_SESSION['errorMessage'] = "Error cancelling order: " . $conn->error;
            }
        } else {
            $_SESSION['errorMessage'] = "This order can no longer be cancelled.";
        }

        // Redirect to prevent form resubmission
        header("Location: customer_dashboard.php");
        exit();
    }
}

// Fetch all restaurants with their names
$restaurantsQuery = "SELECT DISTINCT BusinessID, RestaurantName FROM BusinessProfile";
$restaurantsResult = $conn->query($restaurantsQuery);

// Fetch menu items for a specific restaurant
if (isset($_GET['restaurant_id'])) {
    $restaurantID = (int)$_GET['restaurant_id'];
    $menuQuery = "SELECT * FROM Menu WHERE BusinessID = $restaurantID";
    $menuResult = $conn->query($menuQuery);
}

// Fetch favorite items for the customer
$favoritesQuery = "SELECT Menu.* FROM Favorites JOIN Menu ON Favorites.MenuID = Menu.MenuID WHERE Favorites.CustomerID = $customerID";
$favoritesResult = $conn->query($favoritesQuery);

// Fetch order history for the logged-in customer
$orderHistoryQuery = "SELECT * FROM Checkout WHERE CustomerID = $customerID ORDER BY OrderTimestamp DESC";
$orderHistoryResult = $conn->query($orderHistoryQuery);

$orderHistory = [];
while ($order = $orderHistoryResult->fetch_assoc()) {
    $orderHistory[] = $order;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProCook - Customer Dashboard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="customerStyles.css">
    <style>
        .star {
            cursor: pointer;
            color: gold;
            font-size: 20px;
        }
        .star:hover {
            color: orange;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <h2>ProCook</h2>
            <ul>
                <li><a href="#" id="dashboard-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" id="menu-link"><i class="fas fa-utensils"></i> Menu</a></li>
                <li><a href="#" id="favorite-link"><i class="fas fa-heart"></i> Favorite</a></li>
                <li><a href="#" id="order-link"><i class="fas fa-location"></i> Track Order</a></li>
                <li><a href="#" id="cart-link"><i class="fas fa-shopping-cart"></i> My Cart</a></li>
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
            <p>Welcome to your dashboard, <?php echo $customer['Name']; ?>!</p>
        </div>

        <!-- Menu Content -->
        <div id="menu" class="content-section">
            <h1>Menu</h1>
            <div class="menu-container">
                <?php if (!isset($_GET['restaurant_id'])): ?>
                    <!-- Display list of restaurants -->
                    <h2>Restaurants</h2>
                    <div class="restaurant-grid">
                        <?php while ($restaurant = $restaurantsResult->fetch_assoc()): ?>
                            <div class="restaurant-card">
                                <a href="?restaurant_id=<?php echo $restaurant['BusinessID']; ?>">
                                    <h3><?php echo $restaurant['RestaurantName']; ?></h3>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- Display menu items for the selected restaurant -->
                    <h2>Menu Items</h2>
                    <a href="?" class="back-link">Back to Restaurants</a>
                    <div class="menu-grid">
                        <?php while ($menuItem = $menuResult->fetch_assoc()): ?>
                            <div class="menu-item">
                                <img src="<?php echo $menuItem['FoodImage']; ?>" alt="<?php echo $menuItem['FoodName']; ?>">
                                <h3><?php echo $menuItem['FoodName']; ?></h3>
                                <p><?php echo $menuItem['Description']; ?></p>
                                <div class="details">
                                    <p>Stocks: <?php echo $menuItem['Stocks']; ?></p>
                                    <p>Price: ₱<?php echo $menuItem['Price']; ?></p>
                                </div>
                                <div class="details">
                                    <p>Category: <?php echo $menuItem['Category']; ?></p>
                                    <p>Prep Time: <?php echo $menuItem['PreparationTime']; ?> mins</p>
                                </div>
                                <?php if ($menuItem['Availability'] === 'Available' && $menuItem['Stocks'] > 0): ?>
                                    <form method="POST" action="" class="add-to-cart-form">
                                        <input type="hidden" name="menu_id" value="<?php echo $menuItem['MenuID']; ?>">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" name="quantity" min="1" max="<?php echo $menuItem['Stocks']; ?>" value="1" required>
                                        <button type="submit" name="add_to_cart" onclick="return confirm('Do you want to add this item to your cart?')">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <p class="availability">
                                        <?php echo $menuItem['Availability'] === 'Available' ? 'Available' : 'Not Available'; ?>
                                    </p>
                                    <p class="availability">Out of Stock</p>
                                <?php endif; ?>
                                <!-- Favorite Star -->
                                <form method="POST" action="" class="favorite-form">
                                    <input type="hidden" name="menu_id" value="<?php echo $menuItem['MenuID']; ?>">
                                    <button type="submit" name="add_to_favorites" class="star">&#9733;</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Favorite Content -->
        <div id="favorite" class="content-section">
            <h1>Favorite</h1>
            <div class="favorite-container">
                <?php if ($favoritesResult->num_rows > 0): ?>
                    <div class="menu-grid">
                        <?php while ($favorite = $favoritesResult->fetch_assoc()): ?>
                            <div class="menu-item">
                                <img src="<?php echo $favorite['FoodImage']; ?>" alt="<?php echo $favorite['FoodName']; ?>">
                                <h3><?php echo $favorite['FoodName']; ?></h3>
                                <p><?php echo $favorite['Description']; ?></p>
                                <div class="details">
                                    <p>Price: ₱<?php echo $favorite['Price']; ?></p>
                                    <p>Category: <?php echo $favorite['Category']; ?></p>
                                </div>
                                <div class="details">
                                    <p>Stocks: <?php echo $favorite['Stocks']; ?></p>
                                    <p>Prep Time: <?php echo $favorite['PreparationTime']; ?> mins</p>
                                </div>
                                <?php if ($favorite['Availability'] === 'Available' && $favorite['Stocks'] > 0): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="menu_id" value="<?php echo $favorite['MenuID']; ?>">
                                        <label for="quantity">Quantity:</label>
                                        <input type="number" name="quantity" min="1" max="<?php echo $favorite['Stocks']; ?>" value="1" required>
                                        <button type="submit" name="add_to_cart" onclick="return confirm('Do you want to add this item to your cart?')">Buy Now</button>
                                    </form>
                                <?php else: ?>
                                    <p class="availability">
                                        <?php echo $favorite['Availability'] === 'Available' ? 'Available' : 'Not Available'; ?>
                                    </p>
                                    <p class="availability">Out of Stock</p>
                                <?php endif; ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="menu_id" value="<?php echo $favorite['MenuID']; ?>">
                                    <button type="submit" name="remove_from_favorites" class="remove-button" onclick="return confirm('Do you want to remove this item from favorites?')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>You have no favorite items.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order History Content -->
        <div id="order" class="content-section">
            <h1>Order History</h1>
            <?php if (!empty($orderHistory)): ?>
                <div class="orders-list">
                    <?php foreach ($orderHistory as $order): ?>
                        <div class="order-item">
                            <!-- Order Header -->
                            <div class="order-header">
                                <div class="customer-info">
                                    <h3>Order ID: <?php echo $order['OrderID']; ?></h3>
                                    <p>Status: <?php echo $order['Status']; ?></p>
                                </div>
                            </div>

                            <!-- Order Details -->
                            <div class="order-details">
                                <p><strong>Total Price:</strong> ₱<?php echo $order['TotalPrice']; ?></p>
                                <p><strong>Delivery Option:</strong> <?php echo $order['DeliveryOption']; ?></p>
                                <p><strong>Payment Method:</strong> <?php echo $order['PaymentMethod']; ?></p>
                                <p><strong>Order Timestamp:</strong> <?php echo $order['OrderTimestamp']; ?></p>
                                <p><strong>Estimated Delivery Time:</strong> <?php echo $order['EstimatedDeliveryTime']; ?></p>
                                <p><strong>Distance:</strong> <?php echo $order['Distance']; ?> km</p>
                                <p><strong>Delivery Fee:</strong> ₱<?php echo $order['DeliveryFee']; ?></p>
                            </div>

                            <!-- Order Actions -->
                            <div class="order-actions">
                                <?php if ($order['Status'] === 'Pending'): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="orderID" value="<?php echo $order['OrderID']; ?>">
                                        <button type="submit" name="cancel_order" class="btn-action btn-cancel" value="Cancelled" onclick="return confirm('Are you sure you want to cancel this order?');">
                                            Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>
        </div>

        <!-- Cart Content -->
        <div id="cart" class="content-section">
            <h1>My Cart</h1>
            <div class="cart-container">
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <ul class="cart-items-list">
                        <?php
                        $totalPrice = 0;
                        $totalPreparationTime = 0;
                        foreach ($_SESSION['cart'] as $menuID => $quantity):
                            $itemQuery = "SELECT * FROM Menu WHERE MenuID = $menuID";
                            $itemResult = $conn->query($itemQuery);
                            $item = $itemResult->fetch_assoc();
                            $totalPrice += $item['Price'] * $quantity;
                            $totalPreparationTime += $item['PreparationTime'] * $quantity;
                        ?>
                            <li class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo $item['FoodImage']; ?>" alt="<?php echo $item['FoodName']; ?>">
                                </div>
                                <div class="cart-item-details">
                                    <h3><?php echo $item['FoodName']; ?></h3>
                                    <p>Quantity: <?php echo $quantity; ?></p>
                                    <p>Price: ₱<?php echo $item['Price'] * $quantity; ?></p>
                                </div>
                                <form method="POST" action="">
                                    <input type="hidden" name="menu_id" value="<?php echo $menuID; ?>">
                                    <button type="submit" name="remove_from_cart" class="remove-button" onclick="return confirm('Do you want to remove this item from your cart?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="cart-summary">
                        <p>Total Price: <span id="total-price">₱<?php echo $totalPrice; ?></span></p>
                        <p>Total Preparation Time: <?php echo $totalPreparationTime; ?> mins</p>
                    </div>

                    <!-- Delivery Options -->
                    <h2>Delivery Options</h2>
                    <div class="delivery-options">
                        <div class="delivery-option" data-delivery-type="Slow" data-delivery-fee="<?php echo $slowDeliveryFee; ?>">
                            <div class="delivery-option-content">
                                <h3>Standard (Slow) Delivery</h3>
                                <p>Base Fee: ₱<?php echo $slowBaseFee; ?></p>
                                <p>Rate per km: ₱<?php echo $slowRatePerKm; ?></p>
                                <p>Distance: <?php echo $distance; ?> km</p>
                                <p>Total Fee: ₱<span class="delivery-fee"><?php echo $slowDeliveryFee; ?></span></p>
                                <p>Estimated Time: 30 – 60 minutes</p>
                            </div>
                        </div>
                        <div class="delivery-option" data-delivery-type="Fast" data-delivery-fee="<?php echo $fastDeliveryFee; ?>">
                            <div class="delivery-option-content">
                                <h3>Express (Fast) Delivery</h3>
                                <p>Base Fee: ₱<?php echo $fastBaseFee; ?></p>
                                <p>Rate per km: ₱<?php echo $fastRatePerKm; ?></p>
                                <p>Distance: <?php echo $distance; ?> km</p>
                                <p>Total Fee: ₱<span class="delivery-fee"><?php echo $fastDeliveryFee; ?></span></p>
                                <p>Estimated Time: 15 – 30 minutes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Form -->
                    <form method="POST" action="" class="checkout-form">
                        <input type="hidden" id="selected-delivery-fee" name="delivery_fee" value="<?php echo $slowDeliveryFee; ?>">
                        <input type="hidden" id="selected-delivery-type" name="delivery_option" value="Slow">
                        <label for="payment_method">Payment Method:</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="COD">Cash on Delivery (COD)</option>
                            <option value="Online">Online Payment</option>
                        </select>
                        <button type="submit" name="checkout" class="checkout-button" onclick="return confirm('Do you want to proceed with checkout?')">
                            <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                        </button>
                    </form>
                <?php else: ?>
                    <p class="empty-cart-message">Your cart is empty.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Content -->
        <div id="profile" class="content-section">
            <div class="profile-header">
                <h1>Customer Profile</h1>
                <?php if ($customer['ProfilePicture']): ?>
                    <div class="profile-image-container">
                        <img src="<?php echo $customer['ProfilePicture']; ?>" alt="Profile Picture" class="profile-image">
                    </div>
                <?php endif; ?>
            </div>
            <div class="profile-form-container">
                <!-- Personal Information Form -->
                <div class="profile-form">
                    <h2>Personal Information</h2>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" value="<?php echo $customer['Name']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" value="<?php echo $customer['Email']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo $customer['Phone']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address:</label>
                            <input type="text" id="address" name="address" value="<?php echo $customer['Address']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="profilePicture">Profile Picture:</label>
                            <input type="file" id="profilePicture" name="profilePicture">
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender:</label>
                            <select id="gender" name="gender" required>
                                <option value="Male" <?php echo ($customer['Gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($customer['Gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($customer['Gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="age">Age:</label>
                            <input type="number" id="age" name="age" value="<?php echo $customer['Age']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio:</label>
                            <textarea id="bio" name="bio"><?php echo $customer['Bio']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="foodPreferences">Food Preferences:</label>
                            <input type="text" id="foodPreferences" name="foodPreferences" value="<?php echo $customer['FoodPreferences']; ?>">
                        </div>
                        <button type="submit" name="update_profile" class="btn-primary">Update Profile</button>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="profile-form">
                    <h2>Change Password</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="currentPassword">Current Password:</label>
                            <input type="password" id="currentPassword" name="currentPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password:</label>
                            <input type="password" id="newPassword" name="newPassword" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmNewPassword">Confirm New Password:</label>
                            <input type="password" id="confirmNewPassword" name="confirmNewPassword" required>
                        </div>
                        <button type="submit" name="change_password" class="btn-primary">Change Password</button>
                    </form>
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
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deliveryOptions = document.querySelectorAll('.delivery-option');
            const totalPriceElement = document.getElementById('total-price');
            const selectedDeliveryFeeInput = document.getElementById('selected-delivery-fee');
            const selectedDeliveryTypeInput = document.getElementById('selected-delivery-type');

            // Parse the initial total price (remove the ₱ symbol)
            const baseTotalPrice = parseFloat(totalPriceElement.textContent.replace('₱', ''));

            deliveryOptions.forEach(option => {
                option.addEventListener('click', function () {
                    // Remove active class from all options
                    deliveryOptions.forEach(opt => opt.classList.remove('active'));

                    // Add active class to the selected option
                    this.classList.add('active');

                    // Get the delivery fee and type
                    const deliveryFee = parseFloat(this.getAttribute('data-delivery-fee'));
                    const deliveryType = this.getAttribute('data-delivery-type');

                    // Calculate the new total price (base total + delivery fee)
                    const newTotalPrice = baseTotalPrice + deliveryFee;

                    // Update the displayed total price
                    totalPriceElement.textContent = `₱${newTotalPrice.toFixed(2)}`;

                    // Update hidden inputs for form submission
                    selectedDeliveryFeeInput.value = deliveryFee;
                    selectedDeliveryTypeInput.value = deliveryType;
                });
            });

            // Set the first delivery option as active by default
            if (deliveryOptions.length > 0) {
                deliveryOptions[0].click();
            }
        });
    </script>
</body>
</html>