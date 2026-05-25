<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Books';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = clean($_POST['title'] ?? '');
    $isbn         = clean($_POST['isbn'] ?? '');
    $author_id    = intval($_POST['author_id'] ?? 0);
    $category_id  = intval($_POST['category_id'] ?? 0);
    $publisher    = clean($_POST['publisher'] ?? '');
    $publish_year = intval($_POST['publish_year'] ?? 0);
    $page_count   = intval($_POST['page_count'] ?? 0);
    $stock        = intval($_POST['stock'] ?? 1);
    $description  = clean($_POST['description'] ?? '');

    if (empty($title)) {
        setFlash('Book title is required.', 'danger');
    } elseif ($stock < 0) {
        setFlash('Stock cannot be negative.', 'danger');
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $conn->prepare('INSERT INTO books (isbn, title, author_id, category_id, publisher, publish_year, page_count, stock, description) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssiissiii', $isbn, $title, $author_id, $category_id, $publisher, $publish_year, $page_count, $stock, $description);
            $stmt->execute();
            setFlash('Book added successfully.');
        } else {
            $eid = intval($_POST['edit_id']);

            // Check how many copies are currently borrowed
            $chk = $conn->prepare('SELECT COUNT(*) FROM borrow_records WHERE book_id=? AND status="borrowed"');
            $chk->bind_param('i', $eid); $chk->execute();
            $borrowed_count = $chk->get_result()->fetch_row()[0];

            if ($stock < $borrowed_count) {
                setFlash('Stock cannot be less than the number of copies currently borrowed (' . $borrowed_count . ').', 'danger');
                header('Location: books.php?action=edit&id=' . $eid); exit();
            }

            $stmt = $conn->prepare('UPDATE books SET isbn=?, title=?, author_id=?, category_id=?, publisher=?, publish_year=?, page_count=?, stock=?, description=? WHERE id=?');
            $stmt->bind_param('ssiissiiii', $isbn, $title, $author_id, $category_id, $publisher, $publish_year, $page_count, $stock, $description, $eid);
            $stmt->execute();
            setFlash('Book updated successfully.');
        }
        header('Location: books.php'); exit();
    }
}

if ($action === 'delete' && $id) {
    // Block delete if any copy is currently borrowed
    $chk = $conn->prepare('SELECT COUNT(*) FROM borrow_records WHERE book_id=? AND status="borrowed"');
    $chk->bind_param('i', $id); $chk->execute();
    $borrowed_count = $chk->get_result()->fetch_row()[0];

    if ($borrowed_count > 0) {
        setFlash('Cannot delete this book — ' . $borrowed_count . ' copy/copies are currently borrowed. Please return them first.', 'danger');
    } else {
        $stmt = $conn->prepare('DELETE FROM books WHERE id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        setFlash('Book deleted.');
    }
    header('Location: books.php'); exit();
}

$record = null;
if ($action === 'edit' && $id) {
    $r = $conn->prepare('SELECT * FROM books WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $record = $r->get_result()->fetch_assoc();
}

$authors    = $conn->query('SELECT id, full_name FROM authors ORDER BY full_name');
$categories = $conn->query('SELECT id, name FROM categories ORDER BY name');

$topbar_btn = ['url' => 'books.php?action=add', 'icon' => 'bi-plus-lg', 'label' => 'New Book'];
require_once 'includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-card" style="max-width:700px">
    <h6 class="mb-3"><?= $action === 'add' ? 'Add New Book' : 'Edit Book' ?></h6>
    <form method="POST" action="books.php">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
        <?php if ($record): ?><input type="hidden" name="edit_id" value="<?= $record['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($record['title'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control" value="<?= htmlspecialchars($record['isbn'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Author</label>
                <select name="author_id" class="form-select">
                    <option value="">— Select Author —</option>
                    <?php $authors->data_seek(0); while($a = $authors->fetch_assoc()): ?>
                        <option value="<?= $a['id'] ?>" <?= ($record['author_id'] ?? '') == $a['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['full_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— Select Category —</option>
                    <?php $categories->data_seek(0); while($c = $categories->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>" <?= ($record['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Publisher</label>
                <input type="text" name="publisher" class="form-control" value="<?= htmlspecialchars($record['publisher'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <input type="number" name="publish_year" class="form-control" min="1000" max="<?= date('Y') ?>" value="<?= $record['publish_year'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Pages</label>
                <input type="number" name="page_count" class="form-control" min="1" value="<?= $record['page_count'] ?? '' ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control" min="0" value="<?= $record['stock'] ?? 1 ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($record['description'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Save</button>
            <a href="books.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="table-card">
    <div class="table-header">
        <div class="search-box" style="min-width:260px">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search books...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr><th>#</th><th>Title</th><th>Author</th><th>Category</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody id="booksTable">
            <?php
            $books = $conn->query('SELECT b.*, a.full_name AS author, c.name AS category FROM books b LEFT JOIN authors a ON b.author_id=a.id LEFT JOIN categories c ON b.category_id=c.id ORDER BY b.id DESC');
            while ($r = $books->fetch_assoc()):
            ?>
            <tr>
                <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
                <td>
                    <div style="font-weight:500"><?= htmlspecialchars($r['title']) ?></div>
                    <?php if ($r['isbn']): ?><div style="font-size:0.75rem;color:#9ca3af"><?= htmlspecialchars($r['isbn']) ?></div><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['author'] ?? '—') ?></td>
                <td><?= $r['category'] ? '<span class="badge-custom badge-blue">'.htmlspecialchars($r['category']).'</span>' : '—' ?></td>
                <td><?= $r['stock'] > 0 ? '<span class="badge-custom badge-green">'.$r['stock'].' left</span>' : '<span class="badge-custom badge-red">Out of stock</span>' ?></td>
                <td>
                    <a href="books.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
                    <a href="books.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete '<?= htmlspecialchars($r['title']) ?>'?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>liveSearch('searchInput', 'booksTable', 'http://libraryms.byethost12.com/ajax/search_books.php');</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
