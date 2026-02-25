/* 2024 SilverDust) S. Maceren */

$(document).ready(function(){

    // birthdate and age
    let birthDateInput = $('#birthDate');
    let ageInput = $('#age');

    // Add an event listener to the birthDate input
    birthDateInput.on('input', function() {
        let birthDate = new Date(birthDateInput.val());
        let currentDate = new Date();

        let age = currentDate.getFullYear() - birthDate.getFullYear();

        ageInput.val(age);
    });

    // regions and branch
    let selectedRegionId = $('#region').val();
    let branchSelect = $('#branch');
    let defBranch = $('#defBranch').val();

    branchSelect.empty();
    $.ajax({
        url: '/get-branches',
        method: 'GET',
        data: { regionId: selectedRegionId },
        dataType: 'json',
        cache: false,
        success: function(branches) {

            if(branches.length === 0){
                branchSelect.append('<option value="0">Not available</option>');
            }
            else{
                branches.forEach(function(branch) {
                    if(branch.Id == defBranch){
                        branchSelect.append('<option value="' + branch.Id + '" selected>' + branch.BranchName + '</option>');
                    }
                    else{
                        branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');
                    }

                     // after branches are loaded, fetch the recruited staff
                    let selectedBranchId = $('#branch').val();
                    let recruitedBySelect = $('#recruitedBy');
                    let defRecruitedBy = $('#defRecruitedBy').val();

                    $.ajax({
                        url: '/get-staff',
                        method: 'GET',
                        data: { branchId: selectedBranchId },
                        dataType: 'json',
                        cache: false,
                        success: function(staffs) {

                            recruitedBySelect.empty();
                            if(staffs.length === 0){
                                recruitedBySelect.append('<option value="0">Not available</option>');
                            }
                            else{
                                staffs.forEach(function(staff) {

                                    if(staff.Id == defRecruitedBy){
                                        recruitedBySelect.append('<option value="' + staff.Id + '" selected>' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                    }
                                    else{
                                        recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                    }
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            recruitedBySelect.empty().append('<option value="0">Select staff</option>');
                        }
                    });
                });
            }
        },
        error: function(xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
        }
    });

    // cities
    let selectedProvince = $('#province').val();
    let prevCity = $('#prevCity').val();
    let citySelect = $('#city');

    $.ajax({
        url: '/get-cities',
        method: 'GET',
        data: { provinceName: selectedProvince},
        dataType: 'json',
        cache: false,
        success: function(cities) {

            citySelect.empty();
            if(cities.length === 0){
                citySelect.append('<option value="0">Not available</option>');
            }
            else{
                cities.forEach(function(city) {
                    
                    var option = '<option value="' + city.City + '">' + city.City + '</option';
                    citySelect.append(option);

                    if (prevCity == city.City) {
                        citySelect.val(city.City); 

                        // barangays
                        selectedCity = $('#city').val();
                        let prevBarangay = $('#prevBarangay').val();
                        let barangaySelect = $('#barangay');

                        $.ajax({
                            url: '/get-barangays',
                            method: 'GET',
                            data: { cityName: selectedCity},
                            dataType: 'json',
                            cache: false,
                            success: function(barangays) {

                                barangaySelect.empty();
                                if(barangays.length === 0){
                                    barangaySelect.append('<option value="0">Not available</option>');
                                }
                                else{
                                    barangays.forEach(function(barangay) {

                                        var barangayOption = '<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option';
                                        barangaySelect.append(barangayOption);

                                        if (prevBarangay == barangay.Barangay) {
                                            barangaySelect.val(barangay.Barangay); 
                                        }
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                barangaySelect.append('<option value="0">Not available</option>');
                            }
                        });
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            citySelect.append('<option value="0">Not available</option>');
        }
    });

    // barangays
    selectedCity = $('#city').val();
    let barangaySelect = $('#barangay');

    $.ajax({
        url: '/get-barangays',
        method: 'GET',
        data: { cityName: selectedCity},
        dataType: 'json',
        cache: false,
        success: function(barangays) {

            barangaySelect.empty();
            if(barangays.length === 0){
                barangaySelect.append('<option value="0">Not available</option>');
            }
            else{
                barangays.forEach(function(barangay) {
                    barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                });
            }
        },
        error: function(xhr, status, error) {
            barangaySelect.append('<option value="0">Not available</option>');
        }
    });

    // zipcode
    let zipcodeSelect = $('#zipcode');

    $.ajax({
        url: '/get-cities-zipcode',
        method: 'GET',
        data: { cityName: selectedCity},
        dataType: 'json',
        cache: false,
        success: function(zipcode) {
            zipcodeSelect.val(zipcode);
        },
        error: function(xhr, status, error) {
            zipcodeSelect.val('NA');
        }
    });

     /*** ON CHANGE ****/
    // regions and branches
    $('#region').on('change', function() {

        let selectedRegionId = $(this).val();
        let branchSelect = $('#branch');

        branchSelect.empty();
        $.ajax({
            url: '/get-branches',
            method: 'GET',
            data: { regionId: selectedRegionId },
            dataType: 'json',
            cache: false,
            success: function(branches) {

                if(branches.length === 0){
                    branchSelect.append('<option value="0">Not available</option>');
                }
                else{
                    branches.forEach(function(branch) {
                        branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');
                    });

                    // after branches are loaded, fetch the recruited staff
                    let selectedBranchId = branchSelect.val(); 
                    let recruitedBySelect = $('#recruitedBy');

                    $.ajax({
                        url: '/get-staff',
                        method: 'GET',
                        data: { branchId: selectedBranchId },
                        dataType: 'json',
                        cache: false,
                        success: function(staffs) {

                            recruitedBySelect.empty();
                            if(staffs.length === 0){
                                recruitedBySelect.append('<option value="0">Not available</option>');
                            }
                            else{
                                staffs.forEach(function(staff) {
                                    recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            recruitedBySelect.empty().append('<option value="0">Select staff</option>');
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                branchSelect.empty().append('<option value="0">Select region</option>');
            }
        });
    });
    
    $('#province').on('change', function() {

        // cities
        let selectedProvince = $('#province').val();
        let citySelect = $('#city');

        $.ajax({
            url: '/get-cities',
            method: 'GET',
            data: { provinceName: selectedProvince},
            dataType: 'json',
            cache: false,
            success: function(cities) {

                citySelect.empty();
                if(cities.length === 0){
                    citySelect.append('<option value="0">Not available</option>');
                }
                else{
                    cities.forEach(function(city) {
                        citySelect.append('<option value="' + city.City + '">' + city.City + '</option>');
                    });

                    // get barangays
                    selectedCity = $('#city').val();
                    let barangaySelect = $('#barangay');

                    $.ajax({
                        url: '/get-barangays',
                        method: 'GET',
                        data: { cityName: selectedCity},
                        dataType: 'json',
                        cache: false,
                        success: function(barangays) {

                            barangaySelect.empty();
                            if(barangays.length === 0){
                                barangaySelect.append('<option value="0">Not available</option>');
                            }
                            else{
                                barangays.forEach(function(barangay) {
                                    barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            barangaySelect.append('<option value="0">Not available</option>');
                        }
                    });

                    // zipcode
                    let zipcodeSelect = $('#zipcode');

                    $.ajax({
                        url: '/get-cities-zipcode',
                        method: 'GET',
                        data: { cityName: selectedCity},
                        dataType: 'json',
                        cache: false,
                        success: function(zipcode) {
                            zipcodeSelect.val(zipcode);
                        },
                        error: function(xhr, status, error) {
                            zipcodeSelect.val('NA');
                        }
                    });
                }
            },
            error: function(xhr, status, error) {
                citySelect.append('<option value="0">Not available</option>');
            }
        });
    });

    // barangay
    $('#city').on('change', function() {

        selectedCity = $('#city').val();
        let barangaySelect = $('#barangay');

        $.ajax({
            url: '/get-barangays',
            method: 'GET',
            data: { cityName: selectedCity},
            dataType: 'json',
            cache: false,
            success: function(barangays) {

                barangaySelect.empty();
                if(barangays.length === 0){
                    barangaySelect.append('<option value="0">Not available</option>');
                }
                else{
                    barangays.forEach(function(barangay) {
                        barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
                barangaySelect.append('<option value="0">Not available</option>');
            }
        });

        // zipcode
        let zipcodeSelect = $('#zipcode');

        $.ajax({
            url: '/get-cities-zipcode',
            method: 'GET',
            data: { cityName: selectedCity},
            dataType: 'json',
            cache: false,
            success: function(zipcode) {
                zipcodeSelect.val(zipcode);
            },
            error: function(xhr, status, error) {
                zipcodeSelect.val('NA');
            }
        });
    });
});