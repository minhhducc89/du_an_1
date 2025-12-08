<?php
/** @var array $booking */
/** @var array $service */
/** @var array $statuses */
/** @var array $logs */
/** @var array $guides */
/** @var array $guests */
?>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Thông tin booking #<?= (int)$booking['id'] ?></h3>
        <div class="d-flex gap-2">
          <?php if ($booking['status'] != 3 && $booking['status'] != 4): ?>
            <a href="<?= BASE_URL ?>?act=booking-edit&id=<?= (int)$booking['id'] ?>" class="btn btn-sm btn-warning">
              <i class="bi bi-pencil"></i> Sửa
            </a>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>?act=bookings" class="btn btn-sm btn-secondary">
            <i class="bi bi-arrow-left"></i> Danh sách
          </a>
        </div>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Tour</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($booking['tour_name'] ?? 'Không xác định') ?></dd>

          <dt class="col-sm-4">Loại booking</dt>
          <dd class="col-sm-8">
            <?php
              $bookingType = $service['booking_type'] ?? 'individual';
              $typeLabel = $bookingType === 'group' ? 'Đoàn (3+ người, công ty, tổ chức)' : 'Khách lẻ (1-2 người)';
              $typeBadge = $bookingType === 'group' ? 'bg-primary' : 'bg-info';
            ?>
            <span class="badge <?= $typeBadge ?>">
              <?= htmlspecialchars($typeLabel) ?>
            </span>
          </dd>

          <dt class="col-sm-4">Ngày khởi hành</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($booking['start_date'] ?? '') ?></dd>

          <dt class="col-sm-4">Ngày kết thúc</dt>
          <dd class="col-sm-8">
            <?= $booking['end_date'] ? htmlspecialchars($booking['end_date']) : '<span class="text-muted">Chưa cập nhật</span>' ?>
          </dd>

          <dt class="col-sm-4">Trạng thái hiện tại</dt>
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
            NL: <?= (int)($service['adult'] ?? 0) ?>,
            TE: <?= (int)($service['child'] ?? 0) ?>,
            Tổng: <?= (int)($service['total_guests'] ?? 0) ?>
          </dd>

          <dt class="col-sm-4">Tổng tiền</dt>
          <dd class="col-sm-8">
            <?php if (isset($service['total_amount'])): ?>
              <?= number_format($service['total_amount'], 0, ',', '.') ?> đ
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Yêu cầu đặc biệt</dt>
          <dd class="col-sm-8">
            <?php if (!empty($service['special_requirements'])): ?>
              <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i>
                <?= nl2br(htmlspecialchars($service['special_requirements'] ?? '')) ?>
              </div>
            <?php else: ?>
              <span class="text-muted">Không có yêu cầu đặc biệt</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Ghi chú</dt>
          <dd class="col-sm-8">
            <?= nl2br(htmlspecialchars($booking['notes'] ?? '')) ?: '<span class="text-muted">Không có</span>' ?>
          </dd>

          <dt class="col-sm-4">Hợp đồng</dt>
          <dd class="col-sm-8">
            <?php if (!empty($booking['contract'])): ?>
              <div class="alert alert-light border mb-2">
                <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit; max-height: 200px; overflow-y: auto;"><?= htmlspecialchars($booking['contract'] ?? '') ?></pre>
              </div>
              <div>
                <a 
                  href="<?= BASE_URL ?>?act=booking-contract-export&id=<?= (int)$booking['id'] ?>" 
                  target="_blank"
                  class="btn btn-sm btn-danger"
                >
                  <i class="bi bi-file-earmark-pdf me-1"></i> Xem/Tải hợp đồng PDF
                </a>
              </div>
            <?php else: ?>
              <span class="text-muted">Chưa có thông tin hợp đồng</span>
            <?php endif; ?>
          </dd>
        </dl>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Danh sách khách đoàn</h3>
        <div class="d-flex gap-2">
          <a
            href="<?= BASE_URL ?>?act=booking-guests-export&id=<?= (int)$booking['id'] ?>"
            target="_blank"
            class="btn btn-sm btn-outline-success"
            title="In danh sách khách"
          >
            <i class="bi bi-file-earmark-pdf"></i> In danh sách
          </a>
          <a
            href="<?= BASE_URL ?>?act=booking-guests-export&id=<?= (int)$booking['id'] ?>&attendance=1"
            target="_blank"
            class="btn btn-sm btn-outline-primary"
            title="In danh sách kèm điểm danh"
          >
            <i class="bi bi-check-square"></i> In kèm điểm danh
          </a>
        </div>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['imported'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            Đã import thành công <?= (int)$_GET['imported'] ?> khách.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="mb-3">
          <form
            method="post"
            action="<?= BASE_URL ?>?act=booking-guest-store&id=<?= (int)$booking['id'] ?>"
            class="row g-2"
          >
            <div class="col-md-4">
              <input
                type="text"
                class="form-control form-control-sm"
                name="fullname"
                placeholder="Họ tên *"
                required
              >
            </div>
            <div class="col-md-2">
              <input
                type="date"
                class="form-control form-control-sm"
                name="dob"
                placeholder="Ngày sinh"
              >
            </div>
            <div class="col-md-2">
              <select class="form-select form-select-sm" name="gender">
                <option value="">Giới tính</option>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
                <option value="Khác">Khác</option>
              </select>
            </div>
            <div class="col-md-3">
              <input
                type="text"
                class="form-control form-control-sm"
                name="passport_number"
                placeholder="Số hộ chiếu/CMND"
              >
            </div>
            <div class="col-md-1">
              <button type="submit" class="btn btn-sm btn-primary w-100">
                <i class="bi bi-plus"></i>
              </button>
            </div>
          </form>
        </div>

        <div class="mb-3">
          <form
            method="post"
            action="<?= BASE_URL ?>?act=booking-guests-import&id=<?= (int)$booking['id'] ?>"
            enctype="multipart/form-data"
            class="d-flex gap-2"
          >
            <input
              type="file"
              class="form-control form-control-sm"
              name="csv_file"
              accept=".csv"
              required
            >
            <button type="submit" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-upload"></i> Import CSV
            </button>
          </form>
          <small class="text-muted">
            Format CSV: Họ tên, Ngày sinh (YYYY-MM-DD), Giới tính, Số hộ chiếu/CMND
          </small>
        </div>

        <?php if (empty($guests)): ?>
          <p class="text-muted mb-0">Chưa có khách nào trong danh sách.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th>STT</th>
                  <th>Họ tên</th>
                  <th>Ngày sinh</th>
                  <th>Giới tính</th>
                  <th>Số hộ chiếu/CMND</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($guests as $idx => $guest): ?>
                  <tr>
                    <td><?= $idx + 1 ?></td>
                    <td><?= htmlspecialchars($guest->fullname ?? '') ?></td>
                    <td><?= $guest->dob ? htmlspecialchars($guest->dob) : '-' ?></td>
                    <td><?= $guest->gender ? htmlspecialchars($guest->gender) : '-' ?></td>
                    <td><?= $guest->passport_number ? htmlspecialchars($guest->passport_number) : '-' ?></td>
                    <td>
                      <a
                        href="<?= BASE_URL ?>?act=booking-guest-delete&id=<?= (int)$guest->id ?>&booking_id=<?= (int)$booking['id'] ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Bạn có chắc muốn xóa khách này?')"
                      >
                        <i class="bi bi-trash"></i>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Lịch sử trạng thái</h3>
      </div>
      <div class="card-body">
        <?php if (empty($logs)): ?>
          <p class="text-muted mb-0">Chưa có log trạng thái.</p>
        <?php else: ?>
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Thời gian</th>
                <th>Thay đổi</th>
                <th>Người thực hiện</th>
                <th>Ghi chú</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr>
                  <td><?= htmlspecialchars($log['changed_at']) ?></td>
                  <td>
                    <?= htmlspecialchars($log['old_status_name'] ?? 'N/A') ?>
                    <i class="bi bi-arrow-right"></i>
                    <?= htmlspecialchars($log['new_status_name'] ?? 'N/A') ?>
                  </td>
                  <td><?= htmlspecialchars($log['changed_by_name'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($log['note'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Cập nhật trạng thái</h3>
      </div>
      <div class="card-body">
        <form
          method="post"
          action="<?= BASE_URL ?>?act=booking-change-status&id=<?= (int)$booking['id'] ?>"
        >
          <div class="mb-3">
            <label for="new_status" class="form-label">Trạng thái mới</label>
            <select class="form-select" id="new_status" name="new_status">
              <?php foreach ($statuses as $st): ?>
                <option
                  value="<?= (int)$st['id'] ?>"
                  <?= (int)$st['id'] === (int)$booking['status'] ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($st['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="note" class="form-label">Ghi chú</label>
            <textarea
              class="form-control"
              id="note"
              name="note"
              rows="2"
            ></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">
            Cập nhật trạng thái
          </button>
        </form>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Phân công hướng dẫn viên</h3>
      </div>
      <div class="card-body">
        <form
          method="post"
          action="<?= BASE_URL ?>?act=booking-assign-guide&id=<?= (int)$booking['id'] ?>"
        >
          <div class="mb-3">
            <label for="guide_id" class="form-label">Hướng dẫn viên</label>
            <select class="form-select" id="guide_id" name="guide_id">
              <option value="">-- Chọn HDV --</option>
              <?php foreach ($guides as $g): ?>
                <option
                  value="<?= (int)$g['id'] ?>"
                  <?= (int)$g['id'] === (int)($booking['assigned_guide_id'] ?? 0) ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['email']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-outline-primary w-100">
            Lưu phân công
          </button>
        </form>
      </div>
    </div>
  </div>
</div>


