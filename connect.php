<?php
require_once 'config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data and sanitize it
    $name = htmlspecialchars($_POST['name']);
    $recycle_device = htmlspecialchars($_POST['recycle_device']);
    $quantity = htmlspecialchars($_POST['quantity']);
    $location = htmlspecialchars($_POST['location']);
    $pickup_time = htmlspecialchars($_POST['pickup-time']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);

    // Store the data in a file (you can replace this with a database or other methods)
    $file = 'recycling_requests.txt';

    // Create a string with all the collected data
    $request_data = "Name: $name\nDevice: $recycle_device\nQuantity: $quantity\nLocation: $location\nPickup Time: $pickup_time\nEmail: $email\nPhone: $phone\n\n";
    
    // Append the data to the file
    file_put_contents($file, $request_data, FILE_APPEND);
// Credentials are loaded from config.php

    $trelloUrl = "https://api.trello.com/1/cards";

    // Determine Label Color based on Waste Type (Priority)
    // Medical = Red (High Priority), E-Waste = Yellow, Others = Blue
    $labelColor = 'blue'; // Default
    $medical_items = ['Needles', 'Syringes', 'Used gloves', 'Expired tablets', 'Unused vaccines', 'Disinfectants', 'Cleaning agents', 'Medicines', 'Dressings', 'Bandages'];
    $ewaste_items = ['Battery', 'Mobile', 'Laptop', 'Wires', 'Power Banks', 'Printers', 'Cameras', 'Tablets'];
    
    if (in_array($recycle_device, $medical_items)) {
        $labelColor = 'red';
    } elseif (in_array($recycle_device, $ewaste_items)) {
        $labelColor = 'yellow';
    }

    $cardName = "Pickup Request: " . $name . " - " . $recycle_device;
    $cardDesc = "Quantity: " . $quantity . "\nLocation: " . $location . "\nPickup Time: " . $pickup_time . "\nEmail: " . $email . "\nPhone: " . $phone;

    $data = array(
        'key' => $trelloApiKey,
        'token' => $trelloApiToken,
        'idList' => $trelloListId,
        'name' => $cardName,
        'desc' => $cardDesc,
        'pos' => 'top',                 // Position: Top of the list
        'due' => $pickup_time . ':00Z', // Due Date: From form input
        'labels' => $labelColor         // Label: Color based on priority
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($trelloUrl, false, $context);
    // --------------------------

    // Optionally, you can send an email confirmation here

    // Redirect to a confirmation page or display a success message
    echo "<script>alert('Your recycling request has been submitted successfully!'); window.location.href = 'thank_you.html';</script>";
} else {
    // If the form is not submitted via POST, redirect to the homepage or form page
    header("Location: index.html");
    exit;
}
?>
