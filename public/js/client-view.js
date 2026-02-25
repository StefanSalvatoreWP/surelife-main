/* 2023 SilverDust) S. Maceren */

var loadedTable; // Global variable for payment history table

$(document).ready(function() {

    let clientId = $('#clientid').val();
    loadPaymentHistoryTable(clientId);
    loadLoanPaymentsTable(clientId);

    // Payment filter buttons
    $('.payment-filter-btn').on('click', function() {
        let filterValue = $(this).data('filter');
        
        // Update button styles
        $('.payment-filter-btn').removeClass('active');
        $(this).addClass('active');
        
        // Custom filtering logic for DataTables
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // Only filter the main payment table
                if (settings.nTable.id !== 'common_dataTable') return true;

                let row = $(settings.aoData[dataIndex].nTr);
                let remarks = row.data('remarks');
                let isVoid = row.data('void') == 1;

                if (filterValue === 'clear') return true;
                if (filterValue === 'Void') return isVoid;
                
                // "Plan" includes null, Standard, and Partial
                let isPlan = (remarks === '' || remarks === null || remarks === 'Standard' || remarks === 'Partial');
                
                if (filterValue === 'Plan') return !isVoid && isPlan;
                if (filterValue === 'Others') return !isVoid && !isPlan;
                
                return true;
            }
        );

        loadedTable.draw();
        $.fn.dataTable.ext.search.pop(); // Clear search function after draw
    });
    
    // void payment
    $('#paymentVoidModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let paymentId = button.data('payment-id');
        let orNo = button.data('payment-orno');
        
        let modal = $(this);
        modal.find('#paymentToVoid').text(orNo);
        
        // Remove any previous click handlers to avoid stacking
        modal.find('#confirmVoid').off('click').on('click', function() {
            
            let voidForm = $('#voidForm');
            voidForm.attr('action', '/client-void-payment/' + paymentId);
            voidForm.submit();
        });
    });

    // void loan payment
    $('#loanPaymentVoidModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let loanPaymentId = button.data('loan-payment-id');
        let orNo = button.data('loan-payment-orno');
        
        let modal = $(this);
        modal.find('#loanPaymentToVoid').text(orNo);
        
        // Remove any previous click handlers to avoid stacking
        modal.find('#confirmLoanVoid').off('click').on('click', function() {
            
            let voidForm = $('#voidLoanForm');
            voidForm.attr('action', '/client-void-loanpayment/' + loanPaymentId);
            voidForm.submit();
        });
    });

    // change status
    $('#clientStatusModal').on('show.bs.modal', function(event) {
        console.log('🔵 Verify/Approve modal opened');

        let button = $(event.relatedTarget);
        let clientId = button.data('client-id');
        let clientName = button.data('client-name');
        
        console.log('📋 Client ID:', clientId);
        console.log('👤 Client Name:', clientName);

        let modal = $(this);
        modal.find('#clientStatus').text(clientName);
        
        // Remove any previous click handlers to avoid stacking
        modal.find('#confirmClientStatus').off('click').on('click', function() {
            console.log('✅ Confirm button clicked!');
            console.log('🔄 Submitting form with action:', '/client-update-status/' + clientId);
            
            let clientStatusForm = $('#clientStatusForm');
            clientStatusForm.attr('action', '/client-update-status/' + clientId);
            console.log('📤 Form element:', clientStatusForm);
            console.log('🎯 Form action set to:', clientStatusForm.attr('action'));
            clientStatusForm.submit();
            console.log('✔️ Form submitted');
        });
    });

    // certificate of full payment approval
    $('#showApproveCfpInputModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let clientId = button.data('client-id');
        let modal = $(this);

        // Remove any previous click handlers to avoid stacking
        modal.find('#cofpApproval').off('click').on('click', function() {

            var downloadLink = "/client-printcofp/" + clientId;

            window.location.href = downloadLink;
            $('#showCfpNoInputModal').modal('hide');
        });
    });

    // certificate of full payment
    $('#showCfpNoInputModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let clientId = button.data('client-id');
        let modal = $(this);

        // Remove any previous click handlers to avoid stacking
        modal.find('#downloadCfpWithInput').off('click').on('click', function() {
            var cfpNoInput = modal.find('#cfpNoInput').val();
            var downloadLink = "/client-printcofp/" + clientId + "?cfpNoInput=" + cfpNoInput;

            window.location.href = downloadLink;
            $('#showCfpNoInputModal').modal('hide');
        });
    });
});

function loadPaymentHistoryTable(clientId){

    loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        order: [[0, 'desc']], // Order by hidden Id col
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        responsive: true,
        columnDefs: [
            { targets: 0, visible: false },
            { targets: 7, orderable: false }
        ]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });
}

function loadLoanPaymentsTable(clientId){

    loan_paymentsTable = $('#loan_paymentsTable').DataTable({
        processing: true,
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        responsive: true,
        columnDefs: [
            { targets: 0, visible: false },
            { targets: 7, orderable: false }
        ]
    });
    $('#loan_paymentsTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });
}

// /* 2023 SilverDust) S. Maceren */

// $(document).ready(function() {

//     let clientId = $('#clientid').val();

//     // onload
//     let paymentHistoryTable = loadPaymentHistoryTable(clientId);
//     let loadPaymentsTable = loadLoanPaymentsTable(clientId);

//     let filterText = 'Plan';
//     $('.list-group-item a').each(function() {
//         if ($(this).text() === filterText) {
//             $(this).css({
//                 color: 'green',
//             });
//         }
//     });
//     paymentHistoryTable.ajax.url('/get-payment-history?cid=' + clientId + '&filter=' + filterText).load();

//     // search filter
//     $('.list-group-item a').click(function(e) {

//         e.preventDefault();
        
//         $('.list-group-item a').removeAttr('style');
//         $('.list-group-item a').css('text-decoration', 'none');
       
//         filterText = $(this).text();
       
//         if(filterText != 'Clear Filter'){
//             $(this).css({
//                 color: 'green',
//             });
//         }
        
//         $(document).trigger('filterClicked', [filterText]);
//     });

//     // reload table based on filter
//     $(document).on('filterClicked', function(event, filterText) {
//         paymentHistoryTable.ajax.url('/get-payment-history?cid=' + clientId + '&filter=' + filterText).load();
//     });
    
//     // filter payment history table
//     $('#common_dataTable_filter input').on('keyup', function() {
//         paymentHistoryTable.search(this.value).draw();
//     });

//     // filter loan payments table
//     $('#loan_paymentsTable_filter input').on('keyup', function() {
//         loadPaymentsTable.search(this.value).draw();
//     });

//     // void payment
//     $('#paymentVoidModal').on('show.bs.modal', function(event) {

//         let button = $(event.relatedTarget);
//         let paymentId = button.data('payment-id');
//         let orNo = button.data('payment-orno');
        
//         let modal = $(this);
//         modal.find('#paymentToVoid').text(orNo);
//         modal.find('#confirmVoid').click(function() {
            
//             let voidForm = $('#voidForm');
//             voidForm.attr('action', '/client-void-payment/' + paymentId);
//             voidForm.submit();
//         });
//     });

//     // void loan payment
//     $('#loanPaymentVoidModal').on('show.bs.modal', function(event) {

//         let button = $(event.relatedTarget);
//         let loanPaymentId = button.data('loan-payment-id');
//         let orNo = button.data('loan-payment-orno');
        
//         let modal = $(this);
//         modal.find('#loanPaymentToVoid').text(orNo);
//         modal.find('#confirmLoanVoid').click(function() {
            
//             let voidForm = $('#voidLoanForm');
//             voidForm.attr('action', '/client-void-loanpayment/' + loanPaymentId);
//             voidForm.submit();
//         });
//     });

//     // change status
//     $('#clientStatusModal').on('show.bs.modal', function(event) {

//         let button = $(event.relatedTarget);
//         let clientId = button.data('client-id');
//         let clientName = button.data('client-name');

//         let modal = $(this);
//         modal.find('#clientStatus').text(clientName);
//         modal.find('#confirmClientStatus').click(function() {
            
//             let clientStatusForm = $('#clientStatusForm');
//             clientStatusForm.attr('action', '/client-update-status/' + clientId);
//             clientStatusForm.submit();
//         });
//     });

//     // certificate of full payment approval
//     $('#showApproveCfpInputModal').on('show.bs.modal', function(event) {

//         let button = $(event.relatedTarget);
//         let clientId = button.data('client-id');
//         let modal = $(this);

//         modal.find('#cofpApproval').click(function() {

//             var downloadLink = "/client-printcofp/" + clientId;

//             window.location.href = downloadLink;
//             $('#showCfpNoInputModal').modal('hide');
//         });
//     });

//     // certificate of full payment
//     $('#showCfpNoInputModal').on('show.bs.modal', function(event) {

//         let button = $(event.relatedTarget);
//         let clientId = button.data('client-id');
//         let modal = $(this);

//         modal.find('#downloadCfpWithInput').click(function() {
//             var cfpNoInput = modal.find('#cfpNoInput').val();
//             var downloadLink = "/client-printcofp/" + clientId + "?cfpNoInput=" + cfpNoInput;

//             window.location.href = downloadLink;
//             $('#showCfpNoInputModal').modal('hide');
//         });
//     });
// });

// function loadPaymentHistoryTable(clientId){

//     return loadedTable = $('#common_dataTable').DataTable({
//         processing: true,
//         serverSide: true,
//         pageLength: 10,
//         lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
//         
//         ajax: {
//             url: "/get-payment-history?cid=" + clientId,
//             data: function(d) {
//                 d.search.value = $('#common_dataTable_filter input').val();
//             }
//         },
//         columns: [
//             { data: 'Id', name: 'Id' },
//             { data: 'SeriesCode', name: 'SeriesCode' },
//             { data: 'ORNo', name: 'ORNo' },
//             { 
//                 data: 'AmountPaid', 
//                 name: 'AmountPaid',
//                 render: function(data, type, row) {
//                     return parseFloat(data).toLocaleString('en-US', {
//                         style: 'currency',
//                         currency: 'PHP'
//                     });
//                 }
//             },
//             { 
//                 data: 'Installment', 
//                 name: 'Installment',
//                 render: function(data, type, row){
//                     if (data === null) {
//                         return '<span class="text-secondary">Not available</span>';
//                     }
//                     else{
//                         return data;
//                     }
//                 }
//             },
//             { data: 'Date', name: 'Date' },
//             { 
//                 data: 'Remarks', 
//                 name: 'Remarks',
//                 render: function(data, type, row){

//                     if(data === null){
//                         return '<span class="text-secondary">Standard</span>';
//                     }
//                     
//                     return '<span class="text-secondary">' + data + '</span>';
//                 }
//             },
//             {
//                 data: null,
//                 render: function (data, type, row) {

//                     var voidLink;

//                     if(data.VoidStatus === 1 || data.Remarks == 'Assigned'){
//                         voidLink = '<span class="badge bg-secondary">Void</span>';
//                     }
//                     else{
//                         voidLink = '<a data-bs-toggle="modal" data-bs-target="#paymentVoidModal" data-payment-id="' + data.Id + '" data-payment-orno="' + data.ORNo + '" role="button"><span class="badge bg-danger">Void</span></a>';
//                     }

//                     return voidLink;
//                 }
//             }
//         ],
//         columnDefs: [
//             {
//                 targets: [0],
//                 visible: false
//             },
//         ],
//         order: [
//             [5, 'desc'],
//             [0, 'desc']
//         ]
//     });
// }

// function loadLoanPaymentsTable(clientId){

//     return loadedTable = $('#loan_paymentsTable').DataTable({
//         processing: true,
//         serverSide: true,
//         pageLength: 10,
//         lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
//         
//         ajax: {
//             url: "/get-loan-payments?cid=" + clientId,
//             data: function(d) {
//                 d.search.value = $('#loan_paymentsTable_filter input').val();
//             }
//         },
//         columns: [
//             { data: 'Id', name: 'Id' },
//             { data: 'SeriesCode', name: 'SeriesCode' },
//             { data: 'ORNo', name: 'ORNo' },
//             { 
//                 data: 'Amount', 
//                 name: 'Amount',
//                 render: function(data, type, row) {
//                     return parseFloat(data).toLocaleString('en-US', {
//                         style: 'currency',
//                         currency: 'PHP'
//                     });
//                 }
//             },
//             { 
//                 data: 'Installment', 
//                 name: 'Installment',
//                 render: function(data, type, row){
//                     if (data === null) {
//                         return '<span class="text-secondary">Not available</span>';
//                     }
//                     else{
//                         return data;
//                     }
//                 }
//             },
//             { data: 'PaymentDate', name: 'PaymentDate' },
//             { data: 'Status', name: 'Status' },
//             {
//                 data: null,
//                 render: function (data, type, row) {

//                     var voidLink;
//                     if(data.Status === 'Void'){
//                         voidLink = '<span class="badge bg-secondary">Void</span>';
//                     }
//                     else{
//                         voidLink = '<a data-bs-toggle="modal" data-bs-target="#loanPaymentVoidModal" data-loan-payment-id="' + data.Id + '" data-loan-payment-orno="' + data.ORNo + '" role="button"><span class="badge bg-danger">Void</span></a>';
//                     }

//                     return voidLink;
//                 }
//             }
//         ],
//         columnDefs: [
//             {
//                 targets: [0],
//                 visible: false
//             },
//         ],
//         order: [
//             [3, 'desc'],
//             [0, 'desc']
//         ]
//     });
// }