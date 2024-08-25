<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    // Redirect to login page if not logged in
    header("Location: logout.php");
    exit(); 
}

// Fetch all items from the menu table
$result = mysqli_query($connection, "SELECT MID, Item_Name, Qty, Price, picture FROM menu");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $total_price = $price * $qty;
    $userId = $_SESSION['UserID'];

    // Check if item quantity is available in the menu
    $check_qty_query = "SELECT Qty FROM menu WHERE Item_Name = '$item_name'";
    $check_qty_result = mysqli_query($connection, $check_qty_query);
    if (mysqli_num_rows($check_qty_result) > 0) {
        $menu_item = mysqli_fetch_assoc($check_qty_result);
        $menu_qty = $menu_item['Qty'];

        if ($menu_qty >= $qty) {
            // Reduce the quantity in the menu table
            $update_qty_query = "UPDATE menu SET Qty = Qty - $qty WHERE Item_Name = '$item_name'";
            mysqli_query($connection, $update_qty_query);

            // Update cart session
            if (!isset($_SESSION['cart'][$userId])) {
                $_SESSION['cart'][$userId] = [];
            }

            // Check if item already exists in the cart
            $found = false;
            foreach ($_SESSION['cart'][$userId] as &$cart_item) {
                if ($cart_item['item_name'] == $item_name) {
                    $cart_item['qty'] += $qty;
                    $cart_item['total_price'] += $total_price;
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                $_SESSION['cart'][$userId][] = [
                    'item_name' => $item_name,
                    'price' => $price,
                    'qty' => $qty,
                    'total_price' => $total_price
                ];
            }

            header("Location: customer.php");
            exit();
        } else {
            // Handle case where quantity is insufficient
            // You can add a message or redirect as per your application's logic
            echo "Insufficient quantity available.";
        }
    } else {
        // Handle case where item not found in the menu table
        // You can add a message or redirect as per your application's logic
        echo "Item not found.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menu</title>
    <link rel="stylesheet" href="navbarstyle.css">
    <link rel="stylesheet" href="customer.css">

</head>
<body>
<div class="nav justify-content-center">
    <div class="head">
        <h2>Urban Foods</h2>
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
    <h2>Menu</h2>
    <div class="card-container">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="card">
            <img src="foods/<?php echo $row['picture']; ?>" alt="<?php echo $row['Item_Name']; ?>" class="card-img-top">
            <div class="card-body">
                <h5 class="card-title"><?php echo $row['Item_Name']; ?></h5>
                <p class="card-text">Quantity: <?php echo $row['Qty']; ?></p>
                <p class="card-text">Price: $<?php echo $row['Price']; ?></p>
            </div>
            <div class="card-footer">
                <form action="#" method="post">
                    <input type="hidden" name="item_name" value="<?php echo $row['Item_Name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $row['Price']; ?>">
                    <input type="number" name="qty" placeholder="Quantity" required>
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
    
</body>
</html>

<?php
mysqli_close($connection);
?>
