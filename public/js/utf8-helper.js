/**
 * UTF-8 Helper Utility
 * Fixes common encoding issues with special characters like Ñ
 * 2024 - SureLife Network
 */

// Function to fix UTF-8 encoding issues
function fixUTF8(text) {
    if (!text) return text;
    
    // Common encoding fixes
    const replacements = {
        'Ã±': 'ñ',
        'Ã'': 'Ñ',
        'Ã©': 'é',
        'Ã': 'É',
        'Ã¡': 'á',
        'Ã': 'Á',
        'Ã­': 'í',
        'Ã': 'Í',
        'Ã³': 'ó',
        'Ã"': 'Ó',
        'Ãº': 'ú',
        'Ãš': 'Ú',
        'Ã¼': 'ü',
        'Ãœ': 'Ü'
    };
    
    let fixedText = text;
    for (const [wrong, correct] of Object.entries(replacements)) {
        fixedText = fixedText.replace(new RegExp(wrong, 'g'), correct);
    }
    
    return fixedText;
}

// jQuery extension to fix text in elements
if (typeof jQuery !== 'undefined') {
    $.fn.fixUTF8 = function() {
        return this.each(function() {
            const $this = $(this);
            
            // Fix text content
            if ($this.contents().length) {
                $this.contents().each(function() {
                    if (this.nodeType === 3) { // Text node
                        this.nodeValue = fixUTF8(this.nodeValue);
                    }
                });
            }
            
            // Fix option elements in select
            if ($this.is('select')) {
                $this.find('option').each(function() {
                    const $option = $(this);
                    $option.text(fixUTF8($option.text()));
                });
            }
            
            // Fix input values
            if ($this.is('input, textarea')) {
                $this.val(fixUTF8($this.val()));
            }
        });
    };
}

// Auto-fix function for AJAX responses
function autoFixAjaxResponse(data) {
    if (typeof data === 'string') {
        return fixUTF8(data);
    }
    
    if (Array.isArray(data)) {
        return data.map(item => autoFixAjaxResponse(item));
    }
    
    if (typeof data === 'object' && data !== null) {
        const fixed = {};
        for (const [key, value] of Object.entries(data)) {
            if (typeof value === 'string') {
                fixed[key] = fixUTF8(value);
            } else if (typeof value === 'object') {
                fixed[key] = autoFixAjaxResponse(value);
            } else {
                fixed[key] = value;
            }
        }
        return fixed;
    }
    
    return data;
}

// Setup global AJAX success handler (optional - for all AJAX calls)
function setupGlobalUTF8Fix() {
    if (typeof jQuery !== 'undefined') {
        $(document).ajaxSuccess(function(event, xhr, settings) {
            // Only fix if content-type is JSON
            const contentType = xhr.getResponseHeader('content-type');
            if (contentType && contentType.includes('json')) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    const fixed = autoFixAjaxResponse(response);
                    // Store fixed version for retrieval
                    xhr.fixedResponse = fixed;
                } catch (e) {
                    // Not valid JSON or already processed
                }
            }
        });
    }
}

// Export for use in modules (if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        fixUTF8,
        autoFixAjaxResponse,
        setupGlobalUTF8Fix
    };
}

// Make available globally
window.fixUTF8 = fixUTF8;
window.autoFixAjaxResponse = autoFixAjaxResponse;
window.setupGlobalUTF8Fix = setupGlobalUTF8Fix;
