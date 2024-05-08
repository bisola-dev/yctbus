<?php
require_once('cann.php');
require_once('envar2.php');

// Fetch existing email and phone for display
$chin = "SELECT COUNT(*) AS count FROM [Bus_Booking].[dbo].[Account] WHERE staffid='$staffy'";
$kin = sqlsrv_query($conn, $chin);

// Check if the SQL query was successful
if ($kin === false) {
    // Handle SQL error
    echo "An error occurred while fetching account information.";
} else {
    // Fetch the count of rows
    $row = sqlsrv_fetch_array($kin, SQLSRV_FETCH_ASSOC);
    $count = $row['count'];

    // Check if the account exists
    if ($count === 0 ) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have not created an account. please create an account.");
        window.location.href="createaccount.php";
        </script>';
    } 
}




// Get the current server time in Africa/Lagos timezone
/*date_default_timezone_set('Africa/Lagos');
$currentHour = date('H');

// Check if it's between 11:00 and 12:00 to allow access
$allowAccess = ($currentHour >= 15 && $currentHour < 16);

if (!$allowAccess) {
    // If not within the allowed time range, display an error message and redirect to the dashboard
    echo '<script type="text/javascript">
          alert("Booking is only available between 3:00 and 4:00pm (Africa/Lagos time).");
          window.location.href="busdashboard.php";
          </script>';
    exit; // Stop further execution
}*/


// Fetch descriptions for dropdown
$descriptionQuery = "SELECT DISTINCT description,rid FROM [Bus_Booking].[dbo].[Routes]";
$descriptionResult = sqlsrv_query($conn, $descriptionQuery);
if ($descriptionResult === false) {
    die(print_r(sqlsrv_errors(), true));
}
$descriptions = array();
$rids = array();
while ($row = sqlsrv_fetch_array($descriptionResult, SQLSRV_FETCH_ASSOC)) {
    $descriptions[] = $row['description'];
    $rids[] = $row['rid'];
}


function getMaxSeatNumber($conn, $rid, $currentDate) {
    $maxSeatQuery = "SELECT MAX(seat_no) AS max_seat FROM [Bus_Booking].[dbo].[Transactions] WHERE rid='$rid' AND booking_date='$currentDate'";
    $maxSeatResult = sqlsrv_query($conn, $maxSeatQuery);
    if ($maxSeatResult === false) {
        throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
    }
    $maxSeatRow = sqlsrv_fetch_array($maxSeatResult, SQLSRV_FETCH_ASSOC);
    $maxSeat = $maxSeatRow['max_seat'];
    
    // If no seat has been assigned for the current date yet, start with seat number 1
    if ($maxSeat === null) {
        return 1;
    }
    
    // Increment the maximum seat number by 1 to assign the next available seat
    return $maxSeat + 1;
}



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $rid = $_POST['selectedRid'];

    try {
        // Check if the user has already booked a ticket for the current date
        $currentDate = date("Y-m-d");
      
        $checkBookingQuery = "SELECT COUNT(*) AS booking_count FROM [Bus_Booking].[dbo].[Transactions] WHERE staffid='$staffy' AND booking_date='$currentDate'";
        $checkBookingResult = sqlsrv_query($conn, $checkBookingQuery);
        if ($checkBookingResult === false) {
            throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
        }
        $bookingRow = sqlsrv_fetch_array($checkBookingResult, SQLSRV_FETCH_ASSOC);
        $bookingCount = $bookingRow['booking_count'];
    
        if ($bookingCount > 0) {
            // User has already booked a ticket for the current date, display error message
            echo '<script type="text/javascript">alert("Sorry, you can only book a bus ticket once a day.");</script>';
        } else {
            // Retrieve total available seats for the bus
            $seatCountQuery = "SELECT noseat FROM [Bus_Booking].[dbo].[Routes] WHERE rid='$rid'";
            $seatCountResult = sqlsrv_query($conn, $seatCountQuery);
            if ($seatCountResult === false) {
                throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
            }
            $row = sqlsrv_fetch_array($seatCountResult, SQLSRV_FETCH_ASSOC);
            $totalSeats = $row['noseat'];
    
            // Fetch total number of booked seats for the current date
            $bookedSeatsQuery = "SELECT COUNT(*) AS booked_count FROM [Bus_Booking].[dbo].[Transactions] WHERE rid='$rid' AND booking_date='$currentDate'";
            $bookedSeatsResult = sqlsrv_query($conn, $bookedSeatsQuery);
            if ($bookedSeatsResult === false) {
                throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
            }
            $bookedSeatsRow = sqlsrv_fetch_array($bookedSeatsResult, SQLSRV_FETCH_ASSOC);
            $bookedSeatsCount = $bookedSeatsRow['booked_count'];
    
            if ($bookedSeatsCount >= $totalSeats) {
                // Bus is completely full, display error message
                echo '<script type="text/javascript">alert("Sorry, the bus is completely full.");</script>';
            } else {
                // Retrieve existing amount
                $amountQuery = "SELECT amount FROM [Bus_Booking].[dbo].[Finance] WHERE staffid='$staffy'";
                $amountQueryResult = sqlsrv_query($conn, $amountQuery);
                if ($amountQueryResult === false) {
                    throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
                }
                $row = sqlsrv_fetch_array($amountQueryResult, SQLSRV_FETCH_ASSOC);
                if ($row !== null && isset($row['amount'])) {
                    $existingAmount = $row['amount'];
    
                    // Deduct existing amount from incoming amount
                    $remainingAmount = $existingAmount - $amount;
    
                    if ($remainingAmount >= 0) {
                        // Update existing amount in the finance table
                        $updateQuery = "UPDATE [Bus_Booking].[dbo].[Finance] SET amount = ? WHERE staffid = ?";
                        $updateParams = array($remainingAmount, $staffy);
    
                        $updateResult = sqlsrv_query($conn, $updateQuery, $updateParams);
    
                        if ($updateResult === false) {
                            // Error occurred while updating the record
                            echo '<script type="text/javascript">alert("Error occurred while updating finance records.");</script>';
                        } else {
                        
            // Update successful
            echo '<script type="text/javascript">console.log("Update successful.");</script>';
                        // Proceed with the ticket booking
                        // Fetch total seat count for the bus
                        $seatCountQuery = "SELECT noseat FROM [Bus_Booking].[dbo].[Routes] WHERE rid='$rid'";
                        $seatCountResult = sqlsrv_query($conn, $seatCountQuery);
                        if ($seatCountResult === false) {
                            throw new Exception("Failed to execute query: " . print_r(sqlsrv_errors(), true));
                        }
                        $row = sqlsrv_fetch_array($seatCountResult, SQLSRV_FETCH_ASSOC);
                        $totalSeats = $row['noseat'];
    
                        // Ensure the total number of bookings does not exceed the total seats
                        if ($totalSeats > 0) {
                            $maxSeatNumber = getMaxSeatNumber($conn, $rid, $currentDate);
                            if ($maxSeatNumber <= $totalSeats) {
                                // Proceed with the booking
                                // Insert booking into database
                                $tstamp = date("Y-m-d");
                                $seatNumber = $maxSeatNumber;
                                
                                $sql = "INSERT INTO [Bus_Booking].[dbo].[Transactions] (staffid, booking_date, rid, seat_no) VALUES (?, ?, ?, ?)";
                                $stmt = sqlsrv_prepare($conn, $sql, array(&$staffy, &$tstamp, &$rid, &$seatNumber));
                                if ($stmt) {
                                    if (sqlsrv_execute($stmt)) {
                                        // Log success message to console
                                        echo '<script type="text/javascript">
                                            alert("You have successfully booked a ticket of choice.");
                                            window.location.href="viewbooking.php";
                                        </script>';
                                    } else {
                                        // Log error message to console
                                        echo '<script type="text/javascript">
                                            alert("Ticket booking unsuccessful, Please try again.");
                                        </script>';
                                    }
                                } else {
                                    // Error preparing the SQL query
                                    throw new Exception("Failed to prepare SQL statement");
                                }
                            } else {
                                // All seats are booked for the current date
                                echo '<script type="text/javascript">
                                    alert("Sorry, the bus is completely full.");
                                </script>';
                            }
                        } else {
                            // No seats available for this route
                            echo '<script type="text/javascript">
                                alert("Sorry, no seats available for this route.");
                            </script>';
                        }
                    }
                } else {
                    // User does not have enough balance
                    echo '<script type="text/javascript">
                        alert("Sorry, you dont have enough funds for this destination. Please load your wallet.");
                    </script>';
                }
            } else {
                // No data found for the user or 'amount' key not set
                echo '<script type="text/javascript">
                    alert("please fund your wallet.");
                </script>';
            }
        } 
    } 
    
} catch (Exception $e) {
        // Handle the exception here
        echo "An error occurred: " . $e->getMessage();
    }
    
}

    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Booking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            overflow-x: hidden; /* Prevent horizontal scrollbar */
        }

        .container {
            display: flex;
            justify-content: center; /* Center items horizontally */
            align-items: center;
            flex-direction: column; /* Stack items vertically */
            height: 65vh; /* Adjusted height */
            width: 100%; /* Full width */
            margin-top: 1px; /* Add some top margin */
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 45%; /* Adjusted width */
            text-align: center;
            margin-bottom: 20px; /* Add space between form and table */
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group select, .form-group input {
            width: calc(100% - 22px); /* Adjusted for padding */
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #008000;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #FFFF00;
            color: #000;
        }
    </style>
</head>
<body>
<div class="container">
        <div class="sidebar">
            <?php include "sidebar.php";?>
        </div>
    <div class="form-container">
    <?php
          echo "<p><b>WELCOME,  $name </p></b>";
            echo "<p><i>please book a bus here </p></i>";?>

<form action="" method="post">
            <div class="form-group">
                <label for="description">Description:</label>
                <select id="description" name="selectedRid" required>
                <option value="">Select Description</option>
            <?php foreach ($descriptions as $index => $desc): ?>
                <option value="<?php echo $rids[$index]; ?>"><?php echo $desc; ?></option>
            <?php endforeach; ?>
        </select>
            </div>
            <div class="form-group">
                <label for="amount">Amount (₦):</label>
                <input type="text" id="amount" name="amount" placeholder="Amount" readonly>
            </div>
            <button type="submit" class="btn-submit">Book Bus</button>
        </form>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#description').change(function () {
            var selectedRid = $(this).val();
            if(selectedRid !== "") {
                $.ajax({
                    url: 'get_amount.php',
                    type: 'post',
                    data: {selectedRid: selectedRid},
                    success: function (response) {
                        $('#amount').val(response);
                    },
                    error: function (xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                // Handle the case when no description is selected
                $('#amount').val(""); // Clear the amount field or do something else
            }
        });
    });
</script>

</body>
</html>