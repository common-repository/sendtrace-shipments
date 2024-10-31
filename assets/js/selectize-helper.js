jQuery(document).ready(function($){
    var ajaxurl = WPSTAjax.ajaxurl;

    $('.selectize').find('select').each(function(){
        selectize_elem($(this));
    });
    var selectize_dropdown = $('body').find('select.selectize');
    if (selectize_dropdown.length) {
        selectize_dropdown.each(function(){
            selectize_elem($(this));
        });
        
    }

    function selectize_elem(elem)
    {
        var allow_create = elem.data('allow_create');
        let has_remove = elem.data('has_remove');
        let plugins = has_remove ? ['remove_button'] : [];
        elem.selectize({
            create : allow_create,
            plugins: plugins
        });
    }

    $('body').on('change', 'select.selectize-search', function(){
        $('body').find('.selectize-no-result').remove();
    });

    var selectize_search = $('body').find('select.selectize-search');
    if (selectize_search.length) {
        selectize_search.each(function(){
            let meta_key = $(this).prop('id');
            let post_type = $(this).data('post_type');
            let post_autor = $(this).data('post_autor');

            let search_options = $(this).parents().eq(2).find('#'+meta_key+'_options').val();
            let placeholder = $(this).prop('placeholder');
            let no_result_html = '<div class="selectize-no-result rounded"><span>No result found</span></div>';

            var search_value = '';
            if (search_options) {
                search_options = JSON.parse(atob(search_options));
                search_value = search_options[meta_key];
                search_options = [search_options];
            }

            let selectize_param = {
                valueField: meta_key,
                labelField: meta_key,
                searchField: [meta_key],
                sortField : [meta_key],
                options: search_options,
                placeholder: placeholder,
                create: false,
                render: {
                    option: function(item, escape) {
                        return '<div><span class="title">' + escape(item[meta_key]) + '</span></div>';
                    }
                },
                load: function(query, callback) { 
                    if (query.length < 3) return callback();
                    $.post({
                        url: ajaxurl,
                        data:{
                            action: 'selectize_search',
                            q: query,
                            meta_key: meta_key,
                            post_type: post_type,
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
                        $('body').find('.selectize-no-result').remove();                            
                        $('#'+meta_key).parent().find('.selectize-control').append(no_result_html);
                    } else {
                        $('body').find('.selectize-no-result').remove();
                    }
                },
                onChange:function() {
                    $('body').find('.selectize-no-result').remove();
                },
				onBlur:function() {
                    $('body').find('.selectize-no-result').remove();
                },
				onOptionAdd: function() {
					$('body').find('.selectize-no-result').remove();
				}
            };
            
            $(this).selectize(selectize_param);
            if ($.isFunction($.fn.selectize)) {
                var selectize_elem = $('#'+meta_key)[0].selectize;
                if (search_value) {
                    selectize_elem.setValue(search_value);
                }
            }
        });
    }
});