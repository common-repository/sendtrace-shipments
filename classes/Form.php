<?php
class WPSTForm
{
    public static function start($attributes = array())
    {
        $form_attribute = '';
        if (!array_key_exists('name', $attributes)) {
            $attributes['name'] = 'sendtrace';
        }
        if (!array_key_exists('method', $attributes)) {
            $attributes['method']= 'post';
        }

        if (!empty($attributes)) {
            foreach ($attributes as $name => $value) {
                $form_attribute .= $name.'='.$value.' ';
            }            
        }
        echo '<form '.esc_html($form_attribute).'>';
    }

    public static function end()
    {
        echo '</form>';

    }

    public static function nonce($nonce=array())
    {
        if (!empty($nonce)) {
            wp_nonce_field($nonce['action'], $nonce['field']);
        }
    }

    public static function gen_button($key, $label, $type='submit', $class='btn btn-sm btn-primary', $extras='')
    {
        echo "<button type='".esc_html($type)."' id='".esc_html($key)."' class='".esc_html($class)."' ".esc_html($extras)."> ".esc_html($label)." </button>";
    }

    public static function gen_field($field=array(), $with_form_group=false, $allow_html=false)
    {
        global $sendtrace, $WPSTCountry;

        if (empty($field)) { return false; }
        $label = array_key_exists('label', $field) ? wp_kses($field['label'], wpst_allowed_html_tags()) : '';
        $type = array_key_exists('type', $field) ? sanitize_key($field['type']) : '';
        $key = array_key_exists('key', $field) ? sanitize_key($field['key']) : '';
        if ($allow_html && in_array($type, array('text', 'textarea'))) {
            $value = array_key_exists('value', $field) ?  wp_kses($field['value'], wpst_allowed_html_tags()) : '';
            $placeholder = array_key_exists('placeholder', $field) ? wp_kses($field['placeholder'], wpst_allowed_html_tags()) : '';
        } else {
            $value = array_key_exists('value', $field) ?  wpst_sanitize_data($field['value']) : '';
            $placeholder = array_key_exists('placeholder', $field) ? sanitize_text_field($field['placeholder']) : '';
        }

        if (in_array($type, ['text', 'textarea']) && is_numeric($value) && $value <= 0) {
            $value = '';
        }
        $class = array_key_exists('class', $field) ? wpst_sanitize_data($field['class']) : '';
        $label_class = array_key_exists('label_class', $field) ? wpst_sanitize_data($field['label_class']) : '';
        $group_class = array_key_exists('group_class', $field) ? wpst_sanitize_data($field['group_class']) : '';
        $options = array_key_exists('options', $field) ? wpst_sanitize_data($field['options']) : array();
        $field_class = array_key_exists($type, $sendtrace->field_class()) ? $sendtrace->field_class()[$type] : '';
        $setting = array_key_exists('setting', $field) ? $field['setting'] : '';
        $field_name = !empty($setting) ? $setting.'['.$key.']' : $key;
        $required = array_key_exists('required', $field) ? $field['required'] : false;
        $extras = array_key_exists('extras', $field) ? $field['extras'] : '';
        $extras .= $required || $required == 'YES' ? ' required' : '';
        $description = array_key_exists('description', $field) ? wp_kses($field['description'], wpst_allowed_html_tags()) : '';

        if(!empty($class)){
            $field_class .= ' '.$class;
        }

        if ($with_form_group ) {
            if (!in_array($type, ['checkbox', 'radio'])) {
                echo '<div class="form-group '.esc_html($group_class).'">';
                if (!empty($label)) {
                    echo '<label for="'.esc_html($key).'" class="form-label d-block '.esc_html($label_class).'">'.wp_kses($label, wpst_allowed_html_tags()).'</label>';
                }  
                if ($description) {
                    echo '<p class="description small text-muted mb-1 mx-1">'.wp_kses($description, wpst_allowed_html_tags()).'</p>';
                }
            }            
        } else {
            if ($description) {
                echo '<p class="description small text-muted mb-1 mx-1">'.wp_kses($description, wpst_allowed_html_tags()).'</p>';
            }
        }

        switch ($type) {
            case 'upload':
                $file_type = array_key_exists('file_type', $field) ? wpst_sanitize_data($field['file_type']) : 'image';
                echo '<div class="file-upload-container">';
                    echo self::draw_hidden($field_name, $value, $field_name, "{$key}-field");
                    echo "<span class='remove-file ".($value ? 'd-block' : 'd-none')."'>&times;</span>";
                    echo "<span id='".esc_html($key)."_thumbnail' class='file-placeholder ".($value ? 'd-block' : 'd-none')." my-1'><img width='90px' class='file-placeholder-img' src='" .esc_url(wp_get_attachment_url($value)). "' /></span>";                  
                    echo "<span class='btn btn-sm btn-primary m-0 mb-3 primary-bg wpst-media-uploader' data-btn_txt='Use this media' data-thumbnail='#".esc_html($key)."_thumbnail' data-file_type='".esc_html($file_type)."' data-key='.".esc_html($key)."-field'><i class='fa fa-upload'></i> Upload</span>";
                echo '</div>';
                break;
            case 'url':
                echo '<a href="'.esc_html($value).'" id="'.esc_html($key).'" class="'.esc_html($field_class).'" '.esc_html($extras).'></a>';
                break;
            case 'textarea':
                echo '<textarea id="'.esc_html($key).'" name="'.esc_html($field_name).'" class="'.esc_html($field_class).'" placeholder="'.($allow_html ? wp_kses($placeholder, wpst_allowed_html_tags()) : esc_html($placeholder)).'" '.esc_html($extras).'>'.($allow_html ? wp_kses($value, wpst_allowed_html_tags()) : wpst_sanitize_data($value)).'</textarea>';
                break;
            case 'select':
                $brakets = '';
                $placeholder = !empty($placeholder) ? $placeholder : 'Choose...';
                if (!empty($extras) && strpos($extras, 'multiple') !== false) {
                    $brakets = '[]';
                }
                echo '<select id="'.esc_html($key).'" name="'.esc_html($field_name.$brakets).'" class="custom-select '.esc_html($field_class).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'>';
                    echo '<option value=""> '.esc_html($placeholder).' </option>';    
                    if (!empty($options) && is_array($options)) {
                        $first_idx = array_search(array_values($options)[0], $options);
                        $is_assoc_arr = !is_numeric($first_idx) || $first_idx > 0;
                        $opt_selected = '';
                        foreach ($options as $op_val => $op_label) {
                            if (!$is_assoc_arr) {
                                $op_val = $op_label;
                            }
                            
                            if (is_array($value)) {
                                if (in_array($op_val, $value)) {
                                    $opt_selected = 'selected';
                                }
                            } else {
                                $opt_selected = strcmp($op_val, $value) == 0 ? 'selected' : '';
                            }                            
                            echo '<option value="'.esc_html($op_val).'" '.esc_html($opt_selected).'> '.esc_html($op_label).' </option>';
                        }
                    }
                echo '</select>';
                break;
            case 'checkbox':
            case 'radio':
                $field_name = $type == 'checkbox' ? $field_name.'[]' : $field_name;
                if ($with_form_group) {
                    echo '<label class="form-label d-block '.esc_html($label_class).'">'.esc_html($label).'</label>';
                }            
                if (!empty($options)) {
                    echo '<p class="description small text-secondary mb-1">'.wp_kses_data($description).'</p>';
                    $counter = 0;
                    $is_assoc_arr = !array_key_exists(0, $options);
                    foreach ($options as $op_val => $op_label) {
                        $counter ++;
                        if (!$is_assoc_arr) {
                            $op_val = $op_label;
                        }
                        if ($type == 'checkbox') {
                            $checked = !empty($value) ? checked(in_array($op_val, $value), 1, false) : '';
                        } else {
                            $checked = checked($op_val == $value, 1, false);
                        }
                        echo '<div class="form-check ml-2 '.esc_html($group_class).'">';
                            echo '<input type="'.esc_html($type).'" id="'.esc_html($key).'-'.$counter.'" name="'.esc_html($field_name).'" value="'.esc_html($op_val).'" class="form-check-input '.esc_html($class).'" '.esc_html($extras).' '.esc_html($checked).' type="checkbox">';
                            echo '<label class="form-check-label" for="'.esc_html($key.'-'.$counter).'">'.esc_html($op_label).'</label>';
                        echo '</div>';
                    }
                }
                break;
            case 'date':     
                $placeholder = strtolower(wpst_datepicker_format());           
                echo '<input type="text" id="'.esc_html($key).'" class="'.esc_html($field_class).'" name="'.esc_html($field_name).'" value="'.esc_html($value).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'/>';
                break;
            case 'address':
                if (!empty(wpst_address_field())) {
                    foreach (wpst_address_field() as $add_key => $add_holder) {
                        $_value = is_array($value) && array_key_exists($add_key, $value) ? $value[$add_key] : '';
                        echo '<p class="small mb-1" style="color: #999">'.esc_html($add_holder).'</p>';
                        echo '<p class="mb-1">';
                        if ($add_key == 'country') {
                            echo '<select  id="'.esc_html($field_name).'_'.esc_html($add_key).'" class="custom-select selectize '.esc_html($key).'" name="'.esc_html($field_name).'['.$add_key.']" placeholder="Choose..">';
                            echo '<option value=""> Choose.. </option>';
                            if (!empty($WPSTCountry->list())) {
                                foreach ($WPSTCountry->list() as $country) {
                                    $opt_selected = strtoupper($_value) == strtoupper($country) ? 'selected' : '';
                                    echo '<option value="'.esc_html($country).'" '.esc_html($opt_selected).'> '.esc_html($country).' </option>';
                                }
                            }                           
                            echo '</select>';
                        } else {
                            echo '<input id="'.esc_html($field_name).'_'.esc_html($add_key).'" type="text" class="form-control '.esc_html($key).'" name="'.esc_html($field_name).'['.esc_html($add_key).']" value="'.esc_html($_value).'">';
                        }                            
                        echo '</p>';
                    }
                }  
                break;              
            default: 
                ob_start();
                echo '<input type="'.esc_html($type).'" id="'.esc_html($key).'" class="'.esc_html($field_class).'" name="'.esc_html($field_name).'" value="'.($allow_html ? wp_kses($value, wpst_allowed_html_tags()) : esc_html($value)).'" placeholder="'.esc_html($placeholder).'" '.esc_html($extras).'/>';
                echo apply_filters('wpst_gen_field_default', ob_get_clean(), $type, $key);
        }

        if ($with_form_group && !in_array($type, ['checkbox', 'radio'])) {
            echo '</div>';
        }
    }

    public static function draw_hidden($name, $value='', $id='', $class='', $has_name=true)
    {
        $name_attr = $has_name ? "name={$name}" : "";
        $id = !empty($id) ? $id : $name;
        echo "<input type=hidden id='".esc_html($id)."' ".esc_html($name_attr)." value='".esc_html($value)."' class='".esc_html($class)."'>";
    }

    public static function draw_search_field($meta_key, $value, $label='', $class='', $placeholder='', $extras='', $form_group=false)
    {
        $options = !empty($value) ? [$meta_key => $value] : array();
        $fields = [
            'key' => $meta_key,
            'type' => 'select',
            'label' => $label,
            'options' => $options,
            'value' => $value,
            'class' => 'selectize-search '.$class,
            'extras' => $extras,
            'placeholder' => $placeholder,
            'group_class' => 'm-0'
        ];
        self::draw_hidden("{$meta_key}_options", base64_encode(json_encode($options)), '', '', false);
        echo self::gen_field($fields, $form_group);
    }
}