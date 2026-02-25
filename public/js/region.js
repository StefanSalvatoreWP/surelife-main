/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/region",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'Id' },
            { data: 'RegionName', name: 'RegionName' },
            {
                data: null,
                render: function (data, type, row) {
                    var updateLink = '<a href="/region-update/' + data.Id + '" class="action-btn action-btn-update">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>' +
                        '</svg>' +
                        'Update' +
                        '</a>';
                    
                    var deleteLink = '<a href="#" data-region-id="' + data.Id + '" data-region-name="' + data.RegionName + '" class="action-btn action-btn-delete">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>' +
                        '</svg>' +
                        'Delete' +
                        '</a>';
                    
                    return '<div style="display: flex; gap: 0.5rem;">' + updateLink + deleteLink + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [2],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* MODALS - Use event delegation */
    $(document).on('click', '.action-btn-delete', function(e) {
        e.preventDefault();
        
        let regionId = $(this).data('region-id');
        let regionName = $(this).data('region-name');
        
        $('#regionToDelete').text(regionName);
        $('#confirmDelete').off('click');
        
        $('#confirmDelete').on('click', function() {
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-region-delete/' + regionId);
            deleteForm.submit();
        });
        
        let modal = $('#regionDeleteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        $('#regionDeleteModal').removeClass('show').css('display', 'none');
    });
});