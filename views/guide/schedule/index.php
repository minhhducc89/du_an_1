<?php
/** @var array $schedules */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Lịch trình tour của tôi</h3>
        <a href="<?= BASE_URL ?>?act=guide-history" class="btn btn-outline-secondary btn-sm">
          <i class="bi bi-clock-history"></i> Lịch sử
        </a>
      </div>
      <div class="card-body">
        <?php if (empty($schedules)): ?>
          <div class="text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted"></i>
            <p class="text-muted mt-3 mb-0">Bạn chưa được phân công tour nào.</p>
          </div>
        <?php else: ?>
          <!-- Card View - Tối ưu cho mobile -->
          <div class="row g-3">
            <?php foreach ($schedules as $s): ?>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                  <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                      <h5 class="card-title mb-0"><?= htmlspecialchars($s['tour_name'] ?? 'Không xác định') ?></h5>
                      <span class="badge bg-info">#<?= (int)$s['id'] ?></span>
                    </div>
                    <hr class="my-2">
                    <div class="mb-2">
                      <i class="bi bi-calendar-event text-primary"></i>
                      <strong>Ngày đi:</strong> <?= htmlspecialchars($s['start_date']) ?>
                    </div>
                    <?php if ($s['end_date']): ?>
                      <div class="mb-2">
                        <i class="bi bi-calendar-check text-success"></i>
                        <strong>Ngày về:</strong> <?= htmlspecialchars($s['end_date']) ?>
                      </div>
                    <?php endif; ?>
                    <div class="mb-2">
                      <i class="bi bi-people text-warning"></i>
                      <strong>Số khách:</strong> <?= (int)($s['total_guests'] ?? 0) ?> người
                    </div>
                    <div class="mb-2">
                      <i class="bi bi-geo-alt text-danger"></i>
                      <strong>Điểm đón:</strong> 
                      <span class="text-muted small">
                        <?php
                          $service = [];
                          if (!empty($s['service_detail'])) {
                              $service = json_decode($s['service_detail'], true);
                          }
                          $address = is_array($service) && isset($service['customer']['address']) 
                            ? $service['customer']['address'] 
                            : 'Chưa cập nhật';
                          echo htmlspecialchars($address);
                        ?>
                      </span>
                    </div>
                    <?php
                      $specialReqs = is_array($service) && isset($service['special_requirements']) && !empty($service['special_requirements'])
                        ? $service['special_requirements']
                        : null;
                    ?>
                    <?php if ($specialReqs): ?>
                      <div class="mb-2">
                        <i class="bi bi-exclamation-triangle text-warning"></i>
                        <strong>Yêu cầu đặc biệt:</strong>
                        <div class="alert alert-warning alert-sm mb-0 mt-1 p-2" style="font-size: 0.85rem;">
                          <?= nl2br(htmlspecialchars($specialReqs)) ?>
                        </div>
                      </div>
                    <?php endif; ?>
                    <div class="mb-3">
                      <span class="badge bg-secondary">
                        <?= htmlspecialchars($s['status_name'] ?? 'Không xác định') ?>
                      </span>
                    </div>
                    <div class="d-grid gap-2">
                      <a href="<?= BASE_URL ?>?act=guide-checkin&id=<?= (int)$s['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-check-circle"></i> Điểm danh
                      </a>
                      <div class="btn-group" role="group">
                        <a href="<?= BASE_URL ?>?act=guide-diary&id=<?= (int)$s['id'] ?>" class="btn btn-outline-info btn-sm">
                          <i class="bi bi-journal-text"></i> Nhật ký
                        </a>
                        <a href="<?= BASE_URL ?>?act=guide-schedule-detail&id=<?= (int)$s['id'] ?>" class="btn btn-outline-secondary btn-sm">
                          <i class="bi bi-eye"></i> Chi tiết
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

