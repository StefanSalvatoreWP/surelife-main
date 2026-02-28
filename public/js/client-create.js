/* 2023 SilverDust) S. Maceren */

// Function to fix corrupted UTF-8 characters
function fixCorruptedText(text) {
    if (!text) return text;

    // Fix double-encoded UTF-8 characters
    let fixedText = text;

    // Fix ñ (Ã± -> ñ)
    fixedText = fixedText.replace(/\u00C3\u00B1/g, '\u00F1');
    // Fix Ñ (Ã' -> Ñ)
    fixedText = fixedText.replace(/\u00C3\u0091/g, '\u00D1');
    // Fix á (Ã¡ -> á)
    fixedText = fixedText.replace(/\u00C3\u00A1/g, '\u00E1');
    // Fix é (Ã© -> é)
    fixedText = fixedText.replace(/\u00C3\u00A9/g, '\u00E9');
    // Fix í (Ã­ -> í)
    fixedText = fixedText.replace(/\u00C3\u00AD/g, '\u00ED');
    // Fix ó (Ã³ -> ó)
    fixedText = fixedText.replace(/\u00C3\u00B3/g, '\u00F3');
    // Fix ú (Ãº -> ú)
    fixedText = fixedText.replace(/\u00C3\u00BA/g, '\u00FA');
    // Fix Á (Ã -> Á)
    fixedText = fixedText.replace(/\u00C3\u0081/g, '\u00C1');
    // Fix É (Ã‰ -> É)
    fixedText = fixedText.replace(/\u00C3\u0089/g, '\u00C9');
    // Fix Í (Ã -> Í)
    fixedText = fixedText.replace(/\u00C3\u008D/g, '\u00CD');
    // Fix Ó (Ã" -> Ó)
    fixedText = fixedText.replace(/\u00C3\u0093/g, '\u00D3');
    // Fix Ú (Ãš -> Ú)
    fixedText = fixedText.replace(/\u00C3\u009A/g, '\u00DA');
    // Fix ü (Ã¼ -> ü)
    fixedText = fixedText.replace(/\u00C3\u00BC/g, '\u00FC');
    // Fix Ü (Ãœ -> Ü)
    fixedText = fixedText.replace(/\u00C3\u009C/g, '\u00DC');

    return fixedText;
}

// Function to fix all select options in a dropdown
function fixSelectOptions(selectElement) {
    $(selectElement).find('option').each(function () {
        const originalText = $(this).text();
        const fixedText = fixCorruptedText(originalText);
        if (originalText !== fixedText) {
            $(this).text(fixedText);
        }
    });
}

$(document).ready(function () {

    // Fix corrupted characters in all select dropdowns on page load
    fixSelectOptions('#recruitedBy');
    fixSelectOptions('#region');
    fixSelectOptions('#branch');
    fixSelectOptions('#province');
    fixSelectOptions('#city');
    fixSelectOptions('#barangay');

    // packages
    let selectedPackageId = $('#package').val();
    let packagePriceSelect = $('#packagePrice');

    $.ajax({
        url: '/get-packageprice',
        method: 'GET',
        data: { packageId: selectedPackageId },
        dataType: 'json',
        cache: false,
        success: function (packgePrices) {
            packgePrices.forEach(function (currentPackage) {
                packagePriceSelect.val(currentPackage.Price);
                if (typeof formatCurrency === 'function') {
                    packagePriceSelect.val(formatCurrency(currentPackage.Price));
                }
            });
        },
        error: function (xhr, status, error) {
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
        success: function (paymentTerms) {

            paymentTerms.forEach(function (paymentTerm) {
                if (paymentTerm.Id == defPaymentTerm) {
                    paymentTermSelect.append('<option value="' + paymentTerm.Id + '" selected>' + paymentTerm.Term + '</option>');
                }
                else {
                    paymentTermSelect.append('<option value="' + paymentTerm.Id + '">' + paymentTerm.Term + '</option>');
                }
            });

            // payment term amount
            let selectedPaymentTermId = $('#paymentTerm').val();
            let selectedTermAmount = $('#termAmount');

            $.ajax({
                url: '/get-paymenttermamount',
                method: 'GET',
                data: { paymentTermId: selectedPaymentTermId },
                dataType: 'json',
                cache: false,
                success: function (termAmounts) {

                    selectedTermAmount.val(0);
                    termAmounts.forEach(function (termAmount) {
                        selectedTermAmount.val(termAmount.Price);
                        if (typeof formatCurrency === 'function') {
                            selectedTermAmount.val(formatCurrency(termAmount.Price));
                        }
                    });
                },
                error: function (xhr, status, error) {
                    selectedTermAmount.val(0);
                }
            });
        },
        error: function (xhr, status, error) {
            paymentTermSelect.append('<option value="0">Select package</option>');
        }
    });

    // downpayment
    let downpaymentTypeSelect = $('#downpaymentType');
    let partialPayments = ["100", "150", "200", "250", "350", "400"];
    let paymentAmount = $('#paymentAmount');
    let defDownpaymentAmount = $('#defDownpaymentAmount').val();

    // partial payment
    if (downpaymentTypeSelect.val() === "Partial") {
        paymentAmount.empty();
        partialPayments.forEach(function (partialPayment) {
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
    // change mode payment - fixed at 50
    else if (downpaymentTypeSelect.val() === "Change Mode") {
        paymentAmount.empty();
        paymentAmount.replaceWith('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="50" readonly />');
    }
    // transfer payment - fixed at 250
    else if (downpaymentTypeSelect.val() === "Transfer") {
        paymentAmount.empty();
        paymentAmount.replaceWith('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="250" readonly />');
    }
    // reinstatement payment - fixed at 250
    else if (downpaymentTypeSelect.val() === "Reinstatement") {
        paymentAmount.empty();
        paymentAmount.replaceWith('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="250" readonly />');
    }
    // custom payment
    else if (downpaymentTypeSelect.val() === "Custom") {
        paymentAmount.empty();
        // Allow manual input by making it editable
        paymentAmount.replaceWith('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" placeholder="Enter custom amount" value="' + defDownpaymentAmount + '" />');
    }
    // standard payment
    else {
        paymentAmount.empty();

        let paymentMultiplier = 12;
        let selectedTermAmount = $('#termAmount').val();

        let defPaymentTerm = $('#defPaymentTerm').val();
        let selectedPackageId = $('#package').val();

        // get the default term upon page refresh if not null
        if (defPaymentTerm != null) {
            $.ajax({
                url: '/get-paymentterm',
                method: 'GET',
                data: { packageId: selectedPackageId },
                dataType: 'json',
                cache: false,
                success: function (paymentTerms) {

                    paymentTerms.forEach(function (paymentTerm) {

                        if (paymentTerm.Id == defPaymentTerm) {

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

                                if (defDownpaymentAmount == selectedTermAmount * i) {
                                    paymentValues.attr('selected', 'selected');
                                }
                                paymentAmount.append(paymentValues);
                            }
                        }
                    });
                },
                error: function (xhr, status, error) {
                    paymentTermSelect.append('<option value="0">Select package</option>');
                }
            });
        }
        else {

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

                if (defDownpaymentAmount == selectedTermAmount * i) {
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
        success: function (branches) {

            if (branches.length === 0) {
                branchSelect.append('<option value="0">Not available</option>');
            }
            else {
                branches.forEach(function (branch) {
                    if (branch.Id == defBranch) {
                        branchSelect.append('<option value="' + branch.Id + '" selected>' + branch.BranchName + '</option>');
                    }
                    else {
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
                        success: function (staffs) {

                            recruitedBySelect.empty();
                            recruitedBySelect.append('<option value="">-- None / N/A --</option>');
                            if (staffs.length === 0) {
                                // No staff, but None/N/A is already there
                            }
                            else {
                                staffs.forEach(function (staff) {

                                    if (staff.Id == defRecruitedBy) {
                                        recruitedBySelect.append('<option value="' + staff.Id + '" selected>' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                    }
                                    else {
                                        recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                    }
                                });
                                // Fix corrupted characters after loading
                                fixSelectOptions('#recruitedBy');
                            }
                        },
                        error: function (xhr, status, error) {
                            recruitedBySelect.empty().append('<option value="0">Select staff</option>');
                        }
                    });
                });
            }
        },
        error: function (xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
        }
    });

    // cities
    let selectedProvince = $('#province').val();
    let prevCity = $('#prevCity').val();
    let citySelect = $('#city');

    $.ajax({
        url: '/get-ref-cities',
        method: 'GET',
        data: { provinceName: selectedProvince },
        dataType: 'json',
        contentType: 'application/json; charset=utf-8',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
        },
        cache: false,
        success: function (cities) {

            citySelect.empty();
            if (cities.length === 0) {
                citySelect.append('<option value="0">Not available</option>');
            }
            else {
                cities.forEach(function (city) {

                    var option = '<option value="' + city.City + '">' + city.City + '</option';
                    citySelect.append(option);

                    if (prevCity == city.City) {
                        citySelect.val(city.City);

                        // barangays
                        selectedCity = $('#city').val();
                        let prevBarangay = $('#prevBarangay').val();
                        let barangaySelect = $('#barangay');

                        $.ajax({
                            url: '/get-ref-barangays',
                            method: 'GET',
                            data: { cityName: selectedCity },
                            dataType: 'json',
                            contentType: 'application/json; charset=utf-8',
                            beforeSend: function (xhr) {
                                xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
                            },
                            cache: false,
                            success: function (barangays) {

                                barangaySelect.empty();
                                if (barangays.length === 0) {
                                    barangaySelect.append('<option value="0">Not available</option>');
                                }
                                else {
                                    barangays.forEach(function (barangay) {

                                        var barangayOption = '<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option';
                                        barangaySelect.append(barangayOption);

                                        if (prevBarangay == barangay.Barangay) {
                                            barangaySelect.val(barangay.Barangay);
                                        }
                                    });
                                }
                            },
                            error: function (xhr, status, error) {
                                barangaySelect.append('<option value="0">Not available</option>');
                            }
                        });
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            citySelect.append('<option value="0">Not available</option>');
        }
    });

    // barangays
    selectedCity = $('#city').val();
    let barangaySelect = $('#barangay');

    $.ajax({
        url: '/get-ref-barangays',
        method: 'GET',
        data: { cityName: selectedCity },
        dataType: 'json',
        contentType: 'application/json; charset=utf-8',
        beforeSend: function (xhr) {
            xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
        },
        cache: false,
        success: function (barangays) {

            barangaySelect.empty();
            if (barangays.length === 0) {
                barangaySelect.append('<option value="0">Not available</option>');
            }
            else {
                barangays.forEach(function (barangay) {
                    barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            barangaySelect.append('<option value="0">Not available</option>');
        }
    });

    // zipcode
    let zipcodeSelect = $('#zipcode');

    $.ajax({
        url: '/get-cities-zipcode',
        method: 'GET',
        data: { cityName: selectedCity },
        dataType: 'json',
        cache: false,
        success: function (zipcode) {
            zipcodeSelect.val(zipcode);
        },
        error: function (xhr, status, error) {
            zipcodeSelect.val('NA');
        }
    });

    /*** ON CHANGE ****/

    // packages
    $('#package').on('change', function () {

        let selectedPackageId = $('#package').val();
        let packagePriceSelect = $('#packagePrice');

        packagePriceSelect.empty();

        $.ajax({
            url: '/get-packageprice',
            method: 'GET',
            data: { packageId: selectedPackageId },
            dataType: 'json',
            cache: false,
            success: function (packgePrices) {

                packgePrices.forEach(function (package) {
                    packagePriceSelect.val(package.Price);
                    if (typeof formatCurrency === 'function') {
                        packagePriceSelect.val(formatCurrency(package.Price));
                    }
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
                    success: function (paymentTerms) {

                        paymentTermSelect.empty();
                        if (paymentTerms.length === 0) {
                            paymentTermSelect.append('<option value="0">Not available</option>');
                            selectedTermAmount.val(0);
                        } else {
                            paymentTerms.forEach(function (paymentTerm) {
                                paymentTermSelect.append('<option value="' + paymentTerm.Id + '">' + paymentTerm.Term + '</option>');
                            });

                            // payment term amount
                            let selectedPaymentTermId = $('#paymentTerm').val();

                            $.ajax({
                                url: '/get-paymenttermamount',
                                method: 'GET',
                                data: { paymentTermId: selectedPaymentTermId },
                                dataType: 'json',
                                cache: false,
                                success: function (termAmounts) {

                                    selectedTermAmount.val(0);
                                    termAmounts.forEach(function (termAmount) {
                                        selectedTermAmount.val(termAmount.Price);
                                        if (typeof formatCurrency === 'function') {
                                            selectedTermAmount.val(formatCurrency(termAmount.Price));
                                        }
                                    });

                                    // update downpayment values 
                                    let downpaymentTypeSelect = $('#downpaymentType');
                                    let selectedPaymentType = downpaymentTypeSelect.val();
                                    let termAmount = parseFloat($('#termAmount').val());

                                    if (selectedPaymentType === "Partial") {
                                        let paymentAmount = $('#paymentAmount');
                                        if (paymentAmount.is('select')) {
                                            paymentAmount.empty();
                                            let partialPayments = ["100", "150", "200", "250", "350", "400"];
                                            for (let i = 0; i < partialPayments.length; i++) {
                                                paymentAmount.append($('<option>', {
                                                    value: partialPayments[i],
                                                    text: partialPayments[i]
                                                }));
                                            }
                                        }
                                    }
                                    else if (selectedPaymentType === "Change mode" || selectedPaymentType === "Custom") {
                                        // Keep the input field as is, don't modify
                                    }
                                    else {
                                        let paymentAmount = $('#paymentAmount');
                                        if (paymentAmount.is('select')) {
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
                                    }
                                },
                                error: function (xhr, status, error) {
                                    selectedTermAmount.val(0);
                                }
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        paymentTermSelect.empty();
                        paymentTermSelect.append('<option value="0">Select package</option>');
                    }
                });
            },
            error: function (xhr, status, error) {
                packagePriceSelect.val(0);
            }
        });
    });

    // payment term amount
    $('#paymentTerm').on('change', function () {

        let selectedPaymentTermId = $('#paymentTerm').val();
        let selectedTermAmount = $('#termAmount');

        $.ajax({
            url: '/get-paymenttermamount',
            method: 'GET',
            data: { paymentTermId: selectedPaymentTermId },
            dataType: 'json',
            cache: false,
            success: function (termAmounts) {

                selectedTermAmount.val(0);
                termAmounts.forEach(function (termAmount) {
                    selectedTermAmount.val(termAmount.Price);
                    if (typeof formatCurrency === 'function') {
                        selectedTermAmount.val(formatCurrency(termAmount.Price));
                    }
                });

                // update downpayment values 
                let downpaymentTypeSelect = $('#downpaymentType');
                let selectedPaymentType = downpaymentTypeSelect.val();
                let termAmount = parseFloat($('#termAmount').val());

                if (selectedPaymentType === "Partial") {
                    let paymentAmount = $('#paymentAmount');
                    if (paymentAmount.is('select')) {
                        paymentAmount.empty();
                        let partialPayments = ["100", "150", "200", "250", "350", "400"];
                        for (let i = 0; i < partialPayments.length; i++) {
                            paymentAmount.append($('<option>', {
                                value: partialPayments[i],
                                text: partialPayments[i]
                            }));
                        }
                    }
                }
                else if (selectedPaymentType === "Change mode" || selectedPaymentType === "Custom") {
                    // Keep the input field as is, don't modify
                }
                else {
                    let paymentAmount = $('#paymentAmount');
                    if (paymentAmount.is('select')) {
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
                }
            },
            error: function (xhr, status, error) {
                selectedTermAmount.val(0);
            }
        });
    });

    // downpayment
    $('#downpaymentType').on('change', function () {

        let downpaymentTypeSelect = $('#downpaymentType');
        let paymentAmountElement = $('#paymentAmount');
        let selectedPaymentType = downpaymentTypeSelect.val();
        let termAmount = parseFloat($('#termAmount').val());

        if (selectedPaymentType === "Partial") {
            // Replace with select dropdown for partial payments
            let newSelect = $('<select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount"></select>');
            let partialPayments = ["100", "150", "200", "250", "350", "400"];
            for (let i = 0; i < partialPayments.length; i++) {
                newSelect.append($('<option>', {
                    value: partialPayments[i],
                    text: partialPayments[i]
                }));
            }
            paymentAmountElement.replaceWith(newSelect);
        }
        else if (selectedPaymentType === "Change Mode") {
            // Fixed amount of 50 for Change Mode
            let newInput = $('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="50" readonly />');
            paymentAmountElement.replaceWith(newInput);
        }
        else if (selectedPaymentType === "Transfer") {
            // Fixed amount of 250 for Transfer
            let newInput = $('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="250" readonly />');
            paymentAmountElement.replaceWith(newInput);
        }
        else if (selectedPaymentType === "Reinstatement") {
            // Fixed amount of 250 for Reinstatement
            let newInput = $('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" value="250" readonly />');
            paymentAmountElement.replaceWith(newInput);
        }
        else if (selectedPaymentType === "Custom") {
            // Replace with input field for custom amount
            let newInput = $('<input type="number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount" placeholder="Enter custom amount" />');
            paymentAmountElement.replaceWith(newInput);
        }
        else {
            // Standard payment - replace with select dropdown
            let newSelect = $('<select class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200" id="paymentAmount" name="paymentamount"></select>');

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
            } else if (paymentTermSelect === "Quarterly") {
                paymentMultiplier = 4;
            }

            for (let i = 1; i <= paymentMultiplier; i++) {
                newSelect.append($('<option>', {
                    value: termAmount * i,
                    text: termAmount * i
                }));
            }
            paymentAmountElement.replaceWith(newSelect);
        }
    });

    // regions and branches
    $('#region').on('change', function () {

        let selectedRegionId = $(this).val();
        let branchSelect = $('#branch');
        let branchSkeleton = $('#branchSkeleton');

        // Show loading state
        branchSelect.addClass('hidden');
        branchSkeleton.removeClass('hidden');

        branchSelect.empty();
        $.ajax({
            url: '/get-branches',
            method: 'GET',
            data: { regionId: selectedRegionId },
            dataType: 'json',
            cache: false,
            success: function (branches) {
                // Hide loading state
                branchSelect.removeClass('hidden');
                branchSkeleton.addClass('hidden');

                if (branches.length === 0) {
                    branchSelect.append('<option value="0">Not available</option>');
                }
                else {
                    branches.forEach(function (branch) {
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
                        success: function (staffs) {

                            recruitedBySelect.empty();
                            recruitedBySelect.append('<option value="">-- None / N/A --</option>');
                            if (staffs.length === 0) {
                                // No staff, but None/N/A is already there
                            }
                            else {
                                staffs.forEach(function (staff) {
                                    recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                                });
                                // Fix corrupted characters after loading
                                fixSelectOptions('#recruitedBy');
                            }
                        },
                        error: function (xhr, status, error) {
                            recruitedBySelect.empty().append('<option value="0">Select staff</option>');
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                // Hide loading state on error too
                branchSelect.removeClass('hidden');
                branchSkeleton.addClass('hidden');

                branchSelect.empty().append('<option value="0">Select region</option>');
            }
        });
    });

    // get staff by branch
    $('#branch').on('change', function () {

        let selectedBranchId = $(this).val();
        let recruitedBySelect = $('#recruitedBy');

        $.ajax({
            url: '/get-staff',
            method: 'GET',
            data: { branchId: selectedBranchId },
            dataType: 'json',
            cache: false,
            success: function (staffs) {

                recruitedBySelect.empty();
                recruitedBySelect.append('<option value="">-- None / N/A --</option>');
                if (staffs.length === 0) {
                    // No staff but None/N/A still present
                }
                else {
                    staffs.forEach(function (staff) {
                        recruitedBySelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + '</option>');
                    });
                    // Fix corrupted characters after loading
                    fixSelectOptions('#recruitedBy');
                }
            },
            error: function (xhr, status, error) {
                recruitedBySelect.empty().append('<option value="0">Select staff</option>');
            }
        });
    });

    $('#province').on('change', function () {

        // cities
        let selectedProvince = $('#province').val();
        let citySelect = $('#city');

        $.ajax({
            url: '/get-ref-cities',
            method: 'GET',
            data: { provinceName: selectedProvince },
            dataType: 'json',
            contentType: 'application/json; charset=utf-8',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
            },
            cache: false,
            success: function (cities) {

                citySelect.empty();
                if (cities.length === 0) {
                    citySelect.append('<option value="0">Not available</option>');
                }
                else {
                    cities.forEach(function (city) {
                        citySelect.append('<option value="' + city.City + '">' + city.City + '</option>');
                    });

                    // get barangays
                    selectedCity = $('#city').val();
                    let barangaySelect = $('#barangay');

                    $.ajax({
                        url: '/get-ref-barangays',
                        method: 'GET',
                        data: { cityName: selectedCity },
                        dataType: 'json',
                        contentType: 'application/json; charset=utf-8',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
                        },
                        cache: false,
                        success: function (barangays) {

                            barangaySelect.empty();
                            if (barangays.length === 0) {
                                barangaySelect.append('<option value="0">Not available</option>');
                            }
                            else {
                                barangays.forEach(function (barangay) {
                                    barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                                });
                            }
                        },
                        error: function (xhr, status, error) {
                            barangaySelect.append('<option value="0">Not available</option>');
                        }
                    });

                    // zipcode
                    let zipcodeSelect = $('#zipcode');

                    $.ajax({
                        url: '/get-cities-zipcode',
                        method: 'GET',
                        data: { cityName: selectedCity },
                        dataType: 'json',
                        cache: false,
                        success: function (zipcode) {
                            zipcodeSelect.val(zipcode);
                        },
                        error: function (xhr, status, error) {
                            zipcodeSelect.val('NA');
                        }
                    });
                }
            },
            error: function (xhr, status, error) {
                citySelect.append('<option value="0">Not available</option>');
            }
        });
    });

    // barangay
    $('#city').on('change', function () {

        selectedCity = $('#city').val();
        let barangaySelect = $('#barangay');

        $.ajax({
            url: '/get-ref-barangays',
            method: 'GET',
            data: { cityName: selectedCity },
            dataType: 'json',
            contentType: 'application/json; charset=utf-8',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');
            },
            cache: false,
            success: function (barangays) {

                barangaySelect.empty();
                if (barangays.length === 0) {
                    barangaySelect.append('<option value="0">Not available</option>');
                }
                else {
                    barangays.forEach(function (barangay) {
                        barangaySelect.append('<option value="' + barangay.Barangay + '">' + barangay.Barangay + '</option>');
                    });
                }
            },
            error: function (xhr, status, error) {
                barangaySelect.append('<option value="0">Not available</option>');
            }
        });

        // zipcode
        let zipcodeSelect = $('#zipcode');

        $.ajax({
            url: '/get-cities-zipcode',
            method: 'GET',
            data: { cityName: selectedCity },
            dataType: 'json',
            cache: false,
            success: function (zipcode) {
                zipcodeSelect.val(zipcode);
            },
            error: function (xhr, status, error) {
                zipcodeSelect.val('NA');
            }
        });
    });

    // birthdate and age
    let birthDateInput = $('#birthDate');
    let ageInput = $('#age');

    // Set max date to exactly 18 years ago (client must be 18+)
    (function setMaxBirthDate() {
        let maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);
        let yyyy = maxDate.getFullYear();
        let mm = String(maxDate.getMonth() + 1).padStart(2, '0');
        let dd = String(maxDate.getDate()).padStart(2, '0');
        birthDateInput.attr('max', yyyy + '-' + mm + '-' + dd);
    })();

    // Compute age accurately and enforce 18+ rule
    birthDateInput.on('change input', function () {
        let val = birthDateInput.val();
        let errorEl = document.getElementById('birthDateError');

        if (!val) {
            ageInput.val('');
            if (errorEl) errorEl.classList.add('hidden');
            return;
        }

        let birthDate = new Date(val);
        let today = new Date();

        // Accurate age calculation
        let age = today.getFullYear() - birthDate.getFullYear();
        let monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 18) {
            // Under 18 - reject the date
            ageInput.val('');
            birthDateInput.val('');
            birthDateInput.addClass('border-red-500 ring-2 ring-red-500');
            if (errorEl) errorEl.classList.remove('hidden');
        } else {
            ageInput.val(age);
            birthDateInput.removeClass('border-red-500 ring-2 ring-red-500');
            if (errorEl) errorEl.classList.add('hidden');
        }
    });

    // Same as Current Address functionality
    $('#sameAsCurrentAddress').on('change', function() {
        if ($(this).is(':checked')) {
            // Copy current address values to home address
            $('#homeRegion').val($('#addressRegion').val());
            $('#homeProvince').val($('#addressProvince').val());
            $('#homeCity').val($('#addressCity').val());
            $('#homeBarangay').val($('#addressBarangay').val());
            $('#homeZipcode').val($('#zipcode').val());
            $('#homeStreet').val($('#street').val());
            
            // Trigger change events to load cascading dropdowns
            $('#homeRegion').trigger('change');
            setTimeout(function() {
                $('#homeProvince').trigger('change');
                setTimeout(function() {
                    $('#homeCity').trigger('change');
                }, 200);
            }, 200);
        } else {
            // Clear home address fields
            $('#homeRegion').val('');
            $('#homeProvince').val('');
            $('#homeCity').val('');
            $('#homeBarangay').val('');
            $('#homeZipcode').val('');
            $('#homeStreet').val('');
        }
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