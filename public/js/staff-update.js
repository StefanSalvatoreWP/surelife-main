/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    // regions and branch
    let selectedRegionId = $('#staffRegion').val();
    let branchSelect = $('#staffBranch');

    $.ajax({
        url: '/get-branches',
        method: 'GET',
        data: { regionId: selectedRegionId },
        dataType: 'json',
        cache: false,
        success: function(branches) {
            branches.forEach(function(branch) {
                branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');
            });
        },
        error: function(xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
        }
    });

    // after branches are loaded, fetch the recruited staff
    let selectedBranchId = branchSelect.val(); 
    let recruitedBySelect = $('#staffRecruitedBy');

    $.ajax({
        url: '/get-staff',
        method: 'GET',
        data: { branchId: selectedBranchId },
        dataType: 'json',
        cache: false,
        success: function(staffs) {
            recruitedBySelect.empty();
            staffs.forEach(function(staff) {
                recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
            });
        },
        error: function(xhr, status, error) {
            recruitedBySelect.empty().append('<option value="0">Select staff</option>');
        }
    });

    // city and barangay
    let selectedCity = $('#staffCity').val();
    let selectedBrgy = $('#staffBarangay');

    $.ajax({
        url: '/get-barangays',
        method: 'GET',
        data: { cityName: selectedCity},
        dataType: 'json',
        cache: false,
        success: function(barangays) {
            selectedBrgy.empty();
            barangays.forEach(function(barangay) {
                selectedBrgy.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
            });
        },
        error: function(xhr, status, error) {
            selectedBrgy.empty().append('<option value="0">Select barangay</option>');
        }
    });

    let selectedZipcode = $('#staffZipcode');

    $.ajax({
        url: '/get-cities-zipcode',
        method: 'GET',
        data: { cityName: selectedCity},
        dataType: 'json',
        cache: false,
        success: function(zipcode) {
            selectedZipcode.val(zipcode);
        },
        error: function(xhr, status, error) {
            selectedZipcode.val('NA');
        }
    });

    $('#staffRegion').on('change', function() {

        let selectedRegionId = $(this).val();
        let branchSelect = $('#staffBranch');

        branchSelect.empty();
        $.ajax({
            url: '/get-branches',
            method: 'GET',
            data: { regionId: selectedRegionId },
            dataType: 'json',
            cache: false,
            success: function(branches) {
                
                branches.forEach(function(branch) {
                    branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');
                });

                // after branches are loaded, fetch the recruited staff
                let selectedBranchId =  branchSelect.val();
                let recruitedBySelect = $('#staffRecruitedBy');
                
                $.ajax({
                    url: '/get-staff',
                    method: 'GET',
                    data: { branchId: selectedBranchId },
                    dataType: 'json',
                    cache: false,
                    success: function (staffs) {
                        recruitedBySelect.empty();
                        staffs.forEach(function (staff) {
                            
                            var option = '<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>';
                            recruitedBySelect.append(option);
                        });
                    },
                    error: function(xhr, status, error) {
                        recruitedBySelect.empty().append('<option value="0">Select staff</option>');
                    }
                });
            },
            error: function(xhr, status, error) {
                branchSelect.empty().append('<option value="0">Select region</option>');
            }
        });
    });

    // get staff by branch
    $('#staffBranch').on('change', function() {

        let selectedBranchId = $(this).val(); 
        let recruitedBySelect = $('#staffRecruitedBy');

        $.ajax({
            url: '/get-staff',
            method: 'GET',
            data: { branchId: selectedBranchId },
            dataType: 'json',
            cache: false,
            success: function(staffs) {
                recruitedBySelect.empty(); // Clear the existing options
                staffs.forEach(function(staff) {
                    recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                });
            },
            error: function(xhr, status, error) {
                recruitedBySelect.empty().append('<option value="0">Select staff</option>'); // Clear and add a placeholder option
            }
        });
    });

    
    $('#staffCity').on('change', function() {

        let selectedCity = $(this).val();
        let selectedBrgy = $('#staffBarangay');

        $.ajax({
            url: '/get-barangays',
            method: 'GET',
            data: { cityName: selectedCity},
            dataType: 'json',
            cache: false,
            success: function(barangays) {
                selectedBrgy.empty();
                barangays.forEach(function(barangay) {
                    selectedBrgy.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                });
            },
            error: function(xhr, status, error) {
                selectedBrgy.empty().append('<option value="0">Select barangay</option>');
            }
        });

        let selectedZipcode = $('#staffZipcode');

        $.ajax({
            url: '/get-cities-zipcode',
            method: 'GET',
            data: { cityName: selectedCity},
            dataType: 'json',
            cache: false,
            success: function(zipcode) {
                selectedZipcode.val(zipcode);
            },
            error: function(xhr, status, error) {
                selectedZipcode.val('NA');
            }
        });
    });
});