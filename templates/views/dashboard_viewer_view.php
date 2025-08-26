<h1 class="mb-4">Student Dashboard</h1>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">My Profile</div>
            <div class="card-body">
                <p><strong>Name:</strong> <?php echo escape($student_data['first_name'] . ' ' . $student_data['last_name']); ?></p>
                <p><strong>Class:</strong> <?php echo escape($student_data['class_name']); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Attendance Summary</div>
            <div class="card-body">
                <p>Coming Soon...</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">My Fee Status</div>
            <div class="card-body">
                <p>Fee details will be shown here.</p>
            </div>
        </div>
    </div>
</div>
