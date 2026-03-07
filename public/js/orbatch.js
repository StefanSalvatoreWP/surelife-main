/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/orbatch",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
                d.branch = $('#branchFilter').val();
            }
        },
        columns: [
            
            { data: 'orbatchid', name: 'Id' },
            { data: 'SeriesCode', name: 'SeriesCode' },
            { data: 'BatchCode', name: 'BatchCode' },
            { data: 'RegionName', name: 'tblregion.RegionName' },
            { data: 'BranchName', name: 'tblbranch.BranchName' },
            { data: 'countAvailOR', name: 'countAvailOR' },
            { 
                data: 'Assigned',
                name: 'Assigned',
                render: function(data, type, row) {
                    if (data === null || data === undefined || data.trim() === "") {
                        return '<span class="text-secondary">Not available</span>';
                    } else {
                        return '<span class="text-dark">' + data + '</span>';
                    }
                }
            },
            { data: 'Type', name: 'Type' },
            {
                data: null,
                render: function (data, type, row) {
                    var viewSeriesIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
                    var assignIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>';
                    var deleteIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    var menuIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>';
                    
                    var viewSeriesLink = '<a href="/orbatch-viewseries/' + data.Id + '" class="dropdown-item">' + viewSeriesIcon + ' View Series</a>';
                    var assignLink = '<a href="/orbatch-assign/' + data.Id + '" class="dropdown-item">' + assignIcon + ' Assign</a>';
                    var deleteLink = '<a href="#" data-orbatch-id="' + data.Id + '" data-orbatch-code="' + data.BatchCode + '" data-orseries-code="' + data.SeriesCode + '" class="dropdown-item dropdown-item-danger">' + deleteIcon + ' Delete</a>';
                    
                    return '<div class="action-dropdown">' +
                        '<button class="action-dropdown-btn" onclick="toggleDropdown(this)" aria-label="Actions" title="More actions">' + menuIcon + '</button>' +
                        '<div class="action-dropdown-menu" role="menu">' +
                            viewSeriesLink + assignLink + deleteLink +
                        '</div>' +
                    '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [5, 6, 7],
                searchable: false
            },
            {
                targets: [8],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    // Branch filter change event
    $('#branchFilter').on('change', function() {
        loadedTable.draw();
    });

    /* MODALS - Use event delegation */
    $(document).on('click', '.dropdown-item-danger', function(e) {
        e.preventDefault();
        
        let orBatchId = $(this).data('orbatch-id');
        let orBatchCode = $(this).data('orbatch-code');
        let orSeriesCode = $(this).data('orseries-code');
        
        $('#orbatchToDelete').text(orSeriesCode + " [" + orBatchCode + "]");
        $('#confirmDelete').off('click');
        
        $('#confirmDelete').on('click', function() {
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-orbatch-delete/' + orBatchId);
            deleteForm.submit();
        });
        
        let modal = $('#orbatchDeleteModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        $('#orbatchDeleteModal').removeClass('show').css('display', 'none');
    });
});

/* Dropdown Toggle Function */
function toggleDropdown(btn) {
    const dropdown = btn.closest('.action-dropdown');
    const menu = dropdown.querySelector('.action-dropdown-menu');
    const isOpen = menu.classList.contains('show');
    
    // Close all other dropdowns
    document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
        m.classList.remove('show');
    });
    document.querySelectorAll('.action-dropdown.open').forEach(d => {
        d.classList.remove('open');
    });
    document.body.classList.remove('dropdown-open');
    
    // Toggle current dropdown
    if (!isOpen) {
        menu.classList.add('show');
        dropdown.classList.add('open');
        document.body.classList.add('dropdown-open');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.action-dropdown')) {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => {
            m.classList.remove('show');
        });
        document.querySelectorAll('.action-dropdown.open').forEach(d => {
            d.classList.remove('open');
        });
        document.body.classList.remove('dropdown-open');
    }
});