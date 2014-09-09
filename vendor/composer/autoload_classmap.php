<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);

return array(
    'Access' => $baseDir . '/application_admin/controllers/access.php',
    'Admin' => $baseDir . '/application_admin/controllers/admin.php',
    'Autoemail_Model' => $baseDir . '/application_admin/models/autoemail_model.php',
    'Autoemails' => $baseDir . '/application_admin/controllers/autoemails.php',
    'BOM' => $baseDir . '/application_admin/controllers/estimates/bom.php',
    'CI_Session' => $baseDir . '/application_admin/libraries/Session.php',
    'CKEditor' => $baseDir . '/application_admin/libraries/CKeditor.php',
    'Capability_Model' => $baseDir . '/application_admin/models/users/capability_model.php',
    'Codes_project_Model' => $baseDir . '/application_admin/models/codes/codes_project_model.php',
    'Commodity' => $baseDir . '/application_admin/controllers/exchange/commodity.php',
    'Commodity_Model' => $baseDir . '/application_admin/models/exchange/commodity_model.php',
    'Company' => $baseDir . '/application_admin/controllers/company.php',
    'Company_Address_Model' => $baseDir . '/application_admin/models/company_address_model.php',
    'Company_Model' => $baseDir . '/application_admin/models/company_model.php',
    'Country_Model' => $baseDir . '/application_admin/models/country_model.php',
    'Cron' => $baseDir . '/application_admin/controllers/cron.php',
    'Currency_Model' => $baseDir . '/application_admin/models/exchange/currency_model.php',
    'Currencyrate_Model' => $baseDir . '/application_admin/models/exchange/currencyrate_model.php',
    'Customer' => $baseDir . '/application_admin/controllers/codes/customer.php',
    'Customer_Model' => $baseDir . '/application_admin/models/codes/customer_model.php',
    'Dailyvalue_Model' => $baseDir . '/application_admin/models/exchange/dailyvalue_model.php',
    'Dailyvalues' => $baseDir . '/application_admin/controllers/exchange/dailyvalues.php',
    'Datamatrix' => $vendorDir . '/tecnick.com/tcpdf/include/barcodes/datamatrix.php',
    'Dbfield' => $baseDir . '/application_admin/libraries/Dbfield.php',
    'Division' => $baseDir . '/application_admin/controllers/codes/division.php',
    'Division_Model' => $baseDir . '/application_admin/models/codes/division_model.php',
    'Document' => $baseDir . '/application_admin/controllers/qc/document.php',
    'Document_Model' => $baseDir . '/application_admin/models/vault/document_model.php',
    'Eloquent\\AssembliesComponents' => $baseDir . '/application_admin/models/eloquent/estimates/AssembliesComponents.php',
    'Eloquent\\Assembly' => $baseDir . '/application_admin/models/eloquent/estimates/Assembly.php',
    'Eloquent\\BaseModel' => $baseDir . '/application_admin/models/eloquent/BaseModel.php',
    'Eloquent\\Component' => $baseDir . '/application_admin/models/eloquent/estimates/Component.php',
    'Eloquent\\DataTable' => $baseDir . '/application_admin/models/eloquent/DataTable.php',
    'Eloquent\\Enquiry' => $baseDir . '/application_admin/models/eloquent/estimates/Enquiry.php',
    'Eloquent\\Estimate' => $baseDir . '/application_admin/models/eloquent/estimates/Estimate.php',
    'Eloquent\\FixedCost' => $baseDir . '/application_admin/models/eloquent/estimates/FixedCost.php',
    'Eloquent\\Material' => $baseDir . '/application_admin/models/eloquent/estimates/Material.php',
    'Eloquent\\MaterialCost' => $baseDir . '/application_admin/models/eloquent/estimates/MaterialCost.php',
    'Eloquent\\MaterialGrade' => $baseDir . '/application_admin/models/eloquent/estimates/MaterialGrade.php',
    'Eloquent\\MaterialType' => $baseDir . '/application_admin/models/eloquent/estimates/MaterialType.php',
    'Eloquent\\MeasurementUnit' => $baseDir . '/application_admin/models/eloquent/estimates/MeasurementUnit.php',
    'Eloquent\\MyBuilder' => $baseDir . '/application_admin/models/eloquent/DataTable.php',
    'Eloquent\\Part' => $baseDir . '/application_admin/models/eloquent/estimates/Part.php',
    'Eloquent\\PartCost' => $baseDir . '/application_admin/models/eloquent/estimates/PartCost.php',
    'Eloquent\\PriceBreakTrait' => $baseDir . '/application_admin/models/eloquent/estimates/PriceBreakTrait.php',
    'Eloquent\\Process' => $baseDir . '/application_admin/models/eloquent/estimates/Process.php',
    'Eloquent\\ProcessCost' => $baseDir . '/application_admin/models/eloquent/estimates/ProcessCost.php',
    'Eloquent\\ProcessSubtype' => $baseDir . '/application_admin/models/eloquent/estimates/ProcessSubtype.php',
    'Eloquent\\ProcessType' => $baseDir . '/application_admin/models/eloquent/estimates/ProcessType.php',
    'Eloquent\\Product' => $baseDir . '/application_admin/models/eloquent/estimates/Product.php',
    'Eloquent\\SampleSize' => $baseDir . '/application_admin/models/eloquent/qc/SampleSize.php',
    'Eloquent\\SavedPrice' => $baseDir . '/application_admin/models/eloquent/estimates/SavedPrice.php',
    'Eloquent\\Supplier' => $baseDir . '/application_admin/models/eloquent/estimates/Supplier.php',
    'Eloquent\\User' => $baseDir . '/application_admin/models/eloquent/user.php',
    'Eloquent\\ValidationException' => $baseDir . '/application_admin/models/eloquent/BaseModel.php',
    'Email' => $baseDir . '/application_admin/controllers/qc/email.php',
    'Emaillog_Model' => $baseDir . '/application_admin/models/emaillog_model.php',
    'Enquiry' => $baseDir . '/application_admin/controllers/enquiries/enquiry.php',
    'Enquiry_Ajax' => $baseDir . '/application_admin/controllers/enquiries/enquiry_ajax.php',
    'Enquiry_File_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_file_model.php',
    'Enquiry_Inbound_Quotation_Model' => $baseDir . '/application_admin/models/enquiries/inbound_quotation_model.php',
    'Enquiry_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_model.php',
    'Enquiry_Note_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_note_model.php',
    'Enquiry_Product_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_product_model.php',
    'Enquiry_Staff_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_staff_model.php',
    'Enquiry_Supplier_Product_Model' => $baseDir . '/application_admin/models/enquiries/enquiry_supplier_product_model.php',
    'Estimate' => $baseDir . '/application_admin/controllers/estimates/estimate.php',
    'Export_customer_report' => $baseDir . '/application_admin/controllers/codes/export/export_customer_report.php',
    'Export_division_report' => $baseDir . '/application_admin/controllers/codes/export/export_division_report.php',
    'Export_enquiry' => $baseDir . '/application_admin/controllers/enquiries/export/export_enquiry.php',
    'Export_inbound' => $baseDir . '/application_admin/controllers/enquiries/export/export_inbound.php',
    'Export_inbound_report' => $baseDir . '/application_admin/controllers/enquiries/export/export_inbound_report.php',
    'Export_outbound' => $baseDir . '/application_admin/controllers/enquiries/export/export_outbound.php',
    'Export_pdf' => $baseDir . '/application_admin/controllers/qc/export_pdf.php',
    'Export_procedure' => $baseDir . '/application_admin/controllers/qc/export/export_procedure.php',
    'Export_process_report' => $baseDir . '/application_admin/controllers/codes/export/export_process_report.php',
    'Export_product_report' => $baseDir . '/application_admin/controllers/codes/export/export_product_report.php',
    'Export_project_report' => $baseDir . '/application_admin/controllers/codes/export/export_project_report.php',
    'Export_qc_report' => $baseDir . '/application_admin/controllers/codes/export/export_qc_report.php',
    'Export_supplier' => $baseDir . '/application_admin/controllers/codes/export/export_supplier_report.php',
    'FileManager' => $baseDir . '/application_admin/controllers/vault/filemanager.php',
    'Filter' => $baseDir . '/application_admin/libraries/Filter.php',
    'Fixed_Cost' => $baseDir . '/application_admin/controllers/estimates/fixed_cost.php',
    'FlashData' => $baseDir . '/application_admin/libraries/Flashdata.php',
    'Flotgraph' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'Home' => $baseDir . '/application_admin/controllers/home.php',
    'Inbound' => $baseDir . '/application_admin/controllers/enquiries/inbound.php',
    'Job' => $baseDir . '/application_admin/controllers/qc/job.php',
    'Job_Model' => $baseDir . '/application_admin/models/qc/job_model.php',
    'Jobfile_Model' => $baseDir . '/application_admin/models/qc/jobfile_model.php',
    'Jobphoto_Model' => $baseDir . '/application_admin/models/qc/jobphoto_model.php',
    'Login' => $baseDir . '/application_admin/controllers/login.php',
    'Login_Model' => $baseDir . '/application_admin/models/login_model.php',
    'Logout' => $baseDir . '/application_admin/controllers/logout.php',
    'MY_AJAX_Controller' => $baseDir . '/application_admin/core/MY_AJAX_Controller.php',
    'MY_Controller' => $baseDir . '/application_admin/core/MY_Controller.php',
    'MY_Export_Controller' => $baseDir . '/application_admin/core/MY_Export_Controller.php',
    'MY_Form_validation' => $baseDir . '/application_admin/libraries/MY_Form_validation.php',
    'MY_Model' => $baseDir . '/application_admin/core/MY_Model.php',
    'MY_Pagination' => $baseDir . '/application_admin/core/MY_Pagination.php',
    'MY_Router' => $baseDir . '/application_admin/core/MY_Router.php',
    'MY_log' => $baseDir . '/application_admin/libraries/MY_Log.php',
    'Market' => $baseDir . '/application_admin/controllers/exchange/market.php',
    'Market_Model' => $baseDir . '/application_admin/models/exchange/market_model.php',
    'Material_Cost' => $baseDir . '/application_admin/controllers/estimates/material_cost.php',
    'Material_Grade' => $baseDir . '/application_admin/controllers/estimates/material_grade.php',
    'Material_Type' => $baseDir . '/application_admin/controllers/estimates/material_type.php',
    'Multi_upload' => $baseDir . '/application_admin/libraries/Multi_upload.php',
    'Outbound' => $baseDir . '/application_admin/controllers/enquiries/outbound.php',
    'Outbound_Quotation_Model' => $baseDir . '/application_admin/models/enquiries/outbound_quotation_model.php',
    'Outbound_send' => $baseDir . '/application_admin/controllers/enquiries/outbound_send.php',
    'PDF417' => $vendorDir . '/tecnick.com/tcpdf/include/barcodes/pdf417.php',
    'Part' => $baseDir . '/application_admin/controllers/codes/part.php',
    'Part_Model' => $baseDir . '/application_admin/models/codes/part_model.php',
    'Plupload' => $baseDir . '/application_admin/libraries/Plupload.php',
    'Procedure' => $baseDir . '/application_admin/controllers/qc/procedure.php',
    'Procedure_Model' => $baseDir . '/application_admin/models/qc/procedure_model.php',
    'Procedurefile_Model' => $baseDir . '/application_admin/models/qc/procedurefile_model.php',
    'Procedureitem_Model' => $baseDir . '/application_admin/models/qc/procedureitem_model.php',
    'Process' => $baseDir . '/application_admin/controllers/qc/process.php',
    'Process_Cost' => $baseDir . '/application_admin/controllers/estimates/process_cost.php',
    'Process_Model' => $baseDir . '/application_admin/models/codes/process_model.php',
    'Process_Subtype' => $baseDir . '/application_admin/controllers/estimates/process_subtype.php',
    'Process_Type' => $baseDir . '/application_admin/controllers/estimates/process_type.php',
    'Project' => $baseDir . '/application_admin/controllers/qc/project.php',
    'Project_Model' => $baseDir . '/application_admin/models/qc/project_model.php',
    'Projectfile_Model' => $baseDir . '/application_admin/models/qc/projectfile_model.php',
    'Projectpart_Model' => $baseDir . '/application_admin/models/qc/projectpart_model.php',
    'Projectrelated_Model' => $baseDir . '/application_admin/models/qc/projectrelated_model.php',
    'QRcode' => $vendorDir . '/tecnick.com/tcpdf/include/barcodes/qrcode.php',
    'Qc' => $baseDir . '/application_admin/controllers/codes/qc.php',
    'Qc_Model' => $baseDir . '/application_admin/models/codes/qc_model.php',
    'Quotation_files' => $baseDir . '/application_admin/controllers/enquiries/quotation_files.php',
    'Report' => $baseDir . '/application_admin/controllers/enquiries/report.php',
    'Revision_Model' => $baseDir . '/application_admin/models/qc/revision_model.php',
    'Role' => $baseDir . '/application_admin/controllers/users/role.php',
    'Role_Model' => $baseDir . '/application_admin/models/users/role_model.php',
    'Royalutil' => $baseDir . '/application_admin/libraries/royalutil.php',
    'Sample_Size' => $baseDir . '/application_admin/controllers/qc/sample_size.php',
    'Setting' => $baseDir . '/application_admin/controllers/setting.php',
    'Setting_Model' => $baseDir . '/application_admin/models/setting_model.php',
    'Spec' => $baseDir . '/application_admin/controllers/qc/spec.php',
    'Spec_Model' => $baseDir . '/application_admin/models/qc/spec_model.php',
    'Speccategory_Model' => $baseDir . '/application_admin/models/qc/speccategory_model.php',
    'Specification' => $baseDir . '/application_admin/controllers/qc/specification.php',
    'Specphoto_Model' => $baseDir . '/application_admin/models/qc/specphoto_model.php',
    'Specresult_Model' => $baseDir . '/application_admin/models/qc/specresult_model.php',
    'Specrevision_Model' => $baseDir . '/application_admin/models/qc/specrevision_model.php',
    'Supplier' => $baseDir . '/application_admin/controllers/codes/supplier.php',
    'Supplier_Model' => $baseDir . '/application_admin/models/codes/supplier_model.php',
    'TCPDF' => $vendorDir . '/tecnick.com/tcpdf/tcpdf.php',
    'TCPDF2DBarcode' => $vendorDir . '/tecnick.com/tcpdf/tcpdf_barcodes_2d.php',
    'TCPDFBarcode' => $vendorDir . '/tecnick.com/tcpdf/tcpdf_barcodes_1d.php',
    'TCPDF_COLORS' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_colors.php',
    'TCPDF_FILTERS' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_filters.php',
    'TCPDF_FONTS' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_fonts.php',
    'TCPDF_FONT_DATA' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_font_data.php',
    'TCPDF_IMAGES' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_images.php',
    'TCPDF_IMPORT' => $vendorDir . '/tecnick.com/tcpdf/tcpdf_import.php',
    'TCPDF_PARSER' => $vendorDir . '/tecnick.com/tcpdf/tcpdf_parser.php',
    'TCPDF_STATIC' => $vendorDir . '/tecnick.com/tcpdf/include/tcpdf_static.php',
    'Tempfile_Model' => $baseDir . '/application_admin/models/vault/tempfile_model.php',
    'Toast' => $baseDir . '/application_admin/controllers/test/Toast.php',
    'User' => $baseDir . '/application_admin/controllers/users/user.php',
    'User_Address_Model' => $baseDir . '/application_admin/models/users/user_address_model.php',
    'User_Contact_Model' => $baseDir . '/application_admin/models/users/user_contact_model.php',
    'User_Model' => $baseDir . '/application_admin/models/users/user_model.php',
    'User_Model_tests' => $baseDir . '/application_admin/controllers/test/user_model_tests.php',
    'User_Option_Model' => $baseDir . '/application_admin/models/users/user_option_model.php',
    'Verification' => $baseDir . '/application_admin/controllers/verification.php',
    'Welcome' => $baseDir . '/application_admin/controllers/welcome.php',
    'db_backup' => $baseDir . '/application_admin/controllers/db_backup.php',
    'file' => $baseDir . '/application_admin/controllers/vault/file.php',
    'filter_checkbox' => $baseDir . '/application_admin/libraries/Filter.php',
    'filter_combo' => $baseDir . '/application_admin/libraries/Filter.php',
    'filter_dropdown' => $baseDir . '/application_admin/libraries/Filter.php',
    'filter_text' => $baseDir . '/application_admin/libraries/Filter.php',
    'graph_option' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_bars' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_grid' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_legend' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_lines' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_points' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_selection' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_xaxis' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_option_yaxis' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'graph_options' => $baseDir . '/application_admin/libraries/Flotgraph.php',
    'imglib' => $baseDir . '/application_admin/libraries/imglib.php',
    'pdf' => $baseDir . '/application_admin/libraries/pdf.php',
    'pdf_test' => $baseDir . '/application_admin/controllers/pdf_test.php',
);