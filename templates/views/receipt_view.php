<div class="receipt-container">
    <div class="text-center mb-4">
        <h2><?php echo APP_NAME; ?></h2>
        <p>Official Fee Receipt</p>
    </div>
    <hr>
    <div class="row mb-3">
        <div class="col"><strong>Receipt No:</strong> <?php echo escape($receipt_data['receipt_number']); ?></div>
        <div class="col text-end"><strong>Date:</strong> <?php echo escape($receipt_data['payment_date']); ?></div>
    </div>
    <div class="row mb-3">
        <div class="col"><strong>Student:</strong> <?php echo escape($receipt_data['first_name'] . ' ' . $receipt_data['last_name']); ?></div>
        <div class="col text-end"><strong>Admission ID:</strong> <?php echo escape($receipt_data['admission_id']); ?></div>
    </div>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr><th>Fee Type</th><th>Total Amount</th><th>Amount Paid</th></tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo escape($receipt_data['fee_name']); ?></td>
                <td>$<?php echo escape($receipt_data['total_amount']); ?></td>
                <td>$<?php echo escape($receipt_data['amount_paid']); ?></td>
            </tr>
        </tbody>
    </table>
    <p class="text-end"><strong>Processed by:</strong> Admin/Staff</p>
    <div class="text-center mt-5 no-print">
        <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
    </div>
</div>
