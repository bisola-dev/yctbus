<?php 
// Include necessary files
require_once('cann2.php');
require_once('envary.php');

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
    if ($count > 0) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have already created an account. You can proceed to edit it.");
        window.location.href="editaccount.php";
        </script>';
    } 
}

   
try {
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $phonezx = isset($_POST["phonezx"]) ? trim($_POST["phonezx"]) : '';
    $emailzx = isset($_POST["emailzx"]) ? trim($_POST["emailzx"]) : '';
    
    if (!empty($staffy) || !empty($phonezx) || !empty($emailzx)) {
        // Construct the SQL query
$sql = "INSERT INTO [Bus_Booking].[dbo].[Account] (staffid, email, phone) VALUES (?, ?, ?)";

// Prepare the SQL query
$stmt = sqlsrv_prepare($conn, $sql, array(&$staffy, &$emailzx, &$phonezx));

// Check if preparing the query was successful
if ($stmt) {
    // Execute the prepared query
    if (sqlsrv_execute($stmt)) {
        // Query executed successfully
        echo '<script type="text/javascript">
        alert("Account successfully created,please proceed to fund your wallet");
        window.location.href="funding.php";
        </script>';
    } else {
        // Execution failed
        echo '<script type="text/javascript">alert("Account creation unsuccessful, Please try again");</script>';
    }
} else {
    // Preparation failed
    echo '<script type="text/javascript">alert("Failed to prepare SQL statement");</script>';
}
    }
}





} catch (Exception $e) {
    // Handle the exception here
    echo "An error occurred: " . $e->getMessage();
}


?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }

        .sidebar {
            width: 250px;
            height: 100%;
            background-color: #008000; /* Green */
            position: fixed;
            left: 0;
            top: 0;
            overflow-x: hidden;
            padding-top: 20px;
        }

        .sidebar .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar ul li a {
            color: #fff;
            text-decoration: none;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.8); /* Light background color with opacity */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
            text-align: center;
            margin: 50px auto;
            position: relative; /* Position relative for absolute positioning of background */
            overflow: hidden; /* Hide overflowing background */
        }

       
        .form-container h2 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-submit {
            width: 100%;
            padding: 10px;
            background-color: #008000;
            border: none;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #FFFF00;
            color: #000;
        }

        
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include "sidebar.php";?>
    </div>

    <div class="form-container">
        <h2>Create Account</h2>
        <form action="" method="post">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="Enter your name" value ="<?php echo $name;?>" required disabled>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required pattern="[0-9]{11}">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email address" required>
            </div>
            <button type="submit" class="btn-submit">Create Account</button>
        </form>
    </div>

<script>
    // Function to validate phone number input
    function validatePhone(input) {
        var phoneNumber = input.value.replace(/\D/g, ''); // Remove non-digit characters

        if (phoneNumber.length !== 11) {
            input.setCustomValidity("Phone number must have exactly 11 digits");
        } else {
            input.setCustomValidity(""); // Clear any previous validation message
        }
    }
</script>
</body>
</html>
