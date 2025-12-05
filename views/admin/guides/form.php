<?php
/** @var array $old */
/** @var string[] $errors */
/** @var array $guides */

$isEdit = !empty($old['id']);
?>

<div class="row">
  <div class="col-md-10">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">
          <?= $isEdit ? 'Sửa hồ sơ hướng dẫn viên' : 'Tạo hồ sơ hướng dẫn viên' ?>
        </h3>
      </div>
      <div class="card-body">
        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form
          method="post"
          enctype="multipart/form-data"
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'guide-profile-update' : 'guide-profile-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$old['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="user_id" class="form-label">Hướng dẫn viên</label>
            <select
              class="form-select"
              id="user_id"
              name="user_id"
              <?= $isEdit ? 'disabled' : '' ?>
              required
            >
              <option value="">-- Chọn hướng dẫn viên --</option>
              <?php foreach ($guides as $g): ?>
                <option
                  value="<?= (int)$g['id'] ?>"
                  <?= (int)($old['user_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>
                >
                  <?= htmlspecialchars($g['name']) ?> (<?= htmlspecialchars($g['email']) ?>)
                </option>
              <?php endforeach; ?>
            </select>
            <?php if ($isEdit): ?>
              <input type="hidden" name="user_id" value="<?= (int)$old['user_id'] ?>">
            <?php endif; ?>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="birthdate" class="form-label">Ngày sinh</label>
                <input
                  type="date"
                  class="form-control"
                  id="birthdate"
                  name="birthdate"
                  value="<?= htmlspecialchars($old['birthdate'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="phone" class="form-label">Số điện thoại</label>
                <input
                  type="text"
                  class="form-control"
                  id="phone"
                  name="phone"
                  required
                  value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="certificate" class="form-label">Số thẻ hành nghề</label>
                <input
                  type="text"
                  class="form-control"
                  id="certificate"
                  name="certificate"
                  value="<?= htmlspecialchars($old['certificate'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="languages" class="form-label">Ngôn ngữ (cách nhau bằng dấu phẩy)</label>
            <input
              type="text"
              class="form-control"
              id="languages"
              name="languages"
              placeholder="Ví dụ: Tiếng Việt, Tiếng Anh"
              value="<?= htmlspecialchars($old['languages'] ?? '') ?>"
            >
          </div>

          <div class="mb-3">
            <label for="experience" class="form-label">Kinh nghiệm</label>
            <textarea
              class="form-control"
              id="experience"
              name="experience"
              rows="3"
            ><?= htmlspecialchars($old['experience'] ?? '') ?></textarea>
          </div>

          <div class="row">
            <div class="col-md-4">
              <div class="mb-3">
                <label for="rating" class="form-label">Đánh giá (0-5)</label>
                <input
                  type="number"
                  class="form-control"
                  id="rating"
                  name="rating"
                  min="0"
                  max="5"
                  step="0.01"
                  placeholder="Ví dụ: 4.5"
                  value="<?= htmlspecialchars($old['rating'] ?? '') ?>"
                >
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="group_type" class="form-label">Loại đoàn</label>
                <select class="form-select" id="group_type" name="group_type">
                  <option value="">-- Chọn loại --</option>
                  <option value="nội địa" <?= ($old['group_type'] ?? '') === 'nội địa' ? 'selected' : '' ?>>Nội địa</option>
                  <option value="quốc tế" <?= ($old['group_type'] ?? '') === 'quốc tế' ? 'selected' : '' ?>>Quốc tế</option>
                  <option value="cả hai" <?= ($old['group_type'] ?? '') === 'cả hai' ? 'selected' : '' ?>>Cả hai</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="mb-3">
                <label for="speciality" class="form-label">Chuyên môn</label>
                <input
                  type="text"
                  class="form-control"
                  id="speciality"
                  name="speciality"
                  placeholder="Ví dụ: chuyên tuyến miền Bắc"
                  value="<?= htmlspecialchars($old['speciality'] ?? '') ?>"
                >
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label for="health_status" class="form-label">Tình trạng sức khỏe</label>
            <textarea
              class="form-control"
              id="health_status"
              name="health_status"
              rows="2"
              placeholder="Ví dụ: Tốt, Khá, Có vấn đề về tim mạch..."
            ><?= htmlspecialchars($old['health_status'] ?? '') ?></textarea>
          </div>

          <div class="mb-3">
            <label for="avatar" class="form-label">Ảnh thẻ</label>
            <input
              type="file"
              class="form-control"
              id="avatar"
              name="avatar"
              accept="image/*"
            >
            <?php if (!empty($old['avatar'])): ?>
              <div class="mt-2">
                <img
                  src="<?= asset('uploads/guides/' . $old['avatar']) ?>"
                  alt="Avatar"
                  style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%;"
                >
              </div>
            <?php endif; ?>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật' : 'Lưu hồ sơ' ?>
          </button>
          <a href="<?= BASE_URL ?>?act=guide-profiles" class="btn btn-secondary ms-2">
            Quay lại
          </a>
        </form>
      </div>
    </div>
  </div>
</div>


