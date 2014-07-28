<?php

function pdf_qc_field_sheet($save_file=false, $spec_revision_no=null) {
    $ci = get_instance();
    require_capability('qc:viewqcspecs');

    $config = qc_pdf_config(QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $spec_revision_no);
    extract($config);

    // Adjust spec width
    $spec_width -= 160;

    $lang_strings = prepare_lang_strings(array('qccheckstobeperformed', 'qcspecifications', 'inspectorsfieldreport'));

    // create new PDF document
    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">'.$details['productnamech'].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $details['productname'] .
                                            '<font face="chinese">('.$details['productnamech'].')</font>');

    if (empty($details['productnamech'])) {
        $details['productnamestr'][QC_SPEC_LANGUAGE_CH] = $details['productnamestr'][QC_SPEC_LANGUAGE_COMBINED] = $details['productname'];
    }

    $ci->load->library('pdf', array('header_title' => 'QA specifications: ' . $details['productcode'], 'page_title' => $lang_strings['inspectorsfieldreport'][$lang], 'header_font_size' => 14));
    $ci->pdf->SetSubject($details['jobno'] . ': ' . $details['productnamestr'][$lang]);
    $ci->pdf->SetKeywords('Chinasavvy, QC, Product, China, Specifications');

    qc_pdf_add_project_details($ci->pdf, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $details, $spec_revision_no);

    // Specifications
    $spec_cat_number = 1;
    $specs_output = $ci->load->view('qc/spec/pdf_qc_specs_title', compact('lang', 'lang_strings'), true);

    $files = $ci->project_model->get_files($project_id, QC_FILE_TYPE_QC);

    $files_output = '';

    if (!empty($files) && (empty($categories) || in_array($ci->speccategory_model->get_files_id(), $categories))) {
        $spec_number = 1;
        $spec_width += 80;

        foreach ($files as $file) {
            $files_output .= $ci->load->view('qc/spec/pdf_product_file', compact('file', 'spec_width', 'spec_number', 'spec_cat_number', 'file'), true);
            $spec_number++;
        }

        $files_table = $ci->load->view('qc/spec/pdf_product_files', compact('category_width', 'spec_cat_number', 'files_output'), true);
        $ci->pdf->writeHTML($files_table, false, false, false, false, '');
        $spec_cat_number = 1;
    }

    $spec_width -= 80;

    $project_photos = array();

    if (empty($specs)) {
        $ci->session->set_flashdata('message', 'No QC specs for this project');
        redirect('qc/project/edit/'.$project_id);
    }

    foreach ($specs as $category_name => $specs_array) {
        $found_additional = false;
        $found_observation = false;
        $spec_cat_number++;

        $category_name = stripslashes($category_name);

        $specs_table = '';
        $spec_number = 1;

        foreach ($specs_array as $spec) {
            if (!empty($categories) && !in_array($spec[QC_SPEC_LANGUAGE_EN]['categoryid'], $categories)) {
                continue 2;
            }

            $sub_number = '';
            $row_color = '';

            if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_ADDITIONAL) {
                $sub_number = 'A.';
                $row_color = '#DDEEFF';
                if (!$found_additional) {
                    $spec_number = 1;
                    $found_additional = true;
                }
            } else if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_OBSERVATION) {
                $sub_number = 'B.';
                $row_color = '#DDFFEE';
                if (!$found_observation) {
                    $spec_number = 1;
                    $found_observation = true;
                }
            }

            $row_span = 1;
            if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') {
                $row_span++;
            }

            $importance = get_lang_for_constant_value('QC_SPEC_IMPORTANCE', $spec[QC_SPEC_LANGUAGE_EN]['importance']);

            if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') {
                if (empty($spec[QC_SPEC_LANGUAGE_CH]['data'])) {
                    $spec[QC_SPEC_LANGUAGE_CH] = array('data' => '');
                }
            }

            if (empty($spec[QC_SPEC_LANGUAGE_EN]['specsid'])) {
                $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['id'];
            } else {
                $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['specsid'];
            }

            if ($photos = $ci->specphoto_model->get(array('spec_id' => $photos_spec_id))) {
                foreach ($photos as $photo) {
                    $project_photos[] = $photo;
                }
            }

            switch($spec[QC_SPEC_LANGUAGE_EN]['importance']) {
                case QC_SPEC_IMPORTANCE_CRITICAL:
                    $permitted_defect = floor($details['samplesize'] * ($details['defectcriticallimit'] / 100));
                    break;
                case QC_SPEC_IMPORTANCE_MAJOR:
                    $permitted_defect = floor($details['samplesize'] * ($details['defectmajorlimit'] / 100));
                    break;
                case QC_SPEC_IMPORTANCE_MINOR:
                    $permitted_defect = floor($details['samplesize'] * ($details['defectminorlimit'] / 100));
                    break;
            }

            $params = compact('row_color',
                              'row_span',
                              'category_name',
                              'spec_cat_number',
                              'sub_number',
                              'spec_number',
                              'importance',
                              'permitted_defect',
                              'spec_width',
                              'spec');

            $specs_table .= $ci->load->view('qc/spec/pdf_qc_inspection_sheet', $params, true);
            $spec_number++;
        }

        $specs_output .= $ci->load->view('qc/spec/pdf_qc_inspection_headings', compact('specs_table', 'spec_cat_number', 'category_name', 'category_width'), true);
    }

    $pdf_signatures = $ci->load->view('qc/spec/pdf_signatures', null, true);

    $ci->pdf->writeHTML($specs_output, true, false, false, false, '');

    $ci->pdf->writeHTML($pdf_signatures, true, false, false, false, '');

    qc_pdf_add_photos($project_photos, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $project_id);
    $procedure_reports = qc_get_procedure_reports($project_id, $procedures);
    return qc_pdf_finalise($project_id, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $include_files, $spec_revision_no, array(), $save_file, null, $procedure_reports);
}

function pdf_qc_specs($save_file=false, $spec_revision_no=null) {
    $ci = get_instance();
    require_capability('qc:viewqcspecs');

    $config = qc_pdf_config(QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $spec_revision_no);
    extract($config);

    // Adjust spec width
    $spec_width -= 80;

    $lang_strings = prepare_lang_strings(array('qccheckstobeperformed', 'qcspecifications'));

    // create new PDF document
    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">'.$details['productnamech'].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $details['productname'] .
                                            '<font face="chinese">('.$details['productnamech'].')</font>');

    if (empty($details['productnamech'])) {
        $details['productnamestr'][QC_SPEC_LANGUAGE_CH] = $details['productnamestr'][QC_SPEC_LANGUAGE_COMBINED] = $details['productname'];
    }

    $ci->load->library('pdf', array('header_title' => 'QA specifications: ' . $details['productcode'], 'page_title' => $lang_strings['qcspecifications'][$lang], 'header_font_size' => 14));
    $ci->pdf->SetSubject($details['jobno'] . ': ' . $details['productnamestr'][$lang]);
    $ci->pdf->SetKeywords('Chinasavvy, QC, Product, China, Specifications');

    qc_pdf_add_project_details($ci->pdf, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $details, $spec_revision_no);

    // Specifications
    $spec_cat_number = 1;
    $specs_output = $ci->load->view('qc/spec/pdf_qc_specs_title', compact('lang', 'lang_strings'), true);

    $files = $ci->project_model->get_files($project_id, QC_FILE_TYPE_QC);

    $files_output = '';

    if (!empty($files) && (empty($categories) || in_array($ci->speccategory_model->get_files_id(), $categories))) {
        $spec_number = 1;
        $spec_width += 80;

        foreach ($files as $file) {
            $files_output .= $ci->load->view('qc/spec/pdf_product_file', compact('file', 'spec_width', 'spec_number', 'spec_cat_number', 'file'), true);
            $spec_number++;
        }

        $files_table = $ci->load->view('qc/spec/pdf_product_files', compact('category_width', 'spec_cat_number', 'files_output'), true);
        $ci->pdf->writeHTML($files_table, false, false, false, false, '');
        $spec_cat_number = 1;
    }

    $spec_width -= 80;

    $project_photos = array();

    if (empty($specs)) {
        $ci->session->set_flashdata('message', 'No QC specs for this project');
        redirect('qc/project/edit/'.$project_id);
    }

    foreach ($specs as $category_name => $specs_array) {
        $found_additional = false;
        $found_observation = false;
        $spec_cat_number++;

        $category_name = stripslashes($category_name);

        $specs_table = '';
        $spec_number = 1;

        foreach ($specs_array as $spec) {
            if (!empty($categories) && !in_array($spec[QC_SPEC_LANGUAGE_EN]['categoryid'], $categories)) {
                continue 2;
            }

            $sub_number = '';
            $row_color = '';

            if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_ADDITIONAL) {
                $sub_number = 'A.';
                $row_color = '#DDEEFF';
                if (!$found_additional) {
                    $spec_number = 1;
                    $found_additional = true;
                }
            } else if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_OBSERVATION) {
                $sub_number = 'B.';
                $row_color = '#DDFFEE';
                if (!$found_observation) {
                    $spec_number = 1;
                    $found_observation = true;
                }
            }

            $row_span = 1;
            if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') {
                $row_span++;
            }

            $importance = get_lang_for_constant_value('QC_SPEC_IMPORTANCE', $spec[QC_SPEC_LANGUAGE_EN]['importance']);

            if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') {
                if (empty($spec[QC_SPEC_LANGUAGE_CH]['data'])) {
                    $spec[QC_SPEC_LANGUAGE_CH] = array('data' => '');
                }
            }

            if (empty($spec[QC_SPEC_LANGUAGE_EN]['specsid'])) {
                $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['id'];
            } else {
                $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['specsid'];
            }

            if ($photos = $ci->specphoto_model->get(array('spec_id' => $photos_spec_id))) {
                foreach ($photos as $photo) {
                    $project_photos[] = $photo;
                }
            }

            $params = compact('row_color',
                              'row_span',
                              'category_name',
                              'spec_cat_number',
                              'sub_number',
                              'spec_number',
                              'importance',
                              'spec_width',
                              'spec');

            $specs_table .= $ci->load->view('qc/spec/pdf_qc_spec_table', $params, true);
            $spec_number++;
        }

        $specs_output .= $ci->load->view('qc/spec/pdf_qc_spec_headings', compact('specs_table', 'spec_cat_number', 'category_name', 'category_width'), true);
    }

    $ci->pdf->writeHTML($specs_output, true, false, false, false, '');
    qc_pdf_add_photos($project_photos, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $project_id);
    $procedure_reports = qc_get_procedure_reports($project_id, $procedures);
    return qc_pdf_finalise($project_id, QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER, $include_files, $spec_revision_no, array(), $save_file, null, $procedure_reports);
}

function pdf_qc_suppliers($save_file=false, $spec_revision_no=null) {
    $ci = get_instance();
    $config = qc_pdf_config(QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER, $spec_revision_no);
    extract($config);

    // Adjust spec width
    $spec_width += 80;

    $category_id = $ci->input->post('category_id');
    $supplier_id = $ci->input->post('supplier_id');

    $processes = array();

    $params = array('project_id' => $project_id, 'supplier_id' => $supplier_id);

    if ($category_id != 0) {
        $params['category_id'] = $category_id;
    }

    $jobs = $ci->job_model->get($params);

    if (!empty($jobs)) {
        foreach ($jobs as $job) {
            $processes[$job->id] = $job;
        }
    }

    $valid_category_ids = array();
    foreach ($processes as $job_id => $job) {
        $valid_category_ids[] = $job->category_id;
    }

    require_capability('qc:viewqcspecs');

    $old_specs = $specs;
    $specs = array();

    if (!is_null($spec_revision_no)) {
        foreach ($old_specs as $category_name => $specs_array) {
            foreach ($specs_array as $langs) {
                $spec = stripslashes_deep($langs[QC_SPEC_LANGUAGE_EN]);

                if (in_array($spec['categoryid'], $valid_category_ids)) {
                    $category = $ci->speccategory_model->get($spec['categoryid']);

                    $specs[$category->name][$spec['id']] = array(QC_SPEC_LANGUAGE_EN => (array) $spec,
                                                                 QC_SPEC_LANGUAGE_CH => (array) $langs[QC_SPEC_LANGUAGE_CH]);
                }
            }
        }
    } else {
        $specs = $ci->project_model->get_specs($project_id, QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER, true, false, $valid_category_ids);
    }

    // Adjust spec width
    $spec_width -= 80;

    // create new PDF document
    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">'.$details['productnamech'].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $details['productname'] .
                                            '<font face="chinese">('.$details['productnamech'].')</font>');

    $ci->load->library('pdf', array('header_title' => 'QA specifications: ' . $details['productcode'], 'page_title' => "QA Specifications", 'header_font_size' => 14));
    $ci->pdf->SetSubject($details['jobno'] . ': ' . $details['productnamestr'][$lang]);
    $ci->pdf->SetKeywords('Chinasavvy, QC, Product, China, Specifications');

    qc_pdf_add_project_details($ci->pdf, QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER, $details, 0, true);

    // Specifications
    $spec_cat_number = 1;
    $specs_output = $ci->load->view('qc/spec/pdf_qc_suppliers_title', array(), true);

    foreach ($specs as $category_name => $specs_array) {
        $found_additional = false;
        $found_observation = false;

        $specs_table = '';
        $spec_number = 1;

        foreach ($specs_array as $spec) {
            $sub_number = '';
            $row_color = '';

            if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_ADDITIONAL) {
                $sub_number = 'A.';
                $row_color = '#DDEEFF';
                if (!$found_additional) {
                    $spec_number = 1;
                    $found_additional = true;
                }
            } else if ($spec[QC_SPEC_LANGUAGE_EN]['type'] == QC_SPEC_TYPE_OBSERVATION) {
                $sub_number = 'B.';
                $row_color = '#DDFFEE';
                if (!$found_observation) {
                    $spec_number = 1;
                    $found_observation = true;
                }
            }

            $row_span = 1;
            if ($lang == QC_SPEC_LANGUAGE_COMBINED) {
                $row_span++;
            }

            if ($lang == QC_SPEC_LANGUAGE_COMBINED && $category_name != 'Files') {
                if (empty($spec[QC_SPEC_LANGUAGE_CH]['data'])) {
                    $spec[QC_SPEC_LANGUAGE_CH] = array('data' => '');
                }
            }

            $params = compact('row_color',
                              'row_span',
                              'category_name',
                              'spec_cat_number',
                              'sub_number',
                              'spec_number',
                              'spec_width',
                              'spec');

            $specs_table .= $ci->load->view('qc/spec/pdf_qc_suppliers_table', $params, true);
            $spec_number++;
        }

        $specs_output .= $ci->load->view('qc/spec/pdf_qc_spec_headings', compact('specs_table', 'spec_cat_number', 'category_name', 'category_width'), true);
        $spec_cat_number++;
    }

    $ci->pdf->writeHTML($specs_output, true, false, false, false, '');

    return qc_pdf_finalise($project_id, QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER, false, $spec_revision_no, array(), $save_file);
}

function pdf_product_specs($save_file=false, $spec_revision_no=null) {
    xdebug_break();$ci = get_instance();
    $config = qc_pdf_config(QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS, $spec_revision_no);
    extract($config);
    //var_dump($config);

    $lang_strings = prepare_lang_strings(array('specifications', 'productspecifications', 'dimensions', 'part', 'length', 'width', 'height', 'diameter', 'thickness', 'weight', 'other'));
    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">'.$details['productnamech'].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $details['productname'] . '<font face="chinese">('.$details['productnamech'].')</font>');

    $ci->load->library('pdf', array('header_title' => 'Product specifications: ' . $details['productcode'], 'page_title' => $lang_strings['productspecifications'][$lang], 'header_font_size' => 14));
    $ci->pdf->SetSubject($details['jobno'] . ': ' . $details['productnamestr'][$lang]);
    $ci->pdf->SetKeywords('Chinasavvy, QC, Product, China, Specifications');

    qc_pdf_add_project_details($ci->pdf, QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS, $details, $spec_revision_no);

    $style = ' style="background-color: #EEEEEE;width: %fpx;" ';

    $parts_output = '';
    foreach ($parts as $part) {
        if (!is_array($part)) {
            $part = (array) $part;
        }

        $parts_output .= $ci->load->view('qc/spec/pdf_product_part', array('part' => stripslashes_deep($part)), true);
    }

    $dimensions_output = $ci->load->view('qc/spec/pdf_product_dimensions', compact('parts_output', 'lang_strings', 'lang', 'style'), true);

    $ci->pdf->writeHTML($dimensions_output, false, false, false, false, '');

    // Specifications
    $spec_cat_number = 2;
    $specs_output = $ci->load->view('qc/spec/pdf_product_specs_title', compact('lang', 'lang_strings'), true);

    $files = $ci->project_model->get_files($project_id, QC_FILE_TYPE_PRODUCT);

    $files_output = '';

    if (!empty($files) && (empty($categories) || in_array($ci->speccategory_model->get_files_id(), $categories))) {
        $spec_number = 1;

       xdebug_break(); foreach ($files as $file) {
            $files_output .= $ci->load->view('qc/spec/pdf_product_file', compact('file', 'spec_width', 'spec_number', 'spec_cat_number', 'file'), true);
            $spec_number++;
        }

        $files_table = $ci->load->view('qc/spec/pdf_product_files', compact('category_width', 'spec_cat_number', 'files_output'), true);
        $ci->pdf->writeHTML($files_table, false, false, false, false, '');
        $spec_cat_number = 3;
    }

    $project_photos = array();

    if (empty($specs)) {
        $ci->session->set_flashdata('message', 'No Product specs for this project!');
        redirect('qc/project/edit/'.$project_id);
        return null;
    }

    foreach ($specs as $category_name => $specs_array) {
        $category_name = stripslashes($category_name);

        $specs_table = '';
        $spec_number = 1;

        foreach ($specs_array as $spec) {
            if (!empty($categories) && !in_array($spec[QC_SPEC_LANGUAGE_EN]['categoryid'], $categories)) {
                continue 2;
            }

            $row_span = 1;
            if ($lang == QC_SPEC_LANGUAGE_COMBINED) {
                $row_span++;
            }

            if ($lang == QC_SPEC_LANGUAGE_COMBINED) {
                if (empty($spec[QC_SPEC_LANGUAGE_CH]['data'])) {
                    $spec[QC_SPEC_LANGUAGE_CH] = array('data' => '');
                }
            } else {

                if (empty($spec[QC_SPEC_LANGUAGE_EN]['specsid'])) {
                    $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['id'];
                } else {
                    $photos_spec_id = $spec[QC_SPEC_LANGUAGE_EN]['specsid'];
                }

                if ($photos = $ci->specphoto_model->get(array('spec_id' => $photos_spec_id))) {
                    foreach ($photos as $photo) {
                        $project_photos[] = $photo;
                    }
                }
            }

            $params = compact('row_span',
                              'category_name',
                              'spec_cat_number',
                              'spec_number',
                              'importance',
                              'spec_width',
                              'spec');

            $specs_table .= $ci->load->view('qc/spec/pdf_product_spec_table', $params, true);
            $spec_number++;
        }

        $params = compact('specs_table', 'spec_cat_number', 'category_name', 'category_width');
        $specs_output .= $ci->load->view('qc/spec/pdf_qc_spec_headings', $params, true);
        $spec_cat_number++;
    }

    $ci->pdf->writeHTML($specs_output, true, false, false, false, '');

    qc_pdf_add_photos($project_photos, QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS, $project_id);

    return qc_pdf_finalise($project_id, QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS, $include_files, $spec_revision_no, $files, $save_file);
}

// Currently, there can be only one job per project/category combination. There is a unique index on these two fields. Later we will need to change the following code.
function pdf_qc_results($save_file=false, $spec_revision_no=null) {
    $ci = get_instance();
    require_capability('qc:viewqcresults');

    $config = qc_pdf_config(QC_EMAIL_REPORT_TYPE_QC_RESULTS, $spec_revision_no);
    extract($config);

    $project_id = $ci->input->post('project_id');
    $job_id = $ci->input->post('job_id');
    $category_id = $ci->input->post('category_id');

    if (empty($job_id)) { // guess job_id from project_id and category_id
        $params = array('project_id' => $project_id);
        if (!empty($category_id)) {
            $params['category_id'] = $category_id;
            $job_id = $ci->job_model->get($params, true)->id; // Second param: $first_only
        }
    }

    if (empty($category_id)) {
        $category_id = -1;
    }

    $processes = array();

    $processes = $ci->job_model->get_qc_results($job_id, $project_id, $category_id);

    $first_process = reset($processes);
    $first_job = $first_process['job'];

    $project = $ci->project_model->get($project_id);
    $suppliers = $ci->project_model->get_suppliers($project->id);

    $category_order = array();

    foreach ($specs as $category_name => $specs_array) {
        foreach ($specs_array as $lang_array) {
            if (!empty($lang_array[QC_SPEC_LANGUAGE_EN]['id'])) {
                $lang_array[QC_SPEC_LANGUAGE_EN]['specsid'] = $lang_array[QC_SPEC_LANGUAGE_EN]['id'];
                $lang_array[QC_SPEC_LANGUAGE_EN]['categoryid'] = $lang_array[QC_SPEC_LANGUAGE_EN]['categoryid'];
            }
            if (!empty($processes[$lang_array[QC_SPEC_LANGUAGE_EN]['categoryid']])) {
                $processes[$lang_array[QC_SPEC_LANGUAGE_EN]['categoryid']]['specs'][] = $lang_array[QC_SPEC_LANGUAGE_EN];
                $category_order[$lang_array[QC_SPEC_LANGUAGE_EN]['categoryid']] = 1;
            }
        }
    }

    $sorted_processes = array();
    foreach ($category_order as $cat_id => $ignore) {
        $sorted_processes[$cat_id] = $processes[$cat_id];
    }
    $processes = $sorted_processes;

    $lang_strings = prepare_lang_strings(array(
        'supplier',
        'importance',
        'checked',
        'qualitycontrolreport',
        'result',
        'critical',
        'major',
        'minor',
        'reportdate',
        'inspectiondate',
        'qcinspectorname',
        'qcspecifications'));

    // create new PDF document
    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => '<font face="chinese">'.$details['productnamech'].'</font>',
                                       QC_SPEC_LANGUAGE_COMBINED => $details['productname'] .
                                            '<font face="chinese">('.$details['productnamech'].')</font>');

    $ci->load->library('pdf', array('header_title' => 'Quality Control Report: ' . $details['productcode'], 'page_title' => $lang_strings['qualitycontrolreport'][$lang], 'header_font_size' => 14));
    $ci->pdf->SetSubject($details['jobno'] . ': ' . $details['productnamestr'][$lang]);
    $ci->pdf->SetKeywords('Chinasavvy, QC, Product, China, Specifications');

    qc_pdf_add_project_details($ci->pdf, QC_EMAIL_REPORT_TYPE_QC_RESULTS, $details, $spec_revision_no);

    $project_photos = array();
    $process_number = 1;
    $processes_output = '';

    // For every process, print one heading with the name of the process, then one row per spec, including additional and observations
    foreach ($processes as $cat_id => $process) {
        if (!empty($categories) && !in_array($cat_id, $categories)) {
            continue;
        }

        $category = $ci->speccategory_model->get($cat_id);
        $job = $process['job'];
        $job_specs = $process['specs'];

        // Print checklist
        $spec_number = 1;
        $found_additional = false;
        $found_observation = false;

        $additional_specs = $ci->job_model->get_additional_specs($job->id);

        foreach ($additional_specs[QC_SPEC_TYPE_ADDITIONAL] as $language) {
            $job_specs[$language[QC_SPEC_LANGUAGE_EN]->id] = $language[QC_SPEC_LANGUAGE_EN];
        }

        foreach ($additional_specs[QC_SPEC_TYPE_OBSERVATION] as $language) {
            $job_specs[$language[QC_SPEC_LANGUAGE_EN]->id] = $language[QC_SPEC_LANGUAGE_EN];
        }

        $specs_output = '';

        foreach ($job_specs as $spec) {
            $spec = (array) $spec;
            $spec_id = (empty($spec['specsid'])) ? $spec['id'] : $spec['specsid'];
            $sub_number = '';
            $row_color = '';

            $importance = get_lang_for_constant_value('QC_SPEC_IMPORTANCE_', $spec['importance']);

            $checked = '-';

            if (!($result = $ci->specresult_model->get(array('specs_id' => $spec_id), true))) {
                $result = null;
            } else {
                $checked = 'Yes';

                if (!$result->checked) {
                    $checked = 'No';
                }
            }

            if (is_null($result)) {
                $critical = $major = $minor = '-';
            } else if ($project->sample_size == 0) {
                $critical = $major = $minor = 'Set project sample size!';
            } else {
                $critical = (is_null($result->defects) || $spec['importance'] != QC_SPEC_IMPORTANCE_CRITICAL) ? '-' : round(($result->defects / $project->sample_size) * 100) . '%';
                $major = (is_null($result->defects) || $spec['importance'] != QC_SPEC_IMPORTANCE_MAJOR) ? '-' : round(($result->defects / $project->sample_size) * 100) . '%';
                $minor = (is_null($result->defects) || $spec['importance'] != QC_SPEC_IMPORTANCE_MINOR) ? '-' : round(($result->defects / $project->sample_size) * 100) . '%';
            }

            if ($spec['type'] == QC_SPEC_TYPE_ADDITIONAL) {
                $sub_number = 'A.';
                $row_color = '#DDEEFF';
                if (!$found_additional) {
                    $spec_number = 1;
                    $found_additional = true;
                }
            } else if ($spec['type'] == QC_SPEC_TYPE_OBSERVATION) {
                $sub_number = 'B.';
                $row_color = '#DDFFEE';
                if (!$found_observation) {
                    $spec_number = 1;
                    $found_observation = true;
                }
            }

            $params = compact('process_number',
                              'spec',
                              'importance',
                              'checked',
                              'critical',
                              'major',
                              'sub_number',
                              'spec_number',
                              'minor');

            $specs_output .= $ci->load->view('qc/spec/pdf_qc_results_specs', $params, true);
            $spec_number++;

            if ($photos = $ci->jobphoto_model->get(array('job_id' => $job->id, 'spec_id' => $spec_id))) {
                foreach ($photos as $photo) {
                    $project_photos[] = $photo;
                }
            }
        }

        $params = compact('process_number',
                          'category',
                          'specs_output',
                          'lang_strings',
                          'job',
                          'lang',
                          'suppliers',
                          'cat_id');

        $processes_output .= $ci->load->view('qc/spec/pdf_qc_results_processes', $params, true);
        $process_number++;
    }

    $results_output = $ci->load->view('qc/spec/pdf_qc_results_table', array('processes_output' => $processes_output), true);

    $ci->pdf->writeHTML($results_output, true, false,false,false, '');

    // Process details (only if one process is requested)
    if ($category_id > 0) {

        $supplier = $ci->company_model->get($job->supplier_id);
        $inspector = '';
        $report_date = '';
        $inspection_date = '';

        if (!empty($job->user_id)) {
            $inspector = $ci->user_model->get_name($job->user_id);
        }

        if (!empty($job->report_date)) {
            $report_date = date('d/m/Y', $job->report_date);
        }
        if (!empty($job->inspection_date)) {
            $inspection_date = date('d/m/Y', $job->inspection_date);
        }

        $params = compact('supplier',
                          'report_date',
                          'inspection_date',
                          'lang_strings',
                          'job',
                          'lang',
                          'results',
                          'inspector');

        $process_details_output = $ci->load->view('qc/spec/pdf_qc_results_details', $params, true);
        $ci->pdf->writeHTML($process_details_output, false, false, false, false, '');
    }

    // Photos if no spec_revision_no was requested
    qc_pdf_add_photos($project_photos, QC_EMAIL_REPORT_TYPE_QC_RESULTS, $project_id);
    $files = $ci->job_model->get_files($job_id);
    $additional_files = array();
    foreach ($files as $file) {
        $file->location = ROOTPATH . '/files/qc/pdf/qc_jobs/'.$file->job_id.'/'.$file->hash;
        $additional_files[] = (array) $file;
    }

    return qc_pdf_finalise($project_id, QC_EMAIL_REPORT_TYPE_QC_RESULTS, $include_files, $spec_revision_no, array(), $save_file, null, array(), $additional_files);
}

function qc_pdf_add_project_details(&$pdf, $type, $details, $spec_revision_no=0, $hide_customer_fields=false) {

    $ci = get_instance();
    $style = ' style="background-color: #EEEEEE;" ';
    $lang = $ci->input->post('lang');
    if (empty($lang)) {
        $lang = QC_SPEC_LANGUAGE_EN;
    }

    $lang_strings = prepare_lang_strings(array('projectdetails',
                                               'jobno',
                                               'productname',
                                               'productcode',
                                               'customerproductcode',
                                               'revisionno',
                                               'lastrevisiondate',
                                               'lastupdatedby',
                                               'batchsize',
                                               'inspectionlevel',
                                               'samplesize',
                                               'permitteddefect',
                                               'critical',
                                               'major',
                                               'minor',
                                               'result',
                                               'relatedproducts',
                                               'shippingmarks'
                                               ));

    if (!$spec_revision_no) {
        $revision_string_parts = explode('/', $details['revisionstring']);
        if ($type == QC_EMAIL_REPORT_TYPE_QC_RESULTS) {
            $spec_revision_no = $revision_string_parts[2];
        } else {
            $spec_revision_no = $revision_string_parts[$type];
        }
    }

    $spec_revision_number = 0;
    $product_name_str_all = (empty($details['productnamech'])) ? $details['productname'] : $details['productname'] . " <font face=\"chinese\">({$details['productnamech']})</font>";
    $product_name_str_ch = (empty($details['productnamech'])) ? $details['productname'] : '<font face="chinese">'.$details['productnamech'].'</font>';

    $details['productnamestr'] = array(QC_SPEC_LANGUAGE_EN => $details['productname'],
                                       QC_SPEC_LANGUAGE_CH => $product_name_str_ch,
                                       QC_SPEC_LANGUAGE_COMBINED => $product_name_str_all);

    $related_products_row = $ci->load->view('qc/project/pdf_related_products', compact('details',
                                                                                       'style',
                                                                                       'lang_strings',
                                                                                       'lang'), true);

    $shipping_marks_row = $ci->load->view('qc/project/pdf_shipping_marks', compact('details',
                                                                                   'style',
                                                                                   'lang_strings',
                                                                                   'lang'), true);

    $details_table = $ci->load->view('qc/project/pdf_details', compact('spec_revision_no',
                                                                       'hide_customer_fields',
                                                                       'lang',
                                                                       'details',
                                                                       'lang_strings',
                                                                       'type',
                                                                       'style',
                                                                       'related_products_row',
                                                                       'shipping_marks_row'), true);

    $pdf->setFontSize(8);
    $pdf->writeHTML($details_table, true, false, false, false, '');
}

function qc_pdf_config($type, $spec_revision_no=null) {
    $ci = get_instance();
    $ci->load->model('qc/project_model');
    $ci->load->model('qc/spec_model');
    $ci->load->model('qc/job_model');
    $ci->load->model('qc/jobphoto_model');
    $ci->load->model('qc/specphoto_model');
    $ci->load->model('qc/specresult_model');
    $ci->load->model('qc/speccategory_model');
    $ci->load->model('qc/specrevision_model');
    $ci->load->model('qc/revision_model');
    $ci->load->helper('lang_helper');

    $categories = $ci->input->post('categories');
    $procedures = $ci->input->post('procedures');
    $lang = $ci->input->post('lang');
    if (empty($lang)) {
        $lang = QC_SPEC_LANGUAGE_EN;
    }

    $project_id = $ci->input->post('project_id');

    if (!($project = $ci->project_model->get($project_id))) {
        add_message('This project does not exist!', 'error');
        redirect('qc/project/edit/'.$project_id);
        return;
    }

    if (is_null($spec_revision_no)) {
        $spec_revision_no = $ci->input->post('spec_revision_no');
    }

    $include_files = true;
    if (!empty($categories)) {
        $include_files = false;

        foreach ($categories as $category_id) {
            $category = $ci->speccategory_model->get($category_id);

            if ($category->name == 'Files') {
                $include_files = true;
            }
        }

        if (!empty($procedures)) {
            $include_files = true;
        }
    }

    xdebug_break();$spec_category_type = ($type == QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS) ? QC_SPEC_CATEGORY_TYPE_PRODUCT : QC_SPEC_CATEGORY_TYPE_QC;

    if (!is_null($spec_revision_no)) {
        $params = array('project_id' => $project_id,
                        'type' => $spec_category_type,
                        'number' => $spec_revision_no);
        $spec_revision = $ci->specrevision_model->get($params, true, 'number DESC');

        if (!empty($spec_revision)) {
            $revision = $ci->revision_model->get($spec_revision->revision_id);
            $revision_data = $ci->revision_model->decode($revision->id, $spec_category_type);
            $specs = $revision_data['specs'];
            $details = $revision_data['details'];
            $parts = $revision_data['parts'];
        } else {
            $details = $ci->project_model->get_details($project_id);
            $specs = $ci->project_model->get_specs($project_id, $spec_category_type);
            $parts = $ci->project_model->get_parts($project_id);
        }

    } else {
        $details = $ci->project_model->get_details($project_id);
        $specs = $ci->project_model->get_specs($project_id, $spec_category_type);
        $parts = $ci->project_model->get_parts($project_id);
    }

    if (empty($parts)) {
        $parts = array();
    }

    $config = array('category_width' => 2108,
                    'spec_width' => 2028,
                    'lang' => $lang,
                    'categories' => $categories,
                    'procedures' => $procedures,
                    'project_id' => $project_id,
                    'include_files' => $include_files,
                    'spec_revision_no' => $spec_revision_no,
                    'specs' => $specs,
                    'details' => $details,
                    'parts' => $parts);

    return $config;
}

function qc_pdf_add_photos($project_photos, $type, $project_id) {
    $ci = get_instance();

    if (!empty($project_photos)) {
        $ci->pdf->addPage();

        // Table of 2 x 3 per page
        // TODO refactor this so the HTML is in the view. A bit tricky right now
        $count = 1;
        $image_row = '<tr>';
        $description_row = '<tr>';
        $photos_html = '<table cellpadding="3" style="text-align: center;" border="1" width="600">';

        foreach ($project_photos as $photo) {
            $folder = ($type == QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS) ? 'product' : 'qc';
            $image_dir = ROOTPATH."/files/$folder/$project_id/photos/small/$photo->hash";

            if ($type == QC_EMAIL_REPORT_TYPE_QC_RESULTS) {
                $image_dir = ROOTPATH."/files/$folder/$project_id/process/$photo->job_id/small/$photo->hash";
            }
            if (!file_exists($image_dir)) {
                continue;
            }

            $image_row .= '<td colSpan="2" border="1" width="870"><img height="670" src="'.$image_dir.'" /></td>'."\n";
            $description_row .= '<td width="70">'.$count.'</td><td width="800"><font face="chinese">'.$photo->description.'</font></td>'."\n";

            if ($count % 6 == 0 && count($project_photos) != 6) {
                // Close photos html and print to pdf every 6 images
                $photos_html .= "$image_row</tr>
                    $description_row</tr>
                    </table>";
                $ci->pdf->writeHTML($photos_html, false, false, false, false, '');
                $ci->pdf->addPage();
                $photos_html = '<table cellpadding="3" style="text-align: center;" border="1" width="600">'."\n";
                $image_row = '<tr>'."\n";
                $description_row = '<tr>'."\n";
            } else if ($count % 2 == 0 && count($project_photos) != 2) {
                $image_row .= '</tr>'."\n";
                $description_row .= '</tr>'."\n";
                $photos_html .= $image_row . $description_row;
                $image_row = '<tr>'."\n";
                $description_row = '<tr>'."\n";
            }

            $count++;
        }

        $photos_html .= "$image_row</tr>
            $description_row</tr>
            </table>";

        if ($count > 1) {
            $ci->pdf->writeHTML(cleanHTML($photos_html), false, false, false, false, '');
        }
    }
}

/**
 * For a QC project, retrieves all associated QC Procedures, generates a PDF file for each (including details, items, photos and files), and appends it to the calling PDF report
 * @param int $project_id
 */
function qc_get_procedure_reports($project_id, $procedures_to_print=array()) {
    $ci = get_instance();
    // Get list of procedures
    $reports = array();

    if ($procedures_to_print === false) {
        return $reports;
    }

    $procedures = $ci->project_model->get_procedures($project_id, true);

    // Generate PDFs
    foreach ($procedures as $procedure_id) {
        if (!empty($procedures_to_print) && in_array($procedure_id, $procedures_to_print)) {
            $reports[] = qc_pdf_procedure_report($procedure_id, true);
        }
    }

    return $reports;
}

/**
 * Generates a QC Procedure report, either for saving to disk (and appending), or for ouputting straight to the browser (through force download)
 * @param int $procedure_id
 */
function qc_pdf_procedure_report($procedure_id, $save_file=false) {
    $ci = get_instance();
    $ci->load->model('qc/procedure_model');
    $ci->load->helper('date');

    $procedure_data = (array) $ci->procedure_model->get($procedure_id);
    $procedure_data['creation_date'] = unix_to_human($procedure_data['creation_date']);
    $procedure_data['revision_date'] = unix_to_human($procedure_data['revision_date']);
    $procedure_data['updated_by'] = $ci->user_model->get_name($procedure_data['updated_by']);

    $pdf = new pdf(array('header_title' => "Procedure {$procedure_data['number']}: {$procedure_data['title']}", 'header_font_size' => 14));
    $pdf->SetSubject("Procedure {$procedure_data['number']}: {$procedure_data['title']}");
    $pdf->SetKeywords('Chinasavvy, QC, Product, China, Procedures');

    $items = $ci->procedure_model->get_items($procedure_id);
    $files = $ci->procedure_model->get_files($procedure_id);
    $photos = $ci->procedure_model->get_photos($procedure_id);

    $procedure_output = $ci->load->view("qc/procedure/export_pdf", array( 'procedure_data' => $procedure_data, 'items' => $items, 'files' => $files, 'photos' => $photos, 'pdf' => $pdf), true);

    $pdf->writeHTML(cleanHTML($procedure_output), false, false, false, false, '');

    $pdf->setFont('tahoma', '', 11);
    $pdf->call_method('moveY', array(-15));

    $items_output = $ci->load->view('qc/procedure/pdf_items', array( 'procedure_data' => $procedure_data, 'items' => $items, 'pdf' => $pdf), true);

    $pdf->writeHTML(cleanHTML($items_output), false, false, false, false, '');

    $files_output = $ci->load->view('qc/procedure/pdf_files', array( 'procedure_data' => $procedure_data, 'files' => $files, 'pdf' => $pdf), true);

    $pdf->writeHTML(cleanHTML($files_output), false, false, false, false, '');

    $photos_output = $ci->load->view('qc/procedure/pdf_photos', array( 'procedure_data' => $procedure_data, 'photos' => $photos, 'pdf' => $pdf), true);

    $pdf->writeHTML(cleanHTML($photos_output), false, false, false, false, '');
    $output_type = 'D';
    $file_name = 'qc_procedure_'.$procedure_id.'.pdf';
    $temp_path = $ci->config->item('files_path') . "qc/pdf/procedures/temp/$file_name";

    // Append PDF files if there are any
    xdebug_break();if (!empty($files)) {
        $pdf->Output($temp_path, 'F');
        $command = '/usr/local/bin/pdftk ' . $temp_path;

        foreach ($files as $file) {
            if (file_exists($ci->config->item('files_path') . "qc/procedures/$procedure_id/files/$file->hash")) {
                $command .= " " . $ci->config->item('files_path') . "qc/procedures/$procedure_id/files/$file->hash";
            }
        }
    }

    // If the $save_file variable is true, we save the file to disk and return its location
    // Otherwise we force a download of the file to the browser
    if ($save_file) {
        $final_path = $ci->config->item('files_path') . "qc/pdf/procedures/$file_name";
        $command .= ' cat output '.$final_path;
        passthru($command);
        return $final_path;
    } else {
        $command .= ' cat output - ';
        header('Content-type: application/pdf');
        header('Content-disposition: attachment; filename="'.$file_name.'"');
        passthru($command);
        die();
    }
}

/**
 * Prepares the filename and location for the PDF file, then saves it to hard drive before optionally sending it to browser for download
 * @TODO If the file is not found on the disk, output a message to the browser: currently it just produces an empty or corrupted PDF file
 * @param int $project_id
 * @param int $type The type of report
 * @param bool $include_files Whether or not to append other PDF files to this one.
 * @param int $spec_revision_no The revision number of the requested document. Used here only to name the file
 * @param array $files Array of PDF files to append to this one
 * @param bool $save_file If true, will save the file to hard drive before outputting to the browser
 * @param string $filename_postfix A custom string to add at the end of the file name (such as process name or date)
 * @return mixed
 */
function qc_pdf_finalise(
    $project_id,
    $type,
    $include_files=false,
    $spec_revision_no=null,
    $files=array(),
    $save_file=true,
    $filename_postfix=null,
    $procedure_reports=array(),
    $additional_files=array()) {

    $ci = get_instance();

    $type_strings = array(QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS => 'product',
                          QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER => 'qc',
                          QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER => 'qc',
                          QC_EMAIL_REPORT_TYPE_QC_RESULTS => 'qc_results');

    $type_string = $type_strings[$type];

    $output_type = 'D';
    $spec_revision_string = (empty($spec_revision_string)) ? '' : '_'.$spec_revision_no;
    $file_name = $type_string.'_specs_report_'.$project_id.$spec_revision_string.$filename_postfix.'.pdf';

    // Attached files are not versioned: no matter which revision you request, you always get the currently attached files
    $files = $ci->project_model->get_files($project_id);

    foreach ($additional_files as $additional_file) {
        $files[] = $additional_file;
    }

    // If there are additional files, first save this one, then use pdftk to merge it with these additional files
    //xdebug_start_trace('tcpdfqcpdfhelper999');

// MARK HERE
    xdebug_break();if (!empty($files) && $include_files) {

        $final_path = $ci->config->item('files_path') . "qc/pdf/{$type_string}_reports/$file_name";

        // Create a temporary file containing just the report
        $temp_file_name = $type_string.'_specs_report_'.$project_id.$spec_revision_string.$filename_postfix.'temp.pdf';

        $temp_path = $ci->config->item('files_path') . "qc/pdf/{$type_string}_reports/$temp_file_name";
       xdebug_break();$ci->pdf->Output($temp_path, 'F');

        // Prepare the pdftk merge command, starting with the temporary file
        $command = '/usr/local/bin/pdftk ' . $temp_path;
        foreach ($files as $file) {

            if ($file['type'] == QC_FILE_TYPE_PRODUCT && $type_string != 'product' && empty($file['job_id'])) {
                continue;
            } else if ($file['type'] == QC_FILE_TYPE_QC && $type_string == 'product' && empty($file['job_id'])) {
                continue;
            }

            if (empty($file['location'])) {
                $file['location'] = $ci->config->item('files_path') . "qc/$project_id/{$file['hash']}";
            }

            if (file_exists($file['location'])) {
                $command .= " " . $file['location'];
            } else {
                echo "File {$file['location']} doesn't exist!!!<br />";
            }
        }

        foreach ($procedure_reports as $file_location) {
            if (file_exists($file_location)) {
                $command .= " " . $file_location;
            }
        }

        // If the $save_file variable is true, we save the file to disk and return its location
        // Otherwise we force a download of the file to the browser
        if ($save_file) {
            $command .= ' cat output '.$final_path;

            passthru($command);
            return $final_path;
        } else {
            $command .= ' cat output - ';
            	//$command .= ' cat output '.$final_path;
                header('Content-type: application/pdf');
            	header('Content-disposition: attachment; filename="'.$type_string.'_specs.pdf"');
            	passthru($command,$err);
		//echo $err;
            die();
        }
    } else { // No additional files to concatenate

        // If the $save_file variable is true, we save the file to disk and return its location
        // Otherwise we force a download of the file to the browser ($output_type == 'D')
        if ($save_file) {
            $output_type = 'D';
            $file = $ci->config->item('files_path') . "qc/pdf/{$type_string}_reports/$file_name";
            $ci->pdf->Output($file, $output_type);
        } else {
            $ci->pdf->Output($file_name, $output_type);
        }

        if ($save_file) {
            return $file;
        }
    }
}
