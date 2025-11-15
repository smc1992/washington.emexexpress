<?php
// Universal Airfreight Booking Form Handler
// Supports all cities with environment variables

header('Content-Type: application/json');

// Import PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer
require 'vendor/autoload.php';

// Configuration from Environment Variables
$adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'ops@emexexpress.de';
$fromEmail = $_ENV['FROM_EMAIL'] ?? 'noreply@emexexpress.de';
$cityName = $_ENV['CITY_NAME'] ?? 'Unknown';
$smtpConfig = [
    'host' => $_ENV['SMTP_HOST'] ?? 'mail.ionos.de',
    'port' => intval($_ENV['SMTP_PORT'] ?? 587),
    'username' => $_ENV['SMTP_USER'] ?? 'ops@emexexpress.de',
    'password' => $_ENV['SMTP_PASS'] ?? '',
    'encryption' => $_ENV['SMTP_ENCRYPTION'] ?? 'tls',
    'from_email' => $fromEmail,
    'from_name' => "Emex Express {$cityName}"
];

// City-specific configurations
$cityConfigs = [
    'Chicago' => [
        'subject_prefix' => '[Chicago Airfreight Inquiry]',
        'booking_prefix' => 'CHI',
        'color' => '#1e40af'
    ],
    'New York' => [
        'subject_prefix' => '[New York Airfreight Inquiry]',
        'booking_prefix' => 'NYC',
        'color' => '#dc2626'
    ],
    'UK' => [
        'subject_prefix' => '[UK Airfreight Inquiry]',
        'booking_prefix' => 'UK',
        'color' => '#059669'
    ],
    'Washington' => [
        'subject_prefix' => '[Washington Airfreight Inquiry]',
        'booking_prefix' => 'WAS',
        'color' => '#7c3aed'
    ]
];

$config = $cityConfigs[$cityName] ?? $cityConfigs['Chicago'];
$subjectPrefix = $config['subject_prefix'];
$bookingPrefix = $config['booking_prefix'];
$primaryColor = $config['color'];

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitize and validate input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Required fields validation
$requiredFields = [
    'shipper_name', 'shipper_email', 'shipper_phone', 
    'shipper_address', 'shipper_city', 'shipper_country',
    'receiver_name', 'receiver_address', 'receiver_city', 
    'receiver_country', 'agree_terms'
];

$missingFields = [];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Please fill in all required fields',
        'missing_fields' => $missingFields
    ]);
    exit;
}

// Email validation
if (!filter_var($_POST['shipper_email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Terms acceptance validation
if ($_POST['agree_terms'] !== 'on' && $_POST['agree_terms'] !== 'true') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You must accept the terms and conditions']);
    exit;
}

// Prepare email content
$bookingData = [];
foreach ($_POST as $key => $value) {
    if (is_array($value)) {
        $bookingData[$key] = implode(', ', $value);
    } else {
        $bookingData[$key] = sanitizeInput($value);
    }
}

// Create HTML email message with city-specific styling
$htmlMessage = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>{$cityName} Airfreight Inquiry</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: {$primaryColor}; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .section { margin-bottom: 25px; padding: 15px; border: 1px solid #e5e7eb; border-radius: 5px; }
        .section h3 { color: {$primaryColor}; margin-top: 0; border-bottom: 2px solid {$primaryColor}; padding-bottom: 5px; }
        .field { margin-bottom: 8px; }
        .field strong { color: #374151; }
        .urgent { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; margin: 15px 0; }
        .footer { background: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #6b7280; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🚁 NEW AIRFREIGHT BOOKING INQUIRY - {$cityName}</h1>
        <p>Submitted: " . date('Y-m-d H:i:s') . "</p>
        <p>Source: {$cityName} Landing Page</p>
    </div>
    
    <div class='content'>
        <div class='urgent'>
            <strong>📋 URGENT: New airfreight inquiry received</strong><br>
            Please process this booking request promptly.
        </div>";

// Add Shipment Details
$htmlMessage .= "
        <div class='section'>
            <h3>📦 SHIPMENT DETAILS</h3>
            <div class='field'><strong>Shipment Type:</strong> " . ($bookingData['shipment_type'] ?? 'N/A') . "</div>
            <div class='field'><strong>Origin Airport:</strong> " . ($bookingData['origin_airport'] ?? 'N/A') . "</div>
            <div class='field'><strong>Destination Airport:</strong> " . ($bookingData['destination_airport'] ?? 'N/A') . "</div>
            <div class='field'><strong>Number of Pieces:</strong> " . ($bookingData['pieces'] ?? 'N/A') . "</div>
            <div class='field'><strong>Weight:</strong> " . ($bookingData['weight'] ?? 'N/A') . "</div>
            <div class='field'><strong>Dimensions:</strong> " . ($bookingData['dimensions'] ?? 'N/A') . "</div>
            <div class='field'><strong>Cargo Description:</strong> " . ($bookingData['cargo_description'] ?? 'N/A') . "</div>
        </div>";

// Add Shipper Information
$htmlMessage .= "
        <div class='section'>
            <h3>🏢 SHIPPER INFORMATION</h3>
            <div class='field'><strong>Name:</strong> " . $bookingData['shipper_name'] . "</div>
            <div class='field'><strong>Email:</strong> " . $bookingData['shipper_email'] . "</div>
            <div class='field'><strong>Phone:</strong> " . $bookingData['shipper_phone'] . "</div>
            <div class='field'><strong>Address:</strong> " . $bookingData['shipper_address'] . "</div>
            <div class='field'><strong>City:</strong> " . $bookingData['shipper_city'] . "</div>
            <div class='field'><strong>Postal Code:</strong> " . ($bookingData['shipper_postal'] ?? 'N/A') . "</div>
            <div class='field'><strong>Country:</strong> " . $bookingData['shipper_country'] . "</div>
            <div class='field'><strong>Contact Person:</strong> " . ($bookingData['shipper_contact'] ?? 'N/A') . "</div>
        </div>";

// Add Receiver Information
$htmlMessage .= "
        <div class='section'>
            <h3>📍 RECEIVER INFORMATION</h3>
            <div class='field'><strong>Name:</strong> " . $bookingData['receiver_name'] . "</div>
            <div class='field'><strong>Email:</strong> " . ($bookingData['receiver_email'] ?? 'N/A') . "</div>
            <div class='field'><strong>Phone:</strong> " . ($bookingData['receiver_phone'] ?? 'N/A') . "</div>
            <div class='field'><strong>Address:</strong> " . $bookingData['receiver_address'] . "</div>
            <div class='field'><strong>City:</strong> " . $bookingData['receiver_city'] . "</div>
            <div class='field'><strong>Postal Code:</strong> " . ($bookingData['receiver_postal'] ?? 'N/A') . "</div>
            <div class='field'><strong>Country:</strong> " . $bookingData['receiver_country'] . "</div>
            <div class='field'><strong>Contact Person:</strong> " . ($bookingData['receiver_contact'] ?? 'N/A') . "</div>
        </div>";

// Add Logistics Options
$htmlMessage .= "
        <div class='section'>
            <h3>⚙️ LOGISTICS OPTIONS</h3>
            <div class='field'><strong>Transport Mode:</strong> " . ($bookingData['transport_mode'] ?? 'N/A') . "</div>
            <div class='field'><strong>Incoterms:</strong> " . ($bookingData['incoterms'] ?? 'N/A') . "</div>
            <div class='field'><strong>Delivery Speed:</strong> " . ($bookingData['delivery_speed'] ?? 'N/A') . "</div>
            <div class='field'><strong>Insurance:</strong> " . ($bookingData['insurance'] ?? 'N/A') . "</div>
            <div class='field'><strong>Special Handling:</strong> " . ($bookingData['special'] ?? 'N/A') . "</div>
        </div>";

// Add Schedule & Payment
$htmlMessage .= "
        <div class='section'>
            <h3>📅 SCHEDULE & PAYMENT</h3>
            <div class='field'><strong>Pickup Date:</strong> " . ($bookingData['pickup_date'] ?? 'N/A') . "</div>
            <div class='field'><strong>Pickup Time:</strong> " . ($bookingData['pickup_time'] ?? 'N/A') . "</div>
            <div class='field'><strong>Delivery Deadline:</strong> " . ($bookingData['delivery_deadline'] ?? 'N/A') . "</div>
            <div class='field'><strong>Payment Method:</strong> " . ($bookingData['payment_method'] ?? 'N/A') . "</div>
            <div class='field'><strong>Billing Address:</strong> " . ($bookingData['billing_address'] ?? 'N/A') . "</div>
            <div class='field'><strong>Additional Notes:</strong> " . ($bookingData['notes'] ?? 'N/A') . "</div>
            <div class='field'><strong>✅ Terms Accepted:</strong> Yes</div>
        </div>
    </div>
    
    <div class='footer'>
        <p>This inquiry was submitted via the {$cityName} Airfreight Landing Page</p>
        <p>Emex Express - Air Freight Solutions | +49 69 247455280 | ops@emexexpress.de</p>
    </div>
</body>
</html>";

// Plain text version
$textMessage = "NEW AIRFREIGHT BOOKING INQUIRY - {$cityName}\n";
$textMessage .= "========================================\n\n";
$textMessage .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
$textMessage .= "Source: {$cityName} Landing Page\n\n";
$textMessage .= "Shipper: " . $bookingData['shipper_name'] . " (" . $bookingData['shipper_email'] . ")\n";
$textMessage .= "Receiver: " . $bookingData['receiver_name'] . " in " . $bookingData['receiver_city'] . "\n";
$textMessage .= "Route: " . ($bookingData['origin_airport'] ?? 'N/A') . " → " . ($bookingData['destination_airport'] ?? 'N/A') . "\n";
$textMessage .= "Cargo: " . ($bookingData['cargo_description'] ?? 'N/A') . "\n";
$textMessage .= "Weight: " . ($bookingData['weight'] ?? 'N/A') . " | Pieces: " . ($bookingData['pieces'] ?? 'N/A') . "\n";

// Send email via PHPMailer SMTP
try {
    $subject = $subjectPrefix . ' - ' . $bookingData['shipper_name'] . ' to ' . $bookingData['receiver_city'];
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtpConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpConfig['username'];
    $mail->Password   = $smtpConfig['password'];
    $mail->SMTPSecure = $smtpConfig['encryption'];
    $mail->Port       = $smtpConfig['port'];
    
    // Recipients
    $mail->setFrom($smtpConfig['from_email'], $smtpConfig['from_name']);
    $mail->addAddress($adminEmail);
    $mail->addReplyTo($bookingData['shipper_email'], $bookingData['shipper_name']);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $htmlMessage;
    $mail->AltBody = $textMessage;
    
    $mail->send();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Your airfreight inquiry has been submitted successfully. We will contact you shortly.',
        'booking_id' => $bookingPrefix . date('YmdHis') . strtoupper(substr(md5(uniqid()), 0, 6)),
        'city' => $cityName
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send inquiry. Please try again or contact us directly.',
        'error' => $mail->ErrorInfo,
        'city' => $cityName
    ]);
}
?>
