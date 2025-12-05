<?php
/** @var array $bookings */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách booking</h3>
        <a href="<?= BASE_URL ?>?act=booking-create" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-circle me-1"></i> Tạo booking
        </a>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-hover table-striped mb-0">
          <thead>
            <tr>
              <th style="width: 60px">ID</th>
              <th>Tour</th>
              <th style="width: 100px">Loại</th>
              <th style="width: 120px">Ngày khởi hành</th>
              <th style="width: 120px">Ngày kết thúc</th>
              <th style="width: 100px">Tổng khách</th>
              <th style="width: 140px">Tổng tiền</th>
              <th style="width: 140px">Trạng thái</th>
              <th style="width: 140px">HDV phụ trách</th>
              <th style="width: 200px" class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($bookings)): ?>
              <tr>
                <td colspan="10" class="text-center py-4">Chưa có booking nào.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($bookings as $b): ?>
                <?php
                  $service = [];
                  if (!empty($b['service_detail'])) {
                      $decoded = json_decode($b['service_detail'], true);
                      if (is_array($decoded)) {
                          $service = $decoded;
                      }
                  }
                  $totalGuests = $service['total_guests'] ?? null;
                  $totalAmount = $service['total_amount'] ?? null;
                  $bookingType = $service['booking_type'] ?? 'individual';
                  $typeLabel = $bookingType === 'group' ? 'Đoàn' : 'Khách lẻ';
                  $typeBadge = $bookingType === 'group' ? 'bg-primary' : 'bg-info';
                ?>
                <tr>
                  <td>#<?= (int)$b['id'] ?></td>
                  <td><?= htmlspecialchars($b['tour_name'] ?? 'Không xác định') ?></td>
                  <td>
                    <span class="badge <?= $typeBadge ?>">
                      <?= htmlspecialchars($typeLabel) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($b['start_date']) ?></td>
                  <td>
                    <?= $b['end_date'] ? htmlspecialchars($b['end_date']) : '<span class="text-muted">-</span>' ?>
                  </td>
                  <td>
                    <?= $totalGuests !== null ? (int)$totalGuests : '-' ?>
                  </td>
                  <td>
                    <?php if ($totalAmount !== null): ?>
                      <?= number_format($totalAmount, 0, ',', '.') ?> đ
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge bg-secondary">
                      <?= htmlspecialchars($b['status_name'] ?? 'Không xác định') ?>
                    </span>
                  </td>
                  <td>
                    <?= htmlspecialchars($b['guide_name'] ?? '-') ?>
                  </td>
                  <td class="text-end" style="white-space: nowrap;">
                    <div class="d-inline-flex gap-1 align-items-center">
                      <a href="<?= BASE_URL ?>?act=booking-show&id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-info" title="Xem chi tiết">
                        <i class="bi bi-eye"></i>
                      </a>
                      <?php if ($b['status'] != 3 && $b['status'] != 4): ?>
                        <a href="<?= BASE_URL ?>?act=booking-edit&id=<?= (int)$b['id'] ?>" class="btn btn-sm btn-warning" title="Sửa">
                          <i class="bi bi-pencil"></i>
                        </a>
                      <?php endif; ?>
                      <a
                        href="<?= BASE_URL ?>?act=booking-delete&id=<?= (int)$b['id'] ?>"
                        class="btn btn-sm btn-danger"
                        onclick="return confirm('Bạn có chắc muốn xóa booking này? Hành động này không thể hoàn tác!')"
                        title="Xóa"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
                    </div>
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


