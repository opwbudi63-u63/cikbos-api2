<?php
header('Content-Type: application/json');
require_once 'database.php';

$key = $_REQUEST['key'] ?? '';
$device_id = $_REQUEST['device_id'] ?? '';

if (empty($key) || empty($device_id)) {
    echo json_encode(["success" => false, "valid" => false, "message" => "INVALID_INPUT"]);
    exit;
}

// 1. Cek Key
$stmt = $conn->prepare("SELECT * FROM keys WHERE license_key = :key");
$stmt->execute([':key' => $key]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$license) {
    echo json_encode(["success" => false, "valid" => false, "message" => "NOT_FOUND"]);
    exit;
}

// 2. Cek Expired
$now = new DateTime();
$expired = new DateTime($license['expired_at']);

if ($now > $expired) {
    echo json_encode(["success" => false, "valid" => false, "message" => "EXPIRED"]);
    exit;
}

// 3. Cek Device
$rawDevices = trim($license['devices'] ?? '');
$devices = $rawDevices !== '' ? array_map('trim', explode(',', $rawDevices)) : [];
$max_device = (int)$license['max_device'];

if (!in_array($device_id, $devices)) {
    if (count($devices) >= $max_device) {
        echo json_encode(["success" => false, "valid" => false, "message" => "MAX_DEVICE"]);
        exit;
    }
    
    $devices[] = $device_id;
    $updated_devices = implode(',', $devices);
    
    $updateStmt = $conn->prepare("UPDATE keys SET devices = :dev WHERE license_key = :key");
    $updateStmt->execute([':dev' => $updated_devices, ':key' => $key]);
}

// Respon Valid
echo json_encode([
    "success" => true,
    "valid" => true,
    "message" => "VALID",
    "expired_at" => $license['expired_at']
]);
?>
