<?php
require 'db_config.php';
require 'auth_check.php';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$visitor_id = (int)$_GET['id'];

// الحصول على بيانات الزائر الأساسية
try {
    $stmt = $pdo->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmt->execute([$visitor_id]);
    $visitor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$visitor) {
        die("Visitor not found");
    }
} catch (PDOException $e) {
    die("Error fetching visitor: " . $e->getMessage());
}

// الحصول على جلسات الزائر
try {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE visitor_id = ? ORDER BY session_start DESC");
    $stmt->execute([$visitor_id]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching sessions: " . $e->getMessage());
}

// الحصول على بصمة الجهاز
try {
    $stmt = $pdo->prepare("SELECT * FROM device_fingerprints WHERE visitor_id = ?");
    $stmt->execute([$visitor_id]);
    $fingerprint = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching fingerprint: " . $e->getMessage());
}

// تحليل تفاعلات الزائر
$interactions = [
    'total_clicks' => 0,
    'total_movements' => 0,
    'total_scrolls' => 0,
    'total_keypresses' => 0
];

try {
    // عدد النقرات
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks WHERE visitor_id = ?");
    $stmt->execute([$visitor_id]);
    $interactions['total_clicks'] = $stmt->fetchColumn();
    
    // عدد حركات الماوس
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT JSON_LENGTH(movement_data) as cnt FROM mouse_movements WHERE visitor_id = ?) as t");
    $stmt->execute([$visitor_id]);
    $interactions['total_movements'] = $stmt->fetchColumn();
    
    // عدد مرات التمرير
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT JSON_LENGTH(scroll_data) as cnt FROM scrolls WHERE visitor_id = ?) as t");
    $stmt->execute([$visitor_id]);
    $interactions['total_scrolls'] = $stmt->fetchColumn();
    
    // عدد ضغطات المفاتيح
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT JSON_LENGTH(key_data) as cnt FROM key_presses WHERE visitor_id = ?) as t");
    $stmt->execute([$visitor_id]);
    $interactions['total_keypresses'] = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    die("Error fetching interactions: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الزائر - <?= substr($visitor['visitor_id'], 0, 8) ?>...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        .info-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
        }
        .fingerprint-badge {
            font-family: monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>تفاصيل الزائر</h1>
            <a href="dashboard.php" class="btn btn-secondary">العودة للوحة التحكم</a>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">المعلومات الأساسية</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>معرف الزائر:</strong> <?= $visitor['visitor_id'] ?></p>
                                <p><strong>أول زيارة:</strong> <?= date('Y-m-d H:i', strtotime($visitor['first_visit'])) ?></p>
                                <p><strong>آخر زيارة:</strong> <?= date('Y-m-d H:i', strtotime($visitor['last_visit'])) ?></p>
                                <p><strong>عدد الزيارات:</strong> <?= $visitor['visit_count'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>البلد:</strong> <?= $visitor['country'] ?? 'غير معروف' ?></p>
                                <p><strong>المدينة:</strong> <?= $visitor['city'] ?? 'غير معروف' ?></p>
                                <p><strong>مزود الخدمة:</strong> <?= $visitor['org'] ?? 'غير معروف' ?></p>
                                <p><strong>عنوان IP:</strong> <?= $visitor['ip_address'] ?? 'غير معروف' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">معلومات الجهاز</h5>
                        <p><strong>نوع الجهاز:</strong> <?= $visitor['device_type'] ?? 'غير معروف' ?></p>
                        <p><strong>دقة الشاشة:</strong> <?= $visitor['screen_resolution'] ?? 'غير معروف' ?></p>
                        <p><strong>المتصفح:</strong> <?= $visitor['user_agent'] ?></p>
                        <p><strong>المنصة:</strong> <?= $visitor['platform'] ?? 'غير معروف' ?></p>
                        <p><strong>اللغة:</strong> <?= $visitor['language'] ?? 'غير معروف' ?></p>
                        <p><strong>المنطقة الزمنية:</strong> <?= $visitor['timezone'] ?? 'غير معروف' ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">بصمة الجهاز</h5>
                        <?php if ($fingerprint): ?>
                            <p><strong>عدد الأنوية:</strong> <?= $fingerprint['hardware_concurrency'] ?? 'غير معروف' ?></p>
                            <p><strong>ذاكرة الجهاز:</strong> <?= $fingerprint['device_memory'] ?? 'غير معروف' ?> GB</p>
                            <p><strong>عمق الألوان:</strong> <?= $fingerprint['color_depth'] ?? 'غير معروف' ?></p>
                            <p><strong>تفعيل الكوكيز:</strong> <?= $fingerprint['cookie_enabled'] ? 'نعم' : 'لا' ?></p>
                            <p><strong>تفعيل الجافا:</strong> <?= $fingerprint['java_enabled'] ? 'نعم' : 'لا' ?></p>
                            <p><strong>عدم التتبع (DNT):</strong> <?= $fingerprint['do_not_track'] ?? 'غير معروف' ?></p>
                            <p><strong>دعم اللمس:</strong> <?= $fingerprint['touch_support'] ? 'نعم' : 'لا' ?></p>
                            <p><strong>بائع WebGL:</strong> <?= $fingerprint['webgl_vendor'] ?? 'غير معروف' ?></p>
                            <p><strong>عرض WebGL:</strong> <?= $fingerprint['webgl_renderer'] ?? 'غير معروف' ?></p>
                            <p><strong>بصمة Canvas:</strong> 
                                <span class="badge bg-secondary fingerprint-badge"><?= substr($fingerprint['canvas_fingerprint'], 0, 50) ?>...</span>
                            </p>
                        <?php else: ?>
                            <p class="text-muted">لا توجد بيانات بصمة لهذا الزائر</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card info-card">
                    <div class="card-body">
                        <h5 class="card-title">تحليل التفاعلات</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>إجمالي النقرات:</strong> <?= $interactions['total_clicks'] ?></p>
                                <p><strong>إجمالي حركات الماوس:</strong> <?= $interactions['total_movements'] ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>إجمالي التمرير:</strong> <?= $interactions['total_scrolls'] ?></p>
                                <p><strong>إجمالي ضغطات المفاتيح:</strong> <?= $interactions['total_keypresses'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-header">
                <h5>سجل الجلسات</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>تاريخ الجلسة</th>
                                <th>مدة الجلسة (ثانية)</th>
                                <th>وقت التحميل (مللي ثانية)</th>
                                <th>الصفحة</th>
                                <th>المصدر</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $session): ?>
                            <tr>
                                <td><?= date('Y-m-d H:i', strtotime($session['session_start'])) ?></td>
                                <td><?= round(($session['active_duration'] + $session['inactive_duration']) / 1000, 2) ?></td>
                                <td><?= $session['page_load_time'] ?></td>
                                <td><?= substr($session['page_url'], 0, 50) ?>...</td>
                                <td><?= $session['referrer'] ? substr($session['referrer'], 0, 30) . '...' : 'مباشر' ?></td>
                                <td>
                                    <a href="session_details.php?id=<?= $session['id'] ?>" class="btn btn-sm btn-info">التفاصيل</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>