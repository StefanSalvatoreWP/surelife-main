/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/req-loans",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'Id' },
            { data: 'ContractNumber', name: 'tblclient.ContractNumber' },
            { data: 'LastName', name: 'tblclient.LastName' },
            { data: 'FirstName', name: 'tblclient.FirstName' },
            { data: 'MiddleName', name: 'tblclient.MiddleName' },
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
            { data: 'DateRequested', name: 'DateRequested' },
            { 
                data: 'Status', 
                name: 'Status',
                render: function (data, type, row) {
                    
                    var status = row.Status;

                    if (status == "Pending") {
                        return '<span class="text-secondary">' + status  + '</span>';
                    } 
                    else if (status == "Verified") {
                        return '<span class="text-primary">' + status  + '</span>';
                    } 
                    else if (status == "Approved") {
                        return '<span class="text-success">' + status  + '</span>';
                    } 
                    else {
                        return '<span class="text-warning">' + status  + '</span>';
                    }
                },
            },
            {
                data: null,
                render: function (data, type, row) {

                    var viewLink = '<a href="/req-loans/view/' + data.Id + '"><span class="badge bg-primary">View</span></a>';
                    var deleteLink = '<a data-bs-toggle="modal" data-bs-target="#loanRequestDeleteModal" data-req-loan-id="' + data.Id + '"role="button"><span class="badge bg-danger">Delete</span></a>';
                    
                    var remarksLink = '<span class="badge bg-secondary">Remarks</span>';
                    if(data.Remarks != null && data.Remarks != ""){
                        remarksLink = '<a data-bs-toggle="modal" data-bs-target="#loanRequestRemarksModal" data-loanrequest-remarks="' + data.Remarks + '" role="button"><span class="badge bg-warning">Remarks</span></a>';
                    }

                    return viewLink + ' ' + remarksLink + ' ' + deleteLink;
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

    // /* MODALS */
    $('#loanRequestRemarksModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let loanRequestRemarks = button.data('loanrequest-remarks');
    
        let modal = $(this);
        modal.find('#loanRequestRemarks').text(loanRequestRemarks);
    });

    $('#loanRequestDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let loanRequestId = button.data('req-loan-id');
   
        let modal = $(this);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-req-loan-delete/' + loanRequestId);
            deleteForm.submit();
        });
    });
});