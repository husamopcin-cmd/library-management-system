<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$page_title = 'Members';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name       = clean($_POST['full_name'] ?? '');
    $email           = clean($_POST['email'] ?? '');
    $phone           = clean($_POST['phone'] ?? '');
    $address         = clean($_POST['address'] ?? '');
    $membership_date = clean($_POST['membership_date'] ?? date('Y-m-d'));
    $status          = $_POST['status'] === 'active' ? 'active' : 'inactive';

    if (empty($full_name)) {
        setFlash('Full name is required.', 'danger');
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('Please enter a valid email address.', 'danger');
    } else {
        if ($_POST['form_action'] === 'add') {
            $stmt = $conn->prepare('INSERT INTO members (full_name, email, phone, address, membership_date, status) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('ssssss', $full_name, $email, $phone, $address, $membership_date, $status);
            $stmt->execute();
            setFlash('Member added successfully.');
        } else {
            $eid = intval($_POST['edit_id']);

            // If trying to set inactive, check for active borrows
            if ($status === 'inactive') {
                $chk = $conn->prepare('SELECT COUNT(*) FROM borrow_records WHERE member_id=? AND status="borrowed"');
                $chk->bind_param('i', $eid); $chk->execute();
                $active_borrows = $chk->get_result()->fetch_row()[0];
                if ($active_borrows > 0) {
                    setFlash('Cannot set member as inactive — they have ' . $active_borrows . ' book(s) still borrowed. Please return them first.', 'danger');
                    header('Location: members.php?action=edit&id=' . $eid); exit();
                }
            }

            $stmt = $conn->prepare('UPDATE members SET full_name=?, email=?, phone=?, address=?, membership_date=?, status=? WHERE id=?');
            $stmt->bind_param('ssssssi', $full_name, $email, $phone, $address, $membership_date, $status, $eid);
            $stmt->execute();
            setFlash('Member updated successfully.');
        }
        header('Location: members.php'); exit();
    }
}

if ($action === 'delete' && $id) {
    // Block delete if member has any borrow records
    $chk = $conn->prepare('SELECT COUNT(*) FROM borrow_records WHERE member_id=? AND status="borrowed"');
    $chk->bind_param('i', $id); $chk->execute();
    $active_borrows = $chk->get_result()->fetch_row()[0];
    if ($active_borrows > 0) {
        setFlash('Cannot delete this member — they have ' . $active_borrows . ' book(s) still borrowed. Please return them first.', 'danger');
    } else {
        $stmt = $conn->prepare('DELETE FROM members WHERE id=?');
        $stmt->bind_param('i', $id); $stmt->execute();
        setFlash('Member deleted.');
    }
    header('Location: members.php'); exit();
}

$record = null;
if ($action === 'edit' && $id) {
    $r = $conn->prepare('SELECT * FROM members WHERE id=?');
    $r->bind_param('i', $id); $r->execute();
    $record = $r->get_result()->fetch_assoc();
}

$topbar_btn = ['url' => 'members.php?action=add', 'icon' => 'bi-plus-lg', 'label' => 'New Member'];
require_once 'includes/header.php';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<div class="form-card" style="max-width:600px">
    <h6 class="mb-3"><?= $action === 'add' ? 'Add New Member' : 'Edit Member' ?></h6>
    <form method="POST" action="members.php">
        <input type="hidden" name="form_action" value="<?= $action === 'add' ? 'add' : 'edit' ?>">
        <?php if ($record): ?><input type="hidden" name="edit_id" value="<?= $record['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($record['full_name'] ?? '') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active"   <?= ($record['status'] ?? 'active') === 'active'   ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($record['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($record['email'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($record['phone'] ?? '') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Membership Date</label>
                <input type="date" name="membership_date" class="form-control" value="<?= $record['membership_date'] ?? date('Y-m-d') ?>">
            </div>
            <div class="col-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($record['address'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Save</button>
            <a href="members.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="table-card">
    <div class="table-header">
        <div class="search-box" style="min-width:240px">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search members...">
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>#</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Since</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody id="membersTable">
            <?php
            $members = $conn->query('SELECT * FROM members ORDER BY id DESC');
            while ($r = $members->fetch_assoc()):
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
                    <a href="members.php?action=delete&id=<?= $r['id'] ?>" class="btn-action del btn-confirm-delete" data-message="Delete member '<?= htmlspecialchars($r['full_name']) ?>'?"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
<script>liveSearch('searchInput', 'membersTable', window.location.origin + '/kutuphane/ajax/search_members.php');</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
