// Custom JavaScript for Admin Panel

function handleAjaxFormSubmit(formId, successCallback) {
    $(formId).on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    if(successCallback) {
                        successCallback(response);
                    }
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'An unexpected error occurred.', 'error');
            }
        });
    });
}

function handleDelete(deleteButtonSelector, successCallback) {
    $(document).on('click', deleteButtonSelector, function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                handleAjaxFormSubmit(form, successCallback);
                form.submit(); // Manually trigger the submit event
            }
        });
    });
}
