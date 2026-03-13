<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SLC Admin Panel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- DataTables -->
    <link href="{{ asset('DataTables/datatables.min.css') }}" rel="stylesheet">

    <!-- FSA Rankings Custom Styles -->
    <link href="{{ asset('css/fsa-rankings.css') }}" rel="stylesheet">

    <!-- Enhanced Button Styles -->
    <link href="{{ asset('css/enhanced-buttons.css') }}" rel="stylesheet">

    <!-- Loading Spinner -->
    <link href="{{ asset('css/spinner.css') }}" rel="stylesheet">

    <!-- Font Size Control -->
    <link href="{{ asset('css/font-size-control.css') }}" rel="stylesheet">

    <!-- Page Specific Styles -->
    @yield('styles')
</head>

<body class="bg-gray-50 min-h-screen">
    <div>
        @include(
            'layouts.includes.navbar',
            [
                'roles' => app(\App\Http\Controllers\RoleController::class)
                    ->checkRolePrivilege(request()),
                'menus' => app(\App\Http\Controllers\MenuController::class)
                    ->checkMenuPrivilege(request())
            ]
        )

        @yield('content')

<!-- SCRIPTS -->
        <script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('DataTables/datatables.min.js') }}"></script>
        <script>
            /* ── Global DataTables spinner override ────────────────────
               Applied once here so every DataTable on every page uses
               the premium theme-matched spinner automatically.        */
            if (typeof $.fn.dataTable !== 'undefined') {
                var _slcSpinner = '<div class="slc-spinner-card">' +
                    '<div class="slc-rings">' +
                        '<div class="slc-ring-outer"></div>' +
                        '<div class="slc-ring-mid"></div>' +
                        '<div class="slc-ring-dot"></div>' +
                    '</div>' +
                    '<div class="slc-label">Loading' +
                        '<div class="slc-dots"><span></span><span></span><span></span></div>' +
                    '</div>' +
                    '<div class="slc-bar"></div>' +
                '</div>';
                $.extend(true, $.fn.dataTable.defaults, {
                    language: { processing: _slcSpinner }
                });
            }
        </script>
        <script src="{{ asset('js/chart.js') }}"></script>
        
    <!-- UTF-8 Encoding Fix -->
        <script src="{{ asset('js/encoding-fix.js') }}"></script>
        
        <!-- Swift-Style Modal Component - Available on all pages -->
        @include('components.swift-modal')
        <script src="{{ asset('js/swift-modal.js') }}"></script>

        @stack('scripts')
        @yield('scripts')
    </div>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
       
         &copy; 2022-2024 Surelife Care and Services ver {{ config('app.version') }} :: 
                <a href="/home" class="text-primary-600 hover:text-primary-700 font-medium">Changelog</a>
            </p>
        </div>
    </footer>
</body>
</html>