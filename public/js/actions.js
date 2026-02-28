/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/action",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'action', name: 'Action' },
            { data: 'rolelevel', name: 'rolelevel' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <a href="/action-update/${data.id}" 
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition duration-200 ease-in-out">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Update
                            </a>
                            <a onclick="showDeleteActionModal(${data.id}, '${data.action}')" 
                               role="button"
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-red-50 to-red-100 hover:from-red-100 hover:to-red-200 text-red-900 text-xs font-semibold rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition duration-200 ease-in-out cursor-pointer">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </a>
                        </div>
                    `;
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
        order: [[2, 'asc']]
    });
    
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* Swift-Style Modal Functions */
    function showDeleteActionModal(actionId, actionName) {
        showSwiftModal('Confirm Deletion', `Delete selected action "${actionName}"?\n\nThis action cannot be undone. The action privilege will be permanently removed from the system.`, 'warning', [
            {text: 'Delete Action', class: 'bg-red-500 hover:bg-red-600 text-white', action: `submitDeleteAction(${actionId})`},
            {text: 'Cancel', class: 'bg-gray-200 hover:bg-gray-300 text-gray-800'}
        ]);
    }

    function submitDeleteAction(actionId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/submit-action-delete/' + actionId;
        
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
});