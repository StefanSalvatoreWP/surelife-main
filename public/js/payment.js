/* 2023 SilverDust) S. Maceren */

$(document).ready(function () {

    var table = $('#common_dataTable').DataTable();
    table.destroy();

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        autoWidth: false,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        searchDelay: 500,

        ajax: {
            url: "/payment",
            data: function (d) {
                d.branch = $('#branchFilter').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            {
                data: null,
                name: 'LastName',
                render: function (data, type, row) {
                    return data.LastName + ', ' + data.FirstName + ' ' + data.MiddleName;
                },
                orderable: false
            },
            { data: 'ContractNumber', name: 'ContractNumber', orderable: false },
            { data: 'BranchName', name: 'tblbranch.BranchName' },
            { data: 'ORNo', name: 'ORNo' },
            {
                data: 'AmountPaid',
                name: 'AmountPaid',
                render: function (data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            {
                data: 'Installment',
                name: 'Installment',
                render: function (data, type, row) {
                    if (data === null) {
                        return '<span class="text-secondary">Not available</span>';
                    }

                    return data;
                }
            },
            { data: 'DateCreated', name: 'DateCreated' },
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            }
        ],
        order: [[0, 'desc']]
    });

    // Branch filter change event
    $('#branchFilter').on('change', function () {
        loadedTable.draw();
    });
});