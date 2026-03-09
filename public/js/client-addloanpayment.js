/* 2023 SilverDust) S. Maceren - Loan Payment OR Dropdown */

$(document).ready(function () {

    // *** ADVANCE PAYMENT PREVIEW CALCULATION ***
    
    // Get config from blade template (set in client-addloanpayment.blade.php)
    const config = window.loanPaymentConfig || {
        monthlyPayment: 0,
        remainingBalance: 0,
        totalRepayable: 0,
        totalPaid: 0
    };
    
    const monthlyPayment = config.monthlyPayment;
    const totalRepayable = config.totalRepayable;
    const totalPaid = config.totalPaid || 0;
    
    const paymentAmountInput = $('#paymentAmount');
    const advancePreview = $('#advancePaymentPreview');
    const monthsCoveredEl = $('#monthsCovered');
    const balanceAfterPaymentEl = $('#balanceAfterPayment');
    const nextMonthMinEl = $('#nextMonthMin');
    const excessPaymentEl = $('#excessPayment');
    const paymentAmountError = $('#paymentAmountError');
    const submitBtn = $('button[type="submit"]');
    const maxPayment = config.remainingBalance; // Maximum allowed payment
    const effectiveMinPayment = (maxPayment > 0 && maxPayment < monthlyPayment) ? maxPayment : monthlyPayment;
    
    // Function to validate payment amount
    function validatePaymentAmount() {
        const paymentAmount = parseFloat(paymentAmountInput.val()) || 0;
        
        // Check if payment exceeds remaining balance
        if (paymentAmount > maxPayment && maxPayment > 0) {
            paymentAmountError.text('Payment amount (₱' + paymentAmount.toLocaleString('en-PH', {minimumFractionDigits: 2}) + ') exceeds remaining balance (₱' + maxPayment.toLocaleString('en-PH', {minimumFractionDigits: 2}) + '). Maximum allowed: ₱' + maxPayment.toLocaleString('en-PH', {minimumFractionDigits: 2}));
            paymentAmountError.removeClass('hidden');
            submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            return false;
        }
        
        // Check minimum payment
        // Allow payoff when the remaining balance (maxPayment) is below the monthly minimum.
        if (paymentAmount > 0 && paymentAmount < effectiveMinPayment && paymentAmount !== maxPayment) {
            paymentAmountError.text('Amount must be at least ₱' + effectiveMinPayment.toLocaleString('en-PH', {minimumFractionDigits: 2}));
            paymentAmountError.removeClass('hidden');
            submitBtn.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
            return false;
        }
        
        paymentAmountError.addClass('hidden');
        submitBtn.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        return true;
    }
    
    // Calculate advance payment preview when payment amount changes (input event for real-time)
    paymentAmountInput.on('input change', function() {
        const paymentAmount = parseFloat($(this).val()) || 0;
        
        // Validate payment amount first
        if (!validatePaymentAmount()) {
            advancePreview.addClass('hidden');
            return;
        }
        
        if (paymentAmount > 0) {
            // Calculate total after this payment
            const newTotalPaid = totalPaid + paymentAmount;
            
            // Calculate remaining balance AFTER this payment
            const balanceAfter = Math.max(0, totalRepayable - newTotalPaid);
            
            // Calculate months covered by this payment only
            const monthsCovered = Math.floor(paymentAmount / monthlyPayment);
            const excessAmount = paymentAmount % monthlyPayment;
            
            // Calculate next month minimum payment
            // If there's excess, next month payment is reduced.
            // If the loan becomes fully paid, there is no next-month minimum.
            const nextMonthMin = balanceAfter <= 0
                ? 0
                : (excessAmount > 0 ? (monthlyPayment - excessAmount) : monthlyPayment);
            
            // Show preview
            monthsCoveredEl.text(monthsCovered + ' month' + (monthsCovered > 1 ? 's' : ''));
            balanceAfterPaymentEl.text('₱ ' + balanceAfter.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            
            // Show excess and next month minimum
            if (balanceAfter <= 0) {
                $('#excessInfo').addClass('hidden');
            } else if (excessAmount > 0) {
                excessPaymentEl.text('₱ ' + excessAmount.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                nextMonthMinEl.text('₱ ' + nextMonthMin.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#excessInfo').removeClass('hidden');
            } else {
                $('#excessInfo').addClass('hidden');
            }
            
            // Show/hide advance payment preview
            if (paymentAmount > monthlyPayment) {
                advancePreview.removeClass('hidden');
            } else {
                advancePreview.addClass('hidden');
            }
        } else {
            advancePreview.addClass('hidden');
        }
    });
    
    // Trigger on page load
    paymentAmountInput.trigger('input');

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

    // Function to load O.R series codes by branch (loan type = '1' - same as regular payments)
    function loadOrSeriesCodes() {
        const orType = '1'; // Use same OR pool as regular payments

        console.log('Loading O.R series codes for loan payment:', {
            branchId: clientBranch,
            regionId: clientRegion,
            orType: orType
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
                paymentType: orType
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
                                (isAvailable ? 'hover:bg-purple-50 cursor-pointer text-gray-700' : 'cursor-not-allowed text-gray-400 bg-gray-50'),
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

    // Load series codes on page load - OPTIONAL: Only load if user wants to use OR
    // Commented out to make OR optional - user can trigger by clicking the input
    // loadOrSeriesCodes();

    // Show dropdown when input is focused
    orSeriesCodeInput.on('focus', function () {
        if (orSeriesCodeInput.prop('readonly')) {
            return;
        }
        orSeriesCodeDropdown.removeClass('hidden');
    });

    // Filter dropdown options as user types
    orSeriesCodeInput.on('input', function () {
        if (orSeriesCodeInput.prop('readonly')) {
            return;
        }
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
        if (orSeriesCodeInput.prop('readonly')) {
            return;
        }
        e.stopPropagation();
        orSeriesCodeDropdown.removeClass('hidden');
    });

    // *** O.R NUMBER DROPDOWN FUNCTIONALITY ***

    const orNoInput = $('#orNo');
    const orNoDropdown = $('#orNoDropdown');
    const orNoLoading = $('#orNoLoading');
    const orNoError = $('#orNoError');

    // If series code is already set (e.g., last-used per client), automatically load OR numbers.
    // This prevents forcing the user to re-select a series code just to submit.
    // OPTIONAL: Only auto-load if user wants to use OR (has a value)
    // if (orSeriesCodeInput.val()) {
    //     loadOrNumbers(orSeriesCodeInput.val());
    // }

    // Store available OR numbers
    let availableOrNumbers = [];

    // Function to load O.R numbers by series code
    function loadOrNumbers(seriesCode) {
        const orType = '1'; // Loan payment - use Standard OR pool (same as regular payments)

        if (!seriesCode) {
            console.log('No series code provided');
            orNoDropdown.empty();
            orNoDropdown.append('<div class="px-4 py-2 text-gray-500 text-sm">Select a series code first</div>');
            return;
        }

        console.log('Loading O.R numbers for loan payment:', {
            seriesCode: seriesCode,
            branchId: clientBranch,
            regionId: clientRegion,
            orType: orType
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
                paymentType: orType
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
                            class: 'px-4 py-2 flex justify-between items-center hover:bg-purple-50 cursor-pointer text-gray-700 or-number-option',
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

                // Do not auto-show the dropdown on load; keep it hidden unless user clicks.
                orNoDropdown.addClass('hidden');

                // OPTIONAL: Do not auto-select OR number - let user choose or leave empty
                // if ((!orNoInput.val() || !String(orNoInput.val()).trim()) && orNumbers.length > 0) {
                //     orNoInput.val(orNumbers[0].ORNumber);
                // }

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
    // If the input is readonly (to prevent typing), don't auto-show the dropdown.
    // User can still open it via click.
    orNoInput.on('focus', function () {
        if (orNoInput.prop('readonly')) {
            return;
        }
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
