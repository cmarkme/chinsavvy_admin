var constants = {};
constants.PATHINFO_DIRNAME = '1';
constants.PATHINFO_BASENAME = '2';
constants.PATHINFO_EXTENSION = '4';
constants.PATHINFO_FILENAME = '8';
constants.PATH_SEPARATOR = ';';
constants.CURRENCY_USD = '1';
constants.CURRENCY_AUD = '2';
constants.CURRENCY_EUR = '3';
constants.CURRENCY_GBP = '4';
constants.CURRENCY_CNY = '5';
constants.COMPANY_ADDRESS_TYPE_SHIPPING = '1';
constants.COMPANY_ADDRESS_TYPE_BILLING = '2';
constants.COMPANY_ADDRESS_TYPE_CORPORATE = '3';
constants.COMPANY_ADDRESS_TYPE_PUBLIC = '4';
constants.COMPANY_ADDRESS_TYPE_CH = '5';
constants.COMPANY_ROLE_CUSTOMER = '1';
constants.COMPANY_ROLE_ENQUIRER = '2';
constants.COMPANY_ROLE_SUPPLIER = '3';
constants.COMPANY_TYPE_DISTRIBUTOR = '1';
constants.COMPANY_TYPE_INDIVIDUAL_RETAILER = '2';
constants.COMPANY_TYPE_MAILORDER = '3';
constants.COMPANY_TYPE_MANUFACTURER = '4';
constants.COMPANY_TYPE_RETAILER_CHAIN = '5';
constants.COMPANY_TYPE_TRADING_COMPANY = '6';
constants.COMPANY_TYPE_OTHER = '7';
constants.ENQUIRIES_EMAIL_TYPE_BASE = '32';
constants.ENQUIRIES_EMAIL_TYPE_ENQUIRY_GENERAL = '33';
constants.ENQUIRIES_EMAIL_TYPE_ENQUIRY_PRODUCT = '34';
constants.ENQUIRIES_EMAIL_TYPE_ENQUIRY_ASSIGNMENT = '35';
constants.ENQUIRIES_EMAIL_TYPE_ENQUIRY_NOTIFY = '36';
constants.ENQUIRIES_EMAIL_TYPE_OUTBOUND = '37';
constants.ENQUIRIES_EMAIL_TYPE_PASSWORD_REMINDER = '38';
constants.ENQUIRIES_EMAIL_TYPE_REGISTRATION = '39';
constants.ENQUIRIES_EMAIL_TYPE_GENERAL = '40';
constants.ENQUIRIES_SHIPPING_SEA = '1';
constants.ENQUIRIES_SHIPPING_AIR = '2';
constants.ENQUIRIES_SHIPPING_COURIER = '3';
constants.ENQUIRIES_SHIPPING_NONE = '4';
constants.ENQUIRIES_SOURCE_EXC = '1';
constants.ENQUIRIES_SOURCE_INTGG = '2';
constants.ENQUIRIES_SOURCE_INTY = '3';
constants.ENQUIRIES_SOURCE_INTMSN = '4';
constants.ENQUIRIES_SOURCE_INTAOL = '5';
constants.ENQUIRIES_SOURCE_INTAV = '6';
constants.ENQUIRIES_SOURCE_BING = '7';
constants.ENQUIRIES_SOURCE_INTOTHER = '8';
constants.ENQUIRIES_SOURCE_RECCS = '9';
constants.ENQUIRIES_SOURCE_RECOTHER = '10';
constants.ENQUIRIES_SOURCE_CBBC = '11';
constants.ENQUIRIES_SOURCE_WEBSITE = '12';
constants.ENQUIRIES_SOURCE_ARTICLE = '13';
constants.ENQUIRIES_SOURCE_OTHER = '14';
constants.ENQUIRIES_ENQUIRY_STATUS_PENDING = '1';
constants.ENQUIRIES_ENQUIRY_STATUS_DECLINED = '2';
constants.ENQUIRIES_ENQUIRY_STATUS_ADDITIONAL = '3';
constants.ENQUIRIES_ENQUIRY_STATUS_STARTED = '4';
constants.ENQUIRIES_ENQUIRY_STATUS_COMPLETED = '5';
constants.ENQUIRIES_ENQUIRY_STATUS_QUOTED = '6';
constants.ENQUIRIES_ENQUIRY_STATUS_ORDERED = '7';
constants.ENQUIRIES_ENQUIRY_STATUS_ARCHIVED = '8';
constants.ENQUIRIES_ENQUIRY_STATUS_HOLD = '9';
constants.ENQUIRIES_ENQUIRY_PRIORITY_URGENT = '1';
constants.ENQUIRIES_ENQUIRY_PRIORITY_IMPORTANT = '2';
constants.ENQUIRIES_ENQUIRY_PRIORITY_NORMAL = '3';
constants.ENQUIRIES_TOOL_PAYMENT_TERMS_40_40_20 = '1';
constants.ENQUIRIES_TOOL_PAYMENT_TERMS_50_50 = '2';
constants.ENQUIRIES_TOOL_PAYMENT_TERMS_100 = '3';
constants.ENQUIRIES_ENQUIRY_DELIVERY_FOB = '1';
constants.ENQUIRIES_ENQUIRY_DELIVERY_CFR = '2';
constants.ENQUIRIES_ENQUIRY_DELIVERY_CIF = '3';
constants.ENQUIRIES_ENQUIRY_DELIVERY_DDP = '4';
constants.ENQUIRIES_ENQUIRY_DELIVERY_EXW = '5';
constants.ENQUIRIES_UPLOAD_ALLOWED_TYPES = 'pdf|doc|xls|gif|jpg|png|psd|csv|txt|sql|zip|dwg|PDF|DOC|XLS|GIF|JPG|PNG|PSD|CSV|TXT|SQL|ZIP|DWG';
constants.ENQUIRIES_OUTBOUND_PAYMENT_TERMS_30_70 = '1';
constants.ENQUIRIES_OUTBOUND_PAYMENT_TERMS_50_50 = '2';
constants.ENQUIRIES_OUTBOUND_PAYMENT_TERMS_OTHER = '3';
constants.ENQUIRIES_REPORT_OVERDUE = '1';
constants.ENQUIRIES_REPORT_PENDING_30 = '2';
constants.ENQUIRIES_REPORT_PENDING_90 = '3';
constants.ENQUIRIES_REPORT_PENDING_180 = '4';
constants.CODES_OVERRIDDEN_DUE_DATE = '1';
constants.CODES_OVERRIDDEN_STATUS_TEXT = '2';
constants.CODES_OVERRIDDEN_STATUS_DESCRIPTION = '4';
constants.CODES_OVERRIDDEN_STATUS_DATE = '8';
constants.CODES_DATE_OPTIONS = 'a:3:{s:7:"minYear";i:2010;s:7:"maxYear";i:2024;s:14:"addEmptyOption";a:3:{s:1:"Y";b:1;s:1:"d";b:1;s:1:"M";b:1;}}';
constants.CODES_MESSAGE_INSERTED_OK = '1';
constants.CODES_MESSAGE_UPDATED_OK = '2';
constants.CODES_MESSAGE_DUPLICATED_OK = '3';
constants.CODES_STATUS_CODES = 'a:9:{i:0;s:26:"--Select a preset status--";s:11:"PO received";s:11:"PO received";s:16:"Waiting for data";s:16:"Waiting for data";s:20:"On Hold for customer";s:20:"On Hold for customer";s:29:"Waiting for customer approval";s:29:"Waiting for customer approval";s:19:"Waiting for payment";s:19:"Waiting for payment";s:18:"Being manufactured";s:18:"Being manufactured";s:21:"Shipped from Supplier";s:21:"Shipped from Supplier";s:25:"In transit to customer...";s:25:"In transit to customer...";}';
constants.QC_INSPECTION_LEVEL_A = '1';
constants.QC_INSPECTION_LEVEL_B = '2';
constants.QC_INSPECTION_LEVEL_TOTAL = '3';
constants.QC_INSPECTION_LEVEL_OTHER = '4';
constants.QC_RESULT_PASS = '1';
constants.QC_RESULT_REJECT = '2';
constants.QC_RESULT_HOLD = '3';
constants.QC_RESULT_CONCESSION_CUSTOMER = '4';
constants.QC_RESULT_CONCESSION_CHINASAVVY = '5';
constants.QC_PROJECT_STATUS_PENDING = '1';
constants.QC_PROJECT_STATUS_CLOSED = '2';
constants.QC_SPEC_TYPE_NORMAL = '1';
constants.QC_SPEC_TYPE_ADDITIONAL = '2';
constants.QC_SPEC_TYPE_OBSERVATION = '3';
constants.QC_SPEC_LANGUAGE_EN = '1';
constants.QC_SPEC_LANGUAGE_CH = '2';
constants.QC_SPEC_LANGUAGE_COMBINED = '3';
constants.QC_SPEC_DATATYPE_INT = '1';
constants.QC_SPEC_DATATYPE_STRING = '2';
constants.QC_SPEC_DATATYPE_FILE = '3';
constants.QC_SPEC_CATEGORY_TYPE_PRODUCT = '1';
constants.QC_SPEC_CATEGORY_TYPE_QC = '2';
constants.QC_FILE_TYPE_PRODUCT = '1';
constants.QC_FILE_TYPE_QC = '2';
constants.QC_SPEC_IMPORTANCE_CRITICAL = '1';
constants.QC_SPEC_IMPORTANCE_MAJOR = '2';
constants.QC_SPEC_IMPORTANCE_MINOR = '3';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_1 = 'Product Name: ';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_2 = 'Product Reference No: ';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_3 = 'Carton Dimensions (cms) LxWxH:  x  x ';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_4 = 'Gross Wt / Net Wt:  / ';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_5 = 'Qty per carton: ';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_6 = 'Carton No.  of  cartons (total qty)';
constants.QC_DEFAULT_SHIPPING_MARKS_LINE_7 = 'Made in China';
constants.QC_EMAIL_TYPE_BASE = '64';
constants.QC_EMAIL_TYPE_PRODUCT_SPECS = '65';
constants.QC_EMAIL_TYPE_QC_SPECS = '66';
constants.QC_EMAIL_TYPE_QC_JOB = '67';
constants.QC_EMAIL_TYPE_QC_RESULTS = '68';
constants.QC_EMAIL_REPORT_TYPE_PRODUCT_SPECS = '1';
constants.QC_EMAIL_REPORT_TYPE_QC_SPECS_CUSTOMER = '2';
constants.QC_EMAIL_REPORT_TYPE_QC_SPECS_SUPPLIER = '3';
constants.QC_EMAIL_REPORT_TYPE_QC_RESULTS = '4';
constants.QC_MESSAGE_INSERTED_OK = '1';
constants.QC_MESSAGE_UPDATED_OK = '2';
constants.QC_MESSAGE_DUPLICATED_OK = '3';
constants.USERS_MESSAGE_UPDATED_OK = '1';
constants.USERS_CONTACT_TYPE_EMAIL = '1';
constants.USERS_CONTACT_TYPE_PHONE = '2';
constants.USERS_CONTACT_TYPE_MOBILE = '3';
constants.USERS_CONTACT_TYPE_FAX = '4';
constants.USERS_TYPE_CORPORATE = '0';
constants.USERS_TYPE_ADMIN = '1';
constants.USERS_TYPE_STAFF = '2';
constants.USERS_TYPE_TECHNICAL = '3';
constants.USER_ADDRESS_TYPE_SHIPPING = '1';
constants.USER_ADDRESS_TYPE_BILLING = '2';
constants.EXCHANGE_COMMODITY_CATEGORY_METALS = '1';
constants.EXCHANGE_COMMODITY_CATEGORY_PLASTICS = '2';
constants.VAULT_FILE_IDENTITY_CUSTOMER = '0';
constants.VAULT_FILE_IDENTITY_CS = '1';
constants.VAULT_FILE_TYPE_ENQUIRY = '0';
constants.VAULT_FILE_TYPE_ORDER = '1';
constants.PATH_IMAGES_ADMIN = '/images/admin';
constants.PATH_QC_FILES = '/files/qc';