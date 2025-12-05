<?php
/** @var array $stats */
/** @var array $recentBookings */
/** @var array $bookingStatusStats */
?>

<style>
  .stat-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
  }
  .stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  .stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
  }
  .stat-card.primary::before { --gradient-start: #4f46e5; --gradient-end: #7c3aed; }
  .stat-card.success::before { --gradient-start: #10b981; --gradient-end: #059669; }
  .stat-card.warning::before { --gradient-start: #f59e0b; --gradient-end: #d97706; }
  .stat-card.info::before { --gradient-start: #06b6d4; --gradient-end: #0891b2; }
  .stat-card.danger::before { --gradient-start: #ef4444; --gradient-end: #dc2626; }
  .stat-card.purple::before { --gradient-start: #8b5cf6; --gradient-end: #7c3aed; }
  .stat-card.teal::before { --gradient-start: #14b8a6; --gradient-end: #0d9488; }
  
  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
  }
  
  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    color: #1f2937;
  }
  
  .stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0;
    font-weight: 500;
  }
  
  .stat-change {
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 4px;
  }
  
  .stat-change.positive {
    color: #10b981;
  }
  
  .stat-change.negative {
    color: #ef4444;
  }
  
  .modern-card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
  }
  
  .modern-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  }
  
  .chart-container {
    position: relative;
    height: 350px;
  }
  
  .quick-action-btn {
    border-radius: 8px;
    transition: all 0.3s ease;
  }
  
  .quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  
  .activity-item {
    border-left: 3px solid #e5e7eb;
    padding-left: 1rem;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
  }
  
  .activity-item:hover {
    border-left-color: #4f46e5;
    background: #f9fafb;
    padding-left: 1.25rem;
  }
  
  .activity-time {
    font-size: 0.75rem;
    color: #9ca3af;
  }
  
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .fade-in {
    animation: fadeIn 0.5s ease-out;
  }
</style>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card stat-card primary fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Tổng số Tour</p>
            <h3 class="stat-value"><?= number_format($stats['total_tours']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-airplane-engines"></i>
          </div>
        </div>
        <a href="<?= BASE_URL ?>?act=tours" class="text-decoration-none text-primary small">
          Xem chi tiết <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card success fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Booking mới hôm nay</p>
            <h3 class="stat-value"><?= number_format($stats['new_bookings_today']) ?></h3>
            <?php if ($stats['new_bookings_today'] > 0): ?>
              <p class="stat-change positive">
                <i class="bi bi-arrow-up"></i> <?= $stats['new_bookings_today'] ?> booking mới
              </p>
            <?php endif; ?>
          </div>
          <div class="stat-icon">
            <i class="bi bi-calendar-check"></i>
          </div>
        </div>
        <a href="<?= BASE_URL ?>?act=bookings" class="text-decoration-none text-success small">
          Xem chi tiết <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card warning fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Doanh thu tháng này</p>
            <h3 class="stat-value"><?= number_format($stats['revenue_this_month'] / 1000000, 1) ?>M</h3>
            <?php 
              $revenueChange = $stats['revenue_last_month'] > 0 
                ? (($stats['revenue_this_month'] - $stats['revenue_last_month']) / $stats['revenue_last_month']) * 100 
                : 0;
            ?>
            <?php if ($revenueChange != 0): ?>
              <p class="stat-change <?= $revenueChange > 0 ? 'positive' : 'negative' ?>">
                <i class="bi bi-arrow-<?= $revenueChange > 0 ? 'up' : 'down' ?>"></i> 
                <?= number_format(abs($revenueChange), 1) ?>% so với tháng trước
              </p>
            <?php endif; ?>
          </div>
          <div class="stat-icon">
            <i class="bi bi-currency-dollar"></i>
          </div>
        </div>
        <a href="<?= BASE_URL ?>?act=bookings" class="text-decoration-none text-warning small">
          Xem chi tiết <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card info fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Hướng dẫn viên</p>
            <h3 class="stat-value"><?= number_format($stats['total_guides']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-person-badge"></i>
          </div>
        </div>
        <a href="<?= BASE_URL ?>?act=guide-profiles" class="text-decoration-none text-info small">
          Xem chi tiết <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Additional Stats Row -->
<div class="row g-3 mb-4">
  <div class="col-lg-3 col-md-6">
    <div class="card stat-card purple fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Tổng Booking</p>
            <h3 class="stat-value"><?= number_format($stats['total_bookings']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-calendar-event"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card danger fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Chờ xác nhận</p>
            <h3 class="stat-value"><?= number_format($stats['pending_bookings']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-clock-history"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card teal fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Đang hoạt động</p>
            <h3 class="stat-value"><?= number_format($stats['active_bookings']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-play-circle"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6">
    <div class="card stat-card success fade-in">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <p class="stat-label">Đã hoàn thành</p>
            <h3 class="stat-value"><?= number_format($stats['completed_bookings']) ?></h3>
          </div>
          <div class="stat-icon">
            <i class="bi bi-check-circle"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card modern-card">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center">
          <h3 class="card-title mb-0">
            <i class="bi bi-graph-up text-primary"></i> Báo cáo Doanh thu 12 tháng
          </h3>
          <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-primary active" data-chart-type="bar">
              <i class="bi bi-bar-chart"></i> Cột
            </button>
            <button type="button" class="btn btn-outline-primary" data-chart-type="line">
              <i class="bi bi-graph-up"></i> Đường
            </button>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card modern-card">
      <div class="card-header bg-white border-bottom">
        <h3 class="card-title mb-0">
          <i class="bi bi-pie-chart text-info"></i> Booking theo trạng thái
        </h3>
      </div>
      <div class="card-body">
        <div class="chart-container">
          <canvas id="statusChart"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Quick Actions & Recent Bookings -->
<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card modern-card">
      <div class="card-header bg-white border-bottom">
        <h3 class="card-title mb-0">
          <i class="bi bi-lightning-charge text-warning"></i> Thao tác nhanh
        </h3>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="<?= BASE_URL ?>?act=booking-create" class="btn btn-primary quick-action-btn">
            <i class="bi bi-plus-circle"></i> Tạo booking mới
          </a>
          <a href="<?= BASE_URL ?>?act=tour-create" class="btn btn-success quick-action-btn">
            <i class="bi bi-airplane-engines"></i> Thêm tour mới
          </a>
          <a href="<?= BASE_URL ?>?act=customer-create" class="btn btn-info quick-action-btn">
            <i class="bi bi-person-plus"></i> Thêm khách hàng
          </a>
          <a href="<?= BASE_URL ?>?act=guide-profile-create" class="btn btn-warning quick-action-btn">
            <i class="bi bi-person-badge"></i> Thêm hướng dẫn viên
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card modern-card">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
          <i class="bi bi-clock-history text-primary"></i> Booking gần đây
        </h3>
        <a href="<?= BASE_URL ?>?act=bookings" class="btn btn-sm btn-outline-primary">
          Xem tất cả
        </a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Tour</th>
                <th>Khách</th>
                <th>Ngày đi</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentBookings)): ?>
                <tr>
                  <td colspan="6" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Chưa có booking nào.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($recentBookings as $b): ?>
                  <tr>
                    <td><strong>#<?= (int)$b['id'] ?></strong></td>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($b['tour_name'] ?? 'Không xác định') ?></div>
                      <small class="text-muted"><?= htmlspecialchars($b['created_by_name'] ?? '') ?></small>
                    </td>
                    <td>
                      <span class="badge bg-info"><?= (int)($b['total_guests'] ?? 0) ?> người</span>
                      <?php if (isset($b['total_amount']) && $b['total_amount'] > 0): ?>
                        <div class="small text-muted"><?= number_format($b['total_amount'], 0, ',', '.') ?> đ</div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div><?= htmlspecialchars($b['start_date']) ?></div>
                      <?php if ($b['end_date']): ?>
                        <small class="text-muted">→ <?= htmlspecialchars($b['end_date']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php
                        $statusColors = [
                          1 => 'warning',
                          2 => 'info',
                          3 => 'success',
                          4 => 'danger'
                        ];
                        $statusColor = $statusColors[$b['status']] ?? 'secondary';
                      ?>
                      <span class="badge bg-<?= $statusColor ?>">
                        <?= htmlspecialchars($b['status_name'] ?? 'Không xác định') ?>
                      </span>
                    </td>
                    <td>
                      <a href="<?= BASE_URL ?>?act=booking-show&id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  let revenueChart = null;
  let chartType = 'bar';

  // Lấy dữ liệu doanh thu
  fetch('<?= BASE_URL ?>?act=dashboard-revenue-data')
    .then(response => response.json())
    .then(data => {
      const ctx = document.getElementById('revenueChart').getContext('2d');
      
      const chartConfig = {
        data: {
          labels: data.months,
          datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: data.revenues,
            backgroundColor: chartType === 'bar' 
              ? 'rgba(79, 70, 229, 0.6)'
              : 'rgba(79, 70, 229, 0.1)',
            borderColor: 'rgba(79, 70, 229, 1)',
            borderWidth: 2,
            fill: chartType === 'line',
            tension: chartType === 'line' ? 0.4 : 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top',
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                  }).format(context.parsed.y);
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return new Intl.NumberFormat('vi-VN', {
                    style: 'currency',
                    currency: 'VND',
                    notation: 'compact'
                  }).format(value);
                }
              },
              grid: {
                color: 'rgba(0, 0, 0, 0.05)'
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      };

      revenueChart = new Chart(ctx, {
        type: chartType,
        ...chartConfig
      });

      // Toggle chart type
      document.querySelectorAll('[data-chart-type]').forEach(btn => {
        btn.addEventListener('click', function() {
          chartType = this.dataset.chartType;
          revenueChart.destroy();
          
          chartConfig.data.datasets[0].backgroundColor = chartType === 'bar' 
            ? 'rgba(79, 70, 229, 0.6)'
            : 'rgba(79, 70, 229, 0.1)';
          chartConfig.data.datasets[0].fill = chartType === 'line';
          chartConfig.data.datasets[0].tension = chartType === 'line' ? 0.4 : 0;
          
          revenueChart = new Chart(ctx, {
            type: chartType,
            ...chartConfig
          });

          document.querySelectorAll('[data-chart-type]').forEach(b => b.classList.remove('active'));
          this.classList.add('active');
        });
      });
    })
    .catch(error => {
      console.error('Error loading revenue data:', error);
    });

  // Pie chart cho booking status
  const statusData = <?= json_encode($bookingStatusStats) ?>;
  const statusLabels = statusData.map(s => s.name);
  const statusCounts = statusData.map(s => parseInt(s.count || 0));
  const statusColors = [
    'rgba(251, 191, 36, 0.8)',   // warning - Chờ xác nhận
    'rgba(59, 130, 246, 0.8)',   // info - Đã cọc
    'rgba(16, 185, 129, 0.8)',   // success - Hoàn tất
    'rgba(239, 68, 68, 0.8)'     // danger - Hủy
  ];

  const statusCtx = document.getElementById('statusChart').getContext('2d');
  new Chart(statusCtx, {
    type: 'doughnut',
    data: {
      labels: statusLabels,
      datasets: [{
        data: statusCounts,
        backgroundColor: statusColors,
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            padding: 15,
            usePointStyle: true
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed / total) * 100).toFixed(1);
              return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
            }
          }
        }
      }
    }
  });
});
</script>
