<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details</title>
    <style>
  .container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    height: auto;
    width: 90%;
    max-width: 600px;
    margin: 20px auto;
    background: linear-gradient(135deg, #FFFF00, #008000); /* Gradient from yellow to green */
    border: 2px solid #ccc; /* Gray border */
    border-radius: 10px; /* Rounded corners */
    padding: 15px; /* Increased padding */
}
/*.container {
    display: flex;
    justify-content: center; /* Center items horizontally */
    align-items: center;
    flex-direction: column; /* Stack items vertically */
    height: 50vh; /* Adjusted height */
    width: 80%; /* Adjusted width */
    max-width: 400px; /* Maximum width */
    margin: 20px auto; /* Center the container and add some margin 
}
*/
.booking-details {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
    margin-bottom: 20px;
}

.detail {
    margin-bottom: 20px;
    text-align: left;
}

.detail label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.qr-code {
    margin-top: 20px;
}


    </style>
</head>
<body>

<div class="container">
        <div class="sidebar">
            <?php include "sidebar.php";?>
        </div>
        <div id="printableArea">
        <div class="container">
        <h1>Booking Details</h1>
        <div class="booking-details">
            <div class="detail">
                <label>User Name:</label>
                <span><?php echo $name; ?></span>
            </div>
            <div class="detail">
                <label>Route:</label>
                <span><?php echo $description; ?></span>
            </div>
            <div class="detail">
                <label>Amount:</label>
                <span><?php echo $amount; ?></span>
            </div>
            <!-- Uncomment the following block if you have the seat number -->
            <div class="detail">
                <label>Seat Number:</label>
                <span><?php echo $seat_no ; ?></span>
            </div> 
            <div class="qr-code">

<?php 
$qrCodeFilePath = generateQRCode($bookingDetails, 'booking_qrcode.png');?>
                <!-- Display the QR code image -->
                <img src="<?php echo $qrCodeFilePath; ?>" alt="QR Code">
            </div>
        </div>
    </div>
    <button onclick="printDiv('printableArea')">Print</button>
    <script>
    function printDiv(divName) {
    var printContents = document.getElementById(divName).innerHTML;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;
    window.print();

    document.body.innerHTML = originalContents;
}
</script>
</body>

</html>

