// Add this script to your reports page to debug PDF export
// You can add this to the bottom of resources/views/pages/reports/report.blade.php

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== PDF Export Debug Script Loaded ===');
    
    // Find the PDF export button
    const pdfButton = document.querySelector('button[formaction*="reports-daily-pdf"]');
    
    if (pdfButton) {
        console.log('PDF Export button found:', pdfButton);
        
        // Add click listener to log form data
        pdfButton.addEventListener('click', function(e) {
            console.log('=== PDF Export Button Clicked ===');
            
            // Get the form
            const form = pdfButton.closest('form');
            if (form) {
                console.log('Form found:', form);
                
                // Get all form data
                const formData = new FormData(form);
                console.log('Form data being submitted:');
                
                for (let [key, value] of formData.entries()) {
                    console.log('  ' + key + ': ' + value);
                }
                
                // Log specific important fields
                const reportType = formData.get('dailyreporttype');
                const branch = formData.get('dailybranch');
                const mcpr = formData.get('dailymcpr');
                
                console.log('=== Key Parameters ===');
                console.log('Report Type:', reportType);
                console.log('Branch ID:', branch);
                console.log('MCPR ID:', mcpr);
                
                // Check if values are valid
                if (!reportType || reportType === '0') {
                    console.error('ERROR: Report type not selected or invalid');
                }
                if (!branch || branch === '0') {
                    console.error('ERROR: Branch not selected or invalid');
                }
                if (!mcpr || mcpr === '0') {
                    console.error('ERROR: MCPR period not selected or invalid');
                }
                
                // Log the form action
                console.log('Form action:', form.getAttribute('action'));
                console.log('PDF button formaction:', pdfButton.getAttribute('formaction'));
            } else {
                console.error('ERROR: Could not find form for PDF export');
            }
            
            console.log('=== End PDF Export Debug ===');
        });
    } else {
        console.error('ERROR: PDF Export button not found');
        console.log('Looking for buttons with formaction containing pdf...');
        const allButtons = document.querySelectorAll('button[formaction]');
        allButtons.forEach(function(btn, index) {
            console.log('Button ' + index + ':', btn.getAttribute('formaction'));
        });
    }
    
    // Monitor AJAX requests
    const originalFetch = window.fetch;
    window.fetch = function() {
        const url = arguments[0];
        if (typeof url === 'string' && url.includes('reports-daily-pdf')) {
            console.log('=== PDF Export AJAX Request ===');
            console.log('URL:', url);
            console.log('Method:', arguments[1] ? arguments[1].method : 'GET');
            if (arguments[1] && arguments[1].body) {
                console.log('Body:', arguments[1].body);
            }
        }
        return originalFetch.apply(this, arguments);
    };
    
    console.log('=== Debug Script Ready ===');
});
