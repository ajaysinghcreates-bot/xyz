<h1 class="mb-4">Schedule for: <?php echo escape($exam['name']); ?></h1>

<div class="card mb-4">
    <div class="card-header">Select Class</div>
    <div class="card-body">
        <form>
            <select class="form-select" name="class_id" onchange="this.form.submit()">
                 <option value="">-- Select Class --</option>
                 <!-- Populate with classes -->
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Subject Schedule</div>
    <div class="card-body">
        <form id="schedule-form">
            <table class="table">
                <thead><tr><th>Subject</th><th>Date</th><th>Start Time</th><th>End Time</th><th>Max Marks</th><th>Passing Marks</th></tr></thead>
                <tbody>
                    <!-- Student list will be loaded here via AJAX -->
                    <tr><td colspan="6">Select a class to see subjects.</td></tr>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Save Schedule</button>
        </form>
    </div>
</div>
