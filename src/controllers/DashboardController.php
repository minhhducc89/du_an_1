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

    
    }

   

}