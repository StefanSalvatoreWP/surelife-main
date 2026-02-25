/* 2023 SilverDust) S. Maceren */

$(document).ready(function() {
    $('#packageDisableModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let packageId = button.data('package-id');
        let packageName = button.data('package-name');
        
        let modal = $(this);
        modal.find('#packageToDisable').text(packageName);
        modal.find('#confirmDisable').click(function() {
            
            let disableForm = $('#disableForm');
            disableForm.attr('action', '/submit-package-disable/' + packageId);
            disableForm.submit();
        });
    });

    $('#packageEnableModal').on('show.bs.modal', function(event) {

        let button = $(event.relatedTarget);
        let packageId = button.data('package-id');
        let packageName = button.data('package-name');

        let modal = $(this);
        modal.find('#packageToEnable').text(packageName);
        modal.find('#confirmEnable').click(function() {
            
            let enableForm = $('#enableForm');
            enableForm.attr('action', '/submit-package-enable/' + packageId);
            enableForm.submit();
        });
    });
});