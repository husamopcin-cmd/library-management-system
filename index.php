<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Dashboard';

$total_books    = $conn->query('SELECT COUNT(*) FROM books')->fetch_row()[0];
$active_members = $conn->query('SELECT COUNT(*) FROM members WHERE status="active"')->fetch_row()[0];
$borrowed_books = $conn->query('SELECT COUNT(*) FROM borrow_records WHERE status="borrowed"')->fetch_row()[0];
$overdue_books  = $conn->query('SELECT COUNT(*) FROM borrow_records WHERE status="borrowed" AND return_date < CURDATE()')->fetch_row()[0];

$recent_borrows = $conn->query('
    SELECT br.*, b.title, m.full_name
    FROM borrow_records br
    JOIN books b ON br.book_id = b.id
    JOIN members m ON br.member_id = m.id
    ORDER BY br.created_at DESC LIMIT 7
');

require_once 'includes/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-book"></i></div>
            <div><div class="stat-label">Total Books</div><div class="stat-value"><?= $total_books ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-people"></i></div>
            <div><div class="stat-label">Active Members</div><div class="stat-value"><?= $active_members ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon amber"><i class="bi bi-arrow-left-right"></i></div>
            <div><div class="stat-label">Books Borrowed</div><div class="stat-value"><?= $borrowed_books ?></div></div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="stat-label">Overdue Returns</div><div class="stat-value"><?= $overdue_books ?></div></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="table-card">
            <div class="table-header">
                <h6><i class="bi bi-clock-history me-2 text-muted"></i>Recent Borrow Records</h6>
                <a href="borrows.php" class="btn-primary-custom" style="font-size:0.78rem;padding:0.35rem 0.75rem;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr><th>Book</th><th>Member</th><th>Borrow Date</th><th>Return Date</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php while($r = $recent_borrows->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['title']) ?></td>
                            <td><?= htmlspecialchars($r['full_name']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['borrow_date'])) ?></td>
                            <td><?= $r['return_date'] ? date('M d, Y', strtotime($r['return_date'])) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <?php if ($r['status'] === 'borrowed'): ?>
                                    <?php if ($r['return_date'] && $r['return_date'] < date('Y-m-d')): ?>
                                        <span class="badge-custom badge-red">Overdue</span>
                                    <?php else: ?>
                                        <span class="badge-custom badge-amber">Borrowed</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge-custom badge-green">Returned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card">
            <h6 class="mb-3"><i class="bi bi-lightning-charge me-2 text-muted"></i>Quick Actions</h6>
            <div class="d-flex flex-column gap-2">
                <a href="books.php?action=add" class="btn-primary-custom"><i class="bi bi-plus-circle"></i> Add New Book</a>
                <a href="members.php?action=add" class="btn-primary-custom" style="background:#27ae60;"><i class="bi bi-person-plus"></i> Add New Member</a>
                <a href="borrows.php?action=add" class="btn-primary-custom" style="background:#e67e22;"><i class="bi bi-arrow-right-circle"></i> Issue a Book</a>
                <a href="authors.php?action=add" class="btn-primary-custom" style="background:#8e44ad;"><i class="bi bi-person-badge"></i> Add Author</a>
                <a href="categories.php?action=add" class="btn-primary-custom" style="background:#16a085;"><i class="bi bi-tag"></i> Add Category</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
