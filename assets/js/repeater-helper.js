jQuery(document).ready(function($){
    $('.sendtrace .repeater').repeater({
        initEmpty: false,
        defaultValues: {},
        isFirstItemUndeletable: false,
        repeaters: [{
            initEmpty: false,
            defaultValues: {},
            // (Required)
            // Specify the jQuery selector for this nested repeater
            selector: '.inner-repeater',
            show: function () {
                $(this).slideDown();
                if ($('body').find('.wpst-timepicker').length) {
                    $('body').find('.wpst-timepicker').each(function(){
                        $(this).datetimepicker(WPSTAjax.datetime_picker_format);
                    });                
                }
            },
        }],
        show: function () {
            $(this).slideDown();
            if ($('body').find('.wpst-timepicker').length) {
                $('body').find('.wpst-timepicker').each(function(){
                    $(this).datetimepicker(WPSTAjax.datetime_picker_format);
                });                
            }
        },
        hide: function (deleteElement) {
            var item_label = $(this).closest('.repeater').attr('item-label');
            if (!item_label) {
                item_label = 'item';
            }
            if(confirm('Are you sure to delete this '+item_label+'?')) {
                $(this).slideUp(deleteElement);
            }
        },
        ready: function (setIndexes) {
        }
    });
});