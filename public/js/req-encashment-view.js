/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
    
    var sel_encashmentId = $('#encashmentReqId').val();

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/encashmentview-req-clients?eid="+ sel_encashmentId,
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'PaymentDate', name: 'PaymentDate' },
            { data: 'LastName', name: 'tblclient.LastName' },
            { data: 'FirstName', name: 'tblclient.FirstName' },
            { data: 'MiddleName', name: 'tblclient.MiddleName' },
            { data: 'ContractNo', name: 'ContractNo' },
            { data: 'Package', name: 'tblpackage.Package' },
            { data: 'Term', name: 'tblpaymentterm.Term' },
            { 
                data: 'AmountPaid', 
                name: 'AmountPaid',
                render: function(data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            { 
                data: 'Commission', 
                name: 'Commission',
                render: function(data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
        ],
        order: [[0, 'desc']]
    });

    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* MODALS */
    $('#encashmentReqReleaseModal').on('show.bs.modal', function(event) {

        let modal = $(this);
        modal.find('#confirmEncashmentReqRelease').click(function() {
            
            let encashmentReqReleaseForm = $('#encashmentReqReleaseForm');

            let encashmentReqReleaseId = $('#encashmentReqId').val();
            let voucherCode = $('input[name="vouchercode"]').val();

            encashmentReqReleaseForm.attr('action', '/submit-encashment-req-release/' + encashmentReqReleaseId + '?vc=' + voucherCode);
            encashmentReqReleaseForm.submit();
        });
    });

    $('#encashmentReqRejectModal').on('show.bs.modal', function(event) {

        let modal = $(this);
        modal.find('#confirmEncashmentReqReject').click(function() {
            
            let encashmentReqRejectForm = $('#encashmentReqRejectForm');

            let encashmentReqId = $('#encashmentReqId').val();
            let reject_remarks = $('input[name="rejectremarks"]').val();

            encashmentReqRejectForm.attr('action', '/submit-encashment-req-reject/' + encashmentReqId + '?remarks=' + reject_remarks);
            encashmentReqRejectForm.submit();
        });
    });
});