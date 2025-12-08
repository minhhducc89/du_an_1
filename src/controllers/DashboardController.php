<?php

class DashboardController
{
    // Dashboard cho admin
    public function index(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
        }

        // Thống kê tổng quan
        $stats = [
            'total_tours' => 0,
            'total_bookings' => 0,
            'total_guides' => 0,
            'total_categories' => 0,
            'total_customers' => 0,
            'new_bookings_today' => 0,
            'pending_bookings' => 0,
            'active_bookings' => 0,
            'completed_bookings' => 0,
            'revenue_this_month' => 0,
            'revenue_last_month' => 0,
            'revenue_today' => 0,
        ];

        // Đếm tours
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM tours');
        $row = $stmt->fetch();
        $stats['total_tours'] = (int)($row['count'] ?? 0);

        // Đếm bookings
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings');
        $row = $stmt->fetch();
        $stats['total_bookings'] = (int)($row['count'] ?? 0);

        // Đếm guides
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'guide' AND status = 1");
        $row = $stmt->fetch();
        $stats['total_guides'] = (int)($row['count'] ?? 0);

        // Đếm categories
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM categories WHERE status = 1');
        $row = $stmt->fetch();
        $stats['total_categories'] = (int)($row['count'] ?? 0);

        // Đếm customers
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM customers WHERE status = 1');
        $row = $stmt->fetch();
        $stats['total_customers'] = (int)($row['count'] ?? 0);

        // Số booking mới trong ngày
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = :today");
        $stmt->execute([':today' => $today]);
        $row = $stmt->fetch();
        $stats['new_bookings_today'] = (int)($row['count'] ?? 0);

        // Booking đang chờ xác nhận (status = 1)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 1");
        $row = $stmt->fetch();
        $stats['pending_bookings'] = (int)($row['count'] ?? 0);

        // Booking đang hoạt động (status = 2)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 2");
        $row = $stmt->fetch();
        $stats['active_bookings'] = (int)($row['count'] ?? 0);

        // Booking đã hoàn thành (status = 3)
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 3");
        $row = $stmt->fetch();
        $stats['completed_bookings'] = (int)($row['count'] ?? 0);

        // Doanh thu tháng hiện tại
        $currentMonth = date('Y-m');
        $stmt = $pdo->prepare("
            SELECT service_detail
            FROM bookings
            WHERE status IN (2, 3)
              AND DATE_FORMAT(created_at, '%Y-%m') = :month
        ");
        $stmt->execute([':month' => $currentMonth]);
        $revenue = 0;
        while ($row = $stmt->fetch()) {
            if (!empty($row['service_detail'])) {
                $service = json_decode($row['service_detail'], true);
                if (is_array($service) && isset($service['total_amount'])) {
                    $revenue += (float)$service['total_amount'];
                }
            }
        }
        $stats['revenue_this_month'] = $revenue;

        // Doanh thu tháng trước
        $lastMonth = date('Y-m', strtotime('-1 month'));
        $stmt = $pdo->prepare("
            SELECT service_detail
            FROM bookings
            WHERE status IN (2, 3)
              AND DATE_FORMAT(created_at, '%Y-%m') = :month
        ");
        $stmt->execute([':month' => $lastMonth]);
        $revenueLast = 0;
        while ($row = $stmt->fetch()) {
            if (!empty($row['service_detail'])) {
                $service = json_decode($row['service_detail'], true);
                if (is_array($service) && isset($service['total_amount'])) {
                    $revenueLast += (float)$service['total_amount'];
                }
            }
        }
        $stats['revenue_last_month'] = $revenueLast;

        // Doanh thu hôm nay
        $stmt = $pdo->prepare("
            SELECT service_detail
            FROM bookings
            WHERE status IN (2, 3)
              AND DATE(created_at) = :today
        ");
        $stmt->execute([':today' => $today]);
        $revenueToday = 0;
        while ($row = $stmt->fetch()) {
            if (!empty($row['service_detail'])) {
                $service = json_decode($row['service_detail'], true);
                if (is_array($service) && isset($service['total_amount'])) {
                    $revenueToday += (float)$service['total_amount'];
                }
            }
        }
        $stats['revenue_today'] = $revenueToday;

        // Booking gần đây
        $stmt = $pdo->query("
            SELECT b.*, t.name AS tour_name, ts.name AS status_name,
                   u.name AS created_by_name
            FROM bookings b
            LEFT JOIN tours t ON t.id = b.tour_id
            LEFT JOIN tour_statuses ts ON ts.id = b.status
            LEFT JOIN users u ON u.id = b.created_by
            ORDER BY b.created_at DESC
            LIMIT 10
        ");
        $recentBookings = $stmt->fetchAll();

        // Decode service_detail để lấy thông tin khách
        foreach ($recentBookings as &$b) {
            if (!empty($b['service_detail'])) {
                $service = json_decode($b['service_detail'], true);
                if (is_array($service)) {
                    $b['total_guests'] = $service['total_guests'] ?? 0;
                    $b['total_amount'] = $service['total_amount'] ?? 0;
                }
            }
        }
        unset($b);

        // Thống kê booking theo trạng thái (cho pie chart)
        $stmt = $pdo->query("
            SELECT ts.id, ts.name, COUNT(b.id) as count
            FROM tour_statuses ts
            LEFT JOIN bookings b ON b.status = ts.id
            GROUP BY ts.id, ts.name
            ORDER BY ts.id
        ");
        $bookingStatusStats = $stmt->fetchAll();

        ob_start();
        include view_path('admin.dashboard.index');
        $content = ob_get_clean();

        view('layouts.AdminLayout', [
            'title'      => 'Dashboard',
            'pageTitle'  => 'Dashboard',
            'content'    => $content,
            'breadcrumb' => [
                ['label' => 'Dashboard', 'url' => url('dashboard'), 'active' => true],
            ],
        ]);
    }

    // API: Lấy dữ liệu doanh thu 12 tháng
    public function revenueData(): void
    {
        requireAdmin();

        $pdo = getDB();
        if ($pdo === null) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        // Lấy 12 tháng gần nhất
        $months = [];
        $revenues = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-$i months"));
            $months[] = date('m/Y', strtotime("-$i months"));
            
            $stmt = $pdo->prepare("
                SELECT service_detail
                FROM bookings
                WHERE status IN (2, 3)
                  AND DATE_FORMAT(created_at, '%Y-%m') = :month
            ");
            $stmt->execute([':month' => $date]);
            
            $revenue = 0;
            while ($row = $stmt->fetch()) {
                if (!empty($row['service_detail'])) {
                    $service = json_decode($row['service_detail'], true);
                    if (is_array($service) && isset($service['total_amount'])) {
                        $revenue += (float)$service['total_amount'];
                    }
                }
            }
            $revenues[] = $revenue;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'months' => $months,
            'revenues' => $revenues,
        ]);
        exit;
    }
}

