<?php
require WPST_PLUGIN_PATH. 'vendor/autoload.php';
// reference the Dompdf namespace
use Dompdf\Dompdf;
use Dompdf\Options;

class WPSTPdf
{
    public $multiple_print = false;
    public $shipment_id = 0;
    public $shipment_ids = array();

    function __construct($shipment_id=null)
    {
        if ($shipment_id) {
            $this->shipment_ids = is_array($shipment_id) ? $shipment_id : array($shipment_id);
            $this->shipment_id = is_array($shipment_id) ? $shipment_id[0] : $shipment_id;         
        }        
    }

    function print($type)
    {
        global $sendtrace;
        $font_family = $sendtrace->get_setting('advance_dashboard', 'print_font_family', 'Helvetica');
        $paper = $this->get_paper_type($type);
        $pdf_dpi  = apply_filters('wpst_pdf_dpi', 160);

        if ($this->multiple_print) {
            $filename = $type.'-'.time();
        } else {
            $filename = $type.'-'.preg_replace("/[^A-Za-z0-9 ]/", '', get_the_title($this->shipment_id) ).'-'.time();
        }        

        $options = new Options();
        $options->set('defaultFont', $font_family);
        $options->set('isRemoteEnabled', true);
        $options->set('dpi', $pdf_dpi);
        $pdf = new Dompdf($options);

        $html = $this->get_pdf_content($type, 'pdf');   
        $pdf->loadHtml($html);

        // Setup the paper size and orientation
        $pdf->setPaper($paper['size'], $paper['orient']);

        // Render the HTML as PDF
        $pdf->render();

        // Set Pagination if true
        if ($paper['pagination']) {
            $pdf_width = $pdf->getCanvas()->get_width();
            $paging_pos_x = $pdf_width - 37;
            $pdf->getCanvas()->page_text($paging_pos_x, 5, "{PAGE_NUM} of {PAGE_COUNT}", 'Times Roman', 10, array(0,0,0));
        }

        // Output the generated PDF to Browser
        $output = $pdf->output();
        $result = array(
            'fileurl' => '',
            'filename' => ''
        );

        $sendtrace->clean_files('pdf');
        if (file_put_contents($sendtrace->files_dir_path.$filename.'.pdf', $output)) {
            $result = array(
                'fileurl' => $sendtrace->files_dir_url.$filename.'.pdf',
                'filename' => $filename
            );
        }
        return $result;
    }

    function get_pdf_content($type, $sub_folder='')
    {
        global $sendtrace, $WPSTField;
        $font_size = $sendtrace->get_setting('advance_dashboard', 'print_font_size', 26);
        $logo_id = $sendtrace->get_setting('general', 'company_logo', 0);
        $data = array(
            'site_info' => array(
                'site_url' => get_bloginfo('url'),
                'logo_url' => $logo_id ? wp_get_attachment_url($logo_id) : '#',
                'name' => get_bloginfo('name'),
                'description' => get_bloginfo('description'),                
            )
        );

        $sub_folder = !empty($sub_folder) ? $sub_folder.'/' : '';
        $template_path = wpst_get_template($sub_folder.$type.'.tpl');        
        ob_start();
        ?>
        <style>
            * {
                font-size: <?php echo esc_attr($font_size); ?>px;
            }
            body, html { margin: 15px; }
            table { border-collapse: collapse; width: 100%; }
            a { text-decoration: none; color: #000; }
            h1, h2, h3, h4, h5, h6, p {  margin: 0; padding: 0; margin-bottom: 8px; }
            table td { vertical-align: top; margin: 0;}
            .border { border: 1px solid; }
            .border-top { border-top: 1px solid; }
            .border-bottom { border-bottom: 1px solid; }
            .border-right { border-right: 1px solid; }
            .border-left { border-left: 1px solid; }
            .page-break { page-break-before: always; }
        </style>
        <?php
        if (!empty($this->shipment_ids)) {
            foreach ($this->shipment_ids as $idx => $shipment_id) {
                $data['shipment'] = array(
                    'shipment_id' => $shipment_id,
                    'tracking_no' => get_the_title($shipment_id),
                    'barcode_url' => $sendtrace->generate_barcode_url($shipment_id, $type),
                    'packages' => wpst_get_packages_data($shipment_id),                
                    'custom_fields' => $WPSTField->fields($shipment_id)
                );
        
                require $template_path;
                if ($this->multiple_print && $idx != count($this->shipment_ids)-1) {
                    echo "<div class='page-break'></div>";
                }
            }
        }
        return ob_get_clean();
    }

    function sizes()
    {
        $sizes = array(
            'label' => array(
                'size' => 'A6',
                'orient' => 'portrait',
                'pagination' => true
            ),
            'invoice' => array(
                'size' => 'Letter',
                'orient' => 'portrait',
                'pagination' => false
            ),
            'waybill' => array(
                'size' => 'A4',
                'orient' => 'landscape',
                'pagination' => false
            )
        );
        return apply_filters('wpst_pdf_print_sizes', $sizes);
    }

    function get_paper_type($type)
    {
        $paper_size = array(
            'size' => 'A4',
            'orient' => 'portrait'
        );
        $sizes = $this->sizes();
        if (array_key_exists($type, $sizes)) {
            $paper_size  = $sizes[$type];
        }
        return $paper_size;
    }
}