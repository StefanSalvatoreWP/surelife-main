/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
   
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],

        ajax: {
            url: "/deposit",
            data: function(d) {
                d.bank_id = $('#bankFilter').val();
            }
        },
        columns: [
            { data: 'depoid', name: 'Id' },
            { 
                data: 'SequenceNo', 
                name: 'SequenceNo',
                render: function(data, type, row){
                    if(data === null || data === ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return data;
                    }
                } 
            },
            { 
                data: 'BankName',
                name: 'tblbank.BankName',
                render: function(data, type, row){
                    if(data === null || data === ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return data;
                    }
                }
            },
            { 
                data: 'DepositedAmount', 
                name: 'DepositedAmount',
                render: function(data, type, row){
                    if(data === null || data === ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return parseFloat(data).toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'PHP'
                        });
                    }
                }
            },
            { 
                data: 'Date', 
                name: 'Date',
                render: function(data, type, row){
                    if(data === null || data === ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return data;
                    }
                }
            },
            { 
                data: 'BranchName', 
                name: 'tblbranch.BranchName',
                render: function(data, type, row){
                    if(data === null || data === ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return data;
                    }
                }
            },
            { 
                data: null,
                render: function(data, type, row){
                    if(data.LastName == null || data.LastName == ""){
                        return "<span class='text-secondary'>Not available</span>";
                    }
                    else{
                        return data.LastName + ', ' + data.FirstName + ' ' + data.MiddleName;
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var imageIcon = '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                    var noteIcon = '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
                    var updateIcon = '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
                    var deleteIcon = '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';

                    var viewLink = '<span class="action-btn action-btn-image disabled">' + imageIcon + 'Image</span>';
                    if(data.DepositSlip != null && data.DepositSlip != "" && data.DepositSlip != 'Not available'){
                        viewLink = '<a href="uploads/deposits/' + data.DepositSlip + '" target="_blank" class="action-btn action-btn-image">' + imageIcon + 'Image</a>';
                    }
                    
                    var noteLink = '<span class="action-btn action-btn-note disabled">' + noteIcon + 'Note</span>';
                    if(data.Note != null && data.Note != ""){
                        noteLink = '<a data-bs-toggle="modal" data-bs-target="#depositNoteModal" data-deposit-note="' + data.Note + '" role="button" class="action-btn action-btn-note">' + noteIcon + 'Note</a>';
                    }
                  
                    var updateLink = '<a href="/deposit-update/' + data.Id + '" class="action-btn action-btn-update">' + updateIcon + 'Update</a>';
                    var deleteLink = '<a data-bs-toggle="modal" data-bs-target="#depositDeleteModal" data-deposit-id="' + data.Id + '" data-deposit-sequenceno="' + data.SequenceNo + '" role="button" class="action-btn action-btn-delete">' + deleteIcon + 'Delete</a>';
                    
                    return '<div class="action-buttons-container">' + viewLink + noteLink + updateLink + deleteLink + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [6, 7],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#bankFilter').on('change', function() {
        loadedTable.ajax.reload();
    });

    /* MODALS */
    $('#depositNoteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let depositNote = button.data('deposit-note');
    
        let modal = $(this);
        modal.find('#depositNote').text(depositNote);
    });

    $('#depositDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let depositId = button.data('deposit-id');
        let depositSequenceNo = button.data('deposit-sequenceno');
        
        let modal = $(this);
        modal.find('#depositToDelete').text(depositSequenceNo);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-deposit-delete/' + depositId);
            deleteForm.submit();
        });
    });
});