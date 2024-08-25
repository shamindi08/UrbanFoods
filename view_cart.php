<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    // Redirect to login page if not logged in
    header("Location: logout.php");
    exit();
}

$userId = $_SESSION['UserID'];

// Check if an item should be removed from the cart
if (isset($_GET['item_name'])) {
    $item_name = $_GET['item_name'];

    // Remove item from session cart
    if (isset($_SESSION['cart'][$userId])) {
        foreach ($_SESSION['cart'][$userId] as $key => $cart_item) {
            if ($cart_item['item_name'] == $item_name) {
                // Remove item from session cart
                unset($_SESSION['cart'][$userId][$key]);

                // Update menu table with new quantity
                require 'db_connection.php'; // Adjust path as necessary

                // Retrieve current quantity from menu table
                $select_query = "SELECT Qty FROM menu WHERE Item_Name = ?";
                $stmt_select = mysqli_prepare($connection, $select_query);
                mysqli_stmt_bind_param($stmt_select, "s", $item_name);
                mysqli_stmt_execute($stmt_select);
                mysqli_stmt_bind_result($stmt_select, $current_qty);
                mysqli_stmt_fetch($stmt_select);
                mysqli_stmt_close($stmt_select);

                // Calculate new quantity for the menu table
                $new_qty = $current_qty + $cart_item['qty']; // Adjust as per your business logic

                // Update menu table with new quantity
                $update_query = "UPDATE menu SET Qty = ? WHERE Item_Name = ?";
                $stmt_update = mysqli_prepare($connection, $update_query);
                mysqli_stmt_bind_param($stmt_update, "is", $new_qty, $item_name);
                
                if (mysqli_stmt_execute($stmt_update)) {
                    mysqli_stmt_close($stmt_update);
                    mysqli_close($connection);
                    break; // Exit loop if update successful
                } else {
                    mysqli_stmt_close($stmt_update);
                    mysqli_close($connection);
                    // Handle update failure if necessary
                }
            }
        }
    }

    // Redirect back to view_cart.php
    header("Location: view_cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file for styling -->
    <link rel="stylesheet" href="navbarstyle.css">
    <link rel="stylesheet" href="cart.css">

</head>
<body>
<div class="nav justify-content-center">
    <div class="head">
        <h2>Cart</h2>
    </div>
   
    <div class="nav-items">
        <ul>
            <li class="nav-item"><a class="nav-link" href="customer.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="view_cart.php">View cart</a></li>
            <li class="nav-item"><a class="nav-link" href="customerprofile.php">Profile</a></li>
            <li class="nav-item"><a class="nav-link" href="review.php">Review</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>
    <h2>Cart</h2>
    <table>
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Action</th>
        </tr>
        <?php
        if (isset($_SESSION['cart'][$userId]) && !empty($_SESSION['cart'][$userId])) {
            foreach ($_SESSION['cart'][$userId] as $item) {
                echo "<tr>
                        <td>{$item['item_name']}</td>
                        <td>{$item['qty']}</td>
                        <td>\${$item['total_price']}</td>
                        <td><a href=\"{$_SERVER['PHP_SELF']}?item_name={$item['item_name']}\">Remove</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Your cart is empty</td></tr>";
        }
        ?>
    </table>
    <a href="customer.php">Back to Menu</a>
    <a href="checkout.php">Proceed to Payment</a>
</body>
</html>
