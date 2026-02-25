/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],

        ajax: {
            url: "/contractbatch",
            data: function(d) {
                d.branch_id = $('#branchFilter').val();
            }
        },
        columns: [
            { data: 'contractbatchid', name: 'Id' },
            { data: 'BatchCode', name: 'BatchCode' },
            { data: 'RegionName', name: 'tblregion.RegionName' },
            { data: 'BranchName', name: 'tblbranch.BranchName' },
            { data: 'countAvailContract', name: 'countAvailContract' },
            { 
                data: null,
                render: function(data, type, row) {
                    if (data === null) {
                        return '<span class="text-secondary">Not available</span>';
                    } 
                    else {
                        if (data.Assigned === null || data.Assigned.trim() === "") {
                            return '<span class="text-secondary">Not available</span>';
                        } else {
                            return '<span class=text-dark>' + data.Assigned + '</span>';
                        }
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var viewSeriesLink = '<a href="/contractbatch-viewseries/' + data.Id + '" class="action-btn action-btn-view">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>' +
                        '</svg>' +
                        'View Series' +
                        '</a>';
                    
                    var assignLink = '<a href="/contractbatch-assign/' + data.Id + '" class="action-btn action-btn-assign">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>' +
                        '</svg>' +
                        'Assign' +
                        '</a>';
                    
                    var deleteLink = '<a href="#" data-contractbatch-id="' + data.Id + '" data-contractbatch-code="' + data.BatchCode + '" class="action-btn action-btn-delete">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                        '</svg>' +
                        'Delete' +
                        '</a>';
                    
                    return '<div style="display: flex; gap: 0.375rem; align-items: center;">' + viewSeriesLink + assignLink + deleteLink + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [4, 5],
                searchable: false
            },
            {
                targets: [6],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#branchFilter').on('change', function() {
        loadedTable.ajax.reload();
    });

    /* MODALS - Use event delegation */
    $(document).on('click', '.action-btn-delete', function(e) {
        e.preventDefault();
        
        let contractBatchId = $(this).data('contractbatch-id');
        let contractBatchCode = $(this).data('contractbatch-code');
        
        $('#contractbatchToDelete').text(contractBatchCode);
        $('#confirmDelete').off('click');
        
        $('#confirmDelete').on('click', function() {
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-contractbatch-delete/' + contractBatchId);
            deleteForm.submit();
        });
        
        let modal = $('#contractbatchDeleteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        $('#contractbatchDeleteModal').removeClass('show').css('display', 'none');
    });
});