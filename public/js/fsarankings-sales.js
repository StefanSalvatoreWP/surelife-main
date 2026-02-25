/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
    console.log('FSA Rankings Sales JS loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    console.log('Table element exists:', $('#common_dataTable').length > 0);
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/fsarankings-sales",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                d.search.value = $('#common_dataTable_filter input').val();
                console.log('Sending AJAX request with data:', d);
            },
            dataSrc: function(json) {
                console.log('Received data:', json);
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX Error:', error, thrown);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                console.error('Full XHR:', xhr);
                alert('Error loading data: ' + error + '\nStatus: ' + xhr.status + '\nCheck console for details.');
            }
        },
        columns: [
            {
                data: null,
                name: 'rank',
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    var rank = meta.row + meta.settings._iDisplayStart + 1;
                    var badge = '';
                    
                    if (rank === 1) {
                        badge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-400 text-yellow-900">🥇 #' + rank + '</span>';
                    } else if (rank === 2) {
                        badge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-300 text-gray-900">🥈 #' + rank + '</span>';
                    } else if (rank === 3) {
                        badge = '<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-400 text-orange-900">🥉 #' + rank + '</span>';
                    } else {
                        badge = '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-700">#' + rank + '</span>';
                    }
                    
                    return badge;
                }
            },
            { 
                data: 'LastName', 
                name: 'tblstaff.LastName',
                render: function (data, type, row) {
                    if(!data || data == null){
                        return '<span class="text-gray-400 italic">Not available</span>';
                    }
                    return '<span class="font-semibold text-gray-900">' + data + '</span>';
                },
            },
            { 
                data: 'FirstName', 
                name: 'tblstaff.FirstName',
                render: function (data, type, row) {
                    if(!data || data == null){
                        return '<span class="text-gray-400 italic">Not available</span>';
                    }
                    return '<span class="text-gray-700">' + data + '</span>';
                },
            },
            { 
                data: 'MiddleName', 
                name: 'tblstaff.MiddleName',
                render: function (data, type, row) {
                    if(!data || data == null || data === '--' || data === '-'){
                        return '<span class="text-gray-400 text-sm">—</span>';
                    }
                    return '<span class="text-gray-600">' + data + '</span>';
                },
            },
            { 
                data: 'Sales', 
                name: 'Sales',
                searchable: false,
                render: function (data, type, row, meta) {
                    var rank = meta.row + meta.settings._iDisplayStart + 1;
                    var salesNum = parseInt(data);
                    var badge = '';
                    
                    if (rank === 1) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-green-100 text-green-800 border-2 border-green-400">' + salesNum.toLocaleString() + ' sales</span>';
                    } else if (rank === 2) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-blue-100 text-blue-800 border-2 border-blue-400">' + salesNum.toLocaleString() + ' sales</span>';
                    } else if (rank === 3) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-purple-100 text-purple-800 border-2 border-purple-400">' + salesNum.toLocaleString() + ' sales</span>';
                    } else {
                        badge = '<span class="inline-flex items-center px-3 py-1.5 rounded-md text-base font-semibold bg-gray-50 text-gray-700">' + salesNum.toLocaleString() + '</span>';
                    }
                    
                    return badge;
                }
            },
        ],
        order: [[4, 'desc']],
        rowCallback: function(row, data, index) {
            var rank = index + 1;
            
            // Add rank class for CSS styling
            $(row).addClass('rank-' + rank);
        },
        language: {
            processing: '<div class="flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div><span class="ml-2">Loading rankings...</span></div>',
            emptyTable: '<div class="text-center py-8"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg><p class="mt-2 text-gray-500">No sales data available for this period</p></div>',
            zeroRecords: '<div class="text-center py-8"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg><p class="mt-2 text-gray-500">No matching records found</p></div>',
            info: "Showing _START_ to _END_ of _TOTAL_ FSA rankings",
            infoEmpty: "No rankings to display",
            infoFiltered: "(filtered from _MAX_ total rankings)"
        }
    });
    
    console.log('DataTable initialized:', loadedTable);

    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#searchMcpr').click(function(){

        let sel_mcprId = $('#mcprList').val();
        loadedTable.ajax.url('/fsarankings-sales?mcprid=' + sel_mcprId).load();
    });
});