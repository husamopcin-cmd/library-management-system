<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }
require_once '../includes/db.php';

$q = '%' . htmlspecialchars(strip_tags(trim($_GET['query'] ?? ''))) . '%';
$stmt = $conn->prepare('SELECT a.*, COUNT(b.id) book_count FROM authors a LEFT JOIN books b ON a.id=b.author_id WHERE a.full_name LIKE ? OR a.nationality LIKE ? GROUP BY a.id ORDER BY a.full_name');
$stmt->bind_param('ss', $q, $q);
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()):
?>
<tr>
    <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
    <td style="font-weight:500"><?= htmlspecialchars($r['full_name']) ?></td>
    <td><?= htmlspecialchars($r['nationality'] ?? '—') ?></td>
    <td><span class="badge-custom badge-blue"><?= $r['book_count'] ?> books</span></td>
    <td>
        <a href="authors.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
        <a href="authors.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete?"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
