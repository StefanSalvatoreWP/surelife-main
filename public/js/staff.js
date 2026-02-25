/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/staff",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'staffid', name: 'Id' },
            {
                data: null,
                name: 'LastName',
                render: function(data, type, row) {
                    return data.LastName + ' ' + data.FirstName + ' ' + data.MiddleName;
                }
            },
            { data: 'Role', name: 'tblrole.Role' },
            { data: 'RegionName', name: 'tblregion.RegionName' },
            { data: 'BranchName', name: 'tblbranch.BranchName' },
            {
                data: null,
                render: function (data, type, row) {
                    var viewLink = '<a href="/staff-view/' + data.Id + '" class="action-btn action-btn-view">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>' +
                        '</svg>' +
                        'View' +
                        '</a>';
                    var deleteLink = '<a href="#" data-staff-id="' + data.Id + '" data-staff-name="' + data.LastName + ', ' + data.FirstName + ' ' + data.MiddleName + '" class="action-btn action-btn-delete">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                        '</svg>' +
                        'Delete' +
                        '</a>';
                    return '<div style="display: flex; gap: 0.5rem;">' + viewLink + deleteLink + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [5],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });
    
    /* MODALS - Use event delegation for dynamically loaded content */
    $(document).on('click', '.action-btn-delete', function(e) {
        e.preventDefault();
        
        let staffId = $(this).data('staff-id');
        let staffName = $(this).data('staff-name');
        
        // Update modal content
        $('#staffToDelete').text(staffName);
        
        // Remove any previous click handlers to prevent multiple submissions
        $('#confirmDelete').off('click');
        
        // Add new click handler
        $('#confirmDelete').on('click', function() {
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-staff-delete/' + staffId);
            deleteForm.submit();
        });
        
        // Show the modal - try Bootstrap 5 first, fallback to jQuery
        let modal = $('#staffDeleteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], {
                backdrop: false // Disable backdrop
            });
            bsModal.show();
        } else {
            // Fallback: show modal with jQuery (no backdrop)
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    // Handle modal close
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        let modal = $('#staffDeleteModal');
        modal.removeClass('show').css('display', 'none');
    });
});