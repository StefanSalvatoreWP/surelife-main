/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/menu",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'menuitem', name: 'MenuItem' },
            { data: 'rolelevel', name: 'rolelevel' },
            {
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="flex items-center gap-2">
                            <a href="/menu-update/${data.id}" 
                               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm hover:shadow-md transform hover:scale-105 transition duration-200 ease-in-out">
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Update
                            </a>
                            <a data-bs-toggle="modal" 
                               data-bs-target="#menuDeleteModal" 
                               data-menu-id="${data.id}" 
                               data-menu-name="${data.menuitem}" 
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

    /* MODALS */
    $('#menuDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let menuId = button.data('menu-id');
        let menuName = button.data('menu-name');
        
        let modal = $(this);
        modal.find('#menuToDelete').text(menuName);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-menu-delete/' + menuId);
            deleteForm.submit();
        });
    });
});