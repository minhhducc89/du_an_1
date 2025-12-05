<?php

class DashboardController{
    public function index(): void
    {
      requireAdmin();

      $pdo = getDB();
      if($pdo === null){
        throw new RuntimeException('Không thể kết nối cơ sở dữ liệu');
      }
      
      // Thống kê dữ liệu
      $stats = [
        'total_tour' => 0,
        'total_bookings' => 0,
        'total_guides' => 0,
        'total_categories' => 0,
        'total_customers' => 0,
        'new_bookings_today' => 0,
        'pending_bookings' => 0,
        'active_bookings' => 0,
        'completted_bookings' => 0,
        'revenue_this_month' => 0
        'revenue_last_month' => 0
        'revenue_today' => 0
      ]

      // Đếm tour
      $stmt = $pdo->query('SELECT COUNT(*) as count FROM tour ');
      $row = $stmt->featch();
      $stats['total_tours'] = (int)($row['count'] ?? 0);
      
      // bookings
      $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings ');
      $row = $stmt->featch();
      $stats['total_bookings'] = (int)($row['count'] ?? 0);
      //guides
      $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'guide' AND status = 1");
      $row = $stmt->featch();
      $stats['total_guides'] = (int)($row['count'] ?? 0);
      
      //categories
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE status = 1");
      $row = $stmt->featch();
      $stats['total_guides'] = (int)($row['count'] ?? 0);

      // customers
      $stmt = $pdo->query('SELECT COUNT(*) as count FROM customers WHERE status = 1');
      $row = $stmt->featch();
      $stats['total_customers'] = (int)($row['count'] ?? 0);

      // số booking mới trong ngày
      $today = date('Y-m-d');
      $stmt = $pdo->pepare('SELECT COUNT(*) as count FROM bookings WHERE DATE(create_at) = :today');
      $stmt->execute([':today'=>$today]);
      $row = $stmt->fetch();
      $stats['new_bookings_today']= (int)($row['count'] ?? 0);
      
      //booking dang cho xac nhan
      $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings WHERE status = 1 ');
      $row = $stmt->featch();
      $stats['pending_bookings'] = (int)($row['count'] ?? 0);

      //booking dang hoat dong
      $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings WHERE status = 2 ');
      $row = $stmt->featch();
      $stats['active_bookings'] = (int)($row['count'] ?? 0);

      //booking da hoan thanh
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM bookings WHERE status = 3 ');
      $row = $stmt->featch();
      $stats['completed_bookings'] = (int)($row['count'] ?? 0);

      //Doanh thu thang hien tai
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("
       SELECT service_detail
       FROM bookings
       WHERE status IN (2,3)
       AND DATE_FORMAT(create_at, '%Y-%m') = :month
    ");
    $stmt->execute([':month' => $currentMonth]);
    $revenue = 0 ;
    while($row = $stmt->fetch()){
      if(!empty($row['service_detail'])){
        $service = json_decode($row['service_deltail'], true);
        if (is_array($service) && isset($service['total_amount'])) {
          $revenue += (float)$service['total_amount'];
        }
      }
    }
    $stats['revenue_this_month'] = $revenue;

    // Doanh thu thang truoc
    $lastMonth = date('Y-m', strtotime('-1 month'));
    $stmt = $pdo->prepare("
       SELECT service_detail
       FROM bookings
       WHERE status IN (2,3)
       AND DATE_FORMAT(create_at, '%Y-%m') = :month
    ");
    $stmt->execute([':month' => $lastMonth]);
    $revenueLast = 0;
     while($row = $stmt->fetch()){
      if(!empty($row['service_detail'])){
        $service = json_decode($row['service_deltail'], true);
        if (is_array($service) && isset($service['total_amount'])) {
          $revenueLast += (float)$service['total_amount'];
        }
      }
    }
    $stats['revenue_last_month'] = $revenueLast;


  // doanh thu hom nay
    $stmt = $pdo->prepare("
       SELECT service_detail
       FROM bookings
       WHERE status IN (2,3)
       AND DATE(created_at) = :today
       ")
 
    $stmt->execute([':today' => $today]);
    $revenueToday = 0;
     while($row = $stmt->fetch()){
      if(!empty($row['service_detail'])){
        $service = json_decode($row['service_deltail'], true);
        if (is_array($service) && isset($service['total_amount'])) {
          $revenueToday += (float)$service['total_amount'];
        }
      }
    }
   $stats['revenue_today'] = $revenueToday;

   //booking gan day

   $stmt = $pdo->query("
   SELECT b.*, t.name AS tour_name, ts.name AS status_name,
   u.name AS created_by_name
   FROM bookings b
   LEFT JOIN tour t ON t.id = b.tour_id
   LEFT JOIN tour_statuses ts ON ts.id = b.status
   LEFT JOIN users u ON u.id = b.created_by
   ORDER BY b.created_at DESC
   LIMIT 10");
  $recentBookings = $stmt->fetchAll();

  // service_detail

  foreach($recentBookings as &$b){
    if (!empty($b['service_detail'])) {
    $service = json_decode($b['service_detail'], true);
    if (is_array($service)){
      $b['total_guests'] = $service['total_guests'] ?? 0;
      $b['total_amount'] = $service['total_amount'] ?? 0;
    }
  }
}
unset($b);
 // thong ke booking theo trang thai
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

 view('layout.AdminLayout',[
  'title' => 'Dashboard',
  'pageTitle' => 'Dashboard',
  'content' => $content,
  'breadcrumb' => [
    ['label' => "Dashboard", 'url' => url('dashboard'), 'active' => true],
  ],
]);
}

// API: lay du lieu doanh thu cua 12 thang
public function revenueData(): void
{
  requireAdmin();
  $pdo = getDB();
  id($pdo === null){
    header('Content-Type: application/json');
    echo json_decode(['error' => 'Database connection failed']);
    exit;
    }

    // lay 12 thang gan nhat

    $months = [];
    $revenues = [];
    for(i =11; $i >= 0; $i--){
      $date = date('Y-m', strtotime("-$i months"));
      $months[] = date('m/Y' , strtotime("-$i months"));

      $stmt = $pdo->prepare("
      SELECT service_detail
      FROM bookings
      WHERE status IN (2, 3)
      AND DATE_FORMAT(created_at, '%Y-%m') = :month
      ");

      $stmt->execute([':month' => $date]);

      $revenue = 0 ;
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
    echo json_decode([
      'months' => $months,
      'revenues' => $revenues,
    ]);
    exit;
}
}