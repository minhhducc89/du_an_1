<?php
/** @var array $booking */
/** @var array $tour */
/** @var array $guests */
?>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Điểm danh & Check-in</h3>
        <a href="<?= BASE_URL ?>?act=guide-schedule" class="btn btn-sm btn-secondary">
          <i class="bi bi-arrow-left"></i> Quay lại
        </a>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['success'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            Đã lưu điểm danh thành công!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="mb-4">
          <h5>Thông tin tour</h5>
          <p class="mb-1"><strong>Tour:</strong> <?= htmlspecialchars($tour['name'] ?? 'Không xác định') ?></p>
          <p class="mb-1"><strong>Ngày khởi hành:</strong> <?= htmlspecialchars($booking['start_date']) ?></p>
          <?php if ($booking['end_date']): ?>
            <p class="mb-1"><strong>Ngày kết thúc:</strong> <?= htmlspecialchars($booking['end_date']) ?></p>
          <?php endif; ?>
          <p class="mb-0"><strong>Tổng số khách:</strong> <?= count($guests) ?> người</p>
          <?php
            $service = json_decode($booking['service_detail'] ?? '{}', true);
            $pickupAddress = $service['customer']['address'] ?? 'Chưa cập nhật';
            $specialReqs = $service['special_requirements'] ?? '';
          ?>
          <p class="mb-0 mt-2"><strong>Điểm đón:</strong> <?= htmlspecialchars($pickupAddress) ?></p>
          <?php if (!empty($specialReqs)): ?>
            <div class="alert alert-warning mt-2 mb-0">
              <strong><i class="bi bi-exclamation-triangle"></i> Yêu cầu đặc biệt:</strong>
              <div class="mt-1"><?= nl2br(htmlspecialchars($specialReqs)) ?></div>
            </div>
          <?php endif; ?>
        </div>

        <?php
          // Lấy trạng thái điểm danh hiện tại
          $attendance = [];
          if (!empty($booking['schedule_detail'])) {
              $scheduleDetail = json_decode($booking['schedule_detail'], true);
              if (is_array($scheduleDetail) && isset($scheduleDetail['attendance'])) {
                  $attendance = $scheduleDetail['attendance'];
              }
          }
        ?>

        <form method="post" action="<?= BASE_URL ?>?act=guide-save-checkin">
          <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
          
          <h5 class="mb-3">Danh sách khách đoàn</h5>
          
          <?php if (empty($guests)): ?>
            <div class="alert alert-warning">
              Chưa có danh sách khách. Vui lòng liên hệ admin để thêm danh sách khách.
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th style="width: 50px">STT</th>
                    <th>Họ tên</th>
                    <th style="width: 120px">Ngày sinh</th>
                    <th style="width: 100px">Giới tính</th>
                    <th style="width: 150px" class="text-center">Trạng thái</th>
                    <th style="width: 100px" class="text-center">Điểm danh</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($guests as $idx => $guest): ?>
                    <?php
                      $isPresent = isset($attendance[$guest->id]) && $attendance[$guest->id] === 'present';
                    ?>
                    <tr>
                      <td><?= $idx + 1 ?></td>
                      <td><strong><?= htmlspecialchars($guest->fullname) ?></strong></td>
                      <td><?= $guest->dob ? htmlspecialchars($guest->dob) : '-' ?></td>
                      <td><?= $guest->gender ? htmlspecialchars($guest->gender) : '-' ?></td>
                      <td class="text-center">
                        <?php if ($isPresent): ?>
                          <span class="badge bg-success">Đã có mặt</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Chưa điểm danh</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center">
                        <div class="form-check form-check-lg">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            name="attendance[]"
                            value="<?= (int)$guest->id ?>"
                            id="guest_<?= (int)$guest->id ?>"
                            <?= $isPresent ? 'checked' : '' ?>
                          >
                          <label class="form-check-label" for="guest_<?= (int)$guest->id ?>"></label>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="submit" class="btn btn-primary btn-lg flex-fill">
                <i class="bi bi-check-circle"></i> Lưu điểm danh
              </button>
              <?php if (!empty($guests)): ?>
                <a 
                  href="<?= BASE_URL ?>?act=booking-guests-export&id=<?= (int)$booking['id'] ?>" 
                  target="_blank"
                  class="btn btn-success btn-lg"
                  title="In danh sách khách"
                >
                  <i class="bi bi-printer"></i>
                </a>
                <a 
                  href="<?= BASE_URL ?>?act=booking-guests-export&id=<?= (int)$booking['id'] ?>&attendance=1" 
                  target="_blank"
                  class="btn btn-info btn-lg"
                  title="In danh sách kèm điểm danh"
                >
                  <i class="bi bi-file-earmark-pdf"></i>
                </a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

