<?php
//
// send_location.php
//
// This script expects a JSON POST body with:
//   - ownerEmail
//   - latitude
//   - longitude
//   - deviceInfo
//   - timestamp
//
// Then it composes an email and sends it to YOUR_EMAIL_HERE.
//

header("Content-Type: application/json; charset=UTF-8");

// 1. Read raw POST data
$raw = file_get_contents("php://input");
if (!$raw) {
    echo json_encode([ "success" => false, "error" => "No data received." ]);
    exit;
}

// 2. Decode JSON
$data = json_decode($raw, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([ "success" => false, "error" => "Invalid JSON." ]);
    exit;
}

// 3. Validate required fields
$required = ["ownerEmail", "latitude", "longitude", "deviceInfo", "timestamp"];
foreach ($required as $field) {
    if (empty($data[$field]) && $data[$field] !== "0") {
        echo json_encode([ "success" => false, "error" => "Missing field: $field" ]);
        exit;
    }
}

// 4. Sanitize / assign
$ownerEmail = filter_var($data["ownerEmail"], FILTER_VALIDATE_EMAIL);
if (!$ownerEmail) {
    echo json_encode([ "success" => false, "error" => "Invalid email address." ]);
    exit;
}

// Cast to floats
$latitude  = floatval($data["latitude"]);
$longitude = floatval($data["longitude"]);

// Strip any HTML tags from deviceInfo & timestamp
$deviceInfo = strip_tags($data["deviceInfo"]);
$timestamp  = strip_tags($data["timestamp"]);

// 5. Compose the email
// Replace this with your real destination email address:
$to      = "syarief.azman@gmail.com";
$subject = "New Location from {$ownerEmail}";

$body  = "You have a new location submission:\n\n";
$body .= "• Owner Email: {$ownerEmail}\n";
$body .= "• Timestamp  : {$timestamp}\n";
$body .= "• Latitude   : {$latitude}\n";
$body .= "• Longitude  : {$longitude}\n\n";
$body .= "Device Info:\n{$deviceInfo}\n\n";
$body .= "— End of message —\n";

// 6. Set headers
$headers  = "From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n";
$headers .= "Reply-To: {$ownerEmail}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// 7. Attempt to send
$mailSent = mail($to, $subject, $body, $headers);

if ($mailSent) {
    echo json_encode([ "success" => true ]);
} else {
    // If mail() fails, return an error message
    echo json_encode([ "success" => false, "error" => "Mail function failed." ]);
}
