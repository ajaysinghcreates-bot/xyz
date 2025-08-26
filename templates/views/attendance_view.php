<h1 class="mb-4">Take Attendance</h1>

<?php if (!$active_session): ?>
    <div class="alert alert-warning">There is no active session. Please set one in the <a href="sessions">Sessions</a> page.</div>
<?php else: ?>
    <div class="card mb-4">
        <div class="card-header">Select Class and Date</div>
        <div class="card-body">
            <form id="attendance-selector-form">
                <div class="row">
                    <div class="col-md-6">
                        <label>Class</label>
                        <select class="form-select" name="class_id" required>
                            <option value="">-- Select Class --</option>
                            <!-- Populate with classes -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" class="form-control" name="attendance_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Load Students</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="student-list-container"></div>
<?php endif; ?>

<script>
// AJAX logic to load student list based on selection
// and then submit the attendance data.
</script>
