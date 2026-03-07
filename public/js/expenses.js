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
                    var imageIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>';
                    var noteIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>';
                    var updateIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
                    var deleteIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
                    var menuIcon = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>';
                    
                    var imageLink;
                    if(data.image != null && data.image != "" && data.image != 'Not available'){
                        imageLink = '<a href="uploads/expenses/' + data.image + '" target="_blank" class="dropdown-item">' + imageIcon + ' Image</a>';
                    } else {
                        imageLink = '<span class="dropdown-item disabled">' + imageIcon + ' Image</span>';
                    }
                    
                    var noteLink;
                    if(data.note != null && data.note != ""){
                        noteLink = '<a onclick="showExpenseNoteModal(\'' + data.note.replace(/'/g, "\\'") + '\')" role="button" class="dropdown-item">' + noteIcon + ' Note</a>';
                    } else {
                        noteLink = '<span class="dropdown-item disabled">' + noteIcon + ' Note</span>';
                    }
                    
                    var updateLink = '<a href="/expense-update/' + data.exid + '" class="dropdown-item">' + updateIcon + ' Update</a>';
                    var deleteLink = '<a onclick="showDeleteExpenseModal(' + data.exid + ', \'' + (data.description || 'N/A').replace(/'/g, "\\'") + '\')" role="button" class="dropdown-item dropdown-item-danger">' + deleteIcon + ' Delete</a>';
                    
                    return '<div class="action-dropdown">' +
                        '<button class="action-dropdown-btn" onclick="toggleDropdown(this)" aria-label="Actions" title="More actions">' + menuIcon + '</button>' +
                        '<div class="action-dropdown-menu" role="menu">' +
                            imageLink + noteLink + updateLink + deleteLink +
                        '</div>' +
                    '</div>';
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

});

/* Swift-Style Modal Functions */
function showExpenseNoteModal(note) {
    showSwiftModal('Expense Note', note, 'info', [
        {text: 'Close', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}
    ]);
}

function showDeleteExpenseModal(expenseId, description) {
    showSwiftModal('Confirm Deletion', `Delete selected expense "${description}"?\n\nThis action cannot be undone. The expense record will be permanently removed from the system.`, 'warning', [
        {text: 'Delete Expense', class: 'bg-red-500 hover:bg-red-600 text-white', action: `submitDeleteExpense(${expenseId})`},
        {text: 'Cancel', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}
    ]);
}

function submitDeleteExpense(expenseId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/submit-expense-delete/' + expenseId;
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const method = document.createElement('input');
    method.type = 'hidden';
    method.name = '_method';
    method.value = 'DELETE';
    
    form.appendChild(csrfToken);
    form.appendChild(method);
    document.body.appendChild(form);
    form.submit();
}

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