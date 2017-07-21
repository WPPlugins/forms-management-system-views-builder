jQuery(document).ready(function ($) {
    var Settings = {
        init: function () {
            this.popover();
            this.chosen();
            this.saveSettings();
        },
        popover: function () {
            $('div#fvb-user-settings').find('textarea, input, select').popover();
        },
        saveSettings: function () {
            $('button#fvb-save-settings').off('click').on('click', function () {
                var params = {action: 'fvb_save_settings'},
                    saving = $("span.fvb-saving"),
                    saved = $("span.fvb-saved"),
                    data = $("form.fvb-settings-form").serialize() + '&' + $.param(params);
                if (confirm('Are you sure?')) {
                    $(this).prop('disabled', 'disabled');
                    saving.show();
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: data,
                        success: function (result) {
                            $('button#fvb-save-settings').prop("disabled", null);
                            saving.hide();
                            saved.show();
                            setTimeout(
                                function () {
                                    saved.hide();
                                }, 1000);
                        }
                    });
                }
            });
        },
        chosen: function () {
            //jQuery chosen
            //$('select').chosen({
            //    width: "30%"
            //});
        }
    };
    Settings.init();
});