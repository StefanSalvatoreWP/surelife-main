/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    let selectedCity = $('#selectedCity').val();
    
    let selectedProvinceId = $('#provinceName').val();
    let citySelect = $('#cityName');

    citySelect.empty();
    citySelect.append('<option value="0">Select a city</option>');

    $.ajax({
        url: '/get-cities',
        method: 'GET',
        data: { provinceId: selectedProvinceId },
        dataType: 'json',
        cache: false,
        success: function(cities) {
            cities.forEach(function(city) {
                citySelect.append('<option value="' + city.Id + '">' + city.City + '</option>');

                if(selectedCity == city.Id){
                    citySelect.append('<option hidden selected value="' + city.Id + '">' + city.City + '</option>');
                }
            });
        }
    });

    $('#provinceName').on('change', function() {

        let selectedProvinceId = $(this).val();
        let citySelect = $('#cityName');

        citySelect.empty();
        citySelect.append('<option value="0">Select a city</option>');

        $.ajax({
            url: '/get-cities',
            method: 'GET',
            data: { provinceId: selectedProvinceId },
            dataType: 'json',
            cache: false,
            success: function(cities) {
                cities.forEach(function(city) {
                    citySelect.append('<option value="' + city.Id + '">' + city.City + '</option>');
                });
            }
        });
    });
});