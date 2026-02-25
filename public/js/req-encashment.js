/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/req-encashments",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'tblencashmentreq.Id' },
            { data: 'LastName', name: 'tblstaff.LastName' },
            { data: 'FirstName', name: 'tblstaff.FirstName' },
            { data: 'MiddleName', name: 'tblstaff.MiddleName' },
            { 
                data: 'Amount', 
                name: 'Amount',
                render: function(data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            { data: 'DateRequested', name: 'tblencashmentreq.DateRequested' },
            { data: 'Status', name: 'tblencashmentreq.Status' },
            {
                data: null,
                render: function (data, type, row) {
                    var viewLink = '<a href="/view-req-encashment/' + data.Id + '"><span class="badge bg-primary">View</span></a>';
                    return viewLink;
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [7],
                orderable: false
            }
        ],
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {

            // status color
            if (data.Status === 'Pending') {
                $(row).find('td:eq(5)').css('color', 'gray');
            } 
            else if (data.Status === 'Verified') {
                $(row).find('td:eq(5)').css('color', 'blue');
            } 
            else if (data.Status === 'Recorded') {
                $(row).find('td:eq(5)').css('color', 'orange');
            } 
            else if (data.Status === 'Approved') {
                $(row).find('td:eq(5)').css('color', 'green');
            } 
            else if (data.Status === 'Released') {
                $(row).find('td:eq(5)').css('color', 'green');
            }
            else if (data.Status === 'Claimed') {
                $(row).find('td:eq(5)').css('color', 'green');
            } 
            else if (data.Status === 'Rejected') {
                $(row).find('td:eq(5)').css('color', 'red');
            }

            // voucher code
            if (data.VoucherCode === 'Not available') {
                $(row).find('td:eq(6)').css('color', 'lightgray');
            }
        }
    });
    
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* MODALS */
    // $('#roleDeleteModal').on('show.bs.modal', function(event) {

    //     let button = $(event.relatedTarget);
    //     let roleId = button.data('role-id');
    //     let roleName = button.data('role-name');
        
    //     let modal = $(this);
    //     modal.find('#roleToDelete').text(roleName);
    //     modal.find('#confirmDelete').click(function() {
            
    //         let deleteForm = $('#deleteForm');
    //         deleteForm.attr('action', '/submit-role-delete/' + roleId);
    //         deleteForm.submit();
    //     });
    // });
});