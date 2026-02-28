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

    // Set icon based on type
    const icons = {
        success: `<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
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
    messageEl.textContent = message;

    // Set buttons
    actionsEl.innerHTML = '';

    const runAction = (action) => {
        if (!action) return;
        if (typeof action === 'function') {
            action();
            return;
        }

        if (typeof action === 'string') {
            // Execute in global scope; avoids inline HTML/attribute quoting issues.
            Function(action)();
        }
    };

    const addButton = (btn) => {
        const el = document.createElement('button');
        el.type = 'button';
        el.className = `w-full py-3 px-6 ${btn.class || ''} font-semibold rounded-xl transition duration-200`;
        el.textContent = btn.text || 'OK';
        el.addEventListener('click', () => {
            runAction(btn.action);
            hideSwiftModal();
        });
        actionsEl.appendChild(el);
    };

    if (!buttons || buttons.length === 0) {
        addButton({
            text: 'OK',
            class: 'bg-gray-100 hover:bg-gray-200 text-gray-800',
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
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('swiftModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideSwiftModal();
            }
        });
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideSwiftModal();
    }
});
