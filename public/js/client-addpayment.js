/* 2023 SilverDust) S. Maceren */

$(document).ready(function () {

    // payment
    let paymentTypeSelect = $('#paymentType');
    let partialPayments = ["100", "150", "200", "250", "350", "400"];
    let paymentAmount = $('#paymentAmount');
    let defDownpaymentAmount = $('#defDownpaymentAmount').val();

    console.log('=== PAYMENT FORM INITIALIZED ===');
    console.log('Payment Type:', paymentTypeSelect.val());
    console.log('Payment Amount Element:', paymentAmount);
    console.log('Default Payment Amount:', defDownpaymentAmount);

    // partial payment
    if (paymentTypeSelect.val() === "Partial") {
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
    else if (paymentTypeSelect.val() === "Transfer") {

        paymentAmount.empty();
        paymentAmount.append($('<option>', {
            value: 250,
            text: 250
        }));
    }
    else if (paymentTypeSelect.val() === "Reinstatement") {

        paymentAmount.empty();
        paymentAmount.append($('<option>', {
            value: 250,
            text: 250
        }));
    }
    else if (paymentTypeSelect.val() === "Change Mode") {

        paymentAmount.empty();
        paymentAmount.append($('<option>', {
            value: 50,
            text: 50
        }));
    }
    else if (paymentTypeSelect.val() === "Custom") {

        paymentAmount.empty();
        paymentAmount.replaceWith($('<input>', {
            type: 'number',
            id: 'paymentAmount',
            name: 'paymentamount',
            class: 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200',
            placeholder: 'Enter custom amount',
            value: defDownpaymentAmount || ''
        }));
        paymentAmount = $('#paymentAmount');
    }
    // standard payment
    else {
        paymentAmount.empty();

        let selectedTermAmount = $('#termAmount').val();

        // Only populate if termAmount exists (non-assigned members)
        if (selectedTermAmount && selectedTermAmount !== '') {
            let paymentMultiplier = 12;

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

                let paymentMultiplier = 12;
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
    }

    // ** ON CHANGE ** //

    // payment
    $('#paymentType').on('change', function () {

        let paymentTypeSelect = $('#paymentType');
        let paymentAmount = $('#paymentAmount');

        let selectedPaymentType = paymentTypeSelect.val();
        let termAmount = parseFloat($('#termAmount').val());

        console.log('=== PAYMENT TYPE CHANGED ===');
        console.log('Selected Payment Type:', selectedPaymentType);
        console.log('Current Element Type:', paymentAmount.prop('tagName'));
        console.log('Is Input?', paymentAmount.is('input'));
        console.log('Is Select?', paymentAmount.is('select'));

        // If current element is an input (Custom type), replace it with select
        if (paymentAmount.is('input')) {
            console.log('Replacing input with select...');
            paymentAmount.replaceWith($('<select>', {
                id: 'paymentAmount',
                name: 'paymentamount',
                class: 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200'
            }));
            paymentAmount = $('#paymentAmount');
            console.log('Input replaced with select');
        }

        if (selectedPaymentType === "Partial") {
            console.log('Processing Partial payment...');
            paymentAmount.empty();
            let partialPayments = ["100", "150", "200", "250", "350", "400"];
            for (let i = 0; i < partialPayments.length; i++) {
                paymentAmount.append($('<option>', {
                    value: partialPayments[i],
                    text: partialPayments[i]
                }));
            }
            console.log('Partial payment options added:', partialPayments);
        }
        else if (selectedPaymentType === "Standard") {

            paymentAmount.empty();

            // Only populate if termAmount is valid
            if (termAmount && !isNaN(termAmount)) {
                let paymentMultiplier = 12;

                for (let i = 1; i <= paymentMultiplier; i++) {
                    paymentAmount.append($('<option>', {
                        value: termAmount * i,
                        text: termAmount * i
                    }));
                }
            }
            else {
                // Show placeholder if termAmount is not available
                paymentAmount.append($('<option>', {
                    value: '',
                    text: 'Term amount not available'
                }));
            }
        }
        else if (selectedPaymentType === "Transfer") {

            paymentAmount.empty();
            paymentAmount.append($('<option>', {
                value: 250,
                text: 250
            }));
        }
        else if (selectedPaymentType === "Reinstatement") {

            paymentAmount.empty();
            paymentAmount.append($('<option>', {
                value: 250,
                text: 250
            }));
        }
        else if (selectedPaymentType === "Change Mode") {

            paymentAmount.empty();
            paymentAmount.append($('<option>', {
                value: 50,
                text: 50
            }));
        }
        else if (selectedPaymentType === "Custom") {
            console.log('Processing Custom payment...');
            console.log('Replacing select with input...');
            paymentAmount.replaceWith($('<input>', {
                type: 'number',
                id: 'paymentAmount',
                name: 'paymentamount',
                class: 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200',
                placeholder: 'Enter custom amount',
                value: ''
            }));
            paymentAmount = $('#paymentAmount');
            console.log('Select replaced with input for custom amount entry');
        }
    });

    // *** O.R SERIES CODE DROPDOWN FUNCTIONALITY ***

    // Get client branch and region from hidden inputs
    const clientBranch = $('input[name="clientbranch"]').val();
    const clientRegion = $('input[name="clientregion"]').val();
    const orSeriesCodeInput = $('#orSeriesCode');
    const orSeriesCodeDropdown = $('#orSeriesCodeDropdown');
    const orSeriesCodeLoading = $('#orSeriesCodeLoading');
    const orSeriesCodeError = $('#orSeriesCodeError');

    // Store available series codes
    let availableSeriesCodes = [];

    // Function to load O.R series codes by branch
    function loadOrSeriesCodes() {
        const paymentType = $('#paymentType').val();

        console.log('Loading O.R series codes with:', {
            branchId: clientBranch,
            regionId: clientRegion,
            paymentType: paymentType
        });

        if (!clientBranch) {
            console.log('No branch ID found');
            return;
        }

        // Show loading state
        orSeriesCodeLoading.removeClass('hidden');
        orSeriesCodeError.addClass('hidden');
        orSeriesCodeDropdown.addClass('hidden');

        $.ajax({
            url: '/get-or-series-by-branch',
            method: 'GET',
            data: {
                branchId: clientBranch,
                regionId: clientRegion,
                paymentType: paymentType
            },
            dataType: 'json',
            cache: false,
            success: function (seriesCodes) {
                orSeriesCodeLoading.addClass('hidden');
                availableSeriesCodes = seriesCodes;

                // Populate dropdown
                orSeriesCodeDropdown.empty();

                if (seriesCodes.length === 0) {
                    orSeriesCodeDropdown.append('<div class="px-4 py-2 text-gray-500 text-sm">No O.R series codes found for this branch</div>');
                } else {
                    seriesCodes.forEach(function (item) {
                        const availableCount = item.available_count || 0;
                        const isAvailable = availableCount > 0;

                        // Create the availability badge
                        const badgeClass = isAvailable
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800';
                        const badgeText = isAvailable
                            ? availableCount + ' available'
                            : 'Not available';

                        const option = $('<div>', {
                            class: 'px-4 py-2 flex justify-between items-center series-code-option ' +
                                (isAvailable ? 'hover:bg-green-50 cursor-pointer text-gray-700' : 'cursor-not-allowed text-gray-400 bg-gray-50'),
                            'data-value': item.SeriesCode,
                            'data-available': isAvailable ? '1' : '0'
                        });

                        // Series code text
                        const seriesText = $('<span>').text(item.SeriesCode);

                        // Availability badge
                        const badge = $('<span>', {
                            class: 'text-xs px-2 py-1 rounded-full ' + badgeClass,
                            text: badgeText
                        });

                        option.append(seriesText).append(badge);
                        orSeriesCodeDropdown.append(option);
                    });
                }

                console.log('Loaded O.R series codes:', seriesCodes);
            },
            error: function (xhr, status, error) {
                orSeriesCodeLoading.addClass('hidden');
                orSeriesCodeError.text('Failed to load O.R series codes').removeClass('hidden');
                console.error('Error loading O.R series codes:', error);
            }
        });
    }

    // Load series codes on page load
    loadOrSeriesCodes();

    // Reload series codes when payment type changes
    $('#paymentType').on('change', function () {
        loadOrSeriesCodes();
        // Clear the current selection when payment type changes
        orSeriesCodeInput.val('');
    });

    // Show dropdown when input is focused
    orSeriesCodeInput.on('focus', function () {
        orSeriesCodeDropdown.removeClass('hidden');
    });

    // Filter dropdown options as user types
    orSeriesCodeInput.on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        orSeriesCodeDropdown.find('.series-code-option').each(function () {
            const optionText = $(this).text().toLowerCase();
            if (optionText.includes(searchTerm)) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
        orSeriesCodeDropdown.removeClass('hidden');
    });

    // Handle dropdown option click - load OR numbers when series code is selected
    $(document).on('click', '.series-code-option', function () {
        // Prevent selection of unavailable series codes
        if ($(this).data('available') === '0' || $(this).data('available') === 0) {
            return; // Don't select if not available
        }

        const selectedValue = $(this).data('value');
        orSeriesCodeInput.val(selectedValue);
        orSeriesCodeDropdown.addClass('hidden');

        // Clear the OR No. field and load available OR numbers
        $('#orNo').val('');
        loadOrNumbers(selectedValue);
    });

    // Hide dropdown when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#orSeriesCode, #orSeriesCodeDropdown').length) {
            orSeriesCodeDropdown.addClass('hidden');
        }
        if (!$(e.target).closest('#orNo, #orNoDropdown').length) {
            $('#orNoDropdown').addClass('hidden');
        }
    });

    // Show dropdown on input click
    orSeriesCodeInput.on('click', function (e) {
        e.stopPropagation();
        orSeriesCodeDropdown.removeClass('hidden');
    });

    // *** O.R NUMBER DROPDOWN FUNCTIONALITY ***

    const orNoInput = $('#orNo');
    const orNoDropdown = $('#orNoDropdown');
    const orNoLoading = $('#orNoLoading');
    const orNoError = $('#orNoError');

    // Store available OR numbers
    let availableOrNumbers = [];

    // Function to load O.R numbers by series code
    function loadOrNumbers(seriesCode) {
        const paymentType = $('#paymentType').val();

        if (!seriesCode) {
            console.log('No series code provided');
            orNoDropdown.empty();
            orNoDropdown.append('<div class="px-4 py-2 text-gray-500 text-sm">Select a series code first</div>');
            return;
        }

        console.log('Loading O.R numbers with:', {
            seriesCode: seriesCode,
            branchId: clientBranch,
            regionId: clientRegion,
            paymentType: paymentType
        });

        // Show loading state
        orNoLoading.removeClass('hidden');
        orNoError.addClass('hidden');
        orNoDropdown.addClass('hidden');

        $.ajax({
            url: '/get-or-numbers',
            method: 'GET',
            data: {
                seriesCode: seriesCode,
                branchId: clientBranch,
                regionId: clientRegion,
                paymentType: paymentType
            },
            dataType: 'json',
            cache: false,
            success: function (orNumbers) {
                orNoLoading.addClass('hidden');
                availableOrNumbers = orNumbers;

                // Populate dropdown
                orNoDropdown.empty();

                if (orNumbers.length === 0) {
                    orNoDropdown.append('<div class="px-4 py-2 text-gray-500 text-sm">No available O.R numbers for this series code</div>');
                } else {
                    orNumbers.forEach(function (item) {
                        const option = $('<div>', {
                            class: 'px-4 py-2 flex justify-between items-center hover:bg-green-50 cursor-pointer text-gray-700 or-number-option',
                            'data-value': item.ORNumber
                        });

                        // OR number text
                        const orText = $('<span>').text(item.ORNumber);

                        // Available badge
                        const badge = $('<span>', {
                            class: 'text-xs px-2 py-1 rounded-full bg-green-100 text-green-800',
                            text: 'Available'
                        });

                        option.append(orText).append(badge);
                        orNoDropdown.append(option);
                    });
                }

                // Show the dropdown
                orNoDropdown.removeClass('hidden');

                console.log('Loaded O.R numbers:', orNumbers.length);
            },
            error: function (xhr, status, error) {
                orNoLoading.addClass('hidden');
                orNoError.text('Failed to load O.R numbers').removeClass('hidden');
                console.error('Error loading O.R numbers:', error);
            }
        });
    }

    // Show dropdown when OR No. input is focused
    orNoInput.on('focus', function () {
        if (availableOrNumbers.length > 0) {
            orNoDropdown.removeClass('hidden');
        }
    });

    // Filter dropdown options as user types
    orNoInput.on('input', function () {
        const searchTerm = $(this).val().toLowerCase();
        orNoDropdown.find('.or-number-option').each(function () {
            const optionText = $(this).text().toLowerCase();
            if (optionText.includes(searchTerm)) {
                $(this).removeClass('hidden');
            } else {
                $(this).addClass('hidden');
            }
        });
        orNoDropdown.removeClass('hidden');
    });

    // Handle OR number option click
    $(document).on('click', '.or-number-option', function () {
        const selectedValue = $(this).data('value');
        orNoInput.val(selectedValue);
        orNoDropdown.addClass('hidden');
    });

    // Show dropdown on OR No. input click
    orNoInput.on('click', function (e) {
        e.stopPropagation();
        if (availableOrNumbers.length > 0) {
            orNoDropdown.removeClass('hidden');
        } else if (orSeriesCodeInput.val()) {
            // If series code is selected but no OR numbers loaded, load them
            loadOrNumbers(orSeriesCodeInput.val());
        }
    });
});