jQuery(document).ready(function($){
    var date_picker_format = WPSTAjax.date_picker_format
    var time_picker_format = WPSTAjax.time_picker_format;
    var datetime_picker_format = WPSTAjax.datetime_picker_format;
    console.log(date_picker_format);
    $('.wpst-timepicker').datetimepicker(time_picker_format);
    $('.wpst-datepicker').datetimepicker(date_picker_format);
    $('.wpst-datetimepicker').datetimepicker(datetime_picker_format);

    $('.sendtrace').on('keydown', '.wpst-timepicker, .wpst-datepicker, .wpst-datetimepicker', function(){
        return false;
    });
});