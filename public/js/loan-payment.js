/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
                
    var table = $('#common_dataTable').DataTable();
    table.destroy();

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/loanpayment",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'Id' },
            { data: 'ContractNumber', name: 'tblclient.ContractNumber',  width: '100px' },
            { 
                data: null,
                name: 'tblclient.LastName',
                render: function (data, type, row) {
                    return data.LastName + ', ' + data.FirstName + ' ' + data.MiddleName;
                },
                width: '200px'
            },
            { data: 'ORNo', name: 'ORNo' },
            {
                data: 'Amount',
                name: 'Amount',
                render: function(data, type, row){
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            { 
                data: 'Installment', 
                name: 'Installment',
                render: function(data, type, row){
                    if(data === null){
                        return '<span class="text-secondary">Not available</span>';
                    }

                    return data;
                }
            },
            { data: 'PaymentDate', name: 'tblloanpayment.PaymentDate' },
            { data: 'Code', name: 'tblloanrequest.Code' }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            }
        ],
        order: [[0, 'desc']],
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });
});