<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }
require_once '../includes/db.php';

$q = '%' . htmlspecialchars(strip_tags(trim($_GET['query'] ?? ''))) . '%';
$stmt = $conn->prepare('SELECT * FROM members WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY id DESC');
$stmt->bind_param('sss', $q, $q, $q);
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()):
?>
<tr>
    <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
    <td style="font-weight:500"><?= htmlspecialchars($r['full_name']) ?></td>
    <td style="font-size:0.85rem"><?= htmlspecialchars($r['email'] ?? '—') ?></td>
    <td style="font-size:0.85rem"><?= htmlspecialchars($r['phone'] ?? '—') ?></td>
    <td style="font-size:0.82rem"><?= date('M d, Y', strtotime($r['membership_date'])) ?></td>
    <td><?= $r['status'] === 'active' ? '<span class="badge-custom badge-green">Active</span>' : '<span class="badge-custom badge-red">Inactive</span>' ?></td>
    <td>
        <a href="members.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
        <a href="members.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete?"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
