/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/package",
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'Id' },
            { data: 'Package', name: 'Package' },
            {
                 data: 'Price', 
                 name: 'Price',
                 render: function(data, type, row) {
                    return parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                }
            },
            {
                data: 'Active',
                name: 'Active',
                render: function (data, type, row) {
                    if (data == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-secondary">Inactive</span>';
                    }
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    var viewBtn = '<a href="/package-view/' + data.Id + '" style="display: inline-block; padding: 6px 12px; background-color: #3b82f6; color: white; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none;">View</a>';
                    var updateBtn = '<a href="/package-update/' + data.Id + '" style="display: inline-block; padding: 6px 12px; background-color: #f59e0b; color: white; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none;">Update</a>';
                    var deleteBtn = '<a data-bs-toggle="modal" data-bs-target="#packageDeleteModal" data-package-id="' + data.Id + '" data-package-name="' + data.Package + '" style="display: inline-block; padding: 6px 12px; background-color: #fef2f2; color: #7f1d1d; font-size: 12px; font-weight: 500; border-radius: 6px; text-decoration: none; cursor: pointer; border: 1px solid #fecaca;">Delete</a>';
                    return '<div style="display: flex; gap: 8px; white-space: nowrap;">' + viewBtn + updateBtn + deleteBtn + '</div>';
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [4],
                orderable: false
            }
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    /* MODALS */
    $('#packageDeleteModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let packageId = button.data('package-id');
        let packageName = button.data('package-name');
        
        let modal = $(this);
        modal.find('#packageToDelete').text(packageName);
        modal.find('#confirmDelete').click(function() {
            
            let deleteForm = $('#deleteForm');
            deleteForm.attr('action', '/submit-package-delete/' + packageId);
            deleteForm.submit();
        });
    });
});