jQuery(document).ready(function($){
    var base_url = window.location.href.split('&')[0];
    const SHIPPER_FIELD = WPSTAjax.customer_field.shipper;
    const FIELDS = WPSTAjax.fields;
    const SHIPPER_FIELDS = FIELDS.hasOwnProperty('shipper_information') ? FIELDS.shipper_information.fields : {}
    const RECIEVER_FIELDS = FIELDS.hasOwnProperty('receiver_information') ? FIELDS.receiver_information.fields : {}
    const ADDRESS_FIELDS = WPSTAjax.address_fields;

    $('.wpst-color-field').wpColorPicker();

    $('#wpst-navigation').on('click', '.btn', function(){
        let tab_container = $(this).data('tab_container');
        let current_tab = $(this).data('tab');
        let newUrl = base_url+'&tab='+current_tab;
        change_current_url(newUrl);

        $('#sendtrace-admin .tab-container').removeClass('active');
        $('#sendtrace-admin').find(tab_container).addClass('active');
        $('#wpst-navigation .btn').removeClass('active');
        $(this).addClass('active');
    }); 
    $('.wpst-sub-navigation').on('click', '.btn', function(){
        let base_url = window.location.href.split('&');
        base_url = base_url[0]+'&'+base_url[1];
        let tab_container = $(this).find('.options').data('tab_container');
        let current_tab = $(this).find('.options').data('tab');
        let newUrl = base_url+'&sub='+current_tab;
        change_current_url(newUrl);

        let container = $(this).closest('.wpst-sub-container');
        container.find('.wpst-sub-content').removeClass('active');
        container.find(tab_container).addClass('active');
        $(this).closest('.wpst-sub-navigation').find('.btn').removeClass('active');
        $(this).addClass('active');
    });
    
    function change_current_url(newUrl) {
        if (newUrl) {
            history.replaceState({}, null, newUrl);
        }
    }

    // Generate Report
    $('#wpst_export_form').on('submit', function(e){
        e.preventDefault();
        let date_from = $(this).find('#date_from').val();
        let date_to = $(this).find('#date_to').val();
        let status = $(this).find('#sendtrace_status').val();
        let shipper = $(this).find('#'+SHIPPER_FIELD.key).val();
        let assigned_client = $(this).find('#assigned_client').val();

        if (date_from && date_to) {
            $.post({
                url: WPSTAjax.ajaxurl,
                data: {
                    action: 'generate_report',
                    date_from,
                    date_to,
                    status,
                    shipper,
                    assigned_client
                },
                beforeSend: function() {
                    show_loading();
                },
                success: function(response) {
                    console.log(response);
                    data = JSON.parse(response);
                    $('#wpst_export_form .alert').removeClass('d-none alert-danger alert-success');
                    if (data.status == 'error') {
                        $('#wpst_export_form .alert').addClass('alert-danger');
                        $('#wpst_export_form .alert').html(data.error);
                    } else {
                        $('#wpst_export_form .alert').addClass('alert-success');
                        $('#wpst_export_form .alert').html(data.msg);
                        download_file(data.fileurl, data.filename);
                    }
                    hide_loading();
                }
            });
        }
    });

    $('#post_per_page').on('change', function(){
        if ($(this).val()) {
            $(this).closest('form').trigger('submit');
        }
    });

    // Address Book
    $('body').on('click', '.ab-action', function(){
        let modal = $('#addressBookModal');
        let ab_id = $(this).data('id');
        let action = $(this).data('action');
        let type = $('#ab_type').val();
        modal.find('#ab_id').val(ab_id);
        modal.modal('show');
        clear_input_values(modal.find('.modal-body'));
        modal.find('#action').val(action);
        modal.find('.title-action').text(uc_words(action));
        modal.find('.submit-btn').text(action.toUpperCase());

        let fields = {
            shipper: SHIPPER_FIELDS,
            receiver: RECIEVER_FIELDS
        }

        if (action == 'delete') {
            modal.find('.submit-btn').removeClass('btn-primary').addClass('btn-danger');
        } else {
            modal.find('.submit-btn').removeClass('btn-danger').addClass('btn-primary');
        }
        
        if ($.inArray(action, ['edit', 'delete', 'view']) !== -1) {
            $.post({
                url: WPSTAjax.ajaxurl,
                data: {
                    action: 'get_address_book_data',
                    ab_id: ab_id
                },
                beforeSend: function() {
                    show_loading(modal);
                },
                success: function(res) {
                    res = JSON.parse(res);
                    
                    if (res.status == 'danger') {
                        show_notification(res.message, res.status, 'info');
                    } else {
                        for (let field_key in fields[type]) {
                            let value = res.data[field_key];
                            let elem = modal.find('#'+field_key);
                            if (typeof value == "object") {
                                for (let key in value) {
                                    let elem = modal.find('#'+field_key+'_'+key);
                                    update_input_value(elem, value[key]);
                                }
                            } else {
                                update_input_value(elem, value);
                            }                            
                        }
                    }
                    hide_loading();
                }
            });
        }
    });

    $('#addressBookModalForm').on('submit', function(e){
        e.preventDefault();
        var fieldsData = {};
        var action = $(this).find('#action').val();
        var ab_id = $(this).find('#ab_id').val();
        var ab_type = $('#ab_type').val();

        let fields = {
            shipper: SHIPPER_FIELDS,
            receiver: RECIEVER_FIELDS
        }
        
        if (fields[ab_type]) {
            for (let field_key in fields[ab_type]) {
                let field_type = fields[ab_type][field_key].type;
                if (field_type == 'address') {
                    if (ADDRESS_FIELDS) {
                        let addFields = {}
                        for (let add_key in ADDRESS_FIELDS) {
                            addFields[add_key] = $(this).find('#'+field_key+'_'+add_key).val();
                        }
                        fieldsData[field_key] = addFields;
                    }                    
                } else {
                    fieldsData[field_key] = $(this).find('#'+field_key).val();
                }                
            }
            if (action == 'delete') {
                if (confirm('Are you sure to delete this address book?')) {
                    add_update_address_book(ab_id, ab_type, fieldsData, action);
                }
            } else {
                add_update_address_book(ab_id, ab_type, fieldsData, action);
            }            
        }
    });

    function add_update_address_book(ab_id, ab_type, fieldsData, action)
    {
        $.post({
            url: WPSTAjax.ajaxurl,
            data: {
                action: 'add_update_address_book',
                ab_id: ab_id,
                ab_type: ab_type,
                frm_action: action,
                fieldsData: fieldsData
            },
            beforeSend: function() {
                show_loading($('#addressBookModal'));
            },
            success: function(res) {
                res = JSON.parse(res)
                let status = res.status;
                let icon = 'check';
                if (status != 'success') {
                    status = 'danger';
                    icon = 'info';
                }
                show_notification(res.message, status, icon);
                if (res.status == 'success') {
                    location.reload();
                }
                hide_loading();
            }
        });
    }


});