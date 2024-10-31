jQuery(document).ready(function($){
    const NOTIFICATION = WPSTAjax.notification;
    const LOADING_HTML = '<div class="wpst-loading text-info text-center h3 min-vh-100"> <span class="spinner-grow spinner-border-sm"></span></div>';
    window.show_notification = function(message, type='success', icon = "check"){
        let sendtrace_elem = null;
        if ($('body').find('.sendtrace').length) {
            sendtrace_elem = $('body').find('.sendtrace');
        }
        if ($('body.sendtrace').length) {
            sendtrace_elem = $('body.sendtrace');
        }
        if (sendtrace_elem) {
            sendtrace_elem.append(
                '<div class="wpst-notif alert alert-'+type+'" role="alert">'+
                    '<span class="fa fa-lg fa-'+icon+'-circle"></span> '+ message +
                    '<span class="wpst-notif-dismiss">&times;</span>'+
                '</div>'
            );
        }
    	
        setTimeout(function(){
            $('body').find('.wpst-notif').remove();
        }, 7000);
	}
    window.show_loading = function(elem=null) {
        if (elem) {
            elem.append(LOADING_HTML);
        } else {
            $('.sendtrace').append(LOADING_HTML);
        }        
    }
    window.hide_loading = function() {
        $('body').find('.wpst-loading').remove();
    }
    window.download_file = function(fileURL, fileName) {
        // for non-IE
        if (!window.ActiveXObject) {
            var save = document.createElement('a');
            save.href = fileURL;
            save.target = '_blank';
            var filename = fileURL.substring(fileURL.lastIndexOf('/')+1);
            save.download = fileName || filename;
            if ( navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
                    document.location = save.href; 
                // window event not working here
                }else{
                    var evt = new MouseEvent('click', {
                        'view': window,
                        'bubbles': true,
                        'cancelable': false
                    });
                    save.dispatchEvent(evt);
                    (window.URL || window.webkitURL).revokeObjectURL(save.href);
                }	
        }
        // for IE < 11
        else if ( !! window.ActiveXObject && document.execCommand)     {
            var _window = window.open(fileURL, '_blank');
            _window.document.close();
            _window.document.execCommand('SaveAs', true, fileName || fileURL)
            _window.close();
        }
    }

    window.clear_input_values = function(parent_elem) {
        parent_elem.find('.form-control').val('');
        parent_elem.find('select').each(function(){
            if ($(this).hasClass('selectize')) {
                let selectize = $(this)[0].selectize;
                selectize.clear();
            }
        });
        parent_elem.find('input[type="radio"]').each(function(){
            $(this).prop('checked', false);
        });
        parent_elem.find('input[type="checkbox"]').each(function(){
            $(this).prop('checked', false);
        });
    }

    window.update_input_value = function(elem, value) {
        if (elem.length > 1) {
            elem.each(function(){
                if ($(this).is(':radio')) {
                    if (value == $(this).val()) {
                        $(this).prop('checked', true);
                    }
                }                
            });
        } else if (elem.is(':radio')) {
            if (value == elem.val()) {
                elem.prop('checked', true);
            }
        } else if (elem.is('select')) {
            if (elem.parent().find('.selectize').length) {
                let selectize = elem[0].selectize;
                if (elem.attr('multiple') && value && !$.isArray(value)) {
                    if (typeof value == 'object') {
                        value = $.map(value, function(val, idx){
                            return [val];
                        });
                    } else {
                        value = value.split(',');
                    }
                }
                if (elem.find('option').length < 1) {
                    $.each(value, function(idx, _value){
                        let key = isNaN(idx) ? idx : _value;
                        selectize.addOption({'value': key, 'text' : _value});
                    });                    
                }      
                selectize.setValue(value);
                selectize.refreshItems();
            } else {
                elem.val(value);
                elem.change();
            }
        } else {
            elem.val(value);
        }
    }

    window.uc_words = function(str) {
        str = str.toLowerCase().replace(/\b[a-z]/g, function(letter) {
            return letter.toUpperCase();
        });
        return str;
    }    

    window.update_selectize_options = function(elem, new_options) {
        if (elem.length) {
            let selectize = elem[0].selectize;
            selectize.clearOptions();
            if (new_options) {
                $.each(new_options, function(value, label) {
                    selectize.addOption({value: value, text:label});
                });
                selectize.refreshItems();
            }            
        }        
    }

    if( NOTIFICATION && NOTIFICATION.length != 0 ){
		show_notification( NOTIFICATION.message, NOTIFICATION.type, NOTIFICATION.icon );
	}

    $('body').on('click', '.wpst-notif-dismiss', function(){
        $(this).closest('.wpst-notif').remove();
    });

    $('body').on('click', '.wpst-media-uploader', function(e){
        e.preventDefault();
		var file_frame;
        let file_val_id = $(this).data('key');
        let file_type = $(this).data('file_type') ?? 'any';
        let is_multiple = $(this).data('multiple') ?? false;
        let container = $(this).closest('.file-upload-container');
        let file_placeholder = container.find('.file-placeholder');
        
        // If the media frame is exist, reopen it.
        if (file_frame) {
            file_frame.open();
            return;                        }
            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: $(this).data('title'),
                library : {
                    type : file_type
                },
                button: {
                    text: $(this).data('btn_txt'),
                },
                multiple: is_multiple
                // Set to true to allow multiple files to be selected
            });
            // When an image is selected, run a callback.
            file_frame.on( 'select', function() {
                // We set multiple to false so only get one image from the uploader
                attachment = file_frame.state().get('selection').first().toJSON();
                // Do something with attachment.id and/or attachment.url here
                $(file_val_id).val(attachment.id);
                file_placeholder.find('img').attr('src', attachment.url);
                file_placeholder.addClass('d-block');
                container.find('.remove-file').addClass('d-block');
            });
        // Finally, open the modal
        file_frame.open();
    });

    $('.file-upload-container').on('click', '.remove-file', function(){
        let container = $(this).closest('.file-upload-container');
        let field = container.find('.wpst-media-uploader').data('key');
        container.find('.file-placeholder').removeClass('d-block').addClass('d-none');
        $(this).removeClass('d-block').addClass('d-none');
        $(field).val('');        
    });

    // Manage Shipments
    $('#shipment-list .select-all, .wpst-post-list .select-all').on('change', function(){
        $(this).closest('#shipment-list').find('.shipment-item').prop('checked', $(this).prop('checked'));
        $(this).closest('.wpst-post-list').find('.wpst-post-item').prop('checked', $(this).prop('checked'));
    });

    // Bulk update shipment post status - ADMIN
    $('.bulk-update-post-status').on('click', function(){
        let status = $(this).data('status');
        let shipment_ids = [];

        $('#shipment-list .shipment-item:checked').each(function(){
            if (!isNaN(parseInt($(this).val()))) {
                shipment_ids.push(parseInt($(this).val()));
            }            
        });
        if (!shipment_ids.length) {
            show_notification('Please select shipment(s) to '+status+'.', 'danger', 'info');
            return false;
        }
        if (!status) {
            show_notification('Status is not set for this action.', 'danger', 'info');
            return false;
        }
        if (confirm('Are you sure to '+status+' the selected shipment(s)?')) {
            update_shipments_post_status(shipment_ids, status);
        }        
    });

    // Single update shipment post status
    $('.shipment-row .update-single-shipment').on('click', function(e) {
        e.preventDefault();
        let shipment_id = [parseInt($(this).data('id'))];
        let status = $(this).data('status');
        update_shipments_post_status(shipment_id, status);
    });

    window.update_shipments_post_status = function(shipment_ids, status)
    {
        $.post({
            url: WPSTAjax.ajaxurl,
            data: {
                action: 'bulk_update_post_status',
                shipment_ids,
                status
            },
            beforeSend: function() {
                show_loading();
            },
            success: function(response) {
                if (WPSTAjax.is_debug != 0) {
                    console.log(response);
                }                    
                data = JSON.parse(response);
                if (data.status == 'error') {
                    show_notification(data.error, 'danger', 'info');
                } else {
                    show_notification(data.msg, 'success', 'info');
                    $('#shipment-list').find('.shipment-item').each(function(){
                        if ($.inArray(parseInt($(this).val()), shipment_ids) !== -1) {
                            $(this).closest('.shipment-row').remove();
                        }
                    });
                    if ($('#shipmentsDataTable').length) {
                        $('#shipmentsDataTable').find('.shipment-item').each(function(){
                            if ($.inArray(parseInt($(this).val()), shipment_ids) !== -1) {
                                shipmentsDataTable.row($(this.closest('tr'))).remove().draw();
                            }
                        });
                    }
                    let no_items = shipment_ids.length;
                    let current_el_status = undefined;
                    let opp_el_status = undefined;
                    if (status == 'trash') {
                        current_el_status = $('#wpst-status-nav').find('.active .count');
                        opp_el_status = $('#wpst-status-nav').find('.trash .count');
                    } else {
                        current_el_status = $('#wpst-status-nav').find('.trash .count');
                        opp_el_status = $('#wpst-status-nav').find('.active .count');
                    }
                    let prev_count = current_el_status.text().replace('(', '').replace(')', '');
                    let op_prev_count = opp_el_status.text().replace('(', '').replace(')', '');
                    let new_item_count = parseInt(prev_count) - parseInt(no_items);
                    let new_item_count_op = parseInt(op_prev_count) + parseInt(no_items);
                    current_el_status.text('('+new_item_count+')');
                    if (prev_count && status != 'delete') {
                        opp_el_status.text('('+new_item_count_op+')');
                    }                        
                }
                hide_loading();
            }
        });
    }

    window.wpst_update_post_status = function(post_ids, status, reload=true){
        $.post({
            url: WPSTAjax.ajaxurl,
            data: {
                action: 'bulk_update_item_post_status',
                post_ids,
                status
            },
            beforeSend: function() {
                show_loading();
            },
            success: function(res) {
                res = JSON.parse(res)
                let status = res.status == 'error' ? 'danger' : res.status;
                let icon = status == 'danger' ? 'info' : 'check';
                show_notification(res.msg, status, icon);
                hide_loading();
                if (reload) {
                    location.reload();
                }
            }
        });
    }

    $('.sendtrace-print-option').on('click', '.dropdown-item', function(){
        let shipment_id = $(this).data('id');
        let type = $(this).data('type');
        let error = '';

        if (!shipment_id) {
            error = 'Shipment ID is not defined.';
        }
        if (!type) {
            error = 'Print Type is not defined.';
        }
        if (error) {
            show_notification(error, 'danger', 'info');
        } else {
            $.ajax({
                type: 'POST',
                url: WPSTAjax.ajaxurl,
                data: {
                    shipment_id,
                    type,
                    action: 'print_shipment_pdf'
                },
                beforeSend: function() {
                    show_loading();
                },
                success: function(response) {
                    response = JSON.parse(response);
                    if (response.fileurl) {
                        download_file(response.fileurl, response.filename);
                        hide_loading();
                    }             
                }
            });
        }        
    });

    $('.sendtrace-list').on('click', '.sendtrace-item', function(){
        let active_class = $(this).data('active_class');
        $(this).closest('.sendtrace-list').find('.sendtrace-item').removeClass(active_class);
        $(this).addClass(active_class);
    });

    $('.wpst-update-post-status').on('click', function(){
        let status = $(this).data('status');
        let item_label = $(this).data('item_label');
        if (item_label) {
            item_label = item_label.replace('_', ' ');
        }
        let post_ids = [];

        $('.wpst-post-list .wpst-post-item:checked').each(function(){
            if (!isNaN(parseInt($(this).val()))) {
                post_ids.push(parseInt($(this).val()));
            }            
        });
        if (!post_ids.length) {
            show_notification('Please select '+item_label+' to '+status+'.', 'danger', 'info');
            return false;
        }
        if (!status) {
            show_notification('Status is not set for this action.', 'danger', 'info');
            return false;
        }
        if (confirm('Are you sure to '+status+' the selected '+item_label+'?')) {
            wpst_update_post_status(post_ids, status);
        }
    });

});