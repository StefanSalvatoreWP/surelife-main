/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
    $('#bankDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let bankId = $('#bankName').val();

        let modal = $(this);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-bank-delete/' + bankId);
            deleteForm.submit();
        });
    });
});