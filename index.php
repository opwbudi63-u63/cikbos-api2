<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database.php';

$message = '';

// Proses Pembuatan Key Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duration = (int)($_POST['duration'] ?? 1);
    $unit = $_POST['unit'] ?? 'day';
    $max_device = (int)($_POST['max_device'] ?? 1);

    // Hitung tanggal expired
    $now = new DateTime();
    if ($unit === 'hour') {
        $now->modify("+{$duration} hours");
    } else {
        $now->modify("+{$duration} days");
    }
    $expired_at = $now->format('Y-m-d H:i:s');

    // Generate Key Acak
    $license_key = "CIKBOS-" . strtoupper(bin2hex(random_bytes(4)));

    try {
        $stmt = $conn->prepare("INSERT INTO keys (license_key, max_device, expired_at) VALUES (:key, :max_dev, :exp)");
        $stmt->execute([
            ':key' => $license_key,
            ':max_dev' => $max_device,
            ':exp' => $expired_at
        ]);
        $message = "✅ Key Berhasil Dibuat: <b>{$license_key}</b> (Expired: {$expired_at})";
    } catch (PDOException $e) {
        $message = "❌ Gagal Buat Key: " . $e->getMessage();
    }
}

// Ambil Semua Daftar Key
$stmt = $conn->query("SELECT * FROM keys ORDER BY id DESC");
$keys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cikbos License Panel</title>
    <style>
        body { font-family: sans-serif; background: #121212; color: #fff; padding: 20px; }
        .card { background: #1e1e1e; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; }
        input, select, button { width: 100%; padding: 10px; margin: 8px 0; border-radius: 4px; border: none; }
        button { background: #ff9800; color: #fff; font-weight: bold; cursor: pointer; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; font-size: 12px; }
        th { background: #2c2c2c; }
        .msg { background: #2e7d32; padding: 10px; border-radius: 4px; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="card">
    <h2>⚡ CIKBOS LICENSE PANEL</h2>

    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Durasi Lisensi:</label>
        <input type="number" name="duration" value="1" min="1" required>
        
        <select name="unit">
            <option value="hour">Jam</option>
            <option value="day" selected>Hari</option>
        </select>

        <label>Max Device:</label>
        <input type="number" name="max_device" value="1" min="1" required>

        <button type="submit">BUAT KEY BARU</button>
    </form>

    <h3>Daftar Lisensi</h3>
    <table>
        <tr>
            <th>Key</th>
            <th>Max HP</th>
            <th>Expired</th>
        </tr>
        <?php foreach ($keys as $k): ?>
        <tr>
            <td><b><?= $k['license_key'] ?></b></td>
            <td><?= $k['max_device'] ?></td>
            <td><?= $k['expired_at'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
