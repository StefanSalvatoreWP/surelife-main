/* 2024 SilverDust) S. Maceren */

$(document).ready(function(){

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax : {
            url: "/expenses",
            data: function(d){
                d.searchValue = $('#common_dataTable_filter input').val();
                d.branch = $('#branchFilter').val();
            }
        },
        columns: [
            {data: 'exid', name: 'Id'},
            {data: 'branchname', name: 'tblbranch.BranchName'},
            {data: 'description', name: 'tblexpensesdescription.Description'},
            {
                data: 'amount', 
                name: 'Amount',
                render: function(data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var imageLink;
                    if(data.image != null && data.image != "" && data.image != 'Not available'){
                        imageLink = '<a href="uploads/expenses/' + data.image + '" target="_blank" class="action-btn action-btn-image">' +
                            '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>' +
                            '</svg>' +
                            'Image' +
                            '</a>';
                    } else {
                        imageLink = '<span class="action-btn action-btn-image disabled">' +
                            '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>' +
                            '</svg>' +
                            'Image' +
                            '</span>';
                    }
                    
                    var noteLink;
                    if(data.note != null && data.note != ""){
                        noteLink = '<a href="#" data-expense-note="' + data.note + '" class="action-btn action-btn-note">' +
                            '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>' +
                            '</svg>' +
                            'Note' +
                            '</a>';
                    } else {
                        noteLink = '<span class="action-btn action-btn-note disabled">' +
                            '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>' +
                            '</svg>' +
                            'Note' +
                            '</span>';
                    }
                  
                    var updateLink = '<a href="/expense-update/' + data.exid + '" class="action-btn action-btn-update">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>' +
                        '</svg>' +
                        'Update' +
                        '</a>';
                    
                    var deleteLink = '<a href="#" data-expense-id="' + data.exid + '" data-expense-description="' + data.description + '" class="action-btn action-btn-delete">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                        '</svg>' +
                        'Delete' +
                        '</a>';
                    
                    return '<div style="display: flex; gap: 0.375rem; align-items: center; flex-wrap: wrap;">' + imageLink + noteLink + updateLink + deleteLink + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false,
            },
            {
                targets: [4],
                orderable: false,
            },
        ],
    });
    
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    // Branch filter change event
    $('#branchFilter').on('change', function() {
        loadedTable.ajax.reload();
    });

    /* MODALS - Use event delegation */
    $(document).on('click', '.action-btn-note', function(e) {
        e.preventDefault();
        let expenseNote = $(this).data('expense-note');
        $('#expenseNote').text(expenseNote);
        
        let modal = $('#expenseNoteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });

    $(document).on('click', '.action-btn-delete', function(e) {
        e.preventDefault();
        
        let expenseId = $(this).data('expense-id');
        let description = $(this).data('expense-description');
        
        $('#expenseToDelete').text(description);
        $('#confirmDelete').off('click');
        
        $('#confirmDelete').on('click', function() {
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-expense-delete/' + expenseId);
            deleteForm.submit();
        });
        
        let modal = $('#expenseDeleteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        $('#expenseNoteModal, #expenseDeleteModal').removeClass('show').css('display', 'none');
    });
});