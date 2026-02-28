{{--
    Swift-Style Modal Component
    Single reusable modal for entire application
    Usage: Include in master layout, call showSwiftModal() from any page
--}}

<!-- Swift-Style Modal Container -->
<div id="swiftModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm transition-opacity duration-300" aria-modal="true" role="dialog">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 transform scale-100 transition-transform duration-300 overflow-hidden">
        {{-- Header with Icon --}}
        <div id="swiftModalHeader" class="px-6 pt-6 pb-4 text-center">
            <div id="swiftModalIcon" class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                {{-- Icon SVG injected by JavaScript --}}
            </div>
            <h3 id="swiftModalTitle" class="text-xl font-semibold text-gray-900 mb-2"></h3>
        </div>
        
        {{-- Body with Message --}}
        <div id="swiftModalBody" class="px-6 pb-6 text-center">
            <p id="swiftModalMessage" class="text-gray-600 text-sm leading-relaxed"></p>
        </div>
        
        {{-- Action Buttons --}}
        <div id="swiftModalActions" class="px-6 pb-6 flex flex-col gap-2">
            {{-- Buttons injected by JavaScript --}}
        </div>
    </div>
</div>
