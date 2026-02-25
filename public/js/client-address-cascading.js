/* 
 * Client Address Cascading System - TBLADDRESS Integration
 * 4-level cascading: Region → Province → City → Barangay
 * Uses the new Philippine address reference database
 */

$(document).ready(function() {
    
    // Initialize address cascading system
    initializeAddressCascading();
    
    function initializeAddressCascading() {
        // Get stored values for form reloading - use hidden inputs for reliable access
        const selectedRegion = $('#addressRegion').val() || $('#oldAddressRegion').val();
        const selectedProvince = $('#oldProvince').val();
        const selectedCity = $('#oldCity').val();
        const selectedBarangay = $('#oldBarangay').val();
        
        console.log('Initializing address cascading with values:', {
            region: selectedRegion,
            province: selectedProvince,
            city: selectedCity,
            barangay: selectedBarangay
        });
        
        // Load initial cascading data if region is selected
        if (selectedRegion) {
            loadProvinces(selectedRegion, selectedProvince);
        }
        
        // Store original values for comparison
        window.originalAddressValues = {
            province: selectedProvince,
            city: selectedCity,
            barangay: selectedBarangay
        };
    }
    
    // Helper function to get old form values (simplified, no logging)
    function getOldValue(fieldName) {
        const element = $(`select[name="${fieldName}"], input[name="${fieldName}"]`);
        if (element.length && element.val()) {
            return element.val();
        }
        
        const hiddenInput = $(`input[type="hidden"][name="${fieldName}"]`);
        if (hiddenInput.length && hiddenInput.val()) {
            return hiddenInput.val();
        }
        
        return '';
    }
    
    // Region change handler
    $('#addressRegion').on('change', function() {
        const regionCode = $(this).val();
        
        // Clear dependent dropdowns
        clearDropdown('#addressProvince', 'Select Province');
        clearDropdown('#addressCity', 'Select City/Municipality');
        clearDropdown('#addressBarangay', 'Select Barangay');
        
        if (regionCode) {
            loadProvinces(regionCode);
        }
    });
    
    // Province change handler
    $('#addressProvince').on('change', function() {
        const provinceCode = $(this).val();
        
        // Clear dependent dropdowns
        clearDropdown('#addressCity', 'Select City/Municipality');
        clearDropdown('#addressBarangay', 'Select Barangay');
        
        if (provinceCode) {
            loadCities(provinceCode);
        }
    });
    
    // City change handler
    $('#addressCity').on('change', function() {
        const cityCode = $(this).val();
        
        // Clear dependent dropdown
        clearDropdown('#addressBarangay', 'Select Barangay');
        
        if (cityCode) {
            loadBarangays(cityCode);
            loadZipcode(cityCode); // Auto-load zipcode when city is selected
        } else {
            // Clear zipcode if no city selected and reset to manual input
            const $zipcodeInput = $('#zipcode');
            $zipcodeInput.val('');
            $zipcodeInput.prop('readonly', false);
            $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
            $zipcodeInput.attr('placeholder', 'Select city first');
            $zipcodeInput.attr('title', 'Select a city to auto-fill zipcode or enable manual input');
        }
    });
    
    // Load provinces by region
    function loadProvinces(regionCode, selectedProvince = '') {
        const $provinceSelect = $('#addressProvince');
        
        setLoadingState($provinceSelect, 'Loading provinces...');
        
        $.ajax({
            url: '/get-address-provinces',
            method: 'GET',
            data: { regionCode: regionCode },
            dataType: 'json',
            cache: false,
            success: function(provinces) {
                $provinceSelect.empty();
                $provinceSelect.append('<option value="">Select Province</option>');
                
                if (provinces.length === 0) {
                    $provinceSelect.append('<option value="">No provinces available</option>');
                } else {
                    let selectedFound = false;
                    let bestMatch = null;
                    let bestMatchScore = 0;
                    
                    provinces.forEach(function(province) {
                        const provinceCode = String(province.code);
                        const selectedProvinceStr = String(selectedProvince).toLowerCase().trim();
                        const provinceName = String(province.name).toLowerCase().trim();
                        
                        // Calculate match score
                        let score = 0;
                        if (provinceName === selectedProvinceStr) score = 100;
                        else if (provinceName.includes(selectedProvinceStr)) score = 80;
                        else if (selectedProvinceStr.includes(provinceName)) score = 60;
                        else if (selectedProvinceStr.length >= 3 && provinceName.includes(selectedProvinceStr.substring(0, 3))) score = 40;
                        
                        // Keep track of best match
                        if (score > bestMatchScore) {
                            bestMatchScore = score;
                            bestMatch = province;
                        }
                        
                        const isSelected = (selectedProvinceStr === provinceCode) ? 'selected' : '';
                        
                        if (isSelected) {
                            selectedFound = true;
                        }
                        $provinceSelect.append(`<option value="${province.code}" ${isSelected}>${province.name}</option>`);
                    });
                    
                    // If no exact match found but we have a good match, select it
                    if (!selectedFound && bestMatch && bestMatchScore >= 60) {
                        $provinceSelect.val(bestMatch.code);
                        selectedFound = true;
                        console.log('Auto-selected best province match:', bestMatch.name, '(score:', bestMatchScore + ')');
                    }
                    
                    // Fallback: Explicitly set dropdown value after loading
                    if (selectedProvince && !selectedFound) {
                        console.log('Province not found:', selectedProvince, '- leaving dropdown unselected');
                    }
                    
                    // If a province was selected, load its cities
                    if (selectedFound) {
                        const selectedCityFromHidden = $('#oldCity').val();
                        loadCities($provinceSelect.val(), selectedCityFromHidden);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading provinces:', error);
                $provinceSelect.empty();
                $provinceSelect.append('<option value="">Error loading provinces</option>');
                showAddressError('Failed to load provinces. Please try again.');
            }
        });
    }
    
    // Load cities by province
    function loadCities(provinceCode, selectedCity = '') {
        const $citySelect = $('#addressCity');
        
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
                        const cityName = String(city.name).toLowerCase().trim();
                        
                        // Calculate match score
                        let score = 0;
                        if (cityName === selectedCityStr) score = 100;
                        else if (cityName.includes(selectedCityStr)) score = 80;
                        else if (selectedCityStr.includes(cityName)) score = 60;
                        else if (selectedCityStr.length >= 3 && cityName.includes(selectedCityStr.substring(0, 3))) score = 40;
                        
                        // Keep track of best match
                        if (score > bestMatchScore) {
                            bestMatchScore = score;
                            bestMatch = city;
                        }
                        
                        const isSelected = (cityCode === selectedCityStr) ? 'selected' : '';
                        
                        if (isSelected) {
                            selectedFound = true;
                        }
                        $citySelect.append(`<option value="${city.code}" ${isSelected}>${city.name}</option>`);
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
                        const selectedBarangayFromHidden = $('#oldBarangay').val();
                        loadBarangays($citySelect.val(), selectedBarangayFromHidden);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading cities:', error);
                $citySelect.empty();
                $citySelect.append('<option value="">Error loading cities</option>');
                showAddressError('Failed to load cities. Please try again.');
            }
        });
    }
    
    // Load barangays by city
    function loadBarangays(cityCode, selectedBarangay = '') {
        const $barangaySelect = $('#addressBarangay');
        
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
                        const barangayName = String(barangay.name).toLowerCase().trim();
                        
                        // Calculate match score
                        let score = 0;
                        if (barangayName === selectedBarangayStr) score = 100;
                        else if (barangayName.includes(selectedBarangayStr)) score = 80;
                        else if (selectedBarangayStr.includes(barangayName)) score = 60;
                        else if (selectedBarangayStr.length >= 3 && barangayName.includes(selectedBarangayStr.substring(0, 3))) score = 40;
                        
                        // Keep track of best match
                        if (score > bestMatchScore) {
                            bestMatchScore = score;
                            bestMatch = barangay;
                        }
                        
                        const isSelected = (selectedBarangayStr === barangayCode) ? 'selected' : '';
                        
                        if (isSelected) {
                            selectedFound = true;
                        }
                        $barangaySelect.append(`<option value="${barangay.code}" ${isSelected}>${barangay.name}</option>`);
                    });
                    
                    // If no exact match found but we have a good match, select it
                    if (!selectedFound && bestMatch && bestMatchScore >= 60) {
                        $barangaySelect.val(bestMatch.code);
                        selectedFound = true;
                        console.log('Auto-selected best barangay match:', bestMatch.name, '(score:', bestMatchScore + ')');
                    }
                    
                    // Fallback: Explicitly set dropdown value after loading
                    if (selectedBarangay && !selectedFound) {
                        console.log('Barangay not found:', selectedBarangay, '- leaving dropdown unselected');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading barangays:', error);
                $barangaySelect.empty();
                $barangaySelect.append('<option value="">Error loading barangays</option>');
                showAddressError('Failed to load barangays. Please try again.');
            }
        });
    }
    
    // Smart zipcode loader - auto-fill when available, allow manual input when not
    function loadZipcode(cityCode) {
        const $zipcodeInput = $('#zipcode');
        
        // Show loading state
        $zipcodeInput.val('Loading...');
        $zipcodeInput.prop('readonly', true);
        $zipcodeInput.removeClass('bg-white bg-gray-100').addClass('bg-gray-100');
        
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
                    $zipcodeInput.attr('title', 'Zipcode is pre-defined for this city and cannot be changed');
                    console.log('Auto-filled zipcode:', response.zipcode, 'for city:', response.city_name || cityCode);
                } else {
                    // No zipcode in database - allow manual input
                    $zipcodeInput.val('');
                    $zipcodeInput.prop('readonly', false);
                    $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
                    $zipcodeInput.attr('placeholder', 'Enter zipcode manually');
                    $zipcodeInput.attr('title', 'No pre-defined zipcode available. Please enter manually.');
                    console.log('No zipcode found for city:', response.city_name || cityCode, '- manual input enabled');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading zipcode:', error);
                // On error, allow manual input
                $zipcodeInput.val('');
                $zipcodeInput.prop('readonly', false);
                $zipcodeInput.removeClass('bg-gray-100').addClass('bg-white');
                $zipcodeInput.attr('placeholder', 'Enter zipcode manually');
                $zipcodeInput.attr('title', 'Unable to load zipcode data. Please enter manually.');
            }
        });
    }
    
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
    function showAddressError(message) {
        // Create or update error alert
        let $errorAlert = $('#address-error-alert');
        
        if ($errorAlert.length === 0) {
            $errorAlert = $(`
                <div id="address-error-alert" class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700" id="address-error-message"></p>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button onclick="$('#address-error-alert').fadeOut()" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-400 hover:bg-red-100">
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
            
            // Insert before the address section
            $('.address-information').prepend($errorAlert);
        }
        
        $('#address-error-message').text(message);
        $errorAlert.fadeIn();
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $errorAlert.fadeOut();
        }, 5000);
    }
    
    // Address validation function
    function validateAddressSelection() {
        const region = $('#addressRegion').val();
        const province = $('#addressProvince').val();
        const city = $('#addressCity').val();
        const barangay = $('#addressBarangay').val();
        
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
    window.validateAddressSelection = validateAddressSelection;
    
    // Add form validation on submit
    $('#clientForm').on('submit', function(e) {
        const validation = validateAddressSelection();
        
        if (!validation.isValid) {
            e.preventDefault();
            showAddressError('Please complete all address fields: ' + validation.errors.join(', '));
            
            // Scroll to address section
            $('html, body').animate({
                scrollTop: $('.address-information').offset().top - 100
            }, 500);
            
            return false;
        }
    });
    
    // Fix corrupted characters in address dropdowns after loading
    function fixAddressOptions() {
        ['#addressRegion', '#addressProvince', '#addressCity', '#addressBarangay'].forEach(function(selector) {
            // Check if fixSelectOptions function is available (from encoding-fix.js)
            if (typeof fixSelectOptions === 'function') {
                fixSelectOptions(selector);
            }
        });
    }
    
    // Call fix function after each AJAX load
    $(document).ajaxComplete(function() {
        fixAddressOptions();
    });
});
// Address search functionality (for future enhancement)
function searchAddresses(query, type = 'all') {
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
  