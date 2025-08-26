<h1 class="mb-4">Manage Classes</h1>

<!-- Add/Edit Form -->
<div class="card mb-4">
    <div class="card-header"><?php echo $edit_class ? 'Edit Class' : 'Add New Class'; ?></div>
    <div class="card-body">
        <form id="class-form" action="<?php echo APP_URL; ?>/classes" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?php echo $edit_class['id'] ?? ''; ?>">
            <input type="hidden" name="action" value="save">
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label>Class Name</label>
                    <input type="text" class="form-control" name="class_name" value="<?php echo escape($edit_class['class_name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-5 mb-3">
                    <label>Numeric Level</label>
                    <input type="number" class="form-control" name="numeric_level" value="<?php echo escape($edit_class['numeric_level'] ?? ''); ?>">
                </div>
                <div class="col-md-2 align-self-end mb-3">
                    <button type="submit" class="btn btn-success w-100">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Classes List -->
<div class="card">
    <div class="card-header">Existing Classes</div>
    <div class="card-body">
        <table class="table table-striped">
            <thead><tr><th>Class Name</th><th>Numeric Level</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                <tr>
                    <td><?php echo escape($class['class_name']); ?></td>
                    <td><?php echo escape($class['numeric_level']); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/classes?edit=<?php echo $class['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form class="d-inline" action="<?php echo APP_URL; ?>/classes" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="id" value="<?php echo $class['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm btn-danger delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    handleAjaxFormSubmit('#class-form', function() {
        setTimeout(() => window.location.reload(), 1000);
    });
    handleDelete('.delete-btn', function() {
        setTimeout(() => window.location.reload(), 1000);
    });
});
</script>
