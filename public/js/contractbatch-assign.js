/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {

    let contractbatchId = $('#contractbatchid').val();
    
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/get-staff-assigncontract?contractbatchid=" + contractbatchId,
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
                    var assignLink = '<a href="#" data-staff-id="' + data.Id + '" data-staff-name="' + data.LastName + ', ' + data.FirstName + ' ' + data.MiddleName + '" class="action-btn action-btn-assign">' +
                        '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>' +
                        '</svg>' +
                        'Assign' +
                        '</a>';
                    return assignLink;
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
    
    /* MODALS - Use event delegation */
    $(document).on('click', '.action-btn-assign', function(e) {
        e.preventDefault();
        
        let staffId = $(this).data('staff-id');
        let staffName = $(this).data('staff-name');
        
        $('#staffToAssign').text(staffName);
        $('#confirmAssign').off('click');
        
        $('#confirmAssign').on('click', function() {
            let assignForm = $('#assignForm');
            assignForm.attr('action', '/submit-contractbatch-assign?staffid=' + staffId + "&contractbatchid=" + contractbatchId);
            assignForm.submit();
        });
        
        let modal = $('#staffAssignModal');
        if (typeof bootstrap !== 'undefined') {
            let bsModal = new bootstrap.Modal(modal[0], { backdrop: false });
            bsModal.show();
        } else {
            modal.addClass('show').css('display', 'flex');
        }
    });
    
    $(document).on('click', '[data-bs-dismiss="modal"]', function() {
        $('#staffAssignModal').removeClass('show').css('display', 'none');
    });
});