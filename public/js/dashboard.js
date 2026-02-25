/* 2024 SilverDust) S. Maceren */

$(document).ready(function(){

    let monthValues = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    let barColors = ["#FF5733","#FFBD33","#33FF57","#3360FF","#FF33EB","#FF5733","#33FFC1","#FF3391","#C133FF","#33FFA3","#FF3333","#333EFF"];
      
    let year = document.getElementById('dashboard-year').value;

    // Fetch sales of the day
    console.log('=== SALES OF THE DAY DEBUG ===');
    console.log('Fetching sales data from: /get-sales-today');
    
    $.ajax({
        url: '/get-sales-today',
        method: 'GET',
        dataType: 'json',
        cache: false,
        success: function(data) {
            console.log('Sales of the Day - Full Response:', data);
            console.log('Sales Count:', data.salesToday);
            console.log('Today\'s Date:', data.today);
            console.log('=== TODAY\'S CLIENTS ===');
            console.table(data.todayClientsDetails);
            console.log('=== RECENT CLIENTS (Last 10) ===');
            console.table(data.recentClients);
            
            $('#salesTodayCount').html(data.salesToday);
        },
        error: function(xhr, status, error) {
            console.error('Sales of the Day - ERROR:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            $('#salesTodayCount').html('0');
        }
    });

    // Fetch collections of the day
    console.log('=== COLLECTIONS OF THE DAY DEBUG ===');
    console.log('Fetching collections data from: /get-collections-today');
    
    $.ajax({
        url: '/get-collections-today',
        method: 'GET',
        dataType: 'json',
        cache: false,
        success: function(data) {
            console.log('Collections of the Day - Full Response:', data);
            console.log('Collections Amount:', data.collectionsToday);
            console.log('Today\'s Date:', data.today);
            console.log('=== TODAY\'S PAYMENTS ===');
            console.table(data.todayPaymentsDetails);
            console.log('=== RECENT PAYMENTS (Last 10) ===');
            console.table(data.recentPayments);
            
            // Format the amount as PHP currency
            let formattedAmount = parseFloat(data.collectionsToday).toLocaleString('en-PH', {
                style: 'currency',
                currency: 'PHP'
            });
            
            $('#collectionsTodayAmount').html(formattedAmount);
        },
        error: function(xhr, status, error) {
            console.error('Collections of the Day - ERROR:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            $('#collectionsTodayAmount').html('₱0.00');
        }
    });
    
    // collections
    let collectionValues = Array.from({ length: 12 }, () => 0);
    // sales
    let salesValues = Array.from({ length: 12 }, () => 0);

    $.ajax({
        url: '/get-collections-dashboard',
        method: 'GET',
        data: { currentYear: year },
        dataType: 'json',
        cache: false,
        success: function(collectionsData) {
            Object.keys(collectionsData).forEach(function(monthId) {
                let totalPayment = collectionsData[monthId];
                let index = parseInt(monthId) - 1;
                if (index >= 0 && index < collectionValues.length) {
                    collectionValues[index] = parseFloat(totalPayment);
                }
            });

            createChartCollections();
        },
        error: function(xhr, status, error) {}
    });

    $.ajax({
        url: '/get-newsales-dashboard',
        method: 'GET',
        data: { currentYear: year },
        dataType: 'json',
        cache: false,
        success: function(salesData) {
            Object.keys(salesData).forEach(function(monthId) {
                let totalNewSales = salesData[monthId];
                let index = parseInt(monthId) - 1;
                if (index >= 0 && index < salesValues.length) {
                    salesValues[index] = parseFloat(totalNewSales);
                }
            });

            createChartNewSales();
        },
        error: function(xhr, status, error) {}
    });

    function createChartCollections() {
        new Chart("collectionsChart", {
            type: "bar",
            data: {
                labels: monthValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: collectionValues
                }]
            },
            options: {
                legend: { display: false },
                title: {
                    display: true,
                    text: "Collections " + year
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                return value.toLocaleString('en-US', {
                                    style: 'currency',
                                    currency: 'PHP'
                                });
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return value.toLocaleString('en-US', {
                                style: 'currency',
                                currency: 'PHP'
                            });
                        }
                    }
                }
            }
        });
    }

    function createChartNewSales() {
        new Chart("salesChart", {
            type: "bar",
            data: {
                labels: monthValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: salesValues
                }]
            },
            options: {
                legend: { display: false },
                title: {
                    display: true,
                    text: "New Sales " + year
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                return value.toLocaleString('en-US');
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return value.toLocaleString('en-US');
                        }
                    }
                }
            }
        });
    }

    $('#searchDashboardYear').click(function(){

        var selectedYear = $('#dashboard-year').val();
        window.location.href="/dashboard?year=" + selectedYear;
    });
});