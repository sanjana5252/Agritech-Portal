<?php
session_start();
date_default_timezone_set("Asia/Calcutta"); 
$userlogin = $_SESSION['customer_login_user'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agriculture_portal";

// Create Connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$date = date('d/m/Y');

// Ensure that cart is not empty
$query1 = "SELECT * FROM cart";
$result1 = mysqli_query($conn, $query1);

if (mysqli_num_rows($result1) > 0) {
    while ($row1 = $result1->fetch_assoc()) {
        $x = $row1['quantity'];

        // Update production_approx quantity after crop is bought
        $query2 = "UPDATE production_approx SET quantity = quantity - '" . $row1['quantity'] . "' WHERE crop = '" . $row1['cropname'] . "'";
        if (!mysqli_query($conn, $query2)) {
            echo "Error updating production_approx: " . mysqli_error($conn);
            continue;  // Skip to the next iteration if there's an error
        }

        while (true) {
            $query3 = "SELECT * FROM farmer_crops_trade WHERE Trade_crop = '" . $row1['cropname'] . "' LIMIT 1";
            $result3 = mysqli_query($conn, $query3);
            if (mysqli_num_rows($result3) > 0) {
                $row3 = $result3->fetch_assoc();

                if ($row3['Crop_quantity'] == $x) {
                    // Add entry to farmer_history and delete the trade if full quantity is sold
                    $query11 = "INSERT INTO farmer_history (farmer_id, farmer_crop, farmer_quantity, farmer_price, date) 
                                 VALUES ('" . $row3['farmer_fkid'] . "', '" . $row3['Trade_crop'] . "', '" . $row3['Crop_quantity'] . "', 
                                 '" . $row1['price'] . "', '" . $date . "')";
                    if (!mysqli_query($conn, $query11)) {
                        echo "Error inserting into farmer_history: " . mysqli_error($conn);
                        break;
                    }

                    $query4 = "DELETE FROM farmer_crops_trade WHERE trade_id = '" . $row3['trade_id'] . "'";
                    if (!mysqli_query($conn, $query4)) {
                        echo "Error deleting from farmer_crops_trade: " . mysqli_error($conn);
                    }
                    break;
                }

                if ($row3['Crop_quantity'] > $x) {
                    // Add partial quantity to farmer_history and update crop trade
                    $query12 = "INSERT INTO farmer_history (farmer_id, farmer_crop, farmer_quantity, farmer_price, date) 
                                 VALUES ('" . $row3['farmer_fkid'] . "', '" . $row3['Trade_crop'] . "', '" . $x . "', 
                                 '" . $x . "' * '" . $row3['msp'] . "', '" . $date . "')";
                    if (!mysqli_query($conn, $query12)) {
                        echo "Error inserting into farmer_history: " . mysqli_error($conn);
                        break;
                    }

                    $query5 = "UPDATE farmer_crops_trade SET Crop_quantity = Crop_quantity - '" . $x . "' WHERE trade_id = '" . $row3['trade_id'] . "'";
                    if (!mysqli_query($conn, $query5)) {
                        echo "Error updating farmer_crops_trade: " . mysqli_error($conn);
                    }
                    break;
                }

                if ($row3['Crop_quantity'] < $x) {
                    // If not enough quantity, update history and delete the trade
                    $x = $x - $row3['Crop_quantity'];

                    $query13 = "INSERT INTO farmer_history (farmer_id, farmer_crop, farmer_quantity, farmer_price, date) 
                                 VALUES ('" . $row3['farmer_fkid'] . "', '" . $row3['Trade_crop'] . "', '" . $row3['Crop_quantity'] . "', 
                                 '" . $row3['Crop_quantity'] . "' * '" . $row3['msp'] . "', '" . $date . "')";
                    if (!mysqli_query($conn, $query13)) {
                        echo "Error inserting into farmer_history: " . mysqli_error($conn);
                        break;
                    }

                    $query6 = "DELETE FROM farmer_crops_trade WHERE trade_id = '" . $row3['trade_id'] . "'";
                    if (!mysqli_query($conn, $query6)) {
                        echo "Error deleting from farmer_crops_trade: " . mysqli_error($conn);
                    }
                }
            } else {
                echo "No trade found for crop: " . $row1['cropname'];
                break;
            }
        }

        // Update the MSP after transaction
        $a = 0.0;
        $y = 0;
        $query = "SELECT costperkg FROM farmer_crops_trade WHERE Trade_crop = '" . $row1['cropname'] . "'";
        $result = mysqli_query($conn, $query);
        while ($row = $result->fetch_assoc()) {
            $a = $a + $row["costperkg"];
            $y++;
        }

        // Avoid division by zero
        if ($y > 0) {
            $a = ceil($a / $y);
            $a = $a + ceil($a * 0.5);
        }

        $query7 = "UPDATE farmer_crops_trade SET msp = '$a' WHERE Trade_crop = '" . $row1['cropname'] . "'";
        if (!mysqli_query($conn, $query7)) {
            echo "Error updating MSP: " . mysqli_error($conn);
        }
    }

    // Redirect to money transfer page after processing
    header("location: cmoney_transfered.php");
} else {
    echo "Cart is empty. Please add items to the cart.";
    exit;
}
?>
