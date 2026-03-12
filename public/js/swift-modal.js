/**
 * Swift-Style Modal API
 * Global modal functions for entire application
 * Usage: showSwiftModal(title, message, type, buttons)
 */

// Show Swift-Style Modal
function showSwiftModal(title, message, type = 'error', buttons = []) {
    const modal = document.getElementById('swiftModal');
    const iconDiv = document.getElementById('swiftModalIcon');
    const titleEl = document.getElementById('swiftModalTitle');
    const messageEl = document.getElementById('swiftModalMessage');
    const actionsEl = document.getElementById('swiftModalActions');

    if (!modal || !iconDiv || !titleEl || !messageEl || !actionsEl) {
        return;
    }

    // Auto-detect type from title if not explicitly set or set to default 'error'
    // This fixes missing type parameter in blade templates
    if (type === 'error' || !type) {
        const titleLower = title.toLowerCase();
        if (titleLower.includes('success')) {
            type = 'success';
        } else if (titleLower.includes('warning') || titleLower.includes('confirm')) {
            type = 'warning';
        } else if (titleLower.includes('error') || titleLower.includes('failed') || titleLower.includes('reject')) {
            type = 'error';
        }
    }

    // Set icon based on type
    const icons = {
        success: `<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>`,
        error: `<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>`,
        warning: `<svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>`
    };

    // Set icon background color
    const iconBgColors = {
        success: 'bg-green-100',
        error: 'bg-red-100',
        warning: 'bg-yellow-100'
    };

    // Apply icon
    iconDiv.innerHTML = icons[type] || icons.error;
    iconDiv.className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${iconBgColors[type] || iconBgColors.error}`;

    // Set content
    titleEl.textContent = title;
    // Support both plain text (with \n) and HTML content
    if (message && (message.includes('<') || message.includes('&lt;'))) {
        messageEl.innerHTML = message;
    } else {
        messageEl.textContent = message;
    }

    // Set buttons
    actionsEl.innerHTML = '';

    const runAction = (action) => {
        console.log('=== runAction called ===');
        console.log('Action type:', typeof action);
        console.log('Action value:', action);

        if (!action) {
            console.log('No action provided, returning');
            return;
        }
        if (typeof action === 'function') {
            console.log('Action is a function, executing...');
            action();
            return;
        }

        if (typeof action === 'string') {
            console.log('Action is a string, executing with eval:', action);
            try {
                eval(action);
                console.log('Action executed successfully');
            } catch (e) {
                console.error('Action execution error:', e);
            }
        }
    };

    const addButton = (btn) => {
        console.log('Adding button:', btn.text, 'Action:', btn.action);
        const el = document.createElement('button');
        el.type = 'button';
        el.className = `w-full py-3 px-6 ${btn.class || ''} font-semibold rounded-xl transition duration-200`;
        el.textContent = btn.text || 'OK';
        el.addEventListener('click', () => {
            console.log('=== Button clicked:', btn.text, '===');
            runAction(btn.action);
            hideSwiftModal();
        });
        actionsEl.appendChild(el);
    };

    if (!buttons || buttons.length === 0) {
        // Default button styling based on modal type
        const defaultButtonClasses = {
            success: 'bg-green-500 hover:bg-green-600 text-white',
            error: 'bg-red-500 hover:bg-red-600 text-white',
            warning: 'bg-yellow-500 hover:bg-yellow-600 text-white'
        };
        addButton({
            text: 'OK',
            class: defaultButtonClasses[type] || 'bg-gray-100 hover:bg-gray-200 text-gray-800',
            action: null
        });
    } else {
        buttons.forEach(addButton);
    }

    // Show modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Hide Swift-Style Modal
function hideSwiftModal() {
    const modal = document.getElementById('swiftModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// Close modal on backdrop click
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('swiftModal');
    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                hideSwiftModal();
            }
        });
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        hideSwiftModal();
    }
});
