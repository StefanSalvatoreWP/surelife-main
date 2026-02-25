/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
    
    var hasRequestedStatus = 0;
    $('#request-cancel-btn').prop('disabled', true);

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/commission",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { 
                data: null, 
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    return '<input type="checkbox" class="row-checkbox" value="' + data.Id + '">';
                } 
            },
            { data: 'Id', name: 'tblencashment.Id' },
            { data: 'LastName', name: 'tblclient.LastName' },
            { data: 'FirstName', name: 'tblclient.FirstName' },
            { data: 'MiddleName', name: 'tblclient.MiddleName' },
            { data: 'ContractNo', name: 'tblencashment.ContractNo' },
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
            { data: 'Commission', name: 'Commission' },
            { data: 'PaymentDate', name: 'PaymentDate' },
            { data: 'Status', name: 'tblencashment.Status' },
            { data: 'VoucherCode', name: 'VoucherCode' },
        ],
        columnDefs: [
            {
                targets: [1],
                visible: false
            },
        ],
        order: [[1, 'desc']],
        createdRow: function(row, data, dataIndex) {

            // status color
            if (data.Status === 'Pending') {
                $(row).find('td:eq(8)').css('color', 'gray');
            } 
            else if (data.Status === 'Processing') {
                $(row).find('td:eq(8)').css('color', 'blue');
                $('#request-cancel-btn').removeAttr('disabled');
                hasRequestedStatus = 1;
            } 
            else if (data.Status === 'Claimed') {
                $(row).find('td:eq(8)').css('color', 'green');
            } 
            else if (data.Status === 'For Releasing') {
                $(row).find('td:eq(8)').css('color', 'orange');
            } 
            else if (data.Status === 'Rejected') {
                $(row).find('td:eq(8)').css('color', 'red');
            }
            
            // voucher color
            if (data.VoucherCode === 'Not available') {
                $(row).find('td:eq(9)').css('color', 'lightgray');
            }
            else{
                $(row).find('td:eq(9)').css('color', 'green');
            }
        }
    });

    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    // get selected items
    $('#common_dataTable').on('change', '.row-checkbox', function() {

        var selectedItems = [];
        $('.row-checkbox:checked').each(function() {
            selectedItems.push($(this).val());
        });
       
        if(hasRequestedStatus == 0){
            if (selectedItems.length <= 0) {
                $('#request-btn').attr('disabled', true);
            } 
            else {
                $('#request-btn').removeAttr('disabled');
            }
        }
    });

    /* MODALS */
    $('#comsRequestModal').on('show.bs.modal', function(event) {

        var totalAmountPaid = 0;

        var selectedItems = [];
        $('.row-checkbox:checked').each(function() {
            selectedItems.push($(this).val());

            var rowData = loadedTable.row($(this).closest('tr')).data();
            totalAmountPaid += rowData.AmountPaid;
        });
        
        let modal = $(this);
        modal.find('#confirmComsRequest').click(function() {
            
            let insertData = {
                'comsIds': selectedItems,
                'amount': totalAmountPaid
            };
            
            let comsRequestForm = $('#comsRequestForm');
            comsRequestForm.attr('action', '/submit-encashment-req?encashmentReqData=' + encodeURIComponent(JSON.stringify(insertData)));
            comsRequestForm.submit();
        });
    });

    $('#comsCancelRequestModal').on('show.bs.modal', function(event) {
        
        let modal = $(this);
        modal.find('#confirmCancelComsRequest').click(function() {
            
            let comsCancelRequestForm = $('#comsCancelRequestForm');
            comsCancelRequestForm.attr('action', '/submit-cancel-encashment-req');
            comsCancelRequestForm.submit();
        });
    });
});