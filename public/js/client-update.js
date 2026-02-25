/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    // packages
    let selectedPackageId = $('#package').val();
    let packagePriceSelect = $('#packagePrice');

    $.ajax({
        url: '/get-packageprice',
        method: 'GET',
        data: { packageId: selectedPackageId },
        dataType: 'json',
        cache: false,
        success: function(packgePrices) {
            packgePrices.forEach(function(package) {
                packagePriceSelect.val(package.Price);
            });
        },
        error: function(xhr, status, error) {
            packagePriceSelect.val(0);
        }
    });

    // payment term
    let paymentTermSelect = $('#paymentTerm');
    let defPaymentTerm = $('#defPaymentTerm').val();
    let selectedPaymentTerm;

    paymentTermSelect.empty();
    $.ajax({
        url: '/get-paymentterm',
        method: 'GET',
        data: { packageId: selectedPackageId },
        dataType: 'json',
        cache: false,
        success: function(paymentTerms) {

            paymentTerms.forEach(function(paymentTerm) {

                if(paymentTerm.Id == defPaymentTerm){  
                    paymentTermSelect.append('<option value="' + paymentTerm.Id + '" selected>' + paymentTerm.Term + '</option>');
                }
                else{
                    paymentTermSelect.append('<option value="' + paymentTerm.Id + '">' + paymentTerm.Term + '</option>');
                }
            });

            // payment term amount
            let selectedPaymentTermId = $('#paymentTerm').val();
            let selectedTermAmount = $('#termAmount');

            $.ajax({
                url: '/get-paymenttermamount',
                method: 'GET',
                data: { paymentTermId: selectedPaymentTermId},
                dataType: 'json',
                cache: false,
                success: function(termAmounts) {

                    selectedTermAmount.val(0);
                    termAmounts.forEach(function(termAmount) {
                        selectedTermAmount.val(termAmount.Price);
                    });
                },
                error: function(xhr, status, error) {
                    selectedTermAmount.val(0);
                }
            });
        },
        error: function(xhr, status, error) {
            paymentTermSelect.append('<option value="0">Select package</option>');
        }
    });

    // downpayment
    let downpaymentTypeSelect = $('#downpaymentType');
    let partialPayments = ["100", "150", "200", "250", "350", "400"];
    let paymentAmount = $('#paymentAmount')
    let defDownpaymentAmount = $('#defAmountPaid').val();

    // partial payment
    if (downpaymentTypeSelect.val() === "Partial") {
        paymentAmount.empty();
        partialPayments.forEach(function(partialPayment) {
            let paymentValues = $('<option>', {
                value: partialPayment,
                text: partialPayment
            });

            if (defDownpaymentAmount === partialPayment) {
                paymentValues.attr('selected', 'selected');
            }

            paymentAmount.append(paymentValues);
        });
    }
    // standard payment
    else {
        paymentAmount.empty();

        let paymentMultiplier = 12;
        let selectedTermAmount = $('#termAmount').val();
        
        let defPaymentTerm = $('#defPaymentTerm').val();
        let selectedPackageId = $('#package').val();

        // get the default term upon page refresh if not null
        if(defPaymentTerm != null){
            $.ajax({
                url: '/get-paymentterm',
                method: 'GET',
                data: { packageId: selectedPackageId },
                dataType: 'json',
                cache: false,
                success: function(paymentTerms) {

                    paymentTerms.forEach(function(paymentTerm) {

                        if(paymentTerm.Id == defPaymentTerm){

                            if (paymentTerm.Term === "Spotcash") {
                                paymentMultiplier = 1;
                            } 
                            else if (paymentTerm.Term === "Annual") {
                                paymentMultiplier = 1;
                            } 
                            else if (paymentTerm.Term === "Semi-Annual") {
                                paymentMultiplier = 2;
                            } 
                            else if (paymentTerm.Term === "Quarterly") {
                                paymentMultiplier = 4;
                            }

                            for (let i = 1; i <= paymentMultiplier; i++) {
                                
                                let paymentValues = $('<option>', {
                                    value: selectedTermAmount * i,
                                    text: selectedTermAmount * i
                                });

                                if(defDownpaymentAmount == selectedTermAmount * i){
                                    paymentValues.attr('selected', 'selected');
                                }
                                paymentAmount.append(paymentValues);
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    paymentTermSelect.append('<option value="0">Select package</option>');
                }
            });
        }
        else{
            
            // if default term is empty
            let paymentTermSelect = $('#paymentTerm option:selected').text();

            if (paymentTermSelect === "Spotcash") {
                paymentMultiplier = 1;
            } 
            else if (paymentTermSelect === "Annual") {
                paymentMultiplier = 1;
            } 
            else if (paymentTermSelect === "Semi-Annual") {
                paymentMultiplier = 2;
            } 
            else if (paymentTermSelect === "Quarterly") {
                paymentMultiplier = 4;
            }

            for (let i = 1; i <= paymentMultiplier; i++) {
                
                let paymentValues = $('<option>', {
                    value: selectedTermAmount * i,
                    text: selectedTermAmount * i
                });

                if(defDownpaymentAmount == selectedTermAmount * i){
                    paymentValues.attr('selected', 'selected');
                }
                paymentAmount.append(paymentValues);
            }
        }
    }

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

    // packages
    $('#package').on('change', function() {

        let selectedPackageId = $('#package').val();
        let packagePriceSelect = $('#packagePrice');

        packagePriceSelect.empty();

        $.ajax({
            url: '/get-packageprice',
            method: 'GET',
            data: { packageId: selectedPackageId },
            dataType: 'json',
            cache: false,
            success: function(packgePrices) {

                packgePrices.forEach(function(package) {
                    packagePriceSelect.val(package.Price);
                });

                // payment term
                let paymentTermSelect = $('#paymentTerm');
                let selectedTermAmount = $('#termAmount');

                paymentTermSelect.empty();

                $.ajax({
                    url: '/get-paymentterm',
                    method: 'GET',
                    data: { packageId: selectedPackageId },
                    dataType: 'json',
                    cache: false,
                    success: function(paymentTerms) {

                        paymentTermSelect.empty();
                        if (paymentTerms.length === 0) {
                            paymentTermSelect.append('<option value="0">Not available</option>');
                            selectedTermAmount.val(0);
                        } else {
                            paymentTerms.forEach(function(paymentTerm) {
                                paymentTermSelect.append('<option value="' + paymentTerm.Id + '">' + paymentTerm.Term + '</option>');
                            });

                            // payment term amount
                            let selectedPaymentTermId = $('#paymentTerm').val();
                            
                            $.ajax({
                                url: '/get-paymenttermamount',
                                method: 'GET',
                                data: { paymentTermId: selectedPaymentTermId},
                                dataType: 'json',
                                cache: false,
                                success: function(termAmounts) {

                                    selectedTermAmount.val(0);
                                    termAmounts.forEach(function(termAmount) {
                                        selectedTermAmount.val(termAmount.Price);
                                    });
                                    
                                    // update downpayment values 
                                    let downpaymentTypeSelect = $('#downpaymentType');
                                    let paymentAmount = $('#paymentAmount');

                                    let selectedPaymentType = downpaymentTypeSelect.val();
                                    let termAmount = parseFloat($('#termAmount').val());

                                    if (selectedPaymentType === "Partial") {
                                        paymentAmount.empty();
                                        let partialPayments = ["100", "150", "200", "250", "350", "400"];
                                        for (let i = 0; i < partialPayments.length; i++) {
                                            paymentAmount.append($('<option>', {
                                                value: partialPayments[i],
                                                text: partialPayments[i]
                                            }));
                                        }
                                    } 
                                    else {
                                        paymentAmount.empty();

                                        let paymentMultiplier = 12;
                                        let paymentTermSelect = $('#paymentTerm option:selected').text();

                                        if (paymentTermSelect === "Spotcash") {
                                            paymentMultiplier = 1;
                                        } 
                                        else if (paymentTermSelect === "Annual") {
                                            paymentMultiplier = 1;
                                        } 
                                        else if (paymentTermSelect === "Semi-Annual") {
                                            paymentMultiplier = 2;
                                        } 
                                        else if (paymentTermSelect === "Quarterly") {
                                            paymentMultiplier = 4;
                                        }

                                        for (let i = 1; i <= paymentMultiplier; i++) {
                                            paymentAmount.append($('<option>', {
                                                value: termAmount * i,
                                                text: termAmount * i
                                            }));
                                        }
                                    }
                                },
                                error: function(xhr, status, error) {
                                    selectedTermAmount.val(0);
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        paymentTermSelect.empty();
                        paymentTermSelect.append('<option value="0">Select package</option>');
                    }
                });
            },
            error: function(xhr, status, error) {
                packagePriceSelect.val(0);
            }
        });
    });

    // payment term amount
    $('#paymentTerm').on('change', function() {
       
        let selectedPaymentTermId = $('#paymentTerm').val();
        let selectedTermAmount = $('#termAmount');

        $.ajax({
            url: '/get-paymenttermamount',
            method: 'GET',
            data: { paymentTermId: selectedPaymentTermId},
            dataType: 'json',
            cache: false,
            success: function(termAmounts) {

                selectedTermAmount.val(0);
                termAmounts.forEach(function(termAmount) {
                    selectedTermAmount.val(termAmount.Price);
                });

                // update downpayment values 
                let downpaymentTypeSelect = $('#downpaymentType');
                let paymentAmount = $('#paymentAmount');

                let selectedPaymentType = downpaymentTypeSelect.val();
                let termAmount = parseFloat($('#termAmount').val());

                if (selectedPaymentType === "Partial") {
                    paymentAmount.empty();
                    let partialPayments = ["100", "150", "200", "250", "350", "400"];
                    for (let i = 0; i < partialPayments.length; i++) {
                        paymentAmount.append($('<option>', {
                            value: partialPayments[i],
                            text: partialPayments[i]
                        }));
                    }
                } 
                else {
                    paymentAmount.empty();

                    let paymentMultiplier = 12;
                    let paymentTermSelect = $('#paymentTerm option:selected').text();

                    if (paymentTermSelect === "Spotcash") {
                        paymentMultiplier = 1;
                    } 
                    else if (paymentTermSelect === "Annual") {
                        paymentMultiplier = 1;
                    } 
                    else if (paymentTermSelect === "Semi-Annual") {
                        paymentMultiplier = 2;
                    } 
                    else if (paymentTermSelect === "Quarterly") {
                        paymentMultiplier = 4;
                    }

                    for (let i = 1; i <= paymentMultiplier; i++) {
                        paymentAmount.append($('<option>', {
                            value: termAmount * i,
                            text: termAmount * i
                        }));
                    }
                }
            },
            error: function(xhr, status, error) {
                selectedTermAmount.val(0);
            }
        });
    });

    // downpayment
    $('#downpaymentType').on('change', function () {

        let downpaymentTypeSelect = $('#downpaymentType');
        let paymentAmount = $('#paymentAmount');

        let selectedPaymentType = downpaymentTypeSelect.val();
        let termAmount = parseFloat($('#termAmount').val());

        if (selectedPaymentType === "Partial") {
            paymentAmount.empty();
            let partialPayments = ["100", "150", "200", "250", "350", "400"];
            for (let i = 0; i < partialPayments.length; i++) {
                paymentAmount.append($('<option>', {
                    value: partialPayments[i],
                    text: partialPayments[i]
                }));
            }
        } 
        else {
            paymentAmount.empty();

            let paymentMultiplier = 12;
            let paymentTermSelect = $('#paymentTerm option:selected').text();

            if (paymentTermSelect === "Spotcash") {
                paymentMultiplier = 1;
            } 
            else if (paymentTermSelect === "Annual") {
                paymentMultiplier = 1;
            } 
            else if (paymentTermSelect === "Semi-Annual") {
                paymentMultiplier = 2;
            } 
            else if (paymentTermSelect === "Quarterly") {
                paymentMultiplier = 4;
            }

            for (let i = 1; i <= paymentMultiplier; i++) {
                paymentAmount.append($('<option>', {
                    value: termAmount * i,
                    text: termAmount * i
                }));
            }
        }
    });

    
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

    // get staff by branch
    $('#branch').on('change', function() {

        let selectedBranchId = $(this).val(); 
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

    // Prevent email username field from being completely cleared only when "Others" is selected
    $('#email').on('input', function() {
        const emailInput = $(this);
        const currentValue = emailInput.val();
        const emailDomainSelect = $('#emailDomainSelect').val();
        
        // Only prevent clearing if "Others" is selected
        if (emailDomainSelect === 'others' && currentValue === '') {
            // Store the last valid value
            if (!emailInput.data('lastValue')) {
                emailInput.val('-');
            }
        } else {
            // Store current value as last valid value
            emailInput.data('lastValue', currentValue);
        }
    });

    // Form submission debugging - Console log for mobile and email
    $('form').on('submit', function(e) {
        console.log('=== CLIENT UPDATE FORM SUBMISSION DEBUG ===');
        
        // Check if form is actually being submitted
        console.log('Form submission detected');
        console.log('Form action:', $(this).attr('action'));
        console.log('Form method:', $(this).attr('method'));
        console.log('Submit button clicked:', e.originalEvent ? e.originalEvent.submitter : 'Unknown');
        
        // Mobile Number
        const mobileNumber = $('#mobileNumber').val();
        console.log('Mobile Number (+63):', mobileNumber);
        console.log('Full Mobile Number:', '+63' + mobileNumber);
        
        // Email
        const emailUsername = $('#email').val();
        const emailDomainSelect = $('#emailDomainSelect').val();
        const customEmailDomain = $('#customEmailDomain').val();
        
        let fullEmail = '';
        if (emailDomainSelect === 'others') {
            fullEmail = emailUsername + '@' + customEmailDomain;
            console.log('Email Username:', emailUsername);
            console.log('Email Domain (Custom):', customEmailDomain);
            console.log('Full Email Address:', fullEmail);
        } else {
            fullEmail = emailUsername + '@' + emailDomainSelect;
            console.log('Email Username:', emailUsername);
            console.log('Email Domain (Selected):', emailDomainSelect);
            console.log('Full Email Address:', fullEmail);
        }
        
        // Form data that will be sent
        console.log('--- Complete Form Data to be Submitted ---');
        const formData = new FormData(this);
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }
        console.log('--- End Form Data ---');
        
        // Validation warnings
        if (!mobileNumber || mobileNumber.length !== 10) {
            console.warn('⚠️ WARNING: Mobile number should be exactly 10 digits!');
        }
        if (!emailUsername || !emailDomainSelect) {
            console.warn('⚠️ WARNING: Email fields are incomplete!');
        }
        if (emailDomainSelect === 'others' && !customEmailDomain) {
            console.warn('⚠️ WARNING: Custom email domain is empty!');
        }
        
        // Check for required fields
        const requiredFields = ['contractno', 'package', 'paymentterm', 'region', 'branch', 'recruitedby', 'lastname', 'firstname', 'gender', 'birthdate', 'age', 'address_region', 'address_province', 'address_city', 'address_barangay'];
        const missingFields = [];
        
        requiredFields.forEach(fieldName => {
            const fieldValue = $(`[name="${fieldName}"]`).val();
            if (!fieldValue || fieldValue === '0' || fieldValue === '') {
                missingFields.push(fieldName);
            }
        });
        
        if (missingFields.length > 0) {
            console.error('❌ ERROR: Missing required fields:', missingFields);
        } else {
            console.log('✅ All required fields appear to be filled');
        }
        
        console.log('=====================================');
    });
});

// Toggle custom email domain input
function toggleCustomEmailDomain() {
    const emailDomainSelect = document.getElementById('emailDomainSelect');
    const customEmailDomain = document.getElementById('customEmailDomain');
    
    if (emailDomainSelect.value === 'others') {
        customEmailDomain.style.display = 'block';
        customEmailDomain.focus();
    } else {
        customEmailDomain.style.display = 'none';
        customEmailDomain.value = '';
    }
}