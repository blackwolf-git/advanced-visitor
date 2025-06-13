<?php
header('Content-Type: application/json');

// إعدادات اتصال قاعدة البيانات
require 'db_config.php'; // استيراد إعدادات قاعدة البيانات

// الحصول على البيانات المرسلة
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['visitorId']) || !isset($data['interactions'])) {
    die(json_encode(['error' => 'Invalid data received']));
}

try {
    // الحصول على معرف الزائر
    $stmt = $pdo->prepare("SELECT id FROM visitors WHERE visitor_id = ?");
    $stmt->execute([$data['visitorId']]);
    $visitor_id = $stmt->fetchColumn();
    
    if (!$visitor_id) {
        die(json_encode(['error' => 'Visitor not found']));
    }
    
    // حفظ تفاعلات الماوس
    if (!empty($data['interactions']['mouseMovements'])) {
        $stmt = $pdo->prepare("INSERT INTO mouse_movements (visitor_id, movement_data, recorded_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $visitor_id,
            json_encode($data['interactions']['mouseMovements'])
        ]);
    }
    
    // حفظ النقرات
    if (!empty($data['interactions']['clicks'])) {
        $stmt = $pdo->prepare("INSERT INTO clicks (visitor_id, click_data, recorded_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $visitor_id,
            json_encode($data['interactions']['clicks'])
        ]);
    }
    
    // حفظ التمرير
    if (!empty($data['interactions']['scrolls'])) {
        $stmt = $pdo->prepare("INSERT INTO scrolls (visitor_id, scroll_data, recorded_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $visitor_id,
            json_encode($data['interactions']['scrolls'])
        ]);
    }
    
    // حفظ ضغطات المفاتيح
    if (!empty($data['interactions']['keyPresses'])) {
        $stmt = $pdo->prepare("INSERT INTO key_presses (visitor_id, key_data, recorded_at) VALUES (?, ?, NOW())");
        $stmt->execute([
            $visitor_id,
            json_encode($data['interactions']['keyPresses'])
        ]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}