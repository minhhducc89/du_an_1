<!--begin::Sidebar-->
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
  <!--begin::Sidebar Brand-->
  <div class="sidebar-brand">
    <!--begin::Brand Link-->
    <a href="<?= BASE_URL . 'home' ?>" class="brand-link">
      <!--begin::Brand Image-->
      <img
        src="<?= asset('dist/assets/img/AdminLTELogo.png') ?>"
        alt="AdminLTE Logo"
        class="brand-image opacity-75 shadow"
      />
      <!--end::Brand Image-->
      <!--begin::Brand Text-->
      <span class="brand-text fw-light">Quản Lý Tour</span>
      <!--end::Brand Text-->
    </a>
    <!--end::Brand Link-->
  </div>
  <!--end::Sidebar Brand-->
  <!--begin::Sidebar Wrapper-->
  <div class="sidebar-wrapper">
    <nav class="mt-2">
      <!--begin::Sidebar Menu-->
      <ul
        class="nav sidebar-menu flex-column"
        data-lte-toggle="treeview"
        role="menu"
        data-accordion="false"
      >
        <?php if (isAdmin()): ?>
          <li class="nav-item">
            <a href="<?= BASE_URL . '?act=dashboard' ?>" class="nav-link">
              <i class="nav-icon bi bi-speedometer"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon bi bi-airplane-engines"></i>
              <p>
                Quản lý Tour
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=categories' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Danh mục Tour</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=tours' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Danh sách Tour</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=tour-create' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Thêm Tour mới</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=bookings' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Booking</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon bi bi-person-badge"></i>
              <p>
                Hồ sơ HDV
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=guide-profiles' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Danh sách hồ sơ</p>
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon bi bi-people-fill"></i>
            <p>
              Quản lý Khách hàng
              <i class="nav-arrow bi bi-chevron-right"></i>
            </p>
          </a>
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <a href="<?= BASE_URL . '?act=customers' ?>" class="nav-link">
                <i class="nav-icon bi bi-circle"></i>
                <p>Danh sách Khách hàng</p>
              </a>
            </li>
          </ul>
        </li>
        <?php if (isAdmin()): ?>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon bi bi-person-gear"></i>
              <p>
                Quản lý Người dùng
                <i class="nav-arrow bi bi-chevron-right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= BASE_URL . '?act=users' ?>" class="nav-link">
                  <i class="nav-icon bi bi-circle"></i>
                  <p>Danh sách Người dùng</p>
                </a>
              </li>
            </ul>
          </li>
        <?php elseif (isGuide()): ?>
          <li class="nav-item">
            <a href="<?= BASE_URL . '?act=guide-schedule' ?>" class="nav-link">
              <i class="nav-icon bi bi-calendar-event"></i>
              <p>Lịch trình</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="<?= BASE_URL . '?act=guide-history' ?>" class="nav-link">
              <i class="nav-icon bi bi-clock-history"></i>
              <p>Lịch sử</p>
            </a>
          </li>
        <?php endif; ?>
        <li class="nav-header">HỆ THỐNG</li>
        <li class="nav-item">
          <a href="<?= BASE_URL . 'logout' ?>" class="nav-link">
            <i class="nav-icon bi bi-box-arrow-right"></i>
            <p>Đăng xuất</p>
          </a>
        </li>
      </ul>
      <!--end::Sidebar Menu-->
    </nav>
  </div>
  <!--end::Sidebar Wrapper-->
</aside>
<!--end::Sidebar-->

