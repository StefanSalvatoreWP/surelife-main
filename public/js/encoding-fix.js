/* UTF-8 Encoding Fix - Global Helper
 * This script fixes double-encoded UTF-8 characters that appear in the frontend
 * Common issue: Database stores UTF-8 correctly but displays as mojibake (e.g., Ã± instead of ñ)
 */

// Function to fix corrupted UTF-8 characters
function fixCorruptedText(text) {
    if (!text) return text;
    
    // Fix double-encoded UTF-8 characters using Unicode escape sequences
    let fixedText = text;
    
    // Spanish/Filipino special characters
    fixedText = fixedText.replace(/\u00C3\u00B1/g, '\u00F1'); // ñ
    fixedText = fixedText.replace(/\u00C3\u0091/g, '\u00D1'); // Ñ
    fixedText = fixedText.replace(/\u00C3\u00A1/g, '\u00E1'); // á
    fixedText = fixedText.replace(/\u00C3\u00A9/g, '\u00E9'); // é
    fixedText = fixedText.replace(/\u00C3\u00AD/g, '\u00ED'); // í
    fixedText = fixedText.replace(/\u00C3\u00B3/g, '\u00F3'); // ó
    fixedText = fixedText.replace(/\u00C3\u00BA/g, '\u00FA'); // ú
    fixedText = fixedText.replace(/\u00C3\u0081/g, '\u00C1'); // Á
    fixedText = fixedText.replace(/\u00C3\u0089/g, '\u00C9'); // É
    fixedText = fixedText.replace(/\u00C3\u008D/g, '\u00CD'); // Í
    fixedText = fixedText.replace(/\u00C3\u0093/g, '\u00D3'); // Ó
    fixedText = fixedText.replace(/\u00C3\u009A/g, '\u00DA'); // Ú
    fixedText = fixedText.replace(/\u00C3\u00BC/g, '\u00FC'); // ü
    fixedText = fixedText.replace(/\u00C3\u009C/g, '\u00DC'); // Ü
    
    return fixedText;
}

// Function to fix all select options in a dropdown
function fixSelectOptions(selectElement) {
    $(selectElement).find('option').each(function() {
        const originalText = $(this).text();
        const fixedText = fixCorruptedText(originalText);
        if (originalText !== fixedText) {
            $(this).text(fixedText);
        }
    });
}

// Function to fix all text content in an element
function fixElementText(element) {
    $(element).contents().filter(function() {
        return this.nodeType === 3; // Text nodes only
    }).each(function() {
        const originalText = this.nodeValue;
        const fixedText = fixCorruptedText(originalText);
        if (originalText !== fixedText) {
            this.nodeValue = fixedText;
        }
    });
}

// Auto-fix common elements on page load
$(document).ready(function() {
    // Fix all select dropdowns
    $('select').each(function() {
        fixSelectOptions(this);
    });
    
    // Fix table cells (common in DataTables)
    $('td, th').each(function() {
        fixElementText(this);
    });
});
