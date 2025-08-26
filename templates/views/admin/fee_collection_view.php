<h1 class="mb-4">Fee Collection</h1>

<div class="card mb-4">
    <!-- ... Student Search Form ... -->
</div>

<?php if ($student_details): ?>
    <h2>Fee Records for <?php echo escape($student_details['first_name'] . ' ' . $student_details['last_name']); ?></h2>
    <div class="card">
        <div class="card-body">
            <table class="table" id="fees-table">
                <!-- ... table headers ... -->
                <tbody>
                    <?php foreach ($fee_records as $record): ?>
                        <tr>
                            <td><?php echo escape($record['fee_name']); ?></td>
                            <td><?php echo escape($record['total_amount']); ?></td>
                            <td class="amount-paid"><?php echo escape($record['amount_paid']); ?></td>
                            <td class="balance"><?php echo escape($record['total_amount'] - $record['amount_paid']); ?></td>
                            <td><span class="badge bg-info status"><?php echo escape($record['status']); ?></span></td>
                            <td>
                                <?php if ($record['status'] !== 'Paid'): ?>
                                    <button class="btn btn-sm btn-success collect-payment-btn" data-record-id="<?php echo $record['id']; ?>" data-due="<?php echo $record['total_amount'] - $record['amount_paid']; ?>">Collect Payment</button>
                                <?php endif; ?>
                                <a href="#" class="btn btn-sm btn-secondary">History</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="payment-form" action="<?php echo APP_URL; ?>/../src/api/process_payment.php" method="POST">
                    <input type="hidden" name="student_fee_record_id" id="record-id-input">
                    <div class="mb-3">
                        <label>Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount_paid" required>
                    </div>
                    <div class="mb-3">
                        <label>Payment Method</label>
                        <select class="form-select" name="payment_method">
                            <option>Cash</option><option>Card</option><option>Bank Transfer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Notes</label>
                        <textarea class="form-control" name="notes"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

    $('.collect-payment-btn').on('click', function() {
        var recordId = $(this).data('record-id');
        var amountDue = $(this).data('due');
        $('#record-id-input').val(recordId);
        $('#payment-form input[name=amount_paid]').val(amountDue);
        paymentModal.show();
    });

    handleAjaxFormSubmit('#payment-form', function(response) {
        paymentModal.hide();
        if(response.success) {
            Swal.fire({
                title: 'Success!',
                text: response.message,
                icon: 'success',
                footer: '<a href="<?php echo APP_URL; ?>/receipt?payment_id=' + response.payment_id + '" target="_blank">Print Receipt</a>'
            }).then(() => window.location.reload());
        }
    });
});
</script>
