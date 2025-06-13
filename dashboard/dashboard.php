<?php
require 'db_config.php';
require 'auth_check.php'; // ملف للتحقق من صلاحيات الدخول

// إحصائيات عامة
$stats = [
    'total_visitors' => 0,
    'unique_visitors' => 0,
    'returning_visitors' => 0,
    'avg_session' => 0,
    'devices' => [],
    'countries' => []
];

try {
    // إجمالي الزيارات
    $stmt = $pdo->query("SELECT COUNT(*) FROM sessions");
    $stats['total_visitors'] = $stmt->fetchColumn();
    
    // الزوار الفريدون
    $stmt = $pdo->query("SELECT COUNT(*) FROM visitors");
    $stats['unique_visitors'] = $stmt->fetchColumn();
    
    // الزوار العائدون
    $stmt = $pdo->query("SELECT COUNT(*) FROM visitors WHERE visit_count > 1");
    $stats['returning_visitors'] = $stmt->fetchColumn();
    
    // متوسط مدة الجلسة
    $stmt = $pdo->query("SELECT AVG(active_duration + inactive_duration) FROM sessions");
    $stats['avg_session'] = round($stmt->fetchColumn() / 1000, 2); // تحويل إلى ثواني
    
    // توزيع الأجهزة
    $stmt = $pdo->query("SELECT device_type, COUNT(*) as count FROM visitors GROUP BY device_type");
    $stats['devices'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // توزيع الدول
    $stmt = $pdo->query("SELECT country, COUNT(*) as count FROM visitors GROUP BY country ORDER BY count DESC LIMIT 10");
    $stats['countries'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
} catch (PDOException $e) {
    die("Error fetching stats: " . $e->getMessage());
}

// آخر الزوار
$recent_visitors = [];
try {
    $stmt = $pdo->query("
        SELECT v.id, v.visitor_id, v.last_visit, v.country, v.city, v.device_type, 
               COUNT(s.id) as session_count, MAX(s.active_duration + s.inactive_duration) as max_session
        FROM visitors v
        LEFT JOIN sessions s ON v.id = s.visitor_id
        GROUP BY v.id
        ORDER BY v.last_visit DESC
        LIMIT 10
    ");
    $recent_visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching recent visitors: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحليل الزوار</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        .card-counter {
            box-shadow: 2px 2px 10px #DADADA;
            margin: 5px;
            padding: 20px 10px;
            background-color: #fff;
            height: 100px;
            border-radius: 5px;
            transition: .3s linear all;
        }
        .card-counter:hover {
            box-shadow: 4px 4px 20px #DADADA;
            transition: .3s linear all;
        }
        .card-counter.primary {
            background-color: #007bff;
            color: #FFF;
        }
        .card-counter.danger {
            background-color: #ef5350;
            color: #FFF;
        }  
        .card-counter.success {
            background-color: #66bb6a;
            color: #FFF;
        }  
        .card-counter.info {
            background-color: #26c6da;
            color: #FFF;
        }  
        .card-counter i {
            font-size: 5em;
            opacity: 0.2;
        }
        .card-counter .count-numbers {
            position: absolute;
            right: 35px;
            top: 20px;
            font-size: 32px;
            display: block;
        }
        .card-counter .count-name {
            position: absolute;
            right: 35px;
            top: 65px;
            font-style: italic;
            text-transform: capitalize;
            opacity: 0.9;
            display: block;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <h1 class="text-center">لوحة تحليل الزوار</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card-counter primary">
                    <span class="count-numbers"><?= number_format($stats['total_visitors']) ?></span>
                    <span class="count-name">إجمالي الزيارات</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter success">
                    <span class="count-numbers"><?= number_format($stats['unique_visitors']) ?></span>
                    <span class="count-name">زائر فريد</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter info">
                    <span class="count-numbers"><?= number_format($stats['returning_visitors']) ?></span>
                    <span class="count-name">زائر عائد</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter danger">
                    <span class="count-numbers"><?= $stats['avg_session'] ?></span>
                    <span class="count-name">متوسط الجلسة (ثانية)</span>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>توزيع الأجهزة</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="deviceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>أهم الدول</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="countryChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>آخر الزوار</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>معرف الزائر</th>
                                        <th>آخر زيارة</th>
                                        <th>البلد</th>
                                        <th>المدينة</th>
                                        <th>نوع الجهاز</th>
                                        <th>عدد الجلسات</th>
                                        <th>أطول جلسة (ثانية)</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_visitors as $visitor): ?>
                                    <tr>
                                        <td><?= substr($visitor['visitor_id'], 0, 8) ?>...</td>
                                        <td><?= date('Y-m-d H:i', strtotime($visitor['last_visit'])) ?></td>
                                        <td><?= $visitor['country'] ?? 'غير معروف' ?></td>
                                        <td><?= $visitor['city'] ?? 'غير معروف' ?></td>
                                        <td><?= $visitor['device_type'] ?? 'غير معروف' ?></td>
                                        <td><?= $visitor['session_count'] ?></td>
                                        <td><?= round($visitor['max_session'] / 1000, 2) ?></td>
                                        <td>
                                            <a href="visitor_details.php?id=<?= $visitor['id'] ?>" class="btn btn-sm btn-info">التفاصيل</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // رسم مخطط الأجهزة
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceChart = new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_keys($stats['devices'])) ?>,
                datasets: [{
                    data: <?= json_encode(array_values($stats['devices'])) ?>,
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'left',
                    }
                }
            }
        });
        
        // رسم مخطط الدول
        const countryCtx = document.getElementById('countryChart').getContext('2d');
        const countryChart = new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_keys($stats['countries'])) ?>,
                datasets: [{
                    label: 'عدد الزوار',
                    data: <?= json_encode(array_values($stats['countries'])) ?>,
                    backgroundColor: '#17a2b8'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
