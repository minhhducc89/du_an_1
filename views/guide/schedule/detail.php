<?php
/** @var array $booking */
/** @var array $service */
/** @var array $tour */
/** @var array $guests */
?>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle"></i> Cập nhật yêu cầu đặc biệt thành công!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Chi tiết lịch trình tour</h3>
        <div class="d-flex gap-2">
          <a href="<?= BASE_URL ?>?act=guide-schedule" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
          </a>
        </div>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Tour</dt>
          <dd class="col-sm-8">
            <strong><?= htmlspecialchars($booking['tour_name'] ?? 'Không xác định') ?></strong>
            <?php if ($tour && !empty($tour['description'])): ?>
              <div class="text-muted small mt-1"><?= nl2br(htmlspecialchars($tour['description'])) ?></div>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Mã booking</dt>
          <dd class="col-sm-8">#<?= (int)$booking['id'] ?></dd>

          <dt class="col-sm-4">Ngày khởi hành</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($booking['start_date']) ?></dd>

          <dt class="col-sm-4">Ngày kết thúc</dt>
          <dd class="col-sm-8">
            <?= $booking['end_date'] ? htmlspecialchars($booking['end_date']) : '<span class="text-muted">Chưa cập nhật</span>' ?>
          </dd>

          <dt class="col-sm-4">Trạng thái</dt>
          <dd class="col-sm-8">
            <span class="badge bg-secondary">
              <?= htmlspecialchars($booking['status_name'] ?? 'Không xác định') ?>
            </span>
          </dd>

          <dt class="col-sm-4">Khách đại diện</dt>
          <dd class="col-sm-8">
            <?php $cust = $service['customer'] ?? []; ?>
            <div><strong>Tên:</strong> <?= htmlspecialchars($cust['name'] ?? '-') ?></div>
            <div><strong>Điện thoại:</strong> <?= htmlspecialchars($cust['phone'] ?? '-') ?></div>
            <div><strong>Email:</strong> <?= htmlspecialchars($cust['email'] ?? '-') ?></div>
            <div><strong>Địa chỉ:</strong> <?= htmlspecialchars($cust['address'] ?? '-') ?></div>
          </dd>

          <dt class="col-sm-4">Số lượng khách</dt>
          <dd class="col-sm-8">
            Người lớn: <?= (int)($service['adult'] ?? 0) ?>,
            Trẻ em: <?= (int)($service['child'] ?? 0) ?>,
            <strong>Tổng: <?= (int)($service['total_guests'] ?? 0) ?> người</strong>
          </dd>

          <dt class="col-sm-4">Yêu cầu đặc biệt</dt>
          <dd class="col-sm-8">
            <?php if (!empty($service['special_requirements'])): ?>
              <div class="alert alert-warning mb-0">
                <i class="bi bi-exclamation-triangle"></i>
                <?= nl2br(htmlspecialchars($service['special_requirements'])) ?>
              </div>
              <a href="<?= BASE_URL ?>?act=guide-edit-special-requirements&id=<?= (int)$booking['id'] ?>" class="btn btn-sm btn-outline-warning mt-2">
                <i class="bi bi-pencil"></i> Cập nhật yêu cầu đặc biệt
              </a>
            <?php else: ?>
              <span class="text-muted">Chưa có yêu cầu đặc biệt</span>
              <a href="<?= BASE_URL ?>?act=guide-edit-special-requirements&id=<?= (int)$booking['id'] ?>" class="btn btn-sm btn-outline-primary mt-2">
                <i class="bi bi-plus-circle"></i> Thêm yêu cầu đặc biệt
              </a>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Ghi chú</dt>
          <dd class="col-sm-8">
            <?= nl2br(htmlspecialchars($booking['notes'] ?? '')) ?: '<span class="text-muted">Không có</span>' ?>
          </dd>
        </dl>
      </div>
    </div>

    <!-- Danh sách khách đoàn -->
    <?php if (!empty($guests)): ?>
      <div class="card mb-3">
        <div class="card-header">
          <h3 class="card-title mb-0">Danh sách khách đoàn (<?= count($guests) ?> người)</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>STT</th>
                  <th>Họ tên</th>
                  <th>Ngày sinh</th>
                  <th>Giới tính</th>
                  <th>Số hộ chiếu</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($guests as $index => $guest): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($guest->fullname) ?></td>
                    <td><?= $guest->dob ? htmlspecialchars($guest->dob) : '-' ?></td>
                    <td><?= $guest->gender ? htmlspecialchars($guest->gender) : '-' ?></td>
                    <td><?= $guest->passport_number ? htmlspecialchars($guest->passport_number) : '-' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-md-4">
    <!-- Các thao tác -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Thao tác</h3>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="<?= BASE_URL ?>?act=guide-checkin&id=<?= (int)$booking['id'] ?>" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Điểm danh & Check-in
          </a>
          <a href="<?= BASE_URL ?>?act=guide-diary&id=<?= (int)$booking['id'] ?>" class="btn btn-info">
            <i class="bi bi-journal-text"></i> Nhật ký tour
          </a>
          <a href="<?= BASE_URL ?>?act=guide-edit-special-requirements&id=<?= (int)$booking['id'] ?>" class="btn btn-warning">
            <i class="bi bi-exclamation-triangle"></i> Cập nhật yêu cầu đặc biệt
          </a>
        </div>
      </div>
    </div>

    <!-- Thông tin tour -->
    <?php if ($tour): ?>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title mb-0">Thông tin tour</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($tour['schedule'])): ?>
            <?php
              $schedule = json_decode($tour['schedule'], true);
              if (is_array($schedule) && !empty($schedule)):
            ?>
              <h6>Lịch trình:</h6>
              <pre class="bg-light p-2 rounded small"><?= htmlspecialchars(is_array($schedule) ? json_encode($schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $tour['schedule']) ?></pre>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($tour['policies'])): ?>
            <?php
              $policies = json_decode($tour['policies'], true);
              if (is_array($policies) && !empty($policies)):
            ?>
              <h6 class="mt-3">Chính sách:</h6>
              <pre class="bg-light p-2 rounded small"><?= htmlspecialchars(is_array($policies) ? json_encode($policies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $tour['policies']) ?></pre>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

