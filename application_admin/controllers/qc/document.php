<?php
/**
 * Contains the Document Controller class
 * @package controllers
 */

/**
 * Document Controller class
 * @package controllers
 */
class Document extends MY_Controller {
    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('qc' => array('document|QC reports')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/specrevision_model');
    }

    function index() {
        return $this->browse();
    }

    /**
     * Most of the functionality used in this action is coded in MY_Controller. This function takes care of
     * capability checks, setup of search filters and manipulation of model
     */
    function browse($output_type='html') {
        require_capability('qc:viewprojects');
        log_user_action("is currently browsing QC documents");

        $this->load->helper('title');
        $this->load->library('filter');

        $this->filter->add_filter('text', 'Product code', 'code', 'productcode');
        $this->filter->add_filter('text', 'Product name', 'name', 'productname');

        $total_records = $this->project_model->count_all_results();

        // Unless this is an AJAX request, limit the search to 0 results
        $limit = null;

        if (!IS_AJAX && $output_type == 'html') {
            $limit = 1;
        }

        $datatable_params = parent::process_datatable_params($output_type);

        $table_data = $this->project_model->get_data_for_listing($datatable_params, $this->filter->filters, $limit, true);

        // Add table headings
        $table_data['headings']['productspecs'] = 'Product Specs';
        $table_data['headings']['qcspecs'] = 'QC Specs';
        $table_data['headings']['fieldreports'] = 'QC Field Reports';
        $table_data['headings']['qcresults'] = 'QC Results';

        // Add column data to each row
        foreach ($table_data['rows'] as $key => $row) {
            $project_id = $row[0];

            $product_report_cell = '-';
            $qc_report_cell = '-';
            $qc_field_report_cell = '-';
            $results_cell = '-';

            $product_constant = QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS;
            $product_constant_label = 'product';
            $qc_constant = QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER;
            $qc_constant_label = 'qc';
            $results_constant = QC_EMAIL_REPORT_TYPE_QC_RESULTS;
            $results_constant_label = 'results';

            $spec_revisions_array = array($product_constant => array(0 => 'Select revision..'),
                                          $qc_constant => array(0 => 'Select revision..'),
                                          $results_constant => array(0 => 'Select revision..')
                                          );
            $last_revision_nos = array($product_constant => 0, $qc_constant => 0, $results_constant => 0);
            $spec_2_report_types = array(QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => QC_SPEC_CATEGORY_TYPE_PRODUCT,
                                         QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => QC_SPEC_CATEGORY_TYPE_QC,
                                         QC_EMAIL_REPORT_TYPE_QC_RESULTS => QC_SPEC_CATEGORY_TYPE_QC);

            if ($spec_revisions = $this->specrevision_model->get(array('project_id' => $project_id), false, 'number DESC')) {
                foreach ($spec_revisions as $spec_revision) {
                    if ($spec_revision->number > $last_revision_nos[$spec_revision->type]) {
                        if ($spec_revision->type == QC_SPEC_CATEGORY_TYPE_PRODUCT) {
                            $last_revision_nos[$product_constant] = $spec_revision->number;
                        } else {
                            $last_revision_nos[$qc_constant] = $spec_revision->number;
                            $last_revision_nos[$results_constant] = $spec_revision->number;
                        }
                    }

                    if ($spec_revision->type == QC_SPEC_CATEGORY_TYPE_PRODUCT) {
                        $spec_revisions_array[$product_constant][$spec_revision->number] = $spec_revision->number;
                    } else {
                        $spec_revisions_array[$qc_constant][$spec_revision->number] = $spec_revision->number;
                        $spec_revisions_array[$results_constant][$spec_revision->number] = $spec_revision->number;
                    }
                }

                if (count($spec_revisions_array[$qc_constant]) > 1) {
                    $qc_report_cell = '<form action="qc/export_pdf/qc_specs" method="post">';
                    $qc_report_cell .= '<input type="hidden" name="project_id" value="'.$project_id.'" />';
                    $qc_report_cell .= '<input type="hidden" name="lang" value="'.QC_SPEC_LANGUAGE_EN.'" />';
                    $qc_report_cell .= '<input type="hidden" name="revision_no" value="0" />';
                    $qc_report_cell .= form_dropdown('revision_'.$qc_constant_label, $spec_revisions_array[$qc_constant]);
                    $qc_report_cell .= '<br />'.form_dropdown('categories[]', array(0 => 'Select a revision first...'));
                    $qc_report_cell .= '<br />'.form_dropdown('procedures[]', array(0 => 'Select a revision first...'), 0, 'id="procedures_'.$project_id.'"');
                    $qc_report_cell .= '<br /><input type="submit" class="pdfgenerator" id="generate_qc_specs_'.$project_id.'" value="Generate PDF" />';
                    $qc_report_cell .= '</form>';

                    $results_cell = '<form action="qc/export_pdf/qc_results" method="post">';
                    $results_cell .= '<input type="hidden" name="project_id" value="'.$project_id.'" />';
                    $results_cell .= '<input type="hidden" name="lang" value="'.QC_SPEC_LANGUAGE_EN.'" />';
                    $results_cell .= '<input type="hidden" name="revision_no" value="0" />';
                    $results_cell .= form_dropdown('revision_'.$results_constant_label, $spec_revisions_array[$results_constant]);
                    $results_cell .= '<br />'.form_dropdown('categories[]', array(0 => 'Select a revision first...'));
                    $results_cell .= '<br /><input type="submit" class="pdfgenerator" id="generate_qc_results_'.$project_id.'" value="Generate PDF" />';
                    $results_cell .= '</form>';

                    $qc_field_report_cell = '<form action="qc/export_pdf/qc_field_sheet" method="post">';
                    $qc_field_report_cell .= '<input type="hidden" name="project_id" value="'.$project_id.'" />';
                    $qc_field_report_cell .= '<input type="hidden" name="lang" value="'.QC_SPEC_LANGUAGE_EN.'" />';
                    $qc_field_report_cell .= '<input type="hidden" name="revision_no" value="0" />';
                    $qc_field_report_cell .= form_dropdown('revision_'.$qc_constant_label, $spec_revisions_array[$qc_constant]);
                    $qc_field_report_cell .= '<br />'.form_dropdown('categories[]', array(0 => 'Select a revision first...'));
                    $qc_field_report_cell .= '<br />'.form_dropdown('procedures[]', array(0 => 'Select a revision first...'), 0, 'id="procedures_'.$project_id.'"');
                    $qc_field_report_cell .= '<br /><input type="submit" class="pdfgenerator" id="generate_qc_specs_'.$project_id.'" value="Generate PDF" />';
                    $qc_field_report_cell .= '</form>';

                } else {
                    $qc_report_cell = $results_cell = $qc_field_report_cell = '-- No results yet --' .
                        '<br><a href="/qc/project/edit/' . $project_id . '" class="button">Edit Project</a>';
                }

                if (count($spec_revisions_array[$product_constant]) > 1) {
                    $product_report_cell = '<form action="qc/export_pdf/product_specs" method="post">';
                    $product_report_cell .= '<input type="hidden" name="project_id" value="'.$project_id.'" />';
                    $product_report_cell .= '<input type="hidden" name="lang" value="'.QC_SPEC_LANGUAGE_EN.'" />';
                    $product_report_cell .= '<input type="hidden" name="revision_no" value="0" />';
                    $product_report_cell .= form_dropdown('revision_'.$product_constant_label, $spec_revisions_array[$product_constant]);
                    $product_report_cell .= '<br />'.form_dropdown('categories[]', array(0 => 'Select a revision first...'));
                    $product_report_cell .= '<br /><input type="submit" class="pdfgenerator" id="generate_product_specs_'.$project_id.'" value="Generate PDF" />';
                    $product_report_cell .= '</form>';
                }

            }

            if (!has_capability('qc:viewproductspecs')) {
                $product_report_cell = '-';
            }

            if (!has_capability('qc:viewqcspecs')) {
                $qc_report_cell = '-';
                $qc_field_report_cell = '-';
            }

            if (!has_capability('qc:viewqcresults')) {
                $results_cell = '-';
            }

            $table_data['rows'][$key][5] = $product_report_cell;
            $table_data['rows'][$key][6] = $qc_report_cell;
            $table_data['rows'][$key][7] = $qc_field_report_cell;
            $table_data['rows'][$key][8] = $results_cell;
        }

        if ($output_type == 'html') {
            $page_details = parent::get_ajax_table_page_details('qc', 'document', $table_data['headings']);
            $title = 'PDF reports';
            $page_details['main_title'] = get_title(array('title' => $title, 'expand' => 'page', 'icons' => array()));
            $page_details['options_title'] = get_title(array('title' => 'Options', 'expand' => 'options', 'icons' => array(), 'level' => 2));
            $page_details['filters_title'] = get_title(array('title' => 'Filters', 'expand' => 'filters', 'icons' => array(), 'level' => 2));
            $project_icons = array('save');
            if (has_capability('qc:writeprojects')) {
                $project_icons[] = 'add';
            }
            $page_details['projects_title'] = get_title(array('title' => 'Project list', 'expand' => 'projects', 'icons' => $project_icons, 'level' => 2));
            parent::output_ajax_table($page_details, $table_data, $total_records);
        } else {
            $page_details = parent::get_export_page_details('document', $table_data);
            $page_details['widths'] = array(128, 240, 200, 300, 200, 200, 248, 375, 345);

            parent::output_for_export('qc', 'document', $output_type, $page_details);
        }
    }

    public function update_spec_categories($project_id, $revision_no, $type) {


        $type_int = QC_SPEC_CATEGORY_TYPE_PRODUCT;
        $only_with_jobs = $type == 'results';

        if ($type != 'product') {
            $type_int = QC_SPEC_CATEGORY_TYPE_QC;
        }

        $categories = $this->project_model->get_categories($project_id, $type_int, $revision_no, $only_with_jobs);
        echo json_encode($categories);
        die();
    }

    public function update_spec_procedures($project_id) {


        $procedures = $this->project_model->get_procedures($project_id);
        echo json_encode($procedures);
        die();
    }
}
