<?php
/**
 * Contains the Email Controller class
 * @package controllers
 */

/**
 * Email Controller class
 * @package controllers
 */
class Email extends MY_Controller {

    /// Subject and body templates
    public $bodies = array(
            QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => '
<p>Dear [customername]:</p>

<p>Re: [projectname]</p>

<p>Attached is the product specification (or update) for [projectname].</p>

<p>Please review these specifications and let us know if there are any errors or omissions in the specifications.  If we do not hear from you within 48 hours we will assume the specifications are correct and will be used for production of your order.</p>

<p>If there any errors or omissions please email us as soon as possible with full details of the error or omission so that we can adjust the product specifications prior to production.</p>

<p>Best regards,</p>

<p>[signature]</p>
 ',
            QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => '
<p>Dear [customername]</p>

<p>Re: [projectname]</p>

<p>Attached is the Quality Control procedures for [projectname] for which we previous sent you product specifications.</p>

<p>These Quality Control procedures will be carried out by our inspectors to ensure that the products being made for you will meet the product specifications referred to above.</p>

<p>If you are not in agreement with any of the procedures, or if you feel there are any omissions, please contact us immediately so that amendments can be made to procedures prior to our quality control.</p>

<p>Best regards,</p>

<p>[signature]</p>',
            QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => '
<p>尊敬的[suppliername]</p>

<p>回复: [projectname]</p>

<p>   请查阅随函附上[projectname]的产品规格及质量检测.</p>

<p>   由于这些规格都极为重要,所以您提供给我方的货品也必须符合规格,在接受交期之前,我们的质量检测规范将会显示我们的检测事项.</p>

<p>   如果您已收到我方之前提供给您的[projectname]产品规格以及质量检测,请以随函附上的最新产品规格为标准.</p>

<p>谢谢合作!</p>

<p>中国赛威质量检测部</p>
',
    QC_EMAIL_REPORT_TYPE_QC_RESULTS => '
<p>Dear [customername]:</p>

<p>Re: [projectname]</p>

<p>Attached is the QC Report (or update) for [projectname].</p>

<p>We hope they meet with your expectations.</p>

<p>We welcome any comments or feedback regarding our quality procedures.</p>

<p>Best regards,</p>

<p>[signature]</p>'
);

    public $subjects = array(
        QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => 'New/Revised Product Specifications for [projectname]',
        QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => 'New/Revised QC Procedures for [projectname]',
        QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => 'Chinese text',
        QC_EMAIL_REPORT_TYPE_QC_RESULTS => 'Product QC Report for [projectname]'
    );

    /// DYNAMIC FIELDS
    public $dynamic_fields = array(
        'projectname' => 'The name of the product being QCed',
        'customername' => 'The name of the customer',
        'signature' => 'Your automatic signature',
        'suppliername' => 'The name of the supplier',
        'revisionno' => 'The number of the document revision'
    );


    function __construct() {
        parent::__construct();
        $this->config->set_item('replacer', array('qc' => array('email|QC Email Form')));
        $this->config->set_item('exclude', array('browse'));
        $this->load->model('qc/project_model');
        $this->load->model('qc/spec_model');
        $this->load->model('qc/specrevision_model');
        $this->load->model('qc/revision_model');
        $this->load->model('qc/job_model');
    }

    public function index($project_id=null, $report_type=null) {

        $this->load->library('CKeditor');
        log_user_action('is viewing the QC email interface.');
        $project_id = (empty($project_id)) ? $this->input->post('project_id') : $project_id;
        $report_type = (empty($report_type)) ? $this->input->post('report_type') : $report_type;
        $previous_report_type = $this->input->post('previous_report_type');
        $revision_id = $this->input->post('revision_id');
        $category_id = $this->input->post('category_id');
        $customer_emails = $this->input->post('customer_emails');
        $supplier_id = $this->input->post('supplier_id');
        $email_body = $this->input->post('email_body');
        $subject = $this->input->post('subject');
        $lang = $this->input->post('lang');
        $form_data = $this->input->post('form_data');

        $staff = $this->user_model->get($this->session->userdata('user_id'));

        if (empty($category_id)) {
            $category_id = -1;
        }

        if (!empty($project_id)) {
            $project = $this->project_model->get($project_id);
            if ($project->containschanges) {
                add_message('This project contains unversioned changes. Please ' . '<a href="qc/project/edit/'.$project_id.'">save a new version</a> before sending the email', 'warning');
            }
        }

        // Ignore subject and body if report type has been changed
        if ($report_type != $previous_report_type) {
            $email_body = null;
            $subject = null;
        }

        $revision_no = '';
        $spec_revision_no = '';

        if (!empty($revision_id)) {
            $revision = $this->revision_model->get($revision_id);
            $revision_no = $revision->number;

            if (!is_null($report_type) && $report_type != QC_EMAIL_REPORT_TYPE_QC_RESULTS && !empty($project_id)) {
                $type = ($report_type == QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS) ? QC_SPEC_CATEGORY_TYPE_PRODUCT : QC_SPEC_CATEGORY_TYPE_QC;
                $spec_revision = $this->specrevision_model->get(array('type' => $type, 'revision_id' => $revision_id, 'project_id' => $project_id), true);
                if (!empty($spec_revision)) {
                    $spec_revision_no = $spec_revision->number;
                }
            }
        }

        $errors = $this->process_data();

        $lang_en = $lang == QC_SPEC_LANGUAGE_EN || $lang == QC_SPEC_LANGUAGE_COMBINED;
        $lang_ch = $lang == QC_SPEC_LANGUAGE_CH || $lang == QC_SPEC_LANGUAGE_COMBINED;
        $checked_en = ($lang_en) ? 'checked="checked"' : '';
        $checked_ch = ($lang_ch) ? 'checked="checked"' : '';

        $disabled_en = '';
        $disabled_ch = '';

        if ($report_type == QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS || $report_type == QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER) {
            $disabled_en = ' disabled="disabled"';
            $checked_en = ' checked="checked"';
            $lang_en = true;
        } else if ($report_type == QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER) {
            $disabled_ch = ' disabled="disabled"';
            $checked_ch = ' checked="checked"';
            $lang_ch = true;
        }

        if (empty($email_body) && !empty($report_type)) {
            $email_body = $this->bodies[$report_type];
        }
        if (empty($subject) && !empty($report_type)) {
            $subject = $this->subjects[$report_type];
        }

        $projects_array = array(null => 'Select a project...');
        $report_types = array(null => 'Select a report type...');

        $revisions_array = array(null => 'Select a revision...');
        $revision_params = array();

        if (!empty($project_id) && !empty($report_type)) {
            $revision_params['project_id'] = $project_id;

            if ($report_type == QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS) {
                $revision_params['type'] = QC_SPEC_CATEGORY_TYPE_PRODUCT;
            } else if ($report_type == QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER || $report_type == QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER) {
                $revision_params['type'] = QC_SPEC_CATEGORY_TYPE_QC;
            }

            // Don't display any revisions for QC results report
            if ($report_type != QC_EMAIL_REPORT_TYPE_QC_RESULTS) {
                if ($revisions = $this->specrevision_model->get($revision_params, false, 'number DESC')) {
                    foreach ($revisions as $revision) {
                        if (empty($revision_id)) {
                            $revision_id = $revision->revision_id;
                        }
                        $revisions_array[$revision->revision_id] = $revision->number;
                    }
                }
            }

            $report_types = array();
            $projects_array = array();
        }

        if ($projects = $this->project_model->get(array('status' => QC_PROJECT_STATUS_PENDING), false, 'creation_date DESC')) {
            foreach ($projects as $project) {
                // For QC results, do not include projects that have no results
                if ($report_type == QC_EMAIL_REPORT_TYPE_QC_RESULTS) {
                    if (!($this->job_model->get(array('project_id' => $project->id)))) {
                        continue;
                    }
                }

                $projects_array[$project->id] = $this->project_model->get_name($project->part_id, true);
            }
        }

        $report_types += array(QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => 'Product Specifications',
                               QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => 'QA Specifications for Customer',
                               QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => 'QA Specifications for Supplier',
                               QC_EMAIL_REPORT_TYPE_QC_RESULTS => 'QC Reports');

        $customer_recipients = array();
        $supplier_recipients = array(0 => 'Select a supplier...', -1 => 'All suppliers');
        $categories_array = array(0 => 'All supplier-assigned processes');

        if (!empty($project_id)) {
            $customer_recipients = $this->project_model->get_customer_emails($project_id);

            $this->db->join('qc_spec_categories', 'qc_spec_categories.id = qc_specs.category_id');
            $this->db->join('qc_jobs', 'qc_jobs.category_id = qc_spec_categories.id AND qc_jobs.project_id = qc_specs.project_id');
            $this->db->where('qc_spec_categories.type', QC_SPEC_CATEGORY_TYPE_QC);
            $this->db->distinct();
            $this->db->select('qc_spec_categories.name');
            $this->db->select('qc_spec_categories.id');

            if ($categories = $this->spec_model->get(array('qc_specs.project_id' => $project_id))) {
                foreach ($categories as $category) {
                    $categories_array[$category->id] = $category->name;
                }
            }

            if (!is_null($category_id)) {
                $job_params = array('project_id' => $project_id);

                if ($category_id > 0) { // Get all suppliers assigned to this process
                    $job_params['category_id'] = $category_id;
                }

                // TODO ? Do not show jobs that have status Accepted
                if ($jobs = $this->job_model->get($job_params)) {
                    foreach ($jobs as $job) {
                        if ($supplier = $this->company_model->get($job->supplier_id)) {
                            if (!empty($supplier->email)) {
                                $supplier_recipients[$supplier->id] = $supplier->name . ' &lt;' . $supplier->email . '&gt;';
                            }

                            /* Do we need a second email address? Maybe we need a multiple select like for the customers?
                            if (!empty($supplier->email2)) {
                                $supplier_recipients[$supplier->email2] = $supplier->name . ' &lt;' . $supplier->email2 . '&gt;';
                            }
                            */
                        }
                    }
                }

                // There are no suppliers, empty the array
                if (count($supplier_recipients) == 2) {
                    $supplier_recipients = array();
                // There's only one supplier, remove the "Select a supplier" and "All suppliers" options
                } else if (count($supplier_recipients) == 3) {
                    unset($supplier_recipients[0]);
                    unset($supplier_recipients[-1]);
                    $supplier_id = key($supplier_recipients);
                }

                // Only one process, remove "Select a process" option
                if (count($categories_array) == 2) {
                    unset($categories_array[0]);
                    $category_id = key($categories_array);
                }
            }
        }

        $en_selected = ($lang==QC_SPEC_LANGUAGE_EN) ? 'selected="selected"' : '';
        $ch_selected = ($lang==QC_SPEC_LANGUAGE_CH) ? 'selected="selected"' : '';
        $all_selected = ($lang==QC_SPEC_LANGUAGE_COMBINED) ? 'selected="selected"' : '';

        $title = 'QC Email Form';
        $main_title = get_title(array('title' => $title, 'help' => 'Use this form to email customers or suppliers. Selected admins will also receive a copy of this email.', 'expand' => 'entry_form'));

        $this->load->helper('error');

        $pdf_urls = array(
            QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => "qc/export_pdf/product_specs",
            QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => "qc/export_pdf/qc_specs",
            QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => "qc/export_pdf/qc_suppliers",
            QC_EMAIL_REPORT_TYPE_QC_RESULTS => "qc/export_pdf/qc_results"
        );

        $pdf_url_params = array(
            QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => array('project_id' => $project_id, 'lang' => $lang, 'spec_revision_no' => $spec_revision_no),
            QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => array('project_id' => $project_id, 'lang' => $lang, 'spec_revision_no' => $spec_revision_no),
            QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => array('project_id' => $project_id, 'lang' => $lang, 'supplier_id' => $supplier_id, 'category_id' => $category_id, 'spec_revision_no' => $spec_revision_no),
            QC_EMAIL_REPORT_TYPE_QC_RESULTS => array('project_id' => $project_id, 'lang' => $lang, 'category_id' => $category_id)
        );


        $pageDetails = array('main_title' => $main_title,
                             'title' => $title,
                             'content_view' => 'qc/email',
                             'jstoload' => array('jquery/jquery.json', 'application/qc/email','ckeditor/ckeditor', 'ckeditor/adapters/jquery'),
                             'from' => $this->user_contact_model->get_by_user_id($staff->id, USERS_CONTACT_TYPE_EMAIL, true, true, true),
                             'from_name' => $this->user_model->get_name($staff),
                             'report_type' => $report_type,
                             'report_types' => $report_types,
                             'disabled_en' => $disabled_en,
                             'disabled_ch' => $disabled_ch,
                             'projects_array' => $projects_array,
                             'revisions_array' => $revisions_array,
                             'errors' => $errors,
                             'project_id' => $project_id,
                             'revision_id' => $revision_id,
                             'supplier_id' => $supplier_id,
                             'category_id' => $category_id,
                             'supplier_recipients' => $supplier_recipients,
                             'customer_recipients' => $customer_recipients,
                             'customer_emails' => $customer_emails,
                             'categories_array' => $categories_array,
                             'en_selected' => $en_selected,
                             'ch_selected' => $ch_selected,
                             'all_selected' => $all_selected,
                             'pdf_urls' => $pdf_urls,
                             'pdf_url_params' => $pdf_url_params,
                             'dynamic_fields' => $this->dynamic_fields,
                             'bodies' => $this->bodies,
                             'email_body' => $email_body,
                             'subject' => $subject,
                             'ready_for_sending' => true
                             );
        $this->load->view('template/default', $pageDetails);
    }

    public function process_data($project_id=null, $report_type=null) {

        $this->load->library('zip');
        $this->load->library('email');
        $this->load->model('codes/part_model');
        $this->load->model('codes/codes_project_model');
        $this->load->model('company_model');

        $project_id = (empty($project_id)) ? $this->input->post('project_id') : $project_id;
        $report_type = (empty($report_type)) ? $this->input->post('report_type') : $report_type;
        $from = $this->input->post('from');
        $form_data = $this->input->post('form_data');
        $from_name = $this->input->post('from_name');
        $previous_report_type = $this->input->post('previous_report_type');
        $lang = $this->input->post('lang');
        $report_type = $this->input->post('report_type');
        $customer_emails = $this->input->post('customer_emails');
        $subject = $this->input->post('subject');
        $email_body = $this->input->post('email_body');
        $revision_id = $this->input->post('revision_id');
        $category_id = $this->input->post('category_id');
        $supplier_id = $this->input->post('supplier_id');

        $errors = array();
        $result = true;

        $lang_en = ($lang == QC_SPEC_LANGUAGE_EN || $lang == QC_SPEC_LANGUAGE_COMBINED);
        $lang_ch = ($lang == QC_SPEC_LANGUAGE_CH || $lang == QC_SPEC_LANGUAGE_COMBINED);

        // PROCESS EMAIL SUBMISSION
        if (empty($form_data)) {
            return true;
        }

        // Validation
        if (empty($report_type)) {
            $errors['report_type'] = 'Please select a report type';
            $result = false;
        }

        if (empty($project_id)) {
            $errors['project_id'] = 'Please select a project';
            $result = false;
        }

        if (empty($lang_en) && empty($lang_ch)) {
            $errors['languages'] = 'Please select at least one language';
            $result = false;
        }

        // Get company_id from project_id
        if (!($project = $this->project_model->get($project_id))) {
            add_message('Could not find the referenced QC project! ('.$project_id.')', 'error');
            $errors['general'] = 'Could not find the referenced QC project! ('.$project_id.')';
            $result = false;
        }


        $project = $this->project_model->get($project_id);
        $part = $this->part_model->get($project->part_id);
        $codes_project = $this->codes_project_model->get($part->project_id);
        $company_id = $codes_project->company_id;
        $staff = $this->user_model->get($this->session->userdata('user_id'));

        switch ($report_type) {
            case QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS:
                log_user_action('is attempting to send a QC Product Specs email.');
                if (empty($customer_emails)) {
                    $errors['customer_emails'] = 'Please select at least one customer email address';
                    $result = false;
                }

                if (empty($errors)) {
                    // Set up the email
                    $spec_revision = $this->specrevision_model->get(array('type' => QC_SPEC_CATEGORY_TYPE_PRODUCT, 'revision_id' => $revision_id));
                    $spec_revision_no = $spec_revision[0]->number;

                    foreach ($customer_emails as $customer_email) {
                        $this->email->initialize();
                        $this->email->from($from, $from_name);
                        $this->email->to($customer_email);

                        $user_id = $this->user_model->already_exists($customer_email);

                        // Replace dynamic fields with actual content
                        $dynamic_fields = array('projectname' => $this->project_model->get_name($project->part_id, true),
                                                'customername' => $this->user_model->get_name($user_id),
                                                'signature' => $staff->signature,
                                                'revisionno' => $spec_revision_no);

                        foreach ($dynamic_fields as $tag => $replacement) {
                            $email_body = str_replace("[$tag]", $replacement, $email_body);
                            $subject = str_replace("[$tag]", $replacement, $subject);
                        }

                        $this->email->subject($subject);
                        $this->email->message($email_body);

                        $this->load->helper('qc_pdf_helper');
                        $file_location = pdf_product_specs(true, $spec_revision_no);
                        $this->email->attach($file_location);
                        $result = $result && $this->email->send();
                    }
                    log_user_action('has successfully sent a QC Product Specs email to ' . count($customer_emails) . ' customers.');
                }
                break;

            case QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER:
                if (empty($customer_emails)) {
                    $errors['customer_emails'] = 'Please select at least one customer email address';
                    $result = false;
                }

                if (empty($errors)) {
                    $spec_revision = $this->specrevision_model->get(array('type' => QC_SPEC_CATEGORY_TYPE_QC, 'revision_id' => $revision_id));
                    $spec_revision_no = $spec_revision[0]->number;

                    foreach ($customer_emails as $customer_email) {
                        $this->email->initialize();
                        $this->email->from($from, $from_name);
                        $this->email->to($customer_email);

                        $user_id = $this->user_model->already_exists($customer_email);

                        // Replace dynamic fields with actual content
                        $dynamic_fields = array('projectname' => $this->project_model->get_name($project->part_id, true),
                                                'customername' => $this->user_model->get_name($user_id),
                                                'signature' => $staff->signature,
                                                'revisionno' => $spec_revision_no);

                        foreach ($dynamic_fields as $tag => $replacement) {
                            $email_body = str_replace("[$tag]", $replacement, $email_body);
                            $subject = str_replace("[$tag]", $replacement, $subject);
                        }

                        $this->email->subject($subject);
                        $this->email->message($email_body);

                        $this->load->helper('qc_pdf_helper');
                        $file_location = pdf_qc_specs(true, $spec_revision_no);
                        $this->email->attach($file_location);
                        $result = $result && $this->email->send();
                    }
                    log_user_action('has successfully sent a QC Specs email to ' . count($customer_emails) . ' customers.');
                }

                break;
            case QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER:
                $job_params = array('project_id' => $project_id);
                $processes = array();

                if ($category_id != 0) {
                    $job_params['category_id'] = $category_id;
                }

                if ($jobs = $this->job_model->get($job_params)) {
                    foreach ($jobs as $job) {
                        $processes[$job->id] = $job;
                    }
                }

                $suppliers = array();
                $supplier_processes = array();

                if ($supplier_id == -1) { // We get a list of all suppliers that are assigned to the selected process(es)
                    foreach ($processes as $job_id => $process) {
                        $company = $this->company_model->get($process->supplier_id);
                        $suppliers[$company->id] = $company;
                        $supplier_processes[$company->id][$process->id] = $process;
                    }
                } else {
                    $company = $this->company_model->get($supplier_id);
                    $suppliers[$supplier_id] = $company;
                    $supplier_processes[$company->id] = $processes;
                }

                if (empty($errors)) {
                    $spec_revision = $this->specrevision_model->get(array('type' => QC_SPEC_CATEGORY_TYPE_QC, 'revision_id' => $revision_id));
                    $spec_revision_no = $spec_revision[0]->number;

                    foreach ($suppliers as $supplier_id => $supplier) {
                        $my_processes = $supplier_processes[$supplier_id];

                        $this->email->initialize();
                        $this->email->from($from, $from_name);
                        $this->email->to($company->email);

                        // Replace dynamic fields with actual content
                        $dynamic_fields = array('projectname' => $this->project_model->get_name($project->part_id, true),
                                                'suppliername' => $company->name_ch,
                                                'signature' => $staff->signature,
                                                'revisionno' => $spec_revision_no);

                        foreach ($dynamic_fields as $tag => $replacement) {
                            $email_body = str_replace("[$tag]", $replacement, $email_body);
                            $subject = str_replace("[$tag]", $replacement, $subject);
                        }

                        $this->email->subject($subject);
                        $this->email->message($email_body);

                        $this->load->helper('qc_pdf_helper');
                        $file_location = pdf_qc_suppliers(true, $spec_revision_no);
                        $this->email->attach($file_location);
                        $result = $result && $this->email->send();
                    }
                    log_user_action('has successfully sent a QC report email to ' . count($suppliers) . ' suppliers.');
                }

                break;
            case QC_EMAIL_REPORT_TYPE_QC_RESULTS:
                if (empty($customer_emails)) {
                    $errors['customer_emails'] = 'Please select at least one customer email address';
                    $result = false;
                }

                if (empty($errors)) {

                    foreach ($customer_emails as $customer_email) {
                        $this->email->initialize();
                        $this->email->from($from, $from_name);
                        $this->email->to($customer_email);

                        $user_id = $this->user_model->already_exists($customer_email);

                        // Replace dynamic fields with actual content
                        $dynamic_fields = array('projectname' => $this->project_model->get_name($project->part_id, true),
                                                'customername' => $this->user_model->get_name($user_id),
                                                'signature' => $staff->signature);

                        foreach ($dynamic_fields as $tag => $replacement) {
                            $email_body = str_replace("[$tag]", $replacement, $email_body);
                            $subject = str_replace("[$tag]", $replacement, $subject);
                        }

                        $this->email->subject($subject);
                        $this->email->message($email_body);

                        $this->load->helper('qc_pdf_helper');
                        $file_location = pdf_qc_results(true);
                        $this->email->attach($file_location);
                        $result = $result && $this->email->send();
                    }
                    log_user_action('has successfully sent a QC Results email to ' . count($customer_emails) . ' customers.');
                }

                break;
        }

        if ($result) {
            add_message('Your email was sent successfully', 'success');
        } else {
            add_message('Your email could not be sent', 'error');
        }

        return $errors;
    }
}
