<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }
require_once '../includes/db.php';

$q      = '%' . htmlspecialchars(strip_tags(trim($_GET['query'] ?? ''))) . '%';
$filter = $_GET['filter'] ?? 'all';
$extra  = $filter === 'borrowed' ? 'AND br.status="borrowed"' : ($filter === 'returned' ? 'AND br.status="returned"' : '');

$stmt = $conn->prepare("SELECT br.*, b.title, m.full_name FROM borrow_records br JOIN books b ON br.book_id=b.id JOIN members m ON br.member_id=m.id WHERE (b.title LIKE ? OR m.full_name LIKE ?) $extra ORDER BY br.id DESC");
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()):
    $overdue = $r['status'] === 'borrowed' && $r['return_date'] && $r['return_date'] < date('Y-m-d');
?>
<tr>
    <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
    <td style="font-weight:500"><?= htmlspecialchars($r['title']) ?></td>
    <td><?= htmlspecialchars($r['full_name']) ?></td>
    <td style="font-size:0.85rem"><?= date('M d, Y', strtotime($r['borrow_date'])) ?></td>
    <td style="font-size:0.85rem"><?= $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : '<span class="text-muted">—</span>' ?></td>
    <td>
        <?php if ($r['status']==='returned'): ?><span class="badge-custom badge-green">Returned</span>
        <?php elseif ($overdue): ?><span class="badge-custom badge-red">Overdue</span>
        <?php else: ?><span class="badge-custom badge-amber">Borrowed</span>
        <?php endif; ?>
    </td>
    <td>
        <?php if ($r['status']==='borrowed'): ?>
            <a href="borrows.php?action=return&id=<?= $r['id'] ?>" class="btn-action view"><i class="bi bi-check-circle"></i></a>
        <?php endif; ?>
        <a href="borrows.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
        <a href="borrows.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete?"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
