jQuery(document).ready(function($){
	const FIELDS = WPSTAjax.fields;
    const SHIPPER_FIELDS = FIELDS.hasOwnProperty('shipper_information') ? FIELDS.shipper_information.fields : {}
    const RECIEVER_FIELDS = FIELDS.hasOwnProperty('receiver_information') ? FIELDS.receiver_information.fields : {}
    const ADDRESS_FIELDS = WPSTAjax.address_fields;

	var selectize_ab = $('body').find('.wpst-ab-autofill');
    if (selectize_ab.length) {
        selectize_ab.each(function(){
			let section = $(this).closest('.section');
			let ab_type = $(this).data('type');
            let meta_key = $(this).data('meta_key');			
            let post_autor = $(this).data('post_autor');
            let placeholder = $(this).prop('placeholder');
            let no_result_html = '<div class="selectize-no-result rounded"><span>No result found</span></div>';

            let selectize_param = {
                valueField: 'post_id',
                labelField: 'meta_value',
                searchField: ['meta_value'],
                sortField : ['meta_value'],
                options: [],
                placeholder: placeholder,
                create: false,
                render: {
                    option: function(item, escape) {
                        return '<div><span class="title">' + escape(item['meta_value']) + '</span></div>';
                    }
                },
                load: function(query, callback) { 
                    if (query.length < 3) return callback();
                    $.post({
                        url: ajaxurl,
                        data:{
                            action: 'ab_autofill_search',
                            q: query,
                            meta_key: meta_key,
                            ab_type: ab_type,
                            post_autor: post_autor
                        },
                        dataType: 'json',
                        error: function() {
                            callback();
                        },
                        beforeSend: function() {
                            show_loading();
                        },
                        success: function(data) {
                            hide_loading();
                            callback(data);
                        }
                    });
                },
                onType: function(text) {
                    if ( !this.currentResults.items.length) {
                        section.find('.selectize-no-result').remove();                            
                        section.find('.selectize-control').append(no_result_html);
                    } else {
                        section.find('.selectize-no-result').remove();
                    }
                },
                onChange:function() {
                    section.find('.selectize-no-result').remove();
                },
				onBlur:function() {
                    section.find('.selectize-no-result').remove();
                },
				onOptionAdd: function() {
					section.find('.selectize-no-result').remove();
				}
            };
            
            $(this).selectize(selectize_param);
        });
	}

	$('.sendtrace').on('change', '.wpst-ab-autofill', function(){
		let type = $(this).data('type');
		let ab_id = $(this).val();
		let section = $(this).closest('.section');

		let fields = {
            shipper: SHIPPER_FIELDS,
            receiver: RECIEVER_FIELDS
        }	

		if (ab_id) {
			$.post({
				url: WPSTAjax.ajaxurl,
				data: {
					action: 'ab_autofill_get_data',
					ab_id: ab_id
				},
				beforeSend: function() {
					show_loading();
				},
				success: function(res) {
					res = JSON.parse(res);
                    
                    if (res.status == 'danger') {
                        show_notification(res.message, res.status, 'info');
                    } else {
                        for (let field_key in fields[type]) {
                            let value = res.data[field_key];
                            let elem = section.find('#'+field_key);
                            if (typeof value == "object") {
                                for (let key in value) {
                                    let elem = section.find('#'+field_key+'_'+key);
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
		} else {
			clear_input_values(section);
		}
	});
});