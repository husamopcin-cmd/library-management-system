<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Borrow Records';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id     = intval($_POST['book_id'] ?? 0);
    $member_id   = intval($_POST['member_id'] ?? 0);
    $borrow_date = clean($_POST['borrow_date'] ?? date('Y-m-d'));
    $return_date = clean($_POST['return_date'] ?? '');
    $notes       = clean($_POST['notes'] ?? '');

    if (!$book_id || !$member_id) {
        setFlash('Book and member are required.', 'danger');
    } else {
        if ($_POST['form_action'] === 'add') {
            // New records always start as "borrowed"
            $status = 'borrowed';
            $stk = $conn->prepare('SELECT stock FROM books WHERE id=?');
            $stk->bind_param('i', $book_id); $stk->execute();
            $stk_row = $stk->get_result()->fetch_assoc();

            if (!$stk_row || $stk_row['stock'] < 1) {
                setFlash('This book is out of stock and cannot be issued.', 'danger');
            } else {
                $ret  = $return_date ?: null;
                $stmt = $conn->prepare('INSERT INTO borrow_records (book_id, member_id, borrow_date, return_date, status, notes) VALUES (?,?,?,?,?,?)');
                $stmt->bind_param('iissss', $book_id, $member_id, $borrow_date, $ret, $status, $notes);
                $stmt->execute();
                $conn->query("UPDATE books SET stock = stock - 1 WHERE id=" . intval($book_id));
                setFlash('Borrow record created successfully.');
                header('Location: borrows.php'); exit();
            }
        } else {
            $eid     = intval($_POST['edit_id']);
            $status  = in_array($_POST['status'] ?? '', ['borrowed','returned']) ? $_POST['status'] : 'borrowed';

            $old = $conn->prepare('SELECT status, book_id FROM borrow_records WHERE id=?');
            $old->bind_param('i', $eid); $old->execute();
            $old_row = $old->get_result()->fetch_assoc();

            if (!$old_row) {
                setFlash('Record not found.', 'danger');
                header('Location: borrows.php'); exit();
            }

            $old_status  = $old_row['status'];
            $old_book_id = intval($old_row['book_id']);
            $ret = $return_date ?: null;

            // Update the record first
            $stmt = $conn->prepare('UPDATE borrow_records SET book_id=?, member_id=?, borrow_date=?, return_date=?, status=?, notes=? WHERE id=?');
            $stmt->bind_param('iissssi', $book_id, $member_id, $borrow_date, $ret, $status, $notes, $eid);
            $stmt->execute();

            if ($old_book_id === $book_id) {
                // Same book — only adjust for status change
                if ($old_status === 'borrowed' && $status === 'returned') {
                    $conn->query("UPDATE books SET stock = stock + 1 WHERE id=" . intval($book_id));
                } elseif ($old_status === 'returned' && $status === 'borrowed') {
                    $stk2 = $conn->prepare('SELECT stock FROM books WHERE id=?');
                    $stk2->bind_param('i', $book_id); $stk2->execute();
                    $stk2_row = $stk2->get_result()->fetch_assoc();
                    if (!$stk2_row || $stk2_row['stock'] < 1) {
                        // Revert status
                        $rv = $conn->prepare('UPDATE borrow_records SET status="returned" WHERE id=?');
                        $rv->bind_param('i', $eid); $rv->execute();
                        setFlash('Not enough stock to re-issue this book.', 'danger');
                        header('Location: borrows.php'); exit();
                    }
                    $conn->query("UPDATE books SET stock = stock - 1 WHERE id=" . intval($book_id));
                }
            } else {
                // Book changed — restore old book's stock if it was borrowed
                if ($old_status === 'borrowed') {
                    $conn->query("UPDATE books SET stock = stock + 1 WHERE id=" . intval($old_book_id));
                }
                // Deduct new book's stock if new status is borrowed
                if ($status === 'borrowed') {
                    $stk3 = $conn->prepare('SELECT stock FROM books WHERE id=?');
                    $stk3->bind_param('i', $book_id); $stk3->execute();
                    $stk3_row = $stk3->get_result()->fetch_assoc();
                    if (!$stk3_row || $stk3_row['stock'] < 1) {
                        // Revert everything
                        $rv = $conn->prepare('UPDATE borrow_records SET book_id=?, status=? WHERE id=?');
                        $rv->bind_param('isi', $old_book_id, $old_status, $eid);
                        $rv->execute();
                        if ($old_status === 'borrowed') {
                            $conn->query("UPDATE books SET stock = stock - 1 WHERE id=" . intval($old_book_id));
                        }
                        setFlash('New book is out of stock. Record reverted.', 'danger');
                        header('Location: borrows.php'); exit();
                    }
                    $conn->query("UPDATE books SET stock = stock - 1 WHERE id=" . intval($book_id));
                }
            }

            setFlash('Record updated successfully.');
            header('Location: borrows.php'); exit();
        }
    }
}

// Quick return — only works if status is currently "borrowed"
if ($action === 'return' && $id) {
    $r = $conn->prepare('SELECT book_id FROM borrow_records WHERE id=? AND status="borrowed"');
    $r->bind_param('i', $id); $r->execute();
    $row = $r->get_result()->fetch_assoc();
    if ($row) {
        $u = $conn->prepare('UPDATE borrow_records SET status="returned", return_date=CURDATE() WHERE id=?');
        $u->bind_param('i', $id); $u->execute();
        $conn->query("UPDATE books SET stock = stock + 1 WHERE id=" . intval($row['book_id']));
        setFlash('Book returned successfully.');
    } else {
        setFlash('This record is already returned.', 'danger');
    }
    header('Location: borrows.php'); exit();
}

// Delete — restore stock only if the book was still borrowed
if ($action === 'delete' && $id) {
    $r = $conn->prepare('SELECT status, book_id FROM borrow_records WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $row = $r->get_result()->fetch_assoc();
    if ($row) {
        if ($row['status'] === 'borrowed') {
            $conn->query("UPDATE books SET stock = stock + 1 WHERE id=" . intval($row['book_id']));
        }
        $stmt = $conn->prepare('DELETE FROM borrow_records WHERE id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        setFlash('Record deleted.');
    }
    header('Location: borrows.php'); exit();
}

$record = null;
if ($action === 'edit' && $id) {
    $r = $conn->prepare('SELECT * FROM borrow_records WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $record = $r->get_result()->fetch_assoc();
}

$books   = $conn->query('SELECT id, title, stock FROM books ORDER BY title');
$members = $conn->query('SELECT id, full_name FROM members WHERE status="active" ORDER BY full_name');
$filter  = $_GET['filter'] ?? 'all';
$where   = $filter === 'borrowed' ? 'WHERE br.status="borrowed"' : ($filter === 'returned' ? 'WHERE br.status="returned"' : '');

$topbar_btn = ['url' => 'borrows.php?action=add', 'icon' => 'bi-plus-lg', 'label' => 'Issue a Book'];
require_once 'includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-card" style="max-width:620px">
    <h6 class="mb-3"><?= $action === 'add' ? 'Issue a Book' : 'Edit Borrow Record' ?></h6>
    <form method="POST" action="borrows.php">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
        <?php if ($record): ?><input type="hidden" name="edit_id" value="<?= $record['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Book *</label>
                <select name="book_id" class="form-select" required>
                    <option value="">— Select Book —</option>
                    <?php $books->data_seek(0); while ($b = $books->fetch_assoc()): ?>
                        <option value="<?= $b['id'] ?>" <?= ($record['book_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['title']) ?> (Stock: <?= $b['stock'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Member *</label>
                <select name="member_id" class="form-select" required>
                    <option value="">— Select Member —</option>
                    <?php $members->data_seek(0); while ($m = $members->fetch_assoc()): ?>
                        <option value="<?= $m['id'] ?>" <?= ($record['member_id'] ?? '') == $m['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['full_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Borrow Date</label>
                <input type="date" name="borrow_date" class="form-control" value="<?= $record['borrow_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Expected Return</label>
                <input type="date" name="return_date" class="form-control" value="<?= $record['return_date'] ?? '' ?>">
            </div>
            <?php if ($action === 'edit'): ?>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="borrowed" <?= ($record['status'] ?? 'borrowed') === 'borrowed' ? 'selected' : '' ?>>Borrowed</option>
                    <option value="returned" <?= ($record['status'] ?? '') === 'returned' ? 'selected' : '' ?>>Returned</option>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($record['notes'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Save</button>
            <a href="borrows.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>

<div class="d-flex gap-2 mb-3">
    <a href="borrows.php?filter=all"      class="btn-primary-custom" style="<?= $filter!=='all'?'background:#e5e9f0;color:#374151;':'' ?>">All</a>
    <a href="borrows.php?filter=borrowed" class="btn-primary-custom" style="<?= $filter==='borrowed'?'background:#e67e22':'background:#fef9e7;color:#e67e22' ?>">Borrowed</a>
    <a href="borrows.php?filter=returned" class="btn-primary-custom" style="<?= $filter==='returned'?'background:#27ae60':'background:#e8f8ee;color:#27ae60' ?>">Returned</a>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="search-box" style="min-width:260px">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search by book or member...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>#</th><th>Book</th><th>Member</th><th>Borrow Date</th><th>Return Date</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="borrowsTable">
            <?php
            $borrows = $conn->query("
                SELECT br.*, b.title, m.full_name
                FROM borrow_records br
                JOIN books b ON br.book_id = b.id
                JOIN members m ON br.member_id = m.id
                $where ORDER BY br.id DESC
            ");
            while ($r = $borrows->fetch_assoc()):
                $overdue = $r['status'] === 'borrowed' && $r['return_date'] && $r['return_date'] < date('Y-m-d');
            ?>
            <tr>
                <td class="text-muted" style="font-size:0.78rem"><?= $r['id'] ?></td>
                <td style="font-weight:500"><?= htmlspecialchars($r['title']) ?></td>
                <td><?= htmlspecialchars($r['full_name']) ?></td>
                <td style="font-size:0.85rem"><?= date('M d, Y', strtotime($r['borrow_date'])) ?></td>
                <td style="font-size:0.85rem"><?= $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : '<span class="text-muted">—</span>' ?></td>
                <td>
                    <?php if ($r['status'] === 'returned'): ?>
                        <span class="badge-custom badge-green">Returned</span>
                    <?php elseif ($overdue): ?>
                        <span class="badge-custom badge-red">Overdue</span>
                    <?php else: ?>
                        <span class="badge-custom badge-amber">Borrowed</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($r['status'] === 'borrowed'): ?>
                        <a href="borrows.php?action=return&id=<?= $r['id'] ?>" class="btn-action view" title="Mark as Returned"><i class="bi bi-check-circle"></i></a>
                    <?php endif; ?>
                    <a href="borrows.php?action=edit&id=<?= $r['id'] ?>" class="btn-action edit"><i class="bi bi-pencil"></i></a>
                    <a href="borrows.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete this borrow record?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>liveSearch('searchInput', 'borrowsTable', window.location.origin + '/kutuphane/ajax/search_borrows.php?filter=<?= $filter ?>');</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
