/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {

    let selectedBranch = $('#selectedBranch').val();

    let selectedRegionId = $('#regionName').val();
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

            if(selectedBranch == branch.Id){
                branchSelect.append('<option hidden selected value="' + branch.Id + '">' + branch.BranchName + '</option>');
            }
        },
        error: function(xhr, status, error) {
            branchSelect.append('<option value="0">Select region</option>');
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
            },
            error: function(xhr, status, error) {
                branchSelect.append('<option value="0">Select region</option>');
            }
        });
    });
});