jQuery(document).ready(function ($) {
    var Views = {
        init: function () {
            $("select[name='fvb_form']").on('change', this.getFields);
            $("a.fvb_remove_repeatable").on('click', this.removeField);
            $("input:checkbox[name='fvb_settings[restrict]']").on('click', this.showHideRestrictionSection);
            this.showHideRestrictionSection();
            this.sortFields();
            this.popover();
            //this.popover();
        },
        showHideRestrictionSection: function () {
            var self = $("input:checkbox[name='fvb_settings[restrict]']"),
                isChecked = $('input:checkbox[name="fvb_settings[restrict]"]:checked');
            if (isChecked.length > 0) {
                self.closest("div.form-group").nextAll(':lt(2)').slideDown();
            } else {
                self.closest("div.form-group").nextAll(':lt(2)').slideUp();
            }
        },
        popover: function () {
            $('textarea, input, select').popover();
        },
        sortFields: function () {
            $(".fvb_fields_table tbody").sortable({
                handle: '.fvb_draghandle',
                items: '.fvb_repeatable_row',
                placeholder: "ui-state-highlight",
                opacity: 0.6,
                cursor: 'move',
                axis: 'y'
            });
        },
        refresh: function () {
            $("a.fvb_remove_repeatable").off('click').on('click', Views.removeField);
            Views.sortFields();
            Views.popover();
        },
        getFields: function () {
            var $this = $(this),
                nonce = $("input[name='fvb_views_nonce_field']").val(),
                value = parseInt($this.val()),
                loading = $("span.fvb-loading"),
                params = {action: 'fvb_get_form_fields', fvb_views_nonce: nonce, fvb_form_id: value},
                data = $.param(params);
            if (value != 0) {
                loading.show();
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (result) {
                        loading.hide();
                        $(".fvb_fields_area").empty().html(result);
                        Views.refresh();
                    }
                });
            }
        },
        removeField: function (e) {
            e.preventDefault();
            //console.log('yes');
            var self = $(this);
            var rowCount = $(".fvb_fields_table tr").length;

            //to get the id
            var id = self.closest("tr").find("span#fvb_price_id").text();
            if (rowCount > 2) {
                if (confirm('Are you sure?')) {
                    self.closest('tr').remove();
                }
            }
        }
        //popover: function () {
        //    $('div#fvb-user-settings').find('textarea, input, select').popover();
        //},
        //saveViews: function () {
        //    $('button#fvb-save-settings').off('click').on('click', function () {
        //        var params = {action: 'fvb_save_settings'},
        //            saving = $("span.fvb-saving"),
        //            saved = $("span.fvb-saved"),
        //            data = $("form.fvb-settings-form").serialize() + '&' + $.param(params);
        //        if (confirm('Are you sure?')) {
        //            $(this).prop('disabled', 'disabled');
        //            saving.show();
        //            $.ajax({
        //                url: ajaxurl,
        //                type: 'POST',
        //                data: data,
        //                success: function (result) {
        //                    $('button#fvb-save-settings').prop("disabled", null);
        //                    saving.hide();
        //                    saved.show();
        //                    setTimeout(
        //                        function () {
        //                            saved.hide();
        //                        }, 1000);
        //                }
        //            });
        //        }
        //    });
        //},
        //chosen:function(){
        //    //jQuery chosen
        //    //$('select').chosen({
        //    //    width: "30%"
        //    //});
        //}
    };
    Views.init();
});