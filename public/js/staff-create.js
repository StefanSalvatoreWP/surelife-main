/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    // regions and branch
    let selectedRegionId = $('#staffRegion').val();
    let prevBranch = $('#prevBranch').val();
    let branchSelect = $('#staffBranch');

    branchSelect.empty();

    $.ajax({
        url: '/get-branches',
        method: 'GET',
        data: { regionId: selectedRegionId },
        dataType: 'json',
        cache: false,
        success: function (branches) {
            branchSelect.empty();

            if(prevBranch === ''){
                branchSelect.append('<option value="0" hidden selected>Select Branch</option>');
            }
            
            branches.forEach(function (branch) {

                var option = '<option value="' + branch.Id + '">' + branch.BranchName + '</option>';
                branchSelect.append(option);

                if (prevBranch !== '' && prevBranch == branch.Id) {
                    option = '<option value="' + branch.Id + '" selected>' + branch.BranchName + '</option>';
                    branchSelect.append(option);
                }
            });

            // Load recruited by staff for the selected/first branch
            let branchIdToLoad = prevBranch !== '' ? prevBranch : branches[0]?.Id;
            if (branchIdToLoad) {
                loadRecruitedByStaff(branchIdToLoad);
            }
        },
        error: function(xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
        }
    });

    // Function to load recruited by staff
    function loadRecruitedByStaff(branchId) {
        let prevRecruitedBy = $('#prevRecruitedBy').val();
        let recruitedBySelect = $('#staffRecruitedBy');
        
        console.log('Loading staff for branchId:', branchId);
        
        $.ajax({
            url: '/get-staff',
            method: 'GET',
            data: { branchId: branchId },
            dataType: 'json',
            cache: false,
            success: function (staffs) {
                console.log('Staff data received:', staffs);
                console.log('Number of staff:', staffs.length);
                
                recruitedBySelect.empty();
                recruitedBySelect.append('<option value="" selected>Select Recruiter</option>');
                
                if (staffs && staffs.length > 0) {
                    staffs.forEach(function (staff) {
                        // Handle stdClass wrapper and property case variations
                        var staffData = staff.stdClass || staff;
                        var staffId = staffData.Id || staffData.id || '';
                        var firstName = staffData.FirstName || staffData.firstname || 'Unknown';
                        var lastName = staffData.LastName || staffData.lastname || 'Unknown';
                        
                        var isSelected = prevRecruitedBy == staffId ? 'selected' : '';
                        var option = '<option value="' + staffId + '" ' + isSelected + '>' + lastName + ', ' + firstName + '</option>';
                        recruitedBySelect.append(option);
                        console.log('Added staff option:', lastName + ', ' + firstName);
                    });
                } else {
                    recruitedBySelect.append('<option value="">No staff available in this branch</option>');
                    console.log('No staff found for branch:', branchId);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading staff:', error);
                console.error('XHR response:', xhr.responseText);
                recruitedBySelect.empty().append('<option value="">Error loading staff</option>');
            }
        });
    }

    // city and barangay
    let selectedCity = $('#staffCity').val();
    let barangaySelect = $('#staffBarangay');

    $.ajax({
        url: '/get-barangays',
        method: 'GET',
        data: { cityName: selectedCity},
        dataType: 'json',
        cache: false,
        success: function(barangays) {
            barangaySelect.empty();

            let prevBarangay = $('#prevBarangay').val();
            let prevBarangaySelected = false;

            barangays.forEach(function(barangay) {
                
                var option = '<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>';
                barangaySelect.append(option);

                if (prevBarangay == barangay.Barangay && !prevBarangaySelected) {
                    option = '<option value="' + barangay.Barangay + '" hidden selected>' + barangay.Barangay + '</option>';
                    barangaySelect.append(option);
                    prevBarangaySelected = true;
                }
            });
        },
        error: function(xhr, status, error) {
            barangaySelect.empty().append('<option value="0">Select barangay</option>');
        }
    });

    let zipcodeSelect = $('#staffZipcode');

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

    // age 
    let birthDateInput = $('#staffBirthDate');
    let ageInput = $('#staffAge')

    let birthDate = new Date(birthDateInput.val());
    let currentDate = new Date();

    let age = currentDate.getFullYear() - birthDate.getFullYear();
    if (isNaN(age)) {
        age = 0;
    }
    ageInput.val(age);

    // *** ON CHANGE *** //
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
                loadRecruitedByStaff(selectedBranchId);
            },
            error: function(xhr, status, error) {
                branchSelect.empty().append('<option value="0">Select region</option>');
            }
        });
    });

    // get staff by branch
    $('#staffBranch').on('change', function() {
        let selectedBranchId = $(this).val(); 
        loadRecruitedByStaff(selectedBranchId);
    });

    $('#staffCity').on('change', function() {

        let selectedCity = $(this).val();
        let barangaySelect = $('#staffBarangay');

        $.ajax({
            url: '/get-barangays',
            method: 'GET',
            data: { cityName: selectedCity},
            dataType: 'json',
            cache: false,
            success: function(barangays) {
                barangaySelect.empty();
                barangays.forEach(function(barangay) {
                    barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                });
            },
            error: function(xhr, status, error) {
                barangaySelect.empty().append('<option value="0">Select barangay</option>');
            }
        });

        let zipcodeSelect = $('#staffZipcode');

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

    // Add an event listener to the birthDate input
    birthDateInput.on('input', function() {
        let birthDate = new Date(birthDateInput.val());
        let currentDate = new Date();

        let age = currentDate.getFullYear() - birthDate.getFullYear();

        ageInput.val(age);
    });

    // Form validation - scoped to staff create form only
    const $staffCreateForm = $('#staffCreateForm');

    $staffCreateForm.on('submit', function(e) {
        let hasErrors = false;
        let errorMessages = [];

        // Check required fields
        const requiredFields = [
            { field: '#staffUserName', name: 'Username', min: 3, max: 30 },
            { field: '#staffPosition', name: 'Position' },
            { field: '#staffFirstName', name: 'First Name', min: 3, max: 30 },
            { field: '#staffLastName', name: 'Last Name', min: 3, max: 30 },
            { field: '#staffBirthDate', name: 'Birth Date' },
            { field: '#staffAge', name: 'Age', minValue: 18 }
        ];

        requiredFields.forEach(function(item) {
            let field = $(item.field);
            let value = field.val().trim();
            
            // Remove previous error styling
            field.removeClass('border-red-500 bg-red-50');
            field.next('.error-message').remove();

            if (!value || value === '0' || value === '') {
                hasErrors = true;
                errorMessages.push(item.name + ' is required');
                field.addClass('border-red-500 bg-red-50');
                field.after('<p class="error-message text-red-600 text-sm mt-1">' + item.name + ' is required</p>');
            } else {
                // Check min/max length
                if (item.min && value.length < item.min) {
                    hasErrors = true;
                    errorMessages.push(item.name + ' must be at least ' + item.min + ' characters');
                    field.addClass('border-red-500 bg-red-50');
                    field.after('<p class="error-message text-red-600 text-sm mt-1">' + item.name + ' must be at least ' + item.min + ' characters</p>');
                }
                if (item.max && value.length > item.max) {
                    hasErrors = true;
                    errorMessages.push(item.name + ' must not exceed ' + item.max + ' characters');
                    field.addClass('border-red-500 bg-red-50');
                    field.after('<p class="error-message text-red-600 text-sm mt-1">' + item.name + ' must not exceed ' + item.max + ' characters</p>');
                }
                // Check min value for age
                if (item.minValue && parseInt(value) < item.minValue) {
                    hasErrors = true;
                    errorMessages.push(item.name + ' must be at least ' + item.minValue);
                    field.addClass('border-red-500 bg-red-50');
                    field.after('<p class="error-message text-red-600 text-sm mt-1">' + item.name + ' must be at least ' + item.minValue + ' years old</p>');
                }
            }
        });

        if (hasErrors) {
            e.preventDefault();
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.border-red-500:first').offset().top - 100
            }, 500);
            
            // Show summary alert
            alert('Please fix the following errors:\n\n• ' + errorMessages.join('\n• '));
        }
    });

    // Remove error styling on field focus (staff form inputs only)
    $staffCreateForm.find('input, select').on('focus', function() {
        $(this).removeClass('border-red-500 bg-red-50');
        $(this).next('.error-message').remove();
    });
});