<?php
class WPSTDataTable
{
	private $table_values = array();
    private $table_column_fields = array();
	private $columnDefs = array();
	private $styles = array();
	private $scripts = array();
	private $table_buttons = array();
	public $show_entries = 10;
	public $enable_paging = true;
	public $table_name;	
	public $table_class = '';

	function __construct($table_values, $table_column_fields, $table_name='')
	{
		$this->table_values = wpst_sanitize_data($table_values);
		$this->table_column_fields = wpst_sanitize_data($table_column_fields);
		$this->table_name = wpst_sanitize_data($table_name);
	}

	function include_styles($styles)
    {
        $this->styles = $styles;
    }

    function include_scripts($scripts)
    { 
        $this->scripts = $scripts;
    }

	function set_report_buttons($buttons)
    {
        if (!empty($buttons) && is_array($buttons)) {
            foreach ($buttons as $button) {
				$report_label = array_key_exists('label', $button) ? esc_html($button['label']) : '';
                $report_icon = array_key_exists('icon', $button) ? '<i class="fa '.esc_html($button['icon']).'"></i>' : '';
                $report_class = array_key_exists('class', $button) ? esc_html($button['class']) : '';
                $report_title = array_key_exists('filename', $button) ? esc_html($button['filename']) : '';
                $report_extras = array_key_exists('extras', $button) ? wpst_sanitize_data($button['extras']) : [];
                $reportBtn = new stdClass;
                $reportBtn->extend = esc_html($button['type']);
                $reportBtn->text = "{$report_icon} {$report_label}"; 
                $reportBtn->className = $report_class;
                $reportBtn->title = $report_title;
                if (!empty($report_extras)) {
                    foreach ($report_extras as $ex_key => $ex_val) {
                        $reportBtn->$ex_key = esc_html($ex_val);
                    }
                }
                $this->table_buttons[] = $reportBtn;

            }
        }
    }

	function draw()
    {       
		require_once WPST_PLUGIN_PATH. 'assets/datatable-css.php';
		$columns = array();
		do_action(esc_html($this->table_name)."_before_table");
        echo "<table id='".esc_html($this->table_name)."DataTable' class='table table-hover ".esc_html($this->table_name)." ".esc_html($this->table_class)."' style='width:100%'>";
			echo "<thead>";
				ob_start();
				do_action(esc_html($this->table_name)."_before_table_header");
				echo ob_get_clean();
				echo "<tr class='table-head'>";
					if (!empty($this->table_column_fields)) {
						$targeCounter = 0;
						foreach ($this->table_column_fields as $col_key => $col) {
							$th_class = array_key_exists('th_class', $col) ? $col['th_class'] : '';
							echo "<th class='".esc_html($col_key.' '.$th_class)."'>" .wp_kses($col['label'], wpst_allowed_html_tags()). "</th>";
							$field_Obj = new stdClass;
							$field_Obj->data = $col_key;
							$field_Obj->defaultContent = '<strong class="text-danger">*<strong>';
							$columns[] = $field_Obj;

							$td_class = $col_key.' ';
                			$td_class .= array_key_exists('td_class', $col) ? esc_html($col['td_class']) : '';
							$columnDefs = new stdClass;
							$columnDefs->targets = $targeCounter;
							$columnDefs->className = $td_class;              
							$this->columnDefs[] = $columnDefs;
							$targeCounter ++;
						}
					}
				echo "</tr>";
			echo "</thead>";
			echo "<tfoot>";
				echo "<tr>";
					echo "<td colspan='".count($this->table_column_fields)."' class='border-0'>";
						ob_start();
						do_action(esc_html($this->table_name)."_after_table_footer");
						echo ob_get_clean();
					echo "</td>";
				echo "</tr>";
			echo "</tfoot>";
		echo "</table>";
        ?>
		
        <script>
            jQuery(document).ready(function($) {
                var data = <?php echo json_encode(wpst_sanitize_data($this->table_values))?>;              
                var columns = <?php echo json_encode($columns) ?>;
                var columnDefs = <?php echo json_encode($this->columnDefs) ?>;
                var buttons = <?php echo json_encode(wpst_sanitize_data($this->table_buttons)) ?>;
                var enable_paging = <?php echo (int)$this->enable_paging ?>;
                var show_entries = <?php echo esc_html($this->show_entries) ?>;
				let table_name = "<?php echo esc_html($this->table_name) ?>";
				let table_atts = table_name+"_atts";
				let table_goto_1st_page = table_name+"_datatable_1st_page";
				let table_goto_last_page = table_name+"_datatable_last_page";

				if (!table_atts) {
					table_atts = 'table_atts';
				}

                window[table_atts] = {
                    data: data,
                    // dom: "Blrtip",
                    lengthMenu: [[2, 10, 25, 50, -1], [2, 10, 25, 50, "All"]],
                    iDisplayLength: show_entries,
                    columns: columns,
                    columnDefs : columnDefs,
                    buttons: buttons,
                    paging: enable_paging,
                    aaSorting : [],
					createdRow: function(row, data, dataIndex) {
						$(row).addClass('item-row');
					}
                };

				window[table_goto_1st_page] = function() {
					$('body').find('#'+table_name+'DataTable_previous').next().trigger('click');
				}
				window[table_goto_last_page] = function() {
					$('body').find('#'+table_name+'DataTable_next').prev().trigger('click');
				}
            } );
        </script>

        <?php
        // Allow other script to modify data table attributes
		if (!empty($this->scripts)) {
			foreach ($this->scripts as $script) {
				echo "<script type='text/javascript' scr='" .esc_url($script). "'></script>";
			}
		}
        if (!empty($this->styles)) {
			foreach ($this->styles as $style) {
				echo "<link rel='stylesheet' href='" .esc_url($style). "'>";
			}
		}
        ?>
        
        <script>
            jQuery(document).ready(function($) {
                let table_column_fields = <?php echo json_encode($this->table_column_fields) ?>;
				let table = "<?php echo esc_html($this->table_name) ?>";
				let table_name = table+"DataTable";
				if (!table_name) {
					table_name = 'DataTable';
				}

                window[table_name] = $('#'+table+'DataTable').DataTable(<?php echo esc_html($this->table_name) ?>_atts);
				// dataTable.buttons( '.csv' ).disable();

				var headers = $('#'+table+'DataTable thead tr.table-head');
				var filter = $("<tr class='table-search' style='font-weight:normal;'></tr>");

				$.each(table_column_fields, function(field_key, fields){
					let th_elem = $(headers).find("."+field_key);
					let th_class = th_elem.attr('class').replace('has-input-search', '');
					th_class = th_class.replace('sorting', '');
					let text_align = th_elem.css('text-align');                    
					let append_search = $("<td align='"+text_align+"' class='search-container "+th_class+"'></td>");                    
					if (fields.search_html) {
						append_search.append(fields.search_html);
					} else if (!fields.disabled_search && fields.label.length < 100) {
						append_search.append("<input type='text' placeholder='Search " + fields.label + "' class='input-sm form-control' style='width: 100%; text-align:"+text_align+"'/>");
					}
					filter.append(append_search);                    
				});

				//$("table#DataTable thead").append(filter);
				$('#'+table+'DataTable tr.table-search td').each( function (i) {
					$(this).on( 'keyup change', 'input[type="text"]',  function () {
						var index = i;
						if ( <?php echo $this->table_name ?>_table.column(index).search() !== $(this).val() ) {
							<?php echo $this->table_name ?>_table.column(index).search( this.value ).draw();
						}
					} );
				});
            } );
        </script>
        <?php
    }
}