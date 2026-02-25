/* 2024 SilverDust) S. Maceren */

$(document).ready(function(){
    
    /* MODALS */
    $('#showLoanRequestModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let loanRequestId = button.data('loan-req-id');
  
        let modal = $(this);
        modal.find('#loanReqApproval').click(function() {
            
            let loanReqForm = $('#loanRequestForm');
            loanReqForm.attr('action', '/submit-req-loan/' + loanRequestId);
            loanReqForm.submit();
        });
    });
});