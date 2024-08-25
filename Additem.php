<?php
session_start();
require 'db_connection.php';

// Check if the user is logged in and is user ID 'A001'
if (!isset($_SESSION['UserID'])) {
    // Redirect to login page if not logged in
    header("Location: logout.php");
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $qty = $_POST['qty'];
    $price = $_POST['price'];

    // Fetch the picture for the selected item
    $query = "SELECT picture FROM fooditems WHERE Item_Name='$item_name'";
    $result = mysqli_query($connection, $query);
    $row = mysqli_fetch_assoc($result);
    $picture = $row['picture'];

    // Generate the next MID
    $result = mysqli_query($connection, "SELECT MID FROM menu ORDER BY MID DESC LIMIT 1");
    $row = mysqli_fetch_assoc($result);
    $last_mid = $row ? $row['MID'] : 'M000';
    $new_mid = 'M' . str_pad((int)substr($last_mid, 1) + 1, 3, '0', STR_PAD_LEFT);

    // Start transaction to ensure atomicity
    mysqli_begin_transaction($connection);

    // Insert the item into the menu table
    $sql_menu = "INSERT INTO menu (MID, Item_Name, Qty, Price, picture) VALUES ('$new_mid', '$item_name', '$qty', '$price', '$picture')";
    
    if (mysqli_query($connection, $sql_menu)) {
        // Update quantity in fooditems table
        $update_qty_sql = "UPDATE fooditems SET Qty = Qty - $qty WHERE Item_Name = '$item_name'";
        if (!mysqli_query($connection, $update_qty_sql)) {
            echo "Error updating quantity in fooditems table: " . mysqli_error($connection);
            mysqli_rollback($connection);
            exit();
        }

        // Commit transaction
        mysqli_commit($connection);

        echo "New item added to menu successfully";
    } else {
        echo "Error adding item to menu: " . mysqli_error($connection);
        mysqli_rollback($connection);
    }

    mysqli_close($connection);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Item to Menu</title>
    <link rel="stylesheet" href="navbarstyle.css">
    <link rel="stylesheet" href="formitem.css">
</head>
<body>
<div class="nav justify-content-center">
    <div class="head">
        <h2>Add Items</h2>
    </div>
   
    <div class="nav-items">
        <ul>
            <li class="nav-item"><a class="nav-link" href="admin.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="Additem.php">Add Item</a></li>
            <li class="nav-item"><a class="nav-link" href="managelogins.php">Manage Logins</a></li>
            <li class="nav-item"><a class="nav-link" href="viewreview.php">View Reviews</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</div>
    <h2>Add Item to Menu</h2>
    <form action="#" method="post">
        <label for="item_name">Select Item:</label>
        <select id="item_name" name="item_name">
            <?php
            // Fetch items from fooditems table
            require 'db_connection.php';
            $result = mysqli_query($connection, "SELECT Item_Name, Qty, Price FROM fooditems");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['Item_Name']}'>{$row['Item_Name']} - Quantity: {$row['Qty']} - Price: {$row['Price']}</option>";
            }
            mysqli_close($connection);
            ?>
        </select><br><br>
        
        <label for="qty">Quantity:</label>
        <input type="number" id="qty" name="qty" required><br><br>
        
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" required step="0.01"><br><br>
        
        <button type="submit">Add to Menu</button>
    </form>
</body>
</html>
