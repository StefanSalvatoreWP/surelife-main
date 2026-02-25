/* 2024 SilverDust) S. Maceren */

$(document).ready(function() {
    console.log('FSA Rankings Collections JS loaded');
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables available:', typeof $.fn.DataTable !== 'undefined');
    console.log('Table element exists:', $('#common_dataTable').length > 0);
                
    var loadedTable = $('#common_dataTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, "All"]],
        
        ajax: {
            url: "/fsarankings-collections",
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
                data: 'TotalAmountPaid', 
                name: 'TotalAmountPaid',
                searchable: false,
                render: function(data, type, row, meta) {
                    var rank = meta.row + meta.settings._iDisplayStart + 1;
                    var amount = parseFloat(data).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                    var badge = '';
                    
                    if (rank === 1) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-green-100 text-green-800 border-2 border-green-400">' + amount + '</span>';
                    } else if (rank === 2) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-blue-100 text-blue-800 border-2 border-blue-400">' + amount + '</span>';
                    } else if (rank === 3) {
                        badge = '<span class="inline-flex items-center px-4 py-2 rounded-lg text-lg font-bold bg-purple-100 text-purple-800 border-2 border-purple-400">' + amount + '</span>';
                    } else {
                        badge = '<span class="inline-flex items-center px-3 py-1.5 rounded-md text-base font-semibold bg-gray-50 text-gray-700">' + amount + '</span>';
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
            emptyTable: '<div class="text-center py-8"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><p class="mt-2 text-gray-500">No collections data available for this period</p></div>',
            zeroRecords: '<div class="text-center py-8"><svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg><p class="mt-2 text-gray-500">No matching records found</p></div>',
            info: "Showing _START_ to _END_ of _TOTAL_ FSA rankings",
            infoEmpty: "No rankings to display",
            infoFiltered: "(filtered from _MAX_ total rankings)"
        }
    });

    $('#common_dataTable_filter input').on('keyup', function() {
        loadedTable.search(this.value).draw();
    });

    $('#searchMcpr').click(function(){

        let sel_mcprId = $('#mcprList').val();
        loadedTable.ajax.url('/fsarankings-collections?mcprid=' + sel_mcprId).load();
    });
});