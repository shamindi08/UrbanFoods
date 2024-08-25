<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    // Redirect to login page if not logged in
    header("Location: logout.php");
    exit(); 
}

$userId = $_SESSION['UserID'];

// Function to calculate total cart value
function calculateCartTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['total_price'];
    }
    return $total;
}

// Initialize variables
$card_number = $expiry_date = $cvv = $amount = '';
$payment_successful = false;
$error_message = '';

// Check if the payment form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve payment details from the form
    $card_number = $_POST['card_number'];
    $expiry_date = $_POST['expiry_date'];
    $cvv = $_POST['cvv'];
    $amount = $_POST['amount'];

    // Simulate payment processing (replace with actual payment gateway integration)
    $payment_successful = true; // Set this based on the payment gateway response

    if ($payment_successful) {
        // Clear the cart after successful payment
        
        unset($_SESSION['cart'][$userId]);

        // Redirect to customer.php after successful payment
        header("Location: customer.php");
        exit();
    } else {
        // Handle payment failure scenario
        $error_message = "Payment failed. Please try again.";
    }
}

// Calculate total cart value
$totalAmount = isset($_SESSION['cart'][$userId]) ? calculateCartTotal($_SESSION['cart'][$userId]) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment</title>
    <link rel="stylesheet" href="styles.css"> 
</head>
<body>
    <h2>Cart</h2>
    <table>
        <tr>
            <th>Item Name</th>
            <th>Quantity</th>
            <th>Total Price</th>
        </tr>
        <?php
        if (isset($_SESSION['cart'][$userId]) && !empty($_SESSION['cart'][$userId])) {
            foreach ($_SESSION['cart'][$userId] as $item) {
                echo "<tr>
                        <td>{$item['item_name']}</td>
                        <td>{$item['qty']}</td>
                        <td>\${$item['total_price']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Your cart is empty</td></tr>";
        }
        ?>
        <tr>
            <td colspan="2"><strong>Total Amount:</strong></td>
            <td><strong>$<?php echo number_format($totalAmount, 2); ?></strong></td>
        </tr>
    </table>

    <h2>Payment</h2>
    <form action="#" method="post">
        <label for="card_number">Card Number:</label>
        <input type="text" id="card_number" name="card_number" value="<?php echo $card_number; ?>" required><br><br>
        
        <label for="expiry_date">Expiry Date:</label>
        <input type="text" id="expiry_date" name="expiry_date" value="<?php echo $expiry_date; ?>" placeholder="MM/YY" required><br><br>
        
        <label for="cvv">CVV:</label>
        <input type="text" id="cvv" name="cvv" value="<?php echo $cvv; ?>" maxlength="3" required><br><br>
        
        <input type="hidden" name="amount" value="<?php echo $totalAmount; ?>">
        <button type="submit">Pay Now</button>
        <a href="view_cart.php">Cancel</a>
    </form>

    <?php
    // Display error message if payment failed
    if (!empty($error_message)) {
        echo "<p style='color: red;'>{$error_message}</p>";
    }
    ?>
</body>
</html>
