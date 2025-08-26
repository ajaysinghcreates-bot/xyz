<!-- Info Widgets -->
<div class="row">
    <div class="col-md-3">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-graduate"></i> Total Students</h5>
                <p class="card-text fs-4" id="total-students">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-users"></i> Total Staff</h5>
                <p class="card-text fs-4" id="total-staff">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-dollar-sign"></i> Monthly Income</h5>
                <p class="card-text fs-4" id="fees-this-month">-</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Pending Tasks</h5>
                <p class="card-text fs-4">5</p>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">Income vs. Expense (Last 6 Months)</div>
            <div class="card-body">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">User Roles</div>
            <div class="card-body">
                <canvas id="userRolesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $.ajax({
        url: '<?php echo APP_URL; ?>/../src/api/dashboard_stats.php',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            // Update widgets
            $('#total-students').text(data.total_students);
            $('#total-staff').text(data.total_staff);
            $('#fees-this-month').text('$ ' + parseFloat(data.fees_this_month).toFixed(2));

            // Render Income/Expense Chart
            var incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
            new Chart(incomeExpenseCtx, {
                type: 'bar',
                data: {
                    labels: data.income_expense_chart.labels,
                    datasets: [
                        {
                            label: 'Income',
                            data: data.income_expense_chart.income,
                            backgroundColor: '#10B981',
                        },
                        {
                            label: 'Expense',
                            data: data.income_expense_chart.expense,
                            backgroundColor: '#EF4444',
                        }
                    ]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Render User Roles Chart
            var userRolesCtx = document.getElementById('userRolesChart').getContext('2d');
            new Chart(userRolesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Admins', 'Staff', 'Students'],
                    datasets: [{
                        data: [data.total_admins, data.total_staff, data.total_students],
                        backgroundColor: ['#4F46E5', '#10B981', '#F59E0B'],
                    }]
                }
            });
        }
    });
});
</script>