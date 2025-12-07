<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= ($includeAttendance ?? false) ? 'Danh sách khách đoàn & Điểm danh' : 'Danh sách khách đoàn' ?> - Booking #<?= (int)$booking['id'] ?></title>
  <style>
    @media print {
      .no-print { display: none; }
      body { margin: 0; padding: 20px; }
    }
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 20px;
    }
    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }
    .header h1 {
      margin: 0;
      font-size: 18px;
    }
    .info {
      margin-bottom: 20px;
    }
    .info table {
      width: 100%;
      border-collapse: collapse;
    }
    .info td {
      padding: 5px;
      border: 1px solid #ddd;
    }
    .info td:first-child {
      font-weight: bold;
      width: 30%;
      background-color: #f5f5f5;
    }
    .guests-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .guests-table th,
    .guests-table td {
      border: 1px solid #000;
      padding: 8px;
      text-align: left;
    }
    .guests-table th {
      background-color: #f0f0f0;
      font-weight: bold;
      text-align: center;
    }
    .guests-table td {
      text-align: center;
    }
    .guests-table td:nth-child(2) {
      text-align: left;
    }
    .footer {
      margin-top: 30px;
      text-align: right;
    }
    .no-print {
      margin-bottom: 20px;
      text-align: center;
    }
    .no-print button {
      padding: 10px 20px;
      font-size: 14px;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    .no-print button:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>
  <div class="no-print">
    <button onclick="window.print()">In / Lưu PDF</button>
  </div>

  <div class="header">
    <h1><?= ($includeAttendance ?? false) ? 'DANH SÁCH KHÁCH ĐOÀN & ĐIỂM DANH' : 'DANH SÁCH KHÁCH ĐOÀN' ?></h1>
    <p>Booking #<?= (int)$booking['id'] ?></p>
    <?php if ($includeAttendance ?? false): ?>
      <p style="font-size: 14px; margin-top: 5px;">Ngày điểm danh: <?= date('d/m/Y H:i:s') ?></p>
    <?php endif; ?>
  </div>

  <div class="info">
    <table>
      <tr>
        <td>Tour</td>
        <td><?= htmlspecialchars($booking['tour_name'] ?? 'Không xác định') ?></td>
      </tr>
      <tr>
        <td>Ngày khởi hành</td>
        <td><?= htmlspecialchars($booking['start_date']) ?></td>
      </tr>
      <tr>
        <td>Ngày kết thúc</td>
        <td><?= $booking['end_date'] ? htmlspecialchars($booking['end_date']) : '-' ?></td>
      </tr>
      <tr>
        <td>Trạng thái</td>
        <td><?= htmlspecialchars($booking['status_name'] ?? 'Không xác định') ?></td>
      </tr>
      <tr>
        <td>Tổng số khách</td>
        <td><?= count($guests) ?> người</td>
      </tr>
    </table>
  </div>

  <table class="guests-table">
    <thead>
      <tr>
        <th style="width: 5%;">STT</th>
        <th style="width: 30%;">Họ và tên</th>
        <th style="width: 15%;">Ngày sinh</th>
        <th style="width: 10%;">Giới tính</th>
        <th style="width: 20%;">Số hộ chiếu/CMND</th>
        <?php if ($includeAttendance ?? false): ?>
          <th style="width: 20%;">Điểm danh</th>
        <?php else: ?>
          <th style="width: 20%;">Ghi chú</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($guests)): ?>
        <tr>
          <td colspan="<?= ($includeAttendance ?? false) ? '6' : '6' ?>" style="text-align: center; padding: 20px;">
            Chưa có khách nào trong danh sách
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($guests as $idx => $guest): ?>
          <?php
            $isPresent = false;
            if ($includeAttendance ?? false) {
                $isPresent = isset($attendance[$guest->id]) && $attendance[$guest->id] === 'present';
            }
          ?>
          <tr>
            <td><?= $idx + 1 ?></td>
            <td><?= htmlspecialchars($guest->fullname) ?></td>
            <td><?= $guest->dob ? htmlspecialchars($guest->dob) : '-' ?></td>
            <td><?= $guest->gender ? htmlspecialchars($guest->gender) : '-' ?></td>
            <td><?= $guest->passport_number ? htmlspecialchars($guest->passport_number) : '-' ?></td>
            <?php if ($includeAttendance ?? false): ?>
              <td style="text-align: center;">
                <?php if ($isPresent): ?>
                  <strong style="color: #28a745;">✓ Có mặt</strong>
                <?php else: ?>
                  <span style="color: #6c757d;">-</span>
                <?php endif; ?>
              </td>
            <?php else: ?>
              <td></td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">
    <p>Ngày xuất: <?= date('d/m/Y H:i:s') ?></p>
  </div>
</body>
</html>

