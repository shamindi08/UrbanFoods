<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION['UserID'])) {
        // Redirect to login page if not logged in
        header("Location: logout.php");
        exit();
    }

    $cid = $_SESSION['UserID'];
    
    require 'db_connection.php'; // Database connection
    
    $photo = '';
    
    if(isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $target_dir = "customer/";
        $photo = $target_dir .basename($_FILES["profile_photo"]["name"]);
        
        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $photo)) {
            // Update photo path in database
            $update_query = "UPDATE customer SET picture = ? WHERE CID = ?";
            $stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($stmt, "ss", $photo, $cid);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                mysqli_close($connection);
                header("Location: customerprofile.php");
                exit();
            } else {
                echo "Error updating profile picture: " . mysqli_error($connection);
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No file uploaded or an error occurred.";
    }
} else {
    echo "Invalid request method.";
}
?>
