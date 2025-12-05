<?php
/** @var array $old */
/** @var string[] $errors */

$isEdit = !empty($old['id']);
?>

<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title mb-0">
          <?= $isEdit ? 'Sửa người dùng' : 'Tạo người dùng mới' ?>
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
          action="<?= BASE_URL ?>?act=<?= $isEdit ? 'user-update' : 'user-store' ?>"
        >
          <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$old['id'] ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label for="name" class="form-label">Họ tên</label>
            <input
              type="text"
              class="form-control"
              id="name"
              name="name"
              required
              value="<?= htmlspecialchars($old['name'] ?? '') ?>"
            >
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
              type="email"
              class="form-control"
              id="email"
              name="email"
              required
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            >
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">
              Mật khẩu
              <?php if ($isEdit): ?>
                <small class="text-muted">(Để trống nếu không muốn thay đổi)</small>
              <?php endif; ?>
            </label>
            <input
              type="password"
              class="form-control"
              id="password"
              name="password"
              <?= $isEdit ? '' : 'required' ?>
              minlength="6"
            >
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="role" class="form-label">Vai trò</label>
                <select class="form-select" id="role" name="role" required>
                  <option value="guide" <?= ($old['role'] ?? 'guide') === 'guide' ? 'selected' : '' ?>>
                    Hướng dẫn viên (Guide)
                  </option>
                  <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>
                    Quản trị viên (Admin)
                  </option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-select" id="status" name="status">
                  <option value="1" <?= (int)($old['status'] ?? 1) === 1 ? 'selected' : '' ?>>
                    Hoạt động
                  </option>
                  <option value="0" <?= (int)($old['status'] ?? 1) === 0 ? 'selected' : '' ?>>
                    Vô hiệu
                  </option>
                </select>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <?= $isEdit ? 'Cập nhật' : 'Tạo mới' ?>
          </button>
          <a href="<?= BASE_URL ?>?act=users" class="btn btn-secondary ms-2">
            Quay lại
          </a>
        </form>
      </div>
    </div>
  </div>
</div>

