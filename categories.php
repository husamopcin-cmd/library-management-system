<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Categories';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = clean($_POST['name'] ?? '');
    $description = clean($_POST['description'] ?? '');

    if (empty($name)) {
        setFlash('Category name is required.', 'danger');
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $conn->prepare('INSERT INTO categories (name, description) VALUES (?,?)');
            $stmt->bind_param('ss', $name, $description);
            $stmt->execute();
            setFlash('Category added successfully.');
        } else {
            $eid = intval($_POST['edit_id']);
            $stmt = $conn->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
            $stmt->bind_param('ssi', $name, $description, $eid);
            $stmt->execute();
            setFlash('Category updated successfully.');
        }
        header('Location: categories.php'); exit();
    }
}

if ($action === 'delete' && $id) {
    // Block delete if category has books assigned
    $chk = $conn->prepare('SELECT COUNT(*) FROM books WHERE category_id=?');
    $chk->bind_param('i', $id); $chk->execute();
    $book_count = $chk->get_result()->fetch_row()[0];

    if ($book_count > 0) {
        setFlash('Cannot delete this category — it has ' . $book_count . ' book(s) assigned. Please reassign those books first.', 'danger');
    } else {
        $stmt = $conn->prepare('DELETE FROM categories WHERE id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        setFlash('Category deleted.');
    }
    header('Location: categories.php'); exit();
}

$record = null;
if ($action === 'edit' && $id) {
    $r = $conn->prepare('SELECT * FROM categories WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $record = $r->get_result()->fetch_assoc();
}

$topbar_btn = ['url' => 'categories.php?action=add', 'icon' => 'bi-plus-lg', 'label' => 'New Category'];
require_once 'includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-card" style="max-width:500px">
    <h6 class="mb-3"><?= $action === 'add' ? 'Add New Category' : 'Edit Category' ?></h6>
    <form method="POST" action="categories.php">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
        <?php if ($record): ?><input type="hidden" name="edit_id" value="<?= $record['id'] ?>"><?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($record['name'] ?? '') ?>">
        </div>
        <div class="mb-4">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($record['description'] ?? '') ?></textarea>
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Save</button>
            <a href="categories.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="table-card">
    <div class="table-header"><h6>All Categories</h6></div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Books</th><th>Actions</th></tr></thead>
            <tbody>
            <?php
            $cats = $conn->query('SELECT c.*, COUNT(b.id) book_count FROM categories c LEFT JOIN books b ON c.id=b.category_id GROUP BY c.id ORDER BY c.name');
            while ($r = $cats->fetch_assoc()):
            ?>
            <tr>
                <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
                <td style="font-weight:500"><?= htmlspecialchars($r['name']) ?></td>
                <td style="color:#6b7280;font-size:0.85rem"><?= htmlspecialchars($r['description'] ?? '—') ?></td>
                <td><span class="badge-custom badge-blue"><?= $r['book_count'] ?> books</span></td>
                <td>
                    <a href="categories.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
                    <a href="categories.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete category '<?= htmlspecialchars($r['name']) ?>'?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
