/* 
 * Staff Address Cascading System - TBLADDRESS Integration
 * 4-level cascading: Region → Province → City → Barangay
 * Uses the new Philippine address reference database
 */

$(document).ready(function() {
    
    // Initialize address cascading system
    initializeStaffAddressCascading();
    
    function initializeStaffAddressCascading() {
        // Wait a bit for DOM to be fully ready
        setTimeout(function() {
            // Get stored values for form reloading - use hidden inputs for reliable access
            const selectedRegionFromDropdown = $('#staffAddressRegion').val();
            const selectedRegionFromHidden = $('#oldStaffAddressRegion').val();
            const selectedRegion = selectedRegionFromDropdown || selectedRegionFromHidden;
            const selectedProvince = $('#oldStaffProvince').val();
            const selectedCity = $('#oldStaffMunicipality').val(); // Staff uses Municipality field
            const selectedBarangay = $('#oldStaffBarangay').val();
            
            console.log('Initializing staff address cascading with values:', {
                regionFromDropdown: selectedRegionFromDropdown,
                regionFromHidden: selectedRegionFromHidden,
                finalRegion: selectedRegion,
                province: selectedProvince,
                city: selectedCity,
                barangay: selectedBarangay
            });
            
            // Load initial cascading data if region is selected
            if (selectedRegion) {
                console.log('Loading provinces for region:', selectedRegion);
                loadStaffProvinces(selectedRegion, selectedProvince);
            } else {
                console.log('No region selected, skipping province load');
            }
            
            // Initialize zipcode state
            initializeZipcodeState();
            
            // Store original zipcode value to prevent overriding
            const originalZipcode = $('#staffZipcode').val().trim();
            window.originalStaffZipcode = originalZipcode;
            console.log('Stored original zipcode:', originalZipcode);
            
            // Store original values for comparison
            window.originalStaffAddressValues = {
                province: selectedProvince,
                city: selectedCity,
                barangay: selectedBarangay
            };
            
            // Secondary initialization after a longer delay to ensure cascading is complete
            setTimeout(function() {
                console.log('Secondary zipcode initialization...');
                // Restore original zipcode if it was cleared by cascading
                const originalZipcode = window.originalStaffZipcode || '';
                const currentZipcode = $('#staffZipcode').val().trim();
                
                if (originalZipcode && originalZipcode !== '' && currentZipcode === '') {
                    console.log('Restoring original zipcode that was cleared:', originalZipcode);
                    $('#staffZipcode').val(originalZipcode);
                    $('#staffZipcode').prop('readonly', true);
                    $('#staffZipcode').removeClass('bg-white').addClass('bg-gray-100');
                    $('#editZipcodeBtn').show();
                } else {
                    initializeZipcodeState();
                }
            }, 500);
        }, 100); // Small delay to ensure DOM is ready
    }
    
    // Helper function to get old form values
    function getOldValue(fieldName) {
        const input = $(`input[name="${fieldName}"]`);
        return input.length ? input.val() : '';
    }
    
    // Region change handler
    $('#staffAddressRegion').on('change', function() {
        const regionCode = $(this).val();
        
        // Clear dependent dropdowns
        clearDropdown('#staffAddressProvince', 'Select Province');
        clearDropdown('#staffAddressCity', 'Select City/Municipality');
        clearDropdown('#staffAddressBarangay', 'Select Barangay');
        
        if (regionCode) {
            loadStaffProvinces(regionCode);
        }
    });
    
    // Province change handler
    $('#staffAddressProvince').on('change', function() {
        const provinceCode = $(this).val();
        
        // Clear dependent dropdowns
        clearDropdown('#staffAddressCity', 'Select City/Municipality');
        clearDropdown('#staffAddressBarangay', 'Select Barangay');
        
        if (provinceCode) {
            loadStaffCities(provinceCode);
        }
    });
    
    // City change handler
    $('#staffAddressCity').on('change', function() {
        const cityCode = $(this).val();
        const $editBtn = $('#editZipcodeBtn');
        
        // Clear dependent dropdown
        clearDropdown('#staffAddressBarangay', 'Select Barangay');
        
        if (cityCode) {
            loadStaffBarangays(cityCode);
            loadStaffZipcode(cityCode); // Auto-load zipcode when city is selected
        } else {
            // Clear zipcode and allow manual input when no city is selected
            const $zipcodeInput = $('#staffZipcode');
            $zipcodeInput.val('');
            $zipcodeInput.prop('readonly', false);
            $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
            $zipcodeInput.attr('placeholder', 'Enter zipcode manually or select a city to auto-fill');
            $zipcodeInput.attr('title', 'Enter zipcode manually or select a city to auto-fill if available');
            $editBtn.hide();
            console.log('City cleared - zipcode input enabled for manual entry');
        }
    });
    
    // Load provinces by region
    function loadStaffProvinces(regionCode, selectedProvince) {
        console.log('Loading provinces for region:', regionCode);
        
        $.ajax({
            url: '/get-address-provinces',
            method: 'GET',
            data: { regionCode: regionCode },
            dataType: 'json',
            cache: false,
            success: function(provinces) {
                const $provinceSelect = $('#staffAddressProvince');
                $provinceSelect.empty().append('<option value="">Select Province</option>');
                
                $.each(provinces, function(index, province) {
                    $provinceSelect.append('<option value="' + province.code + '">' + (province.name || province.description || 'Unknown') + '</option>');
                });
                
                // For staff, we need to derive province from city since staff don't have province field
                const selectedCity = $('#oldStaffMunicipality').val();
                let derivedProvince = '';
                
                console.log('Deriving province for city:', selectedCity);
                
                if (selectedCity && !isNaN(selectedCity)) {
                    // Dynamic lookup using tbladdress data
                    $.ajax({
                        url: '/get-address-province-from-city',
                        method: 'GET',
                        data: { cityCode: selectedCity },
                        dataType: 'json',
                        async: false, // Make it synchronous to get result before continuing
                        success: function(data) {
                            if (data && data.provinceCode) {
                                derivedProvince = data.provinceCode;
                                console.log('Dynamically derived province:', derivedProvince);
                            } else {
                                console.log('Province not found for city:', selectedCity);
                            }
                        },
                        error: function() {
                            console.log('Error looking up province for city:', selectedCity);
                        }
                    });
                } else if (selectedCity) {
                    // Fallback for non-numeric city names (legacy data) - Enhanced
                    const cityNameLower = selectedCity.toLowerCase();
                    if (cityNameLower.includes('cebu') || cityNameLower.includes('lapu-lapu') || cityNameLower.includes('mandaue')) {
                        derivedProvince = '0722'; // CEBU
                    } else if (cityNameLower.includes('bohol')) {
                        derivedProvince = '0712'; // BOHOL
                    } else if (cityNameLower.includes('negros')) {
                        if (cityNameLower.includes('occidental')) {
                            derivedProvince = '0645'; // NEGROS OCCIDENTAL
                        } else {
                            derivedProvince = '0746'; // NEGROS ORIENTAL
                        }
                    } else if (cityNameLower.includes('siquijor')) {
                        derivedProvince = '0761'; // SIQUIJOR
                    } else if (cityNameLower.includes('ubay')) {
                        derivedProvince = '0712'; // BOHOL (Ubay is in Bohol)
                    } else if (cityNameLower.includes('bacolod')) {
                        derivedProvince = '0645'; // NEGROS OCCIDENTAL (Bacolod is here)
                    } else if (cityNameLower.includes('davao')) {
                        derivedProvince = '1123'; // DAVAO DEL NORTE (default Davao province)
                    } else if (cityNameLower.includes('leyte')) {
                        derivedProvince = '0837'; // LEYTE (correct code)
                    } else if (cityNameLower.includes('samar')) {
                        derivedProvince = '0860'; // SAMAR (WESTERN SAMAR) - default Samar province
                    }
                }
                
                console.log('Final derived province code:', derivedProvince);
                
                // Use derived province or fall back to selectedProvince
                const finalProvinceCode = derivedProvince || selectedProvince;
                console.log('Final province code to select:', finalProvinceCode);
                
                if (finalProvinceCode) {
                    // Try to find exact match first
                    let $matchedOption = $provinceSelect.find('option[value="' + finalProvinceCode + '"]');
                    
                    if ($matchedOption.length) {
                        $provinceSelect.val(finalProvinceCode);
                        console.log('Exact province match found:', finalProvinceCode);
                        
                        // Load cities for the selected province
                        loadStaffCities(finalProvinceCode, selectedCity);
                    } else {
                        console.log('Province not found:', finalProvinceCode, '- leaving dropdown unselected');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading staff provinces:', error);
                const $provinceSelect = $('#staffAddressProvince');
                $provinceSelect.empty();
                $provinceSelect.append('<option value="">Error loading provinces</option>');
                showStaffAddressError('Failed to load provinces. Please try again.');
            }
        });
    }
    
    // Load cities by province
    function loadStaffCities(provinceCode, selectedCity = '') {
        const $citySelect = $('#staffAddressCity');
        
        setLoadingState($citySelect, 'Loading cities...');
        
        $.ajax({
            url: '/get-address-cities',
            method: 'GET',
            data: { provinceCode: provinceCode },
            dataType: 'json',
            cache: false,
            success: function(cities) {
                $citySelect.empty();
                $citySelect.append('<option value="">Select City/Municipality</option>');
                
                if (cities.length === 0) {
                    $citySelect.append('<option value="">No cities available</option>');
                } else {
                    let selectedFound = false;
                    let bestMatch = null;
                    let bestMatchScore = 0;
                    
                    cities.forEach(function(city) {
                        const cityCode = String(city.code);
                        const selectedCityStr = String(selectedCity).toLowerCase().trim();
                        const cityName = String(city.name || city.description).toLowerCase().trim();
                        
                        // Calculate match score - enhanced for both codes and old text format
                        let score = 0;
                        
                        // Exact code match (highest priority)
                        if (cityCode === selectedCityStr) score = 100;
                        // Exact name match
                        else if (cityName === selectedCityStr) score = 95;
                        // Name contains search term (good for old format)
                        else if (cityName.includes(selectedCityStr)) score = 80;
                        // Search term contains name (good for partial matches)
                        else if (selectedCityStr.includes(cityName)) score = 60;
                        // Partial match (3+ characters) - enhanced for old format
                        else if (selectedCityStr.length >= 3 && cityName.includes(selectedCityStr.substring(0, 3))) score = 40;
                        // Special handling for common old format variations
                        else if (selectedCityStr.includes('city') && cityName.includes(selectedCityStr.replace('city', '').trim())) score = 75;
                        else if (selectedCityStr.includes('city of') && cityName.includes(selectedCityStr.replace('city of', '').trim())) score = 75;
                        
                        // Keep track of best match
                        if (score > bestMatchScore) {
                            bestMatchScore = score;
                            bestMatch = city;
                        }
                        
                        const isSelected = (cityCode === selectedCityStr) ? 'selected' : '';
                        
                        if (isSelected) {
                            selectedFound = true;
                        }
                        $citySelect.append(`<option value="${city.code}" ${isSelected}>${city.name || city.description}</option>`);
                    });
                    
                    // If no exact match found but we have a good match, select it
                    if (!selectedFound && bestMatch && bestMatchScore >= 60) {
                        $citySelect.val(bestMatch.code);
                        selectedFound = true;
                        console.log('Auto-selected best city match:', bestMatch.name, '(score:', bestMatchScore + ')');
                    }
                    
                    // Fallback: Explicitly set dropdown value after loading
                    if (selectedCity && !selectedFound) {
                        console.log('City not found:', selectedCity, '- leaving dropdown unselected');
                    }
                    
                    // If a city was selected, load its barangays
                    if (selectedFound) {
                        const selectedBarangayFromHidden = $('#oldStaffBarangay').val();
                        loadStaffBarangays($citySelect.val(), selectedBarangayFromHidden);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading staff cities:', error);
                $citySelect.empty();
                $citySelect.append('<option value="">Error loading cities</option>');
                showStaffAddressError('Failed to load cities. Please try again.');
            }
        });
    }
    
    // Load barangays by city
    function loadStaffBarangays(cityCode, selectedBarangay = '') {
        const $barangaySelect = $('#staffAddressBarangay');
        
        setLoadingState($barangaySelect, 'Loading barangays...');
        
        $.ajax({
            url: '/get-address-barangays',
            method: 'GET',
            data: { cityCode: cityCode },
            dataType: 'json',
            cache: false,
            success: function(barangays) {
                $barangaySelect.empty();
                $barangaySelect.append('<option value="">Select Barangay</option>');
                
                if (barangays.length === 0) {
                    $barangaySelect.append('<option value="">No barangays available</option>');
                } else {
                    let selectedFound = false;
                    let bestMatch = null;
                    let bestMatchScore = 0;
                    
                    barangays.forEach(function(barangay) {
                        const barangayCode = String(barangay.code);
                        const selectedBarangayStr = String(selectedBarangay).toLowerCase().trim();
                        const barangayName = String(barangay.name || barangay.description).toLowerCase().trim();
                        
                        // Calculate match score - enhanced for both codes and old text format
                        let score = 0;
                        
                        // Exact code match (highest priority)
                        if (barangayCode === selectedBarangayStr) score = 100;
                        // Exact name match
                        else if (barangayName === selectedBarangayStr) score = 95;
                        // Name contains search term (good for old format)
                        else if (barangayName.includes(selectedBarangayStr)) score = 80;
                        // Search term contains name (good for partial matches)
                        else if (selectedBarangayStr.includes(barangayName)) score = 60;
                        // Partial match (3+ characters) - enhanced for old format
                        else if (selectedBarangayStr.length >= 3 && barangayName.includes(selectedBarangayStr.substring(0, 3))) score = 40;
                        // Special handling for common old format variations
                        else if (selectedBarangayStr.includes('pob') && barangayName.includes('pob.')) score = 75;
                        else if (selectedBarangayStr.includes('poblacion') && barangayName.includes('pob.')) score = 75;
                        
                        // Keep track of best match
                        if (score > bestMatchScore) {
                            bestMatchScore = score;
                            bestMatch = barangay;
                        }
                        
                        const isSelected = (selectedBarangayStr === barangayCode) ? 'selected' : '';
                        
                        if (isSelected) {
                            selectedFound = true;
                        }
                        $barangaySelect.append(`<option value="${barangay.code}" ${isSelected}>${barangay.name || barangay.description}</option>`);
                    });
                    
                    // If no exact match found but we have a good match, select it
                    if (!selectedFound && bestMatch && bestMatchScore >= 60) {
                        $barangaySelect.val(bestMatch.code);
                        selectedFound = true;
                        console.log('Auto-selected best barangay match:', bestMatch.name || bestMatch.description, '(score:', bestMatchScore + ')');
                    }
                    
                    // Fallback: Explicitly set dropdown value after loading
                    if (selectedBarangay && !selectedFound) {
                        console.log('Barangay not found:', selectedBarangay, '- leaving dropdown unselected');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading staff barangays:', error);
                $barangaySelect.empty();
                $barangaySelect.append('<option value="">Error loading barangays</option>');
                showStaffAddressError('Failed to load barangays. Please try again.');
            }
        });
    }
    
    // Helper function to sync hidden backup zipcode field
    function syncZipcodeBackup(value) {
        const $backupField = $('#staff_zipcode_backup');
        if ($backupField.length > 0) {
            $backupField.val(value);
            console.log('Synced zipcode backup field with value:', value);
        }
    }
    
    // Initialize zipcode state based on existing value
    function initializeZipcodeState() {
        const $zipcodeInput = $('#staffZipcode');
        const $editBtn = $('#editZipcodeBtn');
        
        // Debug: Check if element exists and get its value
        console.log('Zipcode initialization debug:');
        console.log('- Zipcode input exists:', $zipcodeInput.length > 0);
        console.log('- Initial value from HTML:', $zipcodeInput.val());
        console.log('- Trimmed value:', $zipcodeInput.val().trim());
        
        const existingZipcode = $zipcodeInput.val().trim();
        
        if (existingZipcode && existingZipcode !== '' && existingZipcode !== 'Loading...') {
            // If there's an existing zipcode, make it readonly and show edit button
            $zipcodeInput.prop('readonly', true);
            $zipcodeInput.removeClass('bg-white').addClass('bg-gray-100');
            $zipcodeInput.attr('title', 'Existing zipcode. Click the edit button to modify manually.');
            $editBtn.show();
            console.log('✓ Zipcode initialized as readonly with existing value:', existingZipcode);
        } else {
            // If no existing zipcode, allow manual input and hide edit button
            $zipcodeInput.prop('readonly', false);
            $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
            $zipcodeInput.attr('placeholder', 'Enter zipcode manually or select a city to auto-fill');
            $zipcodeInput.attr('title', 'Enter zipcode manually or select a city to auto-fill if available');
            $editBtn.hide();
            console.log('✓ Zipcode initialized as editable (no existing value)');
        }
        
        // Double-check the value after setting states
        console.log('- Final zipcode value after init:', $zipcodeInput.val());
        console.log('- Is readonly:', $zipcodeInput.prop('readonly'));
    }

    // Smart zipcode loader - auto-fill when available, allow manual input when not
    function loadStaffZipcode(cityCode) {
        const $zipcodeInput = $('#staffZipcode');
        const $editBtn = $('#editZipcodeBtn');
        
        // Check if there's already an existing zipcode that should be preserved
        const existingZipcode = $zipcodeInput.val().trim();
        const originalZipcode = window.originalStaffZipcode || '';
        
        console.log('loadStaffZipcode called with:', {
            cityCode: cityCode,
            existingValue: existingZipcode,
            originalValue: originalZipcode,
            shouldPreserve: originalZipcode && originalZipcode !== '' && originalZipcode !== 'Loading...'
        });
        
        // If there's an original zipcode and it's not empty, preserve it
        if (originalZipcode && originalZipcode !== '' && originalZipcode !== 'Loading...') {
            console.log('Preserving existing zipcode:', originalZipcode);
            $zipcodeInput.val(originalZipcode);
            $zipcodeInput.prop('readonly', true);
            $zipcodeInput.removeClass('bg-white').addClass('bg-gray-100');
            $zipcodeInput.attr('title', 'Existing zipcode. Click the edit button to modify manually.');
            $editBtn.show();
            return; // Don't make AJAX call if we're preserving existing value
        }
        
        // Show loading state
        $zipcodeInput.val('Loading...');
        $zipcodeInput.prop('readonly', true);
        $zipcodeInput.removeClass('bg-white bg-gray-100').addClass('bg-gray-100');
        $editBtn.hide();
        
        $.ajax({
            url: '/get-address-zipcode',
            method: 'GET',
            data: { cityName: cityCode },
            dataType: 'json',
            cache: false,
            success: function(response) {
                if (response.zipcode && response.zipcode.trim() !== '') {
                    // Zipcode is available in database - auto-fill and make readonly
                    $zipcodeInput.val(response.zipcode);
                    $zipcodeInput.prop('readonly', true);
                    $zipcodeInput.removeClass('bg-white').addClass('bg-gray-100');
                    $zipcodeInput.attr('title', 'Zipcode is pre-defined for this city. Click the edit button to modify manually.');
                    $editBtn.show();
                    console.log('Auto-filled staff zipcode:', response.zipcode, 'for city:', response.city_name || cityCode);
                } else {
                    // No zipcode in database - allow manual input
                    $zipcodeInput.val('');
                    $zipcodeInput.prop('readonly', false);
                    $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
                    $zipcodeInput.attr('placeholder', 'Enter zipcode manually');
                    $zipcodeInput.attr('title', 'No pre-defined zipcode available. Please enter manually.');
                    $editBtn.hide();
                    console.log('No zipcode found for staff city:', response.city_name || cityCode, '- manual input enabled');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading staff zipcode:', error);
                // On error, allow manual input
                $zipcodeInput.val('');
                $zipcodeInput.prop('readonly', false);
                $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
                $zipcodeInput.attr('placeholder', 'Enter zipcode manually');
                $zipcodeInput.attr('title', 'Unable to load zipcode data. Please enter manually.');
                $editBtn.hide();
            }
        });
    }
    
    // Edit zipcode button click handler
    $('#editZipcodeBtn').on('click', function() {
        const $zipcodeInput = $('#staffZipcode');
        const $editBtn = $(this);
        
        // Enable manual editing
        $zipcodeInput.prop('readonly', false);
        $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
        $zipcodeInput.attr('title', 'Zipcode is now editable. Enter a new zipcode or select a different city to auto-fill.');
        $zipcodeInput.focus();
        $editBtn.hide();
        
        console.log('Zipcode manually enabled for editing');
    });
    
    // Helper function to clear dropdown
    function clearDropdown(selector, placeholder) {
        $(selector).empty().append(`<option value="">${placeholder}</option>`);
    }
    
    // Helper function to set loading state
    function setLoadingState($select, message) {
        $select.empty().append(`<option value="">${message}</option>`);
        $select.prop('disabled', true);
        
        // Re-enable after a short delay
        setTimeout(() => {
            $select.prop('disabled', false);
        }, 500);
    }
    
    // Helper function to show error messages
    function showStaffAddressError(message) {
        // Create or update error alert
        let $errorAlert = $('#staff-address-error-alert');
        
        if ($errorAlert.length === 0) {
            $errorAlert = $(`
                <div id="staff-address-error-alert" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700" id="staff-address-error-message"></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button onclick="$('#staff-address-error-alert').fadeOut()" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-400 hover:bg-red-100">
                                    <span class="sr-only">Dismiss</span>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            // Insert before the staff address section
            $('.staff-address-information').prepend($errorAlert);
        }
        
        $('#staff-address-error-message').text(message);
        $errorAlert.fadeIn();
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $errorAlert.fadeOut();
        }, 5000);
    }
    
    // Staff address validation function
    function validateStaffAddressSelection() {
        const region = $('#staffAddressRegion').val();
        const province = $('#staffAddressProvince').val();
        const city = $('#staffAddressCity').val();
        const barangay = $('#staffAddressBarangay').val();
        
        const errors = [];
        
        if (!region) errors.push('Region is required');
        if (!province) errors.push('Province is required');
        if (!city) errors.push('City/Municipality is required');
        if (!barangay) errors.push('Barangay is required');
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    // Export validation function for use in form submission
    window.validateStaffAddressSelection = validateStaffAddressSelection;
    
    // Add form validation on submit
    $('form[action="/submit-staff-insert"]').on('submit', function(e) {
        const validation = validateStaffAddressSelection();
        
        if (!validation.isValid) {
            e.preventDefault();
            showStaffAddressError('Please complete all staff address fields: ' + validation.errors.join(', '));
            
            // Scroll to address section
            $('html, body').animate({
                scrollTop: $('.staff-address-information').offset().top - 100
            }, 500);
            
            return false;
        }
    });
    
    // Fix corrupted characters in staff address dropdowns after loading
    function fixStaffAddressOptions() {
        ['#staffAddressRegion', '#staffAddressProvince', '#staffAddressCity', '#staffAddressBarangay'].forEach(function(selector) {
            // Check if fixSelectOptions function is available (from encoding-fix.js)
            if (typeof fixSelectOptions === 'function') {
                fixSelectOptions(selector);
            }
        });
    }
    
    // Call fix function after each AJAX load
    $(document).ajaxComplete(function() {
        fixStaffAddressOptions();
    });
});

// Staff address search functionality (for future enhancement)
function searchStaffAddresses(query, type = 'all') {
    return $.ajax({
        url: '/search-addresses',
        method: 'GET',
        data: { 
            search: query,
            type: type
        },
        dataType: 'json',
        cache: false
    });
}
