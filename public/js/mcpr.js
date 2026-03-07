/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/mcpr",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'mcprid', name: 'Id' },
            { data: 'Year', name: 'Year' },
            { data: 'Month', name: 'tblmonth.Month' },
            { data: 'StartingDate', name: 'StartingDate' },
            { data: 'EndingDate', name: 'EndingDate' },
            {
                data: null,
                render: function (data, type, row) {
                    var updateIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
                    var deleteIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    var menuIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>';
                    
                    var updateLink = '<a href="/mcpr-update/' + data.Id + '" class="dropdown-item">' + updateIcon + ' Update</a>';
                    var deleteLink = '<a data-bs-toggle="modal" data-bs-target="#mcprDeleteModal" data-mcpr-id="' + data.Id + '" data-mcpr-name="' + data.Month + ' ' + data.StartingDate + ' to ' + data.EndingDate + '" role="button" class="dropdown-item dropdown-item-danger">' + deleteIcon + ' Delete</a>';
                    
                    return '<div class="action-dropdown">' +
                        '<button class="action-dropdown-btn" onclick="toggleDropdown(this)" aria-label="Actions" title="More actions">' + menuIcon + '</button>' +
                        '<div class="action-dropdown-menu" role="menu">' +
                            updateLink + deleteLink +
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
                targets: [5],
                orderable: false
            }
        ],
        order: [[1, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* MODALS */
    $('#mcprDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let mcprId = button.data('mcpr-id');
        let mcprName = button.data('mcpr-name');
        
        let modal = $(this);
        modal.find('#mcprToDelete').text(mcprName);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-mcpr-delete/' + mcprId);
            deleteForm.submit();
        });
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