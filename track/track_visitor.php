<?php
header('Content-Type: application/json');

// إعدادات اتصال قاعدة البيانات
$db_host = 'localhost';
$db_user = 'username';
$db_pass = 'password';
$db_name = 'visitor_tracking';

// الاتصال بقاعدة البيانات
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}

// الحصول على البيانات المرسلة
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    die(json_encode(['error' => 'Invalid data received']));
}

// حفظ البيانات الأساسية للزائر
try {
    // التحقق مما إذا كان الزائر موجودًا بالفعل
    $stmt = $pdo->prepare("SELECT id FROM visitors WHERE visitor_id = ?");
    $stmt->execute([$data['visitorId']]);
    $visitor_id = $stmt->fetchColumn();
    
    if (!$visitor_id) {
        // زائر جديد
        $stmt = $pdo->prepare("INSERT INTO visitors (
            visitor_id, first_visit, last_visit, visit_count, 
            user_agent, platform, device_type, screen_resolution, 
            language, timezone, country, city, ip_address, org
        ) VALUES (?, NOW(), NOW(), 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $device_type = 'Desktop';
        if (preg_match('/mobile|android|iphone|ipad|ipod/i', $data['device']['userAgent'])) {
            $device_type = 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $data['device']['userAgent'])) {
            $device_type = 'Tablet';
        }
        
        $screen_resolution = $data['device']['screen']['width'] . 'x' . $data['device']['screen']['height'];
        
        $stmt->execute([
            $data['visitorId'],
            $data['device']['userAgent'],
            $data['device']['platform'],
            $device_type,
            $screen_resolution,
            $data['device']['languages'][0] ?? 'unknown',
            $data['device']['timezone'],
            $data['network']['country'] ?? 'unknown',
            $data['network']['city'] ?? 'unknown',
            $data['network']['ip'] ?? 'unknown',
            $data['network']['org'] ?? 'unknown'
        ]);
        
        $visitor_id = $pdo->lastInsertId();
    } else {
        // تحديث بيانات الزائر الحالي
        $stmt = $pdo->prepare("UPDATE visitors SET 
            last_visit = NOW(), 
            visit_count = visit_count + 1 
            WHERE id = ?");
        $stmt->execute([$visitor_id]);
    }
    
    // حفظ تفاصيل الجلسة
    $stmt = $pdo->prepare("INSERT INTO sessions (
        visitor_id, session_start, session_end, 
        page_url, page_title, referrer, 
        active_duration, inactive_duration, 
        page_load_time, dom_ready_time, network_latency
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $visitor_id,
        $data['session']['startTime'],
        $data['session']['endTime'] ?? date('Y-m-d H:i:s'),
        $data['page']['url'],
        $data['page']['title'],
        $data['page']['referrer'],
        $data['session']['activeDuration'] ?? 0,
        $data['session']['inactiveDuration'] ?? 0,
        $data['behavior']['pageLoadTime'] ?? 0,
        $data['behavior']['domReadyTime'] ?? 0,
        $data['behavior']['networkLatency'] ?? 0
    ]);
    
    // حفظ بصمة الجهاز
    $stmt = $pdo->prepare("INSERT INTO device_fingerprints (
        visitor_id, hardware_concurrency, device_memory, 
        color_depth, pixel_depth, cookie_enabled, 
        java_enabled, do_not_track, touch_support, 
        canvas_fingerprint, webgl_vendor, webgl_renderer
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $visitor_id,
        $data['device']['hardwareConcurrency'] ?? 0,
        $data['device']['deviceMemory'] ?? 0,
        $data['device']['screen']['colorDepth'] ?? 0,
        $data['device']['screen']['pixelDepth'] ?? 0,
        $data['device']['cookieEnabled'] ? 1 : 0,
        $data['device']['javaEnabled'] ? 1 : 0,
        $data['device']['doNotTrack'] ?? 'unknown',
        $data['device']['touchSupport'] ? 1 : 0,
        $data['graphics']['canvas'] ?? '',
        $data['graphics']['webgl']['vendor'] ?? '',
        $data['graphics']['webgl']['renderer'] ?? ''
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
