<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Authors';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = clean($_POST['full_name'] ?? '');
    $nationality = clean($_POST['nationality'] ?? '');
    $biography   = clean($_POST['biography'] ?? '');

    if (empty($full_name)) {
        setFlash('Author name is required.', 'danger');
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $conn->prepare('INSERT INTO authors (full_name, nationality, biography) VALUES (?,?,?)');
            $stmt->bind_param('sss', $full_name, $nationality, $biography);
            $stmt->execute();
            setFlash('Author added successfully.');
        } else {
            $eid = intval($_POST['edit_id']);
            $stmt = $conn->prepare('UPDATE authors SET full_name=?, nationality=?, biography=? WHERE id=?');
            $stmt->bind_param('sssi', $full_name, $nationality, $biography, $eid);
            $stmt->execute();
            setFlash('Author updated successfully.');
        }
        header('Location: authors.php'); exit();
    }
}

if ($action === 'delete' && $id) {
    // Block delete if author has books assigned
    $chk = $conn->prepare('SELECT COUNT(*) FROM books WHERE author_id=?');
    $chk->bind_param('i', $id); $chk->execute();
    $book_count = $chk->get_result()->fetch_row()[0];

    if ($book_count > 0) {
        setFlash('Cannot delete this author — they have ' . $book_count . ' book(s) in the system. Please remove or reassign those books first.', 'danger');
    } else {
        $stmt = $conn->prepare('DELETE FROM authors WHERE id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        setFlash('Author deleted.');
    }
    header('Location: authors.php'); exit();
}

$record = null;
if ($action === 'edit' && $id) {
    $r = $conn->prepare('SELECT * FROM authors WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $record = $r->get_result()->fetch_assoc();
}

$topbar_btn = ['url' => 'authors.php?action=add', 'icon' => 'bi-plus-lg', 'label' => 'New Author'];
require_once 'includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-card" style="max-width:600px">
    <h6 class="mb-3"><?= $action === 'add' ? 'Add New Author' : 'Edit Author' ?></h6>
    <form method="POST" action="authors.php">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
        <?php if ($record): ?><input type="hidden" name="edit_id" value="<?= $record['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($record['full_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Nationality</label>
                <input type="text" name="nationality" class="form-control" value="<?= htmlspecialchars($record['nationality'] ?? '') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Biography</label>
                <textarea name="biography" class="form-control" rows="4"><?= htmlspecialchars($record['biography'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Save</button>
            <a href="authors.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="table-card">
    <div class="table-header">
        <div class="search-box" style="min-width:240px">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search authors...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>#</th><th>Full Name</th><th>Nationality</th><th>Books</th><th>Actions</th></tr></thead>
            <tbody id="authorsTable">
            <?php
            $authors = $conn->query('SELECT a.*, COUNT(b.id) book_count FROM authors a LEFT JOIN books b ON a.id=b.author_id GROUP BY a.id ORDER BY a.full_name');
            while ($r = $authors->fetch_assoc()):
            ?>
            <tr>
                <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
                <td style="font-weight:500"><?= htmlspecialchars($r['full_name']) ?></td>
                <td><?= htmlspecialchars($r['nationality'] ?? '—') ?></td>
                <td><span class="badge-custom badge-blue"><?= $r['book_count'] ?> books</span></td>
                <td>
                    <a href="authors.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
                    <a href="authors.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete author '<?= htmlspecialchars($r['full_name']) ?>'?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>liveSearch('searchInput', 'authorsTable', window.location.origin + '/kutuphane/ajax/search_authors.php');</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
