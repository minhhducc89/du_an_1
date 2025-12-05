<?php
/** @var GuideProfile $profile */
/** @var array|null $user */
/** @var array $languages */
/** @var array $history */
?>

<div class="row">
  <div class="col-md-8">
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Thông tin hồ sơ #<?= (int)$profile->id ?></h3>
        <a href="<?= BASE_URL ?>?act=guide-profiles" class="btn btn-sm btn-secondary">
          <i class="bi bi-arrow-left"></i> Danh sách
        </a>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Họ tên</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($user['name'] ?? 'Không xác định') ?></dd>

          <dt class="col-sm-4">Email</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($user['email'] ?? 'Không xác định') ?></dd>

          <dt class="col-sm-4">Ngày sinh</dt>
          <dd class="col-sm-8">
            <?= $profile->birthdate ? htmlspecialchars($profile->birthdate) : '-' ?>
          </dd>

          <dt class="col-sm-4">Số điện thoại</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($profile->phone ?? '-') ?></dd>

          <dt class="col-sm-4">Số thẻ hành nghề</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($profile->certificate ?? '-') ?></dd>

          <dt class="col-sm-4">Ngôn ngữ</dt>
          <dd class="col-sm-8">
            <?php if (!empty($languages)): ?>
              <?= htmlspecialchars(implode(', ', $languages)) ?>
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Kinh nghiệm</dt>
          <dd class="col-sm-8">
            <?= $profile->experience ? nl2br(htmlspecialchars($profile->experience)) : '-' ?>
          </dd>

          <dt class="col-sm-4">Đánh giá</dt>
          <dd class="col-sm-8">
            <?php if ($profile->rating !== null): ?>
              <span class="badge bg-info fs-6">
                <?= number_format((float)$profile->rating, 1) ?>/5.0
              </span>
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Loại đoàn</dt>
          <dd class="col-sm-8">
            <?php if ($profile->group_type): ?>
              <span class="badge bg-secondary"><?= htmlspecialchars($profile->group_type) ?></span>
            <?php else: ?>
              -
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Chuyên môn</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($profile->speciality ?? '-') ?></dd>

          <dt class="col-sm-4">Tình trạng sức khỏe</dt>
          <dd class="col-sm-8">
            <?= $profile->health_status ? nl2br(htmlspecialchars($profile->health_status)) : '-' ?>
          </dd>

          <dt class="col-sm-4">Lịch sử tour</dt>
          <dd class="col-sm-8">
            <?php if (!empty($history)): ?>
              <pre class="bg-light p-2 rounded"><?= htmlspecialchars(json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <?php else: ?>
              <span class="text-muted">Chưa có lịch sử</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Ngày tạo</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($profile->created_at ?? '-') ?></dd>

          <dt class="col-sm-4">Ngày cập nhật</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($profile->updated_at ?? '-') ?></dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title mb-0">Ảnh thẻ</h3>
      </div>
      <div class="card-body text-center">
        <?php if ($profile->avatar): ?>
          <img
            src="<?= asset('uploads/guides/' . $profile->avatar) ?>"
            alt="Avatar"
            class="img-fluid rounded"
            style="max-width: 200px;"
          >
        <?php else: ?>
          <span class="text-muted">Chưa có ảnh</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">Thao tác</h3>
      </div>
      <div class="card-body">
        <a href="<?= BASE_URL ?>?act=guide-profile-edit&id=<?= (int)$profile->id ?>" class="btn btn-warning w-100 mb-2">
          <i class="bi bi-pencil-square"></i> Chỉnh sửa
        </a>
        <a
          href="<?= BASE_URL ?>?act=guide-profile-delete&id=<?= (int)$profile->id ?>"
          class="btn btn-danger w-100"
          onclick="return confirm('Bạn có chắc muốn xóa hồ sơ này? Hành động này không thể hoàn tác!')"
        >
          <i class="bi bi-trash"></i> Xóa hồ sơ
        </a>
      </div>
    </div>
  </div>
</div>

