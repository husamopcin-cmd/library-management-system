<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }
require_once '../includes/db.php';

$q = '%' . htmlspecialchars(strip_tags(trim($_GET['query'] ?? ''))) . '%';
$stmt = $conn->prepare('SELECT b.*, a.full_name AS author, c.name AS category FROM books b LEFT JOIN authors a ON b.author_id=a.id LEFT JOIN categories c ON b.category_id=c.id WHERE b.title LIKE ? OR b.isbn LIKE ? OR a.full_name LIKE ? ORDER BY b.id DESC');
$stmt->bind_param('sss', $q, $q, $q);
$stmt->execute();
$result = $stmt->get_result();
while ($r = $result->fetch_assoc()):
?>
<tr>
    <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
    <td>
        <div style="font-weight:500"><?= htmlspecialchars($r['title']) ?></div>
        <?php if($r['isbn']): ?><div style="font-size:0.75rem;color:#9ca3af"><?= htmlspecialchars($r['isbn']) ?></div><?php endif; ?>
    </td>
    <td><?= htmlspecialchars($r['author'] ?? '—') ?></td>
    <td><?= $r['category'] ? '<span class="badge-custom badge-blue">'.htmlspecialchars($r['category']).'</span>' : '—' ?></td>
    <td><?= $r['stock'] > 0 ? '<span class="badge-custom badge-green">'.$r['stock'].' left</span>' : '<span class="badge-custom badge-red">Out of stock</span>' ?></td>
    <td>
        <a href="books.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
        <a href="books.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete?"><i class="bi bi-trash"></i></a>
    </td>
</tr>
<?php endwhile; ?>
