<?php
/** @var array $history */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Lịch sử tour đã làm</h3>
        <a href="<?= BASE_URL ?>?act=guide-schedule" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-calendar-event"></i> Lịch trình
        </a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th>Tour</th>
              <th style="width: 120px">Ngày khởi hành</th>
              <th style="width: 120px">Ngày kết thúc</th>
              <th style="width: 100px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($history)): ?>
              <tr>
                <td colspan="5" class="text-center py-4">
                  <p class="text-muted mb-0">Chưa có lịch sử tour nào.</p>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($history as $h): ?>
                <tr>
                  <td>#<?= (int)$h['id'] ?></td>
                  <td><?= htmlspecialchars($h['tour_name'] ?? 'Không xác định') ?></td>
                  <td><?= htmlspecialchars($h['start_date']) ?></td>
                  <td><?= htmlspecialchars($h['end_date'] ?? '-') ?></td>
                  <td class="text-end">
                    <a href="<?= BASE_URL ?>?act=booking-show&id=<?= (int)$h['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
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

