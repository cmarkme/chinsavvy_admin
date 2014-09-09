<?php
/**
 * Contains the MY_Controller class
 * @package libraries
 */

/**
 * MY_Controller class
 * @package libraries
 */
class MY_Controller extends CI_Controller {
    /**
     * @var boolean $restricted Set this to false in your controllers to bypass the authentication check: non-logged-in users can view these pages.
     */
    var $restricted = true;
    var $public_uris = array('enquiries/enquiry/add', 'cron/update_currency_values', 'cron/backup_qc_specs');

    function __construct() {
        parent::__construct();
        $this->form_validation->set_error_delimiters('<div class="message"><span class="error">', '</span></div>');
        if ($this->restricted) {
            $this->check_auth();
        }
    }

    function check_auth() {
        $this->load->model('login_model');
        if (!$this->login_model->check_session()) {
            $this->session->set_userdata(array('previous_url' => $this->uri->uri_string()));
            redirect('login');
        }
    }

    function process_datatable_params($outputtype='html', $limit_exports=array()) {

        $params = array('page' => $this->input->post('page'),
                        'start' => $this->input->post('iDisplayStart'),
                        'perpage' => $this->input->post('iDisplayLength'),
                        'search' => $this->input->post('sSearch'),
                        'sortcol_0' => $this->input->post('iSortCol_0'),
                        'sortcol_1' => $this->input->post('iSortCol_1'),
                        'sortcol_2' => $this->input->post('iSortCol_2'),
                        'sortcol_3' => $this->input->post('iSortCol_3'),
                        'sortcol_4' => $this->input->post('iSortCol_4'),
                        'sortdir_0' => $this->input->post('sSortDir_0'),
                        'sortdir_1' => $this->input->post('sSortDir_1'),
                        'sortdir_2' => $this->input->post('sSortDir_2'),
                        'sortdir_3' => $this->input->post('sSortDir_3'),
                        'sortdir_4' => $this->input->post('sSortDir_4'),
                        'sortingcols' => $this->input->post('iSortingCols'));

        // For exports, disable paging
        if ($outputtype != 'html' && !in_array($outputtype, $limit_exports)) {
            $params['page'] = null;
            $params['start'] = null;
            $params['perpage'] = null;
        }

        return $params;
    }

    /**
     * Checks for capabilities for each requested action icon, then generates correct HTML based on subsystem,
     *      section and table headings, and appends it as a cell for each row of the data table. It then returns the table_data array
     * @param string $subsystem
     * @param string $section
     * @param array $table_data
     * @param array $actions_to_use These are output in the order in which they are given. An associative array can be given with labels as keys
     * @param array $additional_capabilities Additional caps for special cases, like array('edit' => 'enquiries:viewassignedenquiries')
     * @return array
     */
    function add_action_column($subsystem, $section, $table_data, $actions_to_use=array('pdf', 'edit', 'delete',), $additional_capabilities=array()) {
        $this->load->helper('inflector');

        $table_data['headings']['actions'] = 'Actions';
        $controller_folder = ($subsystem == 'site') ? '' : "$subsystem/";

        $actions_array = array();
        foreach ($actions_to_use as $label => $action) {
            switch ($action) {
                case 'pdf':
                    $thislabel = (is_int($label)) ? 'PDF' : $label;
                    if (has_capability("$subsystem:view" . plural($section)) || (array_key_exists('pdf', $additional_capabilities) && has_capability($additional_capabilities['pdf']))) {
                        $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.'export/export_'.$section.'/pdf/%d" title="'.$thislabel.'">'
                                                 . img(array('src' => 'images/admin/icons/pdf_16.gif', 'class' => 'icon'))
                                                 . '</a>';
                    }
                    break;
                case 'edit':
                    $thislabel = (is_int($label)) ? 'Edit' : $label;
                    if (has_capability("$subsystem:view" . plural($section)) || (array_key_exists('edit', $additional_capabilities) && has_capability($additional_capabilities['edit']))) {
                        $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/edit/%d" title="'.$thislabel.'">'
                                                 . img(array('src' => 'images/admin/icons/edit_16.gif', 'class' => 'icon'))
                                                 . '</a>';
                    }
                    break;
                case 'delete':
                    $thislabel = (is_int($label)) ? 'Delete' : $label;
                    if (has_capability("$subsystem:delete" . plural($section)) || (array_key_exists('delete', $additional_capabilities) && has_capability($additional_capabilities['delete']))) {
                        $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/delete/%d" title="'.$thislabel.'">'
                                                 . img(array('src' => 'images/admin/icons/delete_16.gif',
                                                             'class' => 'icon',
                                                             'title' => 'Completely delete this '.$section.' and all associated records',
                                                             'onclick' => 'return deletethis();'))
                                                 . '</a>';
                    }
                    break;
                case 'vault':
                    $thislabel = (is_int($label)) ? 'Document Vault' : $label;
                  //  if (has_capability("$subsystem:delete" . plural($section)) || (array_key_exists('delete', $additional_capabilities) && has_capability($additional_capabilities['delete']))) {
                        $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/vault/%d" title="'.$thislabel.'">'
                            . img(array('src' => 'images/admin/icons/file-manager.png',
                                    'class' => 'icon',
                                    'title' => 'Completely delete this '.$section.' and all associated records',
                                    //'onclick' => 'return deletethis();'
                                ))
                            . '</a>';
                 //   }
                    break;
                case 'duplicate':
                    $thislabel = (is_int($label)) ? 'Duplicate' : $label;
                    if (has_capability("$subsystem:write" . plural($section)) || (array_key_exists('duplicate', $additional_capabilities) && has_capability($additional_capabilities['duplicate']))) {
                        $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/duplicate/%d" title="'.$thislabel.'">'
                                                 . img(array('src' => 'images/admin/icons/copy_16.gif', 'class' => 'icon'))
                                                 . '</a>';
                    }
                    break;
                case 'user_edit':
                    $thislabel = (is_int($label)) ? 'Edit users' : $label;
                    $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/user_edit/%d" title="'.$thislabel.'">'
                                             . img(array('src' => 'images/admin/icons/user_edit.png', 'class' => 'icon'))
                                             . '</a>';

                    break;
                case 'key':
                    $thislabel = (is_int($label)) ? 'Edit permissions' : $label;
                    $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/capabilities/%d" title="'.$thislabel.'">'
                                             . img(array('src' => 'images/admin/icons/key.png', 'class' => 'icon'))
                                             . '</a>';

                    break;
                default:
                    $thislabel = (is_int($label)) ? $action : $label;
                    $actions_array[$action] = '<a class="'.$action.'" href="/'.$controller_folder.$section.'/'.$action.'/%d" title="'.$thislabel.'">'
                                             . img(array('src' => 'images/admin/icons/'.$action.'.png', 'class' => 'icon'))
                                             . '</a>';
                    break;
            }
        }

        foreach ($table_data['rows'] as $key => $row) {
            $actions = '';

            foreach ($actions_array as $action) {
                $actions .= sprintf($action, $row[0]);
            }
            $table_data['rows'][$key][] = $actions;
        }

        return $table_data;
    }

    /**
     * Sets up page details for all AJAX lists. This can be overridden by any controller, either by overloading the method,
     * or by editing the resulting array directly.
     * @param string $subsystem
     * @param string $section
     * @param array $headings Associate array of headings => labels
     * @return array
     */
    function get_ajax_table_page_details($subsystem, $section, $headings, $icons=array('pdf', 'csv'), $template_name='list') {
        $this->load->helper('inflector');
        $this->lang->load('general', 'english');
        $controller_folder = ($subsystem == 'site') ? '' : "$subsystem/";

        // Set up title bar
        $title_options = array('title' => $this->lang->line($section) . ' List',
                               'help' => $this->lang->line($section) . ' List',
                               'expand' => 'page',
                               'icons' => $icons,
                               'pdf_url' => '/'.$controller_folder.$section.'/browse/pdf',
                               'csv_url' => '/'.$controller_folder.$section.'/browse/csv'
                               );

        $pageDetails = array(
            'title' => 'View ' . $this->lang->line($section),
            'csstoload' => array('jquery.datatable'),
            'jstoload' => array('jquery/jquery'),
            'jstoloadinfooter' => array('jquery/pause',
                                        'jquery/jquery.json',
                                        'jquery/jquery.urlparser',
                                        'jquery/datatables/media/js/jquery.dataTables',
                                        'datatable_pagination',
                                        'jquery/jquery.dump',
                                        'jquery/jquery.qtip'),
            'content_view' => "$controller_folder$section/$template_name",
            'table_headings' => $headings,
            'report_title' => get_title($title_options));
        return $pageDetails;
    }

    /**
     * Sets up page details for all list exports. This can be overridden by any controller, either by overloading the method,
     * or by editing the resulting array directly.
     * @param string $subsystem
     * @param string $section
     * @param array $table_data The entire contents of the list to output
     * @param string $outputtype pdf|xml|csv
     * @return array
     */
    public function get_export_page_details($section, $table_data) {
        $this->lang->load('general', 'english');
        $pageDetails = array(
            'title' => 'View ' . $this->lang->line($section),
            'table_data' => $table_data,
            'style' => ' style="background-color: #EEEEEE;');
        return $pageDetails;
    }

    /**
     * If the page was loaded through an AJAX requests, outputs a JSON-encoded array for jQuery Datatables. Otherwise load the view for this controller
     * @param array $pageDetails
     * @param array $table_data
     * @param int $total_records
     */
    function output_ajax_table($pageDetails, $table_data, $total_records) {


        $display_records = $table_data['numrows'];

        if (IS_AJAX) {
            $output = new stdClass();
            $output->sEcho = $this->input->post('sEcho');
            $output->iTotalRecords = $total_records;
            $output->iTotalDisplayRecords = $display_records;
            $output->aaData = $table_data['rows'];
            echo json_encode($output);
        } else {
            $this->load->view('template/default', $pageDetails);
        }
    }

    /**
     * Outputs the current page to the requested format (pdf, csv, xml). PDF format allows for in-view manipulation of tcpdf object, including
     * calls to writeHTML. The view can optionally not send anything to the browser.
     */
    function output_for_export($subsystem, $section, $outputtype, $pageDetails, $encoding='UTF-8') {
        $controller_folder = ($subsystem == 'site') ? '' : "$subsystem/";
        $this->load->helper('inflector');
        if ($outputtype == 'pdf') {
            $this->load->library('pdf', array('header_title' => ucfirst(plural($section)) . ' Report', 'header_font_size' => 14));
            $pageDetails['pdf'] = $this->pdf;
            $this->pdf->setEncoding($encoding);
            $this->pdf->setCellPadding(55);

            if ($encoding != 'UTF-8') {
                $this->pdf->setFontSubsetting(false);
                $this->pdf->setUnicode(false);
            }

            $this->pdf->SetSubject(ucfirst(plural($section)) . ' Report');
        }

        $output = $this->load->view("$controller_folder$section/list_$outputtype", $pageDetails, true);

        if ($outputtype == 'pdf') {
            if (!empty($output)) {
                $this->pdf->writeHTML($output, false, false, false, false, '');
            }
            $this->pdf->output("$section-report.pdf", 'D');
        } else if ($outputtype == 'csv') {
            $this->output->set_header("Content-type: application/octet-stream");
            $this->output->set_header("Content-Disposition: attachment; filename=\"$section-report.csv\"");
            $this->output->set_output($output);
        } else if ($outputtype == 'xml') {
            $this->output->set_header("Content-type: text/xml");
            $this->output->set_header("Content-Disposition: attachment; filename=\"$section-report.xml\"");
            $this->output->set_output($output);

        }
    }

    /**
     * Generic delete function for DB objects. Looks up the URI segments to determine which model to load and use
     * @param int $id
     */
    function delete($id, $model_name=null) {
        if ($this->uri->segment(1) == 'company') {
            $section = 'company';
            $subsystem = '';
        } else {
            $subsystem = $this->uri->segment(1).'/';
            $section = $this->uri->segment(2);
        }

        if (!empty($model_name)) {
            $section = $model_name;
        }

        $this->load->model($subsystem.$section.'_model', 'my_model');
        $result = $this->my_model->delete($id);

        if (IS_AJAX) {
            $json = new stdClass();

            if ($result) {
                $json->message = "$section $id was successfully deleted";
                $json->id = $id;
                $json->type = 'success';
            } else {
                $json->message = "$section $id could not be deleted";
                $json->id = $id;
                $json->type = 'error';
            }
            echo json_encode($json);
            die();
        } else {
            // @todo handle non-AJAX delete: flash message and redirection
        }
    }
}
