

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
    if ($count === 0) {
        // Account already exists, redirect to edit page
        echo '<script type="text/javascript">
        alert("You have not created an account. please create an account.");
        window.location.href="createaccount.php";
        </script>';
    } 
}






?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>view transaction</title>
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
            height: 40vh; /* Reduce height to bring it up */
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

        .form-group input {
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

        /* Table styles */
        table {
            width: 47%; /* Adjusted width */
            border-collapse: collapse;
            border: 1px solid #ccc;
            margin-bottom: 20px; /* Add space between form and table */
        }

        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #008000;
            color: #fff;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }


        /* New table styles */
.wallet-table {
    width: 30%; /* Adjusted width */
    border-collapse: collapse;
    border: 1px solid #ccc;
    margin-top: 20px; /* Add space between previous table and new table */
}

.wallet-table th, .wallet-table td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: center;
    font-weight: bold;
}

.wallet-table th {
    background-color: #008000;
    color: #fff;
}
    </style>
</head>
<body>



<div class="container">
    <div class="sidebar">
        <?php include "sidebar.php"; ?>
    </div>

    <?php
    echo "<p><b>WELCOME, $name </p></b>";
    echo "<p><i>View wallet transactions </p></i>";

    // Fetch wallet entries from the database and populate the table rows
    $walletQuery = "SELECT * FROM [Bus_Booking].[dbo].[wallet_trans] WHERE staffid = ?";
    $params = array($staffy);
    $walletResult = sqlsrv_query($conn, $walletQuery, $params);
    if ($walletResult === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Check if there are any rows returned
    if (sqlsrv_has_rows($walletResult)) {
        // Display the table if there are rows
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Staff ID</th>";
        echo "<th>Amount in wallet</th>";
        echo "<th>Remitta_rrr</th>";
        echo "<th>Date(Y-M-D)</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = sqlsrv_fetch_array($walletResult, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . $row['staffid'] . "</td>";
            echo "<td>₦" . $row['amount'] . "</td>";
            echo "<td>" . $row['remita_rrr'] . "</td>";
            echo "<td>" . $row['trans_date']->format('Y-m-d') . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        // Display "No transactions" message if there are no rows
        echo '<div class="no-transactions">No transactions to display.</div>';
    }

    // Free the result set
    sqlsrv_free_stmt($walletResult);
    ?>
<table class="wallet-table">
    <thead>
        <tr>
            <th>Available Balance</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>₦<!-- Here you can display the total amount from your calculation --></td>
        </tr>
    </tbody>
</table>


    
