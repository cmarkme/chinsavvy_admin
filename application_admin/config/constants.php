<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 					'ab');
define('FOPEN_READ_WRITE_CREATE', 				'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 			'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

/* General Admin constants */
define('EMAIL_FROM_ADDRESS', 'cpd@chinasavvy.com');
define('EMAIL_FROM_NAME', 'ChinaSavvy');
define('IS_AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
define('NET2FTP_ADMIN_GROUP', 22002);
define('NET2FTP_USERS_GROUP', 22001);
define('NET2FTP_UID', 22010);

define('LOGIN_MESSAGE_SESSION_IDLE', 0);
define('LOGIN_MESSAGE_SESSION_EXPIRED', 1);

define('CURRENCY_USD', 1);
define('CURRENCY_AUD', 2);
define('CURRENCY_EUR', 3);
define('CURRENCY_GBP', 4);
define('CURRENCY_CNY', 5);

define('CHINASAVVY_LOGO', 'logo2.png');
define('CHINASAVVY_LOGO_WIDTH', 20);

define('COMPANY_ADDRESS_TYPE_SHIPPING', 1);
define('COMPANY_ADDRESS_TYPE_BILLING', 2);
define('COMPANY_ADDRESS_TYPE_CORPORATE', 3);
define('COMPANY_ADDRESS_TYPE_PUBLIC', 4);
define('COMPANY_ADDRESS_TYPE_CH', 5);

define('COMPANY_ROLE_CUSTOMER', 1);
define('COMPANY_ROLE_ENQUIRER', 2);
define('COMPANY_ROLE_SUPPLIER', 3);

define('COMPANY_TYPE_DISTRIBUTOR', 1);
define('COMPANY_TYPE_INDIVIDUAL_RETAILER', 2);
define('COMPANY_TYPE_MAILORDER', 3);
define('COMPANY_TYPE_MANUFACTURER', 4);
define('COMPANY_TYPE_RETAILER_CHAIN', 5);
define('COMPANY_TYPE_TRADING_COMPANY', 6);
define('COMPANY_TYPE_OTHER', 7);

define('FILE_TYPE_ENQUIRY', 1);
define('FILE_TYPE_OUTBOUND', 2);
define('FILE_TYPE_INBOUND', 3);
define('FILE_TYPE_PUBLIC', 4);

/* Enquiry System constants */
define('ENQUIRIES_EMAIL_TYPE_BASE', 32);
define('ENQUIRIES_EMAIL_TYPE_ENQUIRY_GENERAL', ENQUIRIES_EMAIL_TYPE_BASE + 1);
define('ENQUIRIES_EMAIL_TYPE_ENQUIRY_PRODUCT', ENQUIRIES_EMAIL_TYPE_BASE + 2);
define('ENQUIRIES_EMAIL_TYPE_ENQUIRY_ASSIGNMENT', ENQUIRIES_EMAIL_TYPE_BASE + 3);
define('ENQUIRIES_EMAIL_TYPE_ENQUIRY_NOTIFY', ENQUIRIES_EMAIL_TYPE_BASE + 4);
define('ENQUIRIES_EMAIL_TYPE_OUTBOUND', ENQUIRIES_EMAIL_TYPE_BASE + 5);
define('ENQUIRIES_EMAIL_TYPE_PASSWORD_REMINDER', ENQUIRIES_EMAIL_TYPE_BASE + 6);
define('ENQUIRIES_EMAIL_TYPE_REGISTRATION', ENQUIRIES_EMAIL_TYPE_BASE + 7);
define('ENQUIRIES_EMAIL_TYPE_GENERAL', ENQUIRIES_EMAIL_TYPE_BASE + 8);

define('ENQUIRIES_SHIPPING_SEA', 1);
define('ENQUIRIES_SHIPPING_AIR', 2);
define('ENQUIRIES_SHIPPING_COURIER', 3);
define('ENQUIRIES_SHIPPING_NONE', 4);

define('ENQUIRIES_SOURCE_EXC', 1);
define('ENQUIRIES_SOURCE_INTGG', 2);
define('ENQUIRIES_SOURCE_INTY', 3);
define('ENQUIRIES_SOURCE_INTMSN', 4);
define('ENQUIRIES_SOURCE_INTAOL', 5);
define('ENQUIRIES_SOURCE_INTAV', 6);
define('ENQUIRIES_SOURCE_BING', 7);
define('ENQUIRIES_SOURCE_INTOTHER', 8);
define('ENQUIRIES_SOURCE_RECCS', 9);
define('ENQUIRIES_SOURCE_RECOTHER', 10);
define('ENQUIRIES_SOURCE_CBBC', 11);
define('ENQUIRIES_SOURCE_WEBSITE', 12);
define('ENQUIRIES_SOURCE_ARTICLE', 13);
define('ENQUIRIES_SOURCE_OTHER', 14);

define('ENQUIRIES_ENQUIRY_STATUS_PENDING', 1);
define('ENQUIRIES_ENQUIRY_STATUS_DECLINED', 2);
define('ENQUIRIES_ENQUIRY_STATUS_ADDITIONAL', 3);
define('ENQUIRIES_ENQUIRY_STATUS_STARTED', 4);
define('ENQUIRIES_ENQUIRY_STATUS_COMPLETED', 5);
define('ENQUIRIES_ENQUIRY_STATUS_QUOTED', 6);
define('ENQUIRIES_ENQUIRY_STATUS_ORDERED', 7);
define('ENQUIRIES_ENQUIRY_STATUS_ARCHIVED', 8);
define('ENQUIRIES_ENQUIRY_STATUS_HOLD', 9);

define('ENQUIRIES_ENQUIRY_PRIORITY_URGENT', 1);
define('ENQUIRIES_ENQUIRY_PRIORITY_IMPORTANT', 2);
define('ENQUIRIES_ENQUIRY_PRIORITY_NORMAL', 3);

define('ENQUIRIES_TOOL_PAYMENT_TERMS_40_40_20', 1);
define('ENQUIRIES_TOOL_PAYMENT_TERMS_50_50', 2);
define('ENQUIRIES_TOOL_PAYMENT_TERMS_100', 3);

define('ENQUIRIES_ENQUIRY_DELIVERY_FOB', 1);
define('ENQUIRIES_ENQUIRY_DELIVERY_CFR', 2);
define('ENQUIRIES_ENQUIRY_DELIVERY_CIF', 3);
define('ENQUIRIES_ENQUIRY_DELIVERY_DDP', 4);
define('ENQUIRIES_ENQUIRY_DELIVERY_EXW', 5);

define('ENQUIRIES_UPLOAD_ALLOWED_TYPES', 'pdf|doc|xls|gif|jpg|png|psd|csv|txt|sql|zip|dwg|PDF|DOC|XLS|GIF|JPG|PNG|PSD|CSV|TXT|SQL|ZIP|DWG');

define('ENQUIRIES_OUTBOUND_PAYMENT_TERMS_30_70', 1);
define('ENQUIRIES_OUTBOUND_PAYMENT_TERMS_50_50', 2);
define('ENQUIRIES_OUTBOUND_PAYMENT_TERMS_OTHER', 3);

define('ENQUIRIES_REPORT_OVERDUE', 1);
define('ENQUIRIES_REPORT_PENDING_30', 2);
define('ENQUIRIES_REPORT_PENDING_90', 3);
define('ENQUIRIES_REPORT_PENDING_180', 4);

define('ESTIMATES_OVER_AGE_COST_THRESHOLD', 120); // 120 Days

// In addition to these, special regular expressions should be set up to prevent
// the following patterns:
// - filenames ending with CLSID's
//   \.[a-z][a-z0-9]{2,3}\s*\.[a-z0-9]{3}$
// - Filenames with many contiguous white spaces in them
//    \s{10,}
// - double file extensions
// \.[a-z][a-z0-9]{2,3}\s*\.[a-z0-9]{3}$c
define('RESTRICTED_EXTENSIONS', serialize(array(
    'ade','adp','app','bas',
    'bat','chm','class','cmd',  'cnf','com','cpl',
    'crt','dll','exe',  'fxp',  'hlp','hta','inf',
    'ins','isp','js',   'jse',  'lnk','mad','maf',
    'mag','mam','maq',  'mar',  'mas','mat','mav',
    'maw','mdb','mde',  'mhtml','msc','msi','msp',
    'mst','ops','pcd',  'pif',  'prf','prg','reg',
    'scf','scr','sct',  'shb',  'shs','url','vb',
    'vbe','vbs','wsc',  'wsf',  'wsh','xnk',
    'ADE','ADP','APP','BAS',
    'BAT','CHM','CLASS','CMD',  'CNF','COM','CPL',
    'CRT','DLL','EXE',  'FXP',  'HLP','HTA','INF',
    'INS','ISP','JS',   'JSE',  'LNK','MAD','MAF',
    'MAG','MAM','MAQ',  'MAR',  'MAS','MAT','MAV',
    'MAW','MDB','MDE',  'MHTML','MSC','MSI','MSP',
    'MST','OPS','PCD',  'PIF',  'PRF','PRG','REG',
    'SCF','SCR','SCT',  'SHB',  'SHS','URL','VB',
    'VBE','VBS','WSC',  'WSF',  'WSH','XNK'
    )));

/* Codes System */
define('CODES_OVERRIDDEN_DUE_DATE', 1);
define('CODES_OVERRIDDEN_STATUS_TEXT', 2);
define('CODES_OVERRIDDEN_STATUS_DESCRIPTION', 4);
define('CODES_OVERRIDDEN_STATUS_DATE', 8);

define('CODES_DATE_OPTIONS', serialize(array('minYear' => date('Y') - 4, 'maxYear' => date('Y') + 10, 'addEmptyOption' => array('Y' => true, 'd' => true, 'M' => true))));

define('CODES_MESSAGE_INSERTED_OK', 1);
define('CODES_MESSAGE_UPDATED_OK', 2);
define('CODES_MESSAGE_DUPLICATED_OK', 3);
define('CODES_STATUS_CODES', serialize(array(
    0 => '--Select a preset status--',
    'PO received'=>'PO received',
    'Waiting for data'=>'Waiting for data',
    'On Hold for customer'=>'On Hold for customer',
    'Waiting for customer approval'=>'Waiting for customer approval',
    'Waiting for payment'=>'Waiting for payment',
    'Being manufactured'=>'Being manufactured',
    'Shipped from Supplier'=> 'Shipped from Supplier',
    'In transit to customer...'=>'In transit to customer...')));


/* QC System */
define('QC_INSPECTION_LEVEL_A', 1);
define('QC_INSPECTION_LEVEL_B', 2);
define('QC_INSPECTION_LEVEL_TOTAL', 3);
define('QC_INSPECTION_LEVEL_OTHER', 4);

define('QC_RESULT_PASS', 1);
define('QC_RESULT_REJECT', 2);
define('QC_RESULT_HOLD', 3);
define('QC_RESULT_CONCESSION_CUSTOMER', 4);
define('QC_RESULT_CONCESSION_CHINASAVVY', 5);

define('QC_PROJECT_STATUS_PENDING', 1);
define('QC_PROJECT_STATUS_CLOSED', 2);

define('QC_SPEC_TYPE_NORMAL', 1);
define('QC_SPEC_TYPE_ADDITIONAL', 2);
define('QC_SPEC_TYPE_OBSERVATION', 3);

define('QC_SPEC_LANGUAGE_EN', 1);
define('QC_SPEC_LANGUAGE_CH', 2);
define('QC_SPEC_LANGUAGE_COMBINED', 3);

define('QC_SPEC_DATATYPE_INT', 1);
define('QC_SPEC_DATATYPE_STRING', 2);
define('QC_SPEC_DATATYPE_FILE', 3);

define('QC_SPEC_CATEGORY_TYPE_PRODUCT', 1);
define('QC_SPEC_CATEGORY_TYPE_QC', 2);

define('QC_FILE_TYPE_PRODUCT', 1);
define('QC_FILE_TYPE_QC', 2);

define('QC_SPEC_IMPORTANCE_CRITICAL', 1);
define('QC_SPEC_IMPORTANCE_MAJOR', 2);
define('QC_SPEC_IMPORTANCE_MINOR', 3);

define('QC_DEFAULT_SHIPPING_MARKS_LINE_1', "Product Name: ");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_2', "Product Reference No: ");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_3', "Carton Dimensions (cms) LxWxH:  x  x ");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_4', "Gross Wt / Net Wt:  / ");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_5', "Qty per carton: ");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_6', "Carton No.  of  cartons (total qty)");
define('QC_DEFAULT_SHIPPING_MARKS_LINE_7', "Made in China");

define('QC_EMAIL_TYPE_BASE', 64);
define('QC_EMAIL_TYPE_PRODUCT_SPECS', QC_EMAIL_TYPE_BASE + 1);
define('QC_EMAIL_TYPE_QC_SPECS', QC_EMAIL_TYPE_BASE + 2);
define('QC_EMAIL_TYPE_QC_JOB', QC_EMAIL_TYPE_BASE + 3);
define('QC_EMAIL_TYPE_QC_RESULTS', QC_EMAIL_TYPE_BASE + 4);

define('QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS', 1);
define('QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER', 2);
define('QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER', 3);
define('QC_EMAIL_REPORT_TYPE_QC_RESULTS', 4);

define('QC_MESSAGE_INSERTED_OK', 1);
define('QC_MESSAGE_UPDATED_OK', 2);
define('QC_MESSAGE_DUPLICATED_OK', 3);

/* User system */
define('USERS_MESSAGE_UPDATED_OK', 1);

define('USERS_CONTACT_TYPE_EMAIL', 1);
define('USERS_CONTACT_TYPE_PHONE', 2);
define('USERS_CONTACT_TYPE_MOBILE', 3);
define('USERS_CONTACT_TYPE_FAX', 4);

define('USERS_TYPE_CORPORATE', 0);
define('USERS_TYPE_ADMIN', 1);
define('USERS_TYPE_STAFF', 2);
define('USERS_TYPE_TECHNICAL', 3);

define('USER_ADDRESS_TYPE_SHIPPING', 1);
define('USER_ADDRESS_TYPE_BILLING', 2);

/* Exchange system */
define('EXCHANGE_COMMODITY_CATEGORY_METALS', 1);
define('EXCHANGE_COMMODITY_CATEGORY_PLASTICS', 2);

/* Document Vault */
define('VAULT_FILE_IDENTITY_CUSTOMER', 0);
define('VAULT_FILE_IDENTITY_CS', 1);
define('VAULT_FILE_TYPE_ENQUIRY', 0);
define('VAULT_FILE_TYPE_ORDER', 1);

/* Paths */
define('PATH_IMAGES_ADMIN', '/images/admin');
define('PATH_QC_FILES','/files/qc');

// Create a JS file on-the-fly to declare custom constants
$constants = get_defined_constants();

$userconstants = $constants;
$jsconstants = "var constants = {};\n";
$allowedconstants = array('^USER', '^EXCHANGE_', '^QC', '^CODES', '^CS', '^ENQUIRIES', '^COMPANY', '^CURRENCY', '^PATH', '^VAULT');
$forbiddenconstants = array();

foreach ($userconstants as $constant => $value) {
    $constantok = false;
    foreach ($allowedconstants as $pattern) {
        if (preg_match('/'.$pattern.'/', $constant)) {
            $constantok = true;
        }
    }
    foreach ($forbiddenconstants as $pattern) {
        if (preg_match('/'.$pattern.'/', $constant)) {
            $constantok = false;
        }
    }
    if ($constantok) {
        $jsconstants .= "constants.$constant = '".str_replace("'", "\'", $value)."';\n";
    }
}
file_put_contents(ROOTPATH.'/includes/js/constants.js', $jsconstants);

/* End of file constants.php */
/* Location: ./system/application/config/constants.php */
