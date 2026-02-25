/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/bank",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'bankid', name: 'Id'},
            { data: 'BankName', name: 'BankName'},
            { data: 'AccountNumber', name: 'tblbankaccount.AccountNumber'},
            {
                data: null,
                render: function (data, type, row) {
                    var updateBtn = '<a href="/bank-update/' + data.bankid + '?account_number=' + data.AccountNumber + '" style="display: inline-block; padding: 6px 12px; background-color: #f59e0b; color: white; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none;">Update</a>';
                    var deleteBtn = '<a data-bs-toggle="modal" data-bs-target="#bankDeleteModal" data-bank-id="' + data.Id + '" data-bank-accountno="' + data.AccountNumber + '" data-bank-name="' + data.BankName + '" style="display: inline-block; padding: 6px 12px; background-color: #fef2f2; color: #7f1d1d; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none; cursor: pointer; border: 1px solid #fecaca;">Delete</a>';
                    return '<div style="display: flex; gap: 8px; white-space: nowrap;">' + updateBtn + deleteBtn + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [3],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#bankDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let bankId = button.data('bank-id');
        let bankName = button.data('bank-name');
        let bankAccountNo = button.data('bank-accountno');
        
        let modal = $(this);
        modal.find('#bankToDelete').text(bankName + ' [ ' + bankAccountNo + ' ]');
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-bankaccount-delete/' + bankId + '?account_number=' + bankAccountNo);
            deleteForm.submit();
        });
    });
});