<?php
// Fetch staff members from DB
$staff_query = $conn->query("SELECT id, name, department FROM staff WHERE status = 'active' ORDER BY name");
?>

<!-- Request Visit Modal -->
<div class="modal fade" id="requestVisitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background: var(--primary); color: white;">
                <h5 class="modal-title">Request Visit for Staff Member</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="process_request_visit.php">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Visitor Name *</label>
                            <input type="text" name="visitor_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visitor Email *</label>
                            <input type="email" name="visitor_email" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Visitor Phone *</label>
                            <input type="tel" name="visitor_phone" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Visit Date *</label>
                            <input type="date" name="visit_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Staff Member *</label>
                        <select name="staff_id" class="form-select" required>
                            <option value="">-- Select Staff --</option>
                            <?php while ($staff = $staff_query->fetch_assoc()): ?>
                                <option value="<?= $staff['id'] ?>">
                                    <?= htmlspecialchars($staff['name']) ?> (<?= htmlspecialchars($staff['department']) ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Purpose of Visit *</label>
                        <textarea name="purpose" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" style="background: var(--primary); color: white;">
                        <i class="bi bi-send-fill me-1"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>