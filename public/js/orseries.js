/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    let orBatchId = $('#orbatchid').val();

    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/get-orseries?orbatchid=" + orBatchId,
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
            }
        },
        columns: [
            { data: 'Id', name: 'Id' },
            { data: 'ORNumber', name: 'ORNumber'},
            { 
                data: function(row) {
                    return row.Client ? row.Client.LastName + ', ' + row.Client.FirstName + ' ' + row.Client.MiddleName : '<span class="text-secondary">Not available</span>';
                }, 
                name: 'Client.LastName' 
            },
            {
                data: function(row){
                    if(row.Status == 1){
                        return '<span class="status-badge status-available">' +
                                '<svg class="status-icon" fill="currentColor" viewBox="0 0 20 20">' +
                                '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>' +
                                '</svg>' +
                                'Available' +
                                '</span>';
                    }
                    else{
                        return '<span class="status-badge status-voided">' +
                                '<svg class="status-icon" fill="currentColor" viewBox="0 0 20 20">' +
                                '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>' +
                                '</svg>' +
                                'Voided' +
                                '</span>';
                    }
                }
            },
            { 
                data: function(row){
                    if(row.Remarks == 'N/A'){
                        return '<span class="text-secondary">Not available</span>';
                    }
                    else{
                        return row.Remarks;
                    }
                }
            },
            {
                data: function(row){
                    if(row.Status == 1){
                        return '<button class="action-btn action-btn-disabled" disabled>' +
                                '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>' +
                                '</svg>' +
                                'Voided' +
                                '</button>';
                    } else {
                        $('#orseriesToVoid').val(row.ORNumber);

                        return '<a data-bs-toggle="modal" data-bs-target="#orseriesVoidModal" data-orseries-id="' + row.Id + '" data-orseries-orno="' + row.ORNumber + '" role="button" class="action-btn action-btn-void">' +
                                '<svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>' +
                                '</svg>' +
                                'Void' +
                                '</a>';
                    }
                }
            }
        ],
        columnDefs: [
            {
                targets: [0],
                visible: false
            },
            {
                targets: [3,4,5],
                orderable: false,
            },
        ],
        order: [[0, 'desc']]
    });
    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#orseriesVoidModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let orSeriesId = button.data('orseries-id');
        let orNumber= button.data('orseries-orno');

        let modal = $(this);
        modal.find('#orseriesToVoid').text(orNumber);
        modal.find('#confirmVoid').click(function() {
            
            let voidForm = $('#voidForm');
            voidForm.attr('action', '/submit-orseries-void/' + orSeriesId);
            voidForm.submit();
        });
    });
});