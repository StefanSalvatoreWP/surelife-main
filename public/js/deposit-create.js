/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    let selectedBranch = $('#selectedBranch').val();
   
    let selectedRegionId = $('#regionName').val();
    let branchSelect = $('#branchName');
    branchSelect.empty();

    let selectedBankId = $('#bankName').val();
    let selectedBank = $('#selectedBankAccountNo').val();
    
    let bankSelect = $('#bankAccountNo');
    bankSelect.empty();

    let selectedStaffId = $('#selectedStaffId').val();
    let staffSelect = $('#depositedBy');
    staffSelect.empty();

    $.ajax({
        url: '/get-branches',
        method: 'GET',
        data: { regionId: selectedRegionId },
        dataType: 'json',
        cache: false,
        success: function(branches) {
            branches.forEach(function(branch) {
                
                branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');

                if(selectedBranch == branch.Id){
                    branchSelect.append('<option hidden selected value="' + branch.Id + '">' + branch.BranchName + '</option>');
                }
            });

            let currentBranchId = $('#branchName').val();
            $.ajax({
                url: '/get-staff',
                method: 'GET',
                data: { branchId: currentBranchId },
                dataType: 'json',
                cache: false,
                success: function(staffs) {
        
                    staffs.forEach(function(staff) {
                        
                        staffSelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');
        
                        if(selectedStaffId == staff.Id){
                            staffSelect.append('<option hidden selected value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');
                        }
                    });
                },
                error: function(xhr, status, error) {
                    staffSelect.append('<option value="0">Select staff</option>');
                }
            });        
        },
        error: function(xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
        }
    });

    $.ajax({
        url: '/get-bankaccounts',
        method: 'GET',
        data: { bankId: selectedBankId },
        dataType: 'json',
        cache: false,
        success: function(bankaccounts) {

            bankaccounts.forEach(function(bankaccount) {
                bankSelect.append('<option value="' + bankaccount.Id + '">' + bankaccount.AccountNumber + '</option>');
            });

            if(selectedBank == bankaccounts.Id){
                bankSelect.append('<option hidden selected value="' + bankaccount.Id + '">' + bankaccount.AccountNumber + '</option>');
            }
        },
        error: function(xhr, status, error) {
            bankSelect.append('<option value="0">Select bank</option>');
        }
    });

    $('#regionName').on('change', function() {

        let selectedRegionId = $(this).val();
        let branchSelect = $('#branchName');

        branchSelect.empty();
        $.ajax({
            url: '/get-branches',
            method: 'GET',
            data: { regionId: selectedRegionId },
            dataType: 'json',
            cache: false,
            success: function(branches) {
                branches.forEach(function(branch) {
                    branchSelect.append('<option value="' + branch.Id + '">' + branch.BranchName + '</option>');
                });

                let currentBranchId = $('#branchName').val();
                staffSelect.empty();

                $.ajax({
                    url: '/get-staff',
                    method: 'GET',
                    data: { branchId: currentBranchId },
                    dataType: 'json',
                    cache: false,
                    success: function(staffs) {
        
                        staffs.forEach(function(staff) {
                            
                            staffSelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');
        
                            if(selectedStaffId == staff.Id){
                                staffSelect.append('<option hidden selected value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        staffSelect.append('<option value="0">Select staff</option>');
                    }
                });
            },
            error: function(xhr, status, error) {
                branchSelect.append('<option value="0">Select region</option>');
            }
        });
    });

    $('#branchName').on('change', function() {

        let selectedBranchId = $(this).val();
        staffSelect.empty();

        $.ajax({
            url: '/get-staff',
            method: 'GET',
            data: { branchId: selectedBranchId },
            dataType: 'json',
            cache: false,
            success: function(staffs) {

                staffs.forEach(function(staff) {
                    
                    staffSelect.append('<option value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');

                    if(selectedStaffId == staff.Id){
                        staffSelect.append('<option hidden selected value="' + staff.Id + '">' + staff.LastName + ', ' + staff.FirstName + ' '  + staff.MiddleName + '</option>');
                    }
                });
            },
            error: function(xhr, status, error) {
                staffSelect.append('<option value="0">Select staff</option>');
            }
        });
    });
    
    $('#bankName').on('change', function() {

        let selectedBankId = $(this).val();
        let bankSelect = $('#bankAccountNo');

        bankSelect.empty();
            $.ajax({
            url: '/get-bankaccounts',
            method: 'GET',
            data: { bankId: selectedBankId },
            dataType: 'json',
            cache: false,
            success: function(bankaccounts) {

                bankaccounts.forEach(function(bankaccount) {
                    bankSelect.append('<option value="' + bankaccount.Id + '">' + bankaccount.AccountNumber + '</option>');
                });

                if(selectedBank == bankaccounts.Id){
                    bankSelect.append('<option hidden selected value="' + bankaccount.Id + '">' + bankaccount.AccountNumber + '</option>');
                }
            },
            error: function(xhr, status, error) {
                bankSelect.append('<option value="0">Select bank</option>');
            }
        });
    });
});