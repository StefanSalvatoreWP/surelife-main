<!-- Modern Tailwind Navbar -->
<nav class="bg-white border-b-2 border-primary-300 shadow-lg sticky top-0 z-[60]">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center space-x-3">
                @if(session('user_roleid') != 7)
                    <a href="/home" class="flex items-center space-x-3 group">
                @else
                    <a href="/clienthome/{{ session('user_id') }}" class="flex items-center space-x-3 group">
                @endif
                    <img src="{{ asset('images/Surelife.png')}}" alt="SLC Logo" class="h-10 w-10 sm:h-12 sm:w-12 object-contain transform group-hover:scale-110 transition duration-300">
                    <span class="text-primary-600 font-bold text-lg sm:text-xl tracking-tight">SLC Admin Panel</span>
                </a>
            </div>

            <!-- Right Side - Font Size, User Info & Logout -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Font Size Selector -->
                <div class="relative">
                    <button id="fontSizeToggle" class="flex items-center space-x-2 bg-primary-100 hover:bg-primary-200 text-primary-700 px-3 py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                        </svg>
                        <span class="hidden sm:inline font-medium">Font</span>
                    </button>
                    <div id="fontSizeDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-[70]">
                        <div class="py-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">Font Size</div>
                            <button onclick="changeFontSize('small')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition duration-150">
                                <span class="text-xs">Small (Default)</span>
                            </button>
                            <button onclick="changeFontSize('medium')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition duration-150">
                                <span class="text-sm">Medium</span>
                            </button>
                            <button onclick="changeFontSize('large')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition duration-150">
                                <span class="text-base">Large</span>
                            </button>
                            <button onclick="changeFontSize('xlarge')" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 transition duration-150">
                                <span class="text-lg">Extra Large</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-2 bg-gray-100 px-3 py-2 rounded-lg border border-gray-300">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-semibold text-gray-800">{{ session('user_name') }}</span>
                </div>
                <button onclick="document.getElementById('logoutModal').classList.remove('hidden')" 
                        class="flex items-center space-x-2 bg-red-500 hover:bg-red-600 text-white px-3 sm:px-4 py-2 rounded-lg transition duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline font-medium">Logout</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Navigation Menu -->
<div class="bg-white border-b border-gray-200 shadow-sm sticky top-16 z-50">
    <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-8" style="position: relative;">
  @if(session('user_roleid') != 7)
    @php
      $roleLevel = $roles[0]->level;
      
      // menus
      $dashboard = false;
      $fsaRankings = false;

      $manage_staff = false;
      $manage_client = false;
      $manage_branch = false;
      $manage_region = false;
      $manage_province = false;
      $manage_city = false;
      $manage_barangay = false;

      $docu_or = false;
      $docu_contract = false;
      $docu_deposits = false;
      $docu_expenses = false;
     
      $trans_viewpayments = false;
      $trans_viewloanpayments = false;

      $reports = false;

      $req_encashments = false;
      $req_loans = false;
      
      $sys_mcpr = false;
      $sys_bankaccounts = false;
      $sys_packages = false;
      $sys_expense_desc = false;
      $sys_notifications = false;

      $admin_settings = false;
      $admin_menuprivilege = false;
      $admin_actionprivilege = false;
      $admin_roles = false;

      foreach($menus as $m){

        if (str_contains($m, 'Dashboard') && $roleLevel <= $m->rolelevel) {
            $dashboard = true;
        }
        if (str_contains($m, 'FSA Rankings') && $roleLevel <= $m->rolelevel) {
            $fsaRankings = true;
        }

        // manage
        if (str_contains($m, 'Staff') && $roleLevel <= $m->rolelevel) {
            $manage_staff = true;
        }
        if (str_contains($m, 'Client') && $roleLevel <= $m->rolelevel) {
            $manage_client = true;
        }
        if (str_contains($m, 'Branch') && $roleLevel <= $m->rolelevel) {
            $manage_branch = true;
        }
        if (str_contains($m, 'Region') && $roleLevel <= $m->rolelevel) {
            $manage_region = true;
        }
        if (str_contains($m, 'Province') && $roleLevel <= $m->rolelevel) {
            $manage_province = true;
        }
        if (str_contains($m, 'City') && $roleLevel <= $m->rolelevel) {
            $manage_city = true;
        }
        if (str_contains($m, 'Barangay') && $roleLevel <= $m->rolelevel) {
            $manage_barangay = true;
        }

        // documents
        if (str_contains($m, 'Official Receipts') && $roleLevel <= $m->rolelevel) {
            $docu_or = true;
        }
        if (str_contains($m, 'Contracts') && $roleLevel <= $m->rolelevel) {
            $docu_contract = true;
        }
        if (str_contains($m, 'Deposits') && $roleLevel <= $m->rolelevel) {
            $docu_deposits = true;
        }
        if (str_contains($m, 'Expenses') && $roleLevel <= $m->rolelevel) {
            $docu_expenses = true;
        }
       
        // transactions
        if (str_contains($m, 'Payments') && $roleLevel <= $m->rolelevel) {
            $trans_viewpayments = true;
        }
        if(str_contains($m, 'Loan Payments') && $roleLevel <= $m->rolelevel) {
            $trans_viewloanpayments = true;
        }

        // reports
        if(str_contains($m, 'Reports') && $roleLevel <= $m->rolelevel){
          $reports = true;
        }

        // requests
        if(str_contains($m, 'Encashments') && $roleLevel <= $m->rolelevel){
          $req_encashments = true;
        }
        if (str_contains($m, 'Loans') && $roleLevel <= $m->rolelevel) {
          $req_loans = true;
        }

        // system
        if (str_contains($m, 'MCPR') && $roleLevel <= $m->rolelevel) {
            $sys_mcpr = true;
        }
        if (str_contains($m, 'Bank Accounts') && $roleLevel <= $m->rolelevel) {
            $sys_bankaccounts = true;
        }
        if (str_contains($m, 'Expense Description') && $roleLevel <= $m->rolelevel){
            $sys_expense_desc = true;
        }

        if (str_contains($m, 'Packages') && $roleLevel <= $m->rolelevel) {
            $sys_packages = true;
        }
        if (str_contains($m, 'Notifications') && $roleLevel <= $m->rolelevel) {
            $sys_notifications = true;
        }

        // admin settings
        if (str_contains($m, 'Admin Settings') && $roleLevel <= $m->rolelevel) {
            $admin_settings = true;
        }
        if (str_contains($m, 'Menu Privilege') && $roleLevel <= $m->rolelevel) {
            $admin_menuprivilege = true;
        }
        if (str_contains($m, 'Action Privilege') && $roleLevel <= $m->rolelevel) {
            $admin_actionprivilege = true;
        }
        if (str_contains($m, 'Roles') && $roleLevel <= $m->rolelevel) {
            $admin_roles = true;
        }
      }
    @endphp
    <div class="relative" style="overflow-x: auto; overflow-y: visible;">
    <nav class="flex space-x-1 py-2 scrollbar-hide" style="position: static;">
        @if($dashboard)
            <a href="/dashboard" class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </span>
        @endif
        
        @if($fsaRankings)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>FSA Rankings</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    <a href="/fsarankings-sales" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span>New Sales</span>
                        </div>
                    </a>
                    <a href="/fsarankings-collections" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Collections</span>
                        </div>
                    </a>
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>FSA Rankings</span>
            </span>
        @endif
        @if($manage_staff || $manage_client || $manage_branch || $manage_region || $manage_province || $manage_city || $manage_barangay)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Manage</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-48 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($manage_staff)
                        <a href="/staff" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Staff</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Staff</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_client)
                        <a href="/client" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Client</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Client</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_branch)
                        <a href="/branch" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Branch</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Branch</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_region)
                        <a href="/region" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Region</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Region</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_province)
                        <a href="/province" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <span>Province</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <span>Province</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_city)
                        <a href="/city" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>City</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>City</span>
                            </div>
                        </span>
                    @endif
                    @if($manage_barangay)
                        <a href="/barangay" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Barangay</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Barangay</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap">Manage</span>
        @endif
        @if($docu_deposits || $docu_contract || $docu_or || $docu_expenses)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Documents</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($docu_deposits)
                        <a href="/deposit" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Deposits</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Deposits</span>
                            </div>
                        </span>
                    @endif
                    @if($docu_contract)
                        <a href="/contractbatch" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Contracts</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Contracts</span>
                            </div>
                        </span>
                    @endif
                    @if($docu_or)
                        <a href="/orbatch" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Official Receipts</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Official Receipts</span>
                            </div>
                        </span>
                    @endif
                    @if($docu_expenses)
                        <a href="/expenses" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Expenses</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Expenses</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap">Documents</span>
        @endif
        @if($trans_viewpayments || $trans_viewloanpayments)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Transactions</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($trans_viewpayments)
                        <a href="/payment" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Payments</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Payments</span>
                            </div>
                        </span>
                    @endif
                    @if($trans_viewloanpayments)
                        <a href="/loanpayment" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Loan Payments</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Loan Payments</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap">Transactions</span>
        @endif
        @if($reports)
            <a href="/reports" class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Reports</span>
            </a>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Reports</span>
            </span>
        @endif
        @if($req_loans || $req_encashments)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Requests</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($req_loans)
                        <a href="/req-loans" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Loans</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Loans</span>
                            </div>
                        </span>
                    @endif
                    @if($req_encashments)
                        <a href="/req-encashments" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Encashments</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Encashments</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap">Requests</span>
        @endif
        @if($sys_mcpr || $sys_bankaccounts || $sys_expense_desc || $sys_packages || $sys_notifications)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>System</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($sys_mcpr)
                        <a href="/mcpr" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>MCPR</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>MCPR</span>
                            </div>
                        </span>
                    @endif
                    @if($sys_bankaccounts)
                        <a href="/bank" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Bank Accounts</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Bank Accounts</span>
                            </div>
                        </span>
                    @endif
                    @if($sys_expense_desc)
                        <a href="/expense-desc" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Expense Description</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Expense Description</span>
                            </div>
                        </span>
                    @endif
                    @if($sys_packages)
                        <a href="/package" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Packages</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <span>Packages</span>
                            </div>
                        </span>
                    @endif
                    @if($sys_notifications)
                        <a href="/notif" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span>Notifications</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span>Notifications</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @else
            <span class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-400 cursor-not-allowed whitespace-nowrap">System</span>
        @endif
        @if($admin_settings)
            <div class="relative group">
                <button class="px-3 sm:px-4 py-2 text-sm sm:text-base text-gray-700 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition duration-200 font-medium whitespace-nowrap flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>Admin Settings</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="hidden group-hover:block w-56 bg-white rounded-lg shadow-xl border border-gray-100 py-2 dropdown-menu">
                    @if($admin_roles)
                        <a href="/role" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Roles</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Roles</span>
                            </div>
                        </span>
                    @endif
                    @if($admin_menuprivilege)
                        <a href="/menu" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <span>Menu Privileges</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <span>Menu Privileges</span>
                            </div>
                        </span>
                    @endif
                    @if($admin_actionprivilege)
                        <a href="/action" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-600 transition duration-150">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Action Privileges</span>
                            </div>
                        </a>
                    @else
                        <span class="block px-4 py-2.5 text-sm text-gray-400 cursor-not-allowed">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Action Privileges</span>
                            </div>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </nav>
    </div>
    @else
      <!-- Client Navigation Menu -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-wrap items-center justify-between p-2 gap-2">
          <div class="flex flex-wrap gap-2">
            <a href="/clienthome/{{ session('user_id') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('clienthome/*') ? 'bg-green-500 text-white' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }}">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Client Information
            </a>
            <a href="/clienthome-loanrequest/{{ session('user_id') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors {{ request()->is('clienthome-loanrequest/*') ? 'bg-green-500 text-white' : 'text-gray-700 hover:bg-green-50 hover:text-green-600' }}">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Loan Request
            </a>
            <a href="/clienthome-printsoa/{{ session('user_id') }}?export=true" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-green-50 hover:text-green-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Statement of Account (CSV)
            </a>
            <a href="/clienthome-printsoa-pdf/{{ session('user_id') }}" target="_blank" class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              Statement of Account (PDF)
            </a>
          </div>
          <a href="/clienthome-printcofp/{{ session('user_id') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-semibold rounded-md shadow-sm hover:shadow transition duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Certificate of Full Payment
          </a>
        </div>
      </div>
    @endif
</div>

<!-- Tailwind Logout Modal -->
<div id="logoutModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="relative mx-auto w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden transform transition-all">
            <!-- Header -->
            <div class="bg-white border-b-2 border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-gray-100 rounded-full p-2">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Confirm Logout</h3>
                    </div>
                    <button onclick="document.getElementById('logoutModal').classList.add('hidden')" class="text-gray-500 hover:text-gray-700 transition duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Body -->
            <div class="p-6">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-semibold mb-2">Are you sure you want to logout?</p>
                        <p class="text-gray-600 text-sm">You will need to login again to access your account.</p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <form id="logoutForm" method="POST">
                @csrf
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('logoutModal').classList.add('hidden')" 
                            class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 hover:border-gray-400 transition duration-200 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </button>
                    <button type="button" id="confirmLogout" 
                            class="inline-flex items-center px-5 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="{{ asset('js/jquery-3.7.0.min.js') }}"></script>
<script>
  $(document).ready(function() {
      // Handle logout confirmation
      $('#confirmLogout').click(function() {
          var logoutForm = $('#logoutForm');
          logoutForm.attr('action', '/logout');
          logoutForm.submit();
      });

      // Font Size Toggle Dropdown
      $('#fontSizeToggle').click(function(e) {
          e.stopPropagation();
          $('#fontSizeDropdown').toggleClass('hidden');
      });

      // Close dropdown when clicking outside
      $(document).click(function(e) {
          if (!$(e.target).closest('#fontSizeToggle, #fontSizeDropdown').length) {
              $('#fontSizeDropdown').addClass('hidden');
          }
      });

      // Load saved font size on page load
      loadFontSize();
  });

  // Font size change function
  function changeFontSize(size) {
      // Remove all font size classes
      document.body.classList.remove('font-size-small', 'font-size-medium', 'font-size-large', 'font-size-xlarge');
      
      // Add the selected font size class
      document.body.classList.add('font-size-' + size);
      
      // Save to localStorage
      localStorage.setItem('adminFontSize', size);
      
      // Close dropdown
      $('#fontSizeDropdown').addClass('hidden');
  }

  // Load font size from localStorage
  function loadFontSize() {
      var savedSize = localStorage.getItem('adminFontSize');
      if (savedSize) {
          document.body.classList.add('font-size-' + savedSize);
      } else {
          // Default to small
          document.body.classList.add('font-size-small');
      }
  }
</script>