USE new_admin;
-- Dropping tables
DROP TABLE IF EXISTS `admin_news` ;
DROP TABLE IF EXISTS  `chinasavvy_enquiry_keywords` ;
DROP TABLE IF EXISTS  `chinasavvy_keywords` ;
DROP TABLE IF EXISTS  `ci_sessions` ;
DROP TABLE IF EXISTS  `ci_users` ;
DROP TABLE IF EXISTS  `domains` ;
DROP TABLE IF EXISTS  `ftpquotalimits` ;
DROP TABLE IF EXISTS  `ftpquotatallies` ;
DROP TABLE IF EXISTS  `grouptable` ;
DROP TABLE IF EXISTS  `login_attempts` ;
DROP TABLE IF EXISTS  `records` ;
DROP TABLE IF EXISTS  `sessiondata` ;
DROP TABLE IF EXISTS  `supermasters` ;
DROP TABLE IF EXISTS  `translations` ;
DROP TABLE IF EXISTS  `user_autologin` ;
DROP TABLE IF EXISTS  `user_profiles` ;
DROP TABLE IF EXISTS  `vhtable` ;
DROP TABLE IF EXISTS  `xfer_stat` ;

-- Change of table names
ALTER TABLE users_options RENAME TO user_options;
ALTER TABLE users_contacts RENAME TO user_contacts;
ALTER TABLE chinasavvy_companies RENAME TO companies;
ALTER TABLE chinasavvy_enquiries RENAME TO enquiries;
ALTER TABLE chinasavvy_enquiry_products RENAME TO enquiries_enquiry_products;
ALTER TABLE chinasavvy_enquiry_notes RENAME TO enquiries_enquiry_notes;
ALTER TABLE chinasavvy_files RENAME TO enquiries_files;
ALTER TABLE chinasavvy_enquiry_staff RENAME TO enquiries_enquiry_staff;
ALTER TABLE chinasavvy_inbound_quotations RENAME TO enquiries_inbound_quotations;
ALTER TABLE chinasavvy_outbound_quotations RENAME TO enquiries_outbound_quotations;
ALTER TABLE chinasavvy_supplier_products RENAME TO enquiries_supplier_products;

-- Change of column names
ALTER TABLE  `user_contacts` CHANGE  `contact_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE  `user_contacts` CHANGE  `detail`  `contact` VARCHAR( 64 )  CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '0';
ALTER TABLE  `user_contacts` CHANGE  `type`  `type` VARCHAR( 64 )  CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Mobile';
UPDATE `user_contacts` SET type = 1 WHERE type = 'Email';
UPDATE `user_contacts` SET type = 2 WHERE type = 'Home Phone';
UPDATE `user_contacts` SET type = 2 WHERE type = 'Work Phone';
UPDATE `user_contacts` SET type = 3 WHERE type = 'Mobile';
UPDATE `user_contacts` SET type = 4 WHERE type = 'Fax';
ALTER TABLE  `user_contacts` CHANGE  `type`  `type` tinyint(1)  UNSIGNED NOT NULL DEFAULT '1';

ALTER TABLE  `users` CHANGE  `user_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE  `user_options` CHANGE  `option_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE capabilities CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE capabilities CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE capabilities CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_divisions CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_divisions CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_divisions CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_parts CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_parts CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_parts CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_processes CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_processes CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_processes CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_projects CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_projects CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_projects CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_projects CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_projects CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_projects CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE codes_projects CHANGE  company_id  company_id SMALLINT( 5 ) UNSIGNED NULL;
ALTER TABLE codes_qc CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_qc CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE codes_qc CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE companies CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE countries CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE  `countries` CHANGE  `country_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE email_log CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE email_log CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE email_log ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE  `enquiries` CHANGE  `enquiry_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- Change fields to constant-based integers
UPDATE enquiries SET shipping = 1 WHERE shipping = 'sea';
UPDATE enquiries SET shipping = 2 WHERE shipping = 'air';
UPDATE enquiries SET shipping = 3 WHERE shipping = 'courier';
UPDATE enquiries SET shipping = 4 WHERE shipping = 'none';
ALTER TABLE  `enquiries` CHANGE  `shipping`  `shipping` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries SET source = 1 WHERE source = 'exc';
UPDATE enquiries SET source = 2 WHERE source = 'int-gg';
UPDATE enquiries SET source = 3 WHERE source = 'int-y';
UPDATE enquiries SET source = 4 WHERE source = 'int-msn';
UPDATE enquiries SET source = 5 WHERE source = 'int-aol';
UPDATE enquiries SET source = 6 WHERE source = 'int-av';
UPDATE enquiries SET source = 8 WHERE source = 'int-other';
UPDATE enquiries SET source = 9 WHERE source = 'rec-cs';
UPDATE enquiries SET source = 10 WHERE source = 'rec-other';
UPDATE enquiries SET source = 11 WHERE source = 'cbbc';
UPDATE enquiries SET source = 12 WHERE source = 'website';
UPDATE enquiries SET source = 13 WHERE source = 'article';
UPDATE enquiries SET source = 14 WHERE source = 'other';
ALTER TABLE  `enquiries` CHANGE  `source`  `source` tinyint( 2 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries SET status = 1 WHERE status = 'PENDING';
UPDATE enquiries SET status = 2 WHERE status = 'DECLINED';
UPDATE enquiries SET status = 3 WHERE status = 'ADDITIONAL SPECS/INFO REQUIRED';
UPDATE enquiries SET status = 4 WHERE status = 'SOURCING STARTED';
UPDATE enquiries SET status = 5 WHERE status = 'SOURCING COMPLETED';
UPDATE enquiries SET status = 6 WHERE status = 'CUSTOMER QUOTED';
UPDATE enquiries SET status = 8 WHERE status = 'ARCHIVED';
ALTER TABLE  `enquiries` CHANGE  `status`  `status` tinyint( 2 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries SET delivery_terms = 1 WHERE delivery_terms = 'FOB';
UPDATE enquiries SET delivery_terms = 2 WHERE delivery_terms = 'CFR';
UPDATE enquiries SET delivery_terms = 3 WHERE delivery_terms = 'CIF';
UPDATE enquiries SET delivery_terms = 4 WHERE delivery_terms = 'DDP';
ALTER TABLE  `enquiries` CHANGE  `delivery_terms`  `delivery_terms` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries SET currency = 1 WHERE currency = 'USD';
UPDATE enquiries SET currency = 2 WHERE currency = 'AUD';
UPDATE enquiries SET currency = 3 WHERE currency = 'EUR';
UPDATE enquiries SET currency = 4 WHERE currency = 'GBP';
ALTER TABLE  `enquiries` CHANGE  `currency`  `currency` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries_outbound_quotations SET currency = 1 WHERE currency = 'USD';
UPDATE enquiries_outbound_quotations SET currency = 2 WHERE currency = 'AUD';
UPDATE enquiries_outbound_quotations SET currency = 3 WHERE currency = 'EUR';
UPDATE enquiries_outbound_quotations SET currency = 4 WHERE currency = 'GBP';
ALTER TABLE  `enquiries_outbound_quotations` CHANGE  `currency`  `currency` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries_outbound_quotations SET freight = 1 WHERE freight = 'sea';
UPDATE enquiries_outbound_quotations SET freight = 2 WHERE freight = 'air';
UPDATE enquiries_outbound_quotations SET freight = 3 WHERE freight = 'courier';
UPDATE enquiries_outbound_quotations SET freight = 4 WHERE freight = 'none';
ALTER TABLE  `enquiries_outbound_quotations` CHANGE  `freight`  `freight` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries_outbound_quotations SET delivery_terms = 1 WHERE delivery_terms = 'FOB';
UPDATE enquiries_outbound_quotations SET delivery_terms = 2 WHERE delivery_terms = 'CFR';
UPDATE enquiries_outbound_quotations SET delivery_terms = 3 WHERE delivery_terms = 'CIF';
UPDATE enquiries_outbound_quotations SET delivery_terms = 4 WHERE delivery_terms = 'DDP';
ALTER TABLE  `enquiries_outbound_quotations` CHANGE  `delivery_terms`  `delivery_terms` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
UPDATE enquiries_outbound_quotations SET payment_terms = 1 WHERE payment_terms = '30-70';
UPDATE enquiries_outbound_quotations SET payment_terms = 2 WHERE payment_terms = '50-50';
UPDATE enquiries_outbound_quotations SET payment_terms = 3 WHERE payment_terms = 'other';
ALTER TABLE  `enquiries_outbound_quotations` CHANGE  `payment_terms`  `payment_terms` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;

-- Standardise date and status fields throughout the DB
ALTER TABLE enquiries_enquiry_notes CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE enquiries_enquiry_notes CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE enquiries_enquiry_notes ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE enquiries_enquiry_products CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE enquiries_enquiry_products CHANGE  `enquiry_product_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_enquiry_staff CHANGE  `enquiry_staff_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_enquiry_staff CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE enquiries_files CHANGE  `file_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_files CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE enquiries_inbound_quotations CHANGE  `quotation_inbound_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_inbound_quotations CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE enquiries_inbound_quotations CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE enquiries_outbound_quotations CHANGE  `quotation_outbound_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_outbound_quotations CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
UPDATE enquiries_outbound_quotations SET tool_cost_payment_terms = 1 WHERE tool_cost_payment_terms = '40-40-20';
UPDATE enquiries_outbound_quotations SET tool_cost_payment_terms = 2 WHERE tool_cost_payment_terms = '50-50';
UPDATE enquiries_outbound_quotations SET tool_cost_payment_terms = 3 WHERE tool_cost_payment_terms = '100';
ALTER TABLE  enquiries_outbound_quotations CHANGE  `tool_cost_payment_terms`  `tool_cost_payment_terms` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE enquiries_supplier_products CHANGE  `supplier_product_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_supplier_products CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE enquiries_supplier_products CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE enquiries_supplier_products CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';
ALTER TABLE exchange_commodities ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE exchange_currencies CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_currencies CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_currencies ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE exchange_currency_dailyrates CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_currency_dailyrates CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_currency_dailyrates ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE exchange_dailyvalues ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE exchange_markets CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_markets CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_markets ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE exchange_market_commodities CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_market_commodities CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE exchange_market_commodities ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE message_log CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE message_log CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE message_log ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_jobs ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_job_photos CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_job_photos CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_job_photos ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_presets CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_presets CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_presets ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_projects CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_projects CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_projects CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE qc_projects ADD revision_string VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  '1/1/1' AFTER  revision_date;
ALTER TABLE qc_project_files CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_files CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_files ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_project_parts CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_parts CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_parts ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_project_related CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_related CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_project_related ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_revisions CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_revisions ADD creation_date int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER revision_date;
ALTER TABLE qc_revisions ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `creation_date`;
ALTER TABLE qc_specs CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_specs CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_specs ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_specs_results ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_spec_categories CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_categories CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_categories ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_spec_photos CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_photos CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_photos ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE qc_spec_revisions CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_revisions CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE qc_spec_revisions ADD  `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active' AFTER  `revision_date`;
ALTER TABLE roles CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE roles CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE roles CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE roles_capabilities CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE roles_capabilities CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE roles_capabilities CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE users CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE users_logins CHANGE  `login_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE users_logins CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE users_roles CHANGE creation_date creation_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE users_roles CHANGE revision_date revision_date int(10) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE users_roles CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE user_contacts CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
ALTER TABLE user_options CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';

-- Modify qc_project_files to allow for QA files
ALTER TABLE  `qc_project_files` ADD  `type` TINYINT UNSIGNED NOT NULL DEFAULT  '1' AFTER  `project_id`;

-- Change spec datatype values to ints, then change field type
UPDATE `qc_specs` SET datatype = 1 WHERE datatype = 'int';
UPDATE `qc_specs` SET datatype = 2 WHERE datatype = 'string';
UPDATE `qc_specs` SET datatype = 3 WHERE datatype = 'file';
ALTER TABLE  qc_specs CHANGE  `datatype`  `datatype` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;

-- Change spec language values to ints, then change field type
UPDATE `qc_specs` SET language = 1 WHERE language = 'en';
UPDATE `qc_specs` SET language = 2 WHERE language = 'ch';
ALTER TABLE  qc_specs CHANGE  `language`  `language` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;

-- Change document_type values to ints, then change field type
UPDATE `enquiries_files` SET document_type = 1 WHERE document_type = 'enquiry';
UPDATE `enquiries_files` SET document_type = 2 WHERE document_type = 'outbound';
UPDATE `enquiries_files` SET document_type = 3 WHERE document_type = 'inbound';
UPDATE `enquiries_files` SET document_type = 4 WHERE document_type = 'public';
ALTER TABLE  enquiries_files CHANGE  `document_type`  `document_type` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;

-- Creating new tables from addresses
UPDATE `addresses` SET identifier = 1 WHERE identifier = 'shipping';
UPDATE `addresses` SET identifier = 2 WHERE identifier = 'billing';
UPDATE `addresses` SET identifier = 3 WHERE identifier = 'corporate';
UPDATE `addresses` SET identifier = 4 WHERE identifier = 'public';
UPDATE `addresses` SET identifier = 5 WHERE identifier = 'ch';

CREATE  TABLE  `company_addresses` (
 `id` smallint( 5  )  unsigned NOT  NULL  AUTO_INCREMENT ,
 `company_id` smallint( 5  )  unsigned NOT  NULL DEFAULT  '0',
 `type` tinyint( 1 )  unsigned NOT NULL  DEFAULT 1 ,
 `country_id` smallint( 5  ) unsigned  DEFAULT NULL ,
 `address1` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `address2` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `city` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `province` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `state` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `postcode` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `default_address` tinyint( 1  )  NOT  NULL ,
 `creation_date` int( 10  )  unsigned NOT  NULL DEFAULT  '0',
 `revision_date` int( 10  )  unsigned NOT  NULL DEFAULT  '0',
 `status` enum(  'Active',  'Suspended'  )  COLLATE utf8_unicode_ci NOT  NULL DEFAULT  'Active',
 PRIMARY  KEY (  `id`  ) ,
 KEY  `country_id` (  `country_id`  ) ,
 KEY  `status` (  `status`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = utf8 COLLATE  = utf8_unicode_ci ROW_FORMAT  =  DYNAMIC ;

INSERT INTO company_addresses (company_id, type, country_id, address1, address2, city, province, state, postcode, default_address, creation_date, revision_date, status)
    (SELECT linked_id, identifier, country_id, address1, address2, city, province, state, postcode, default_address, creation_date, revision_date, status FROM addresses WHERE linked_type = 'chinasavvy_companies');

UPDATE `company_addresses` SET type = 2 WHERE type = '0';

ALTER TABLE company_addresses CHANGE status status varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active';

CREATE  TABLE  `user_addresses` (
 `id` smallint( 5  )  unsigned NOT  NULL  AUTO_INCREMENT ,
 `user_id` smallint( 5  )  unsigned NOT  NULL DEFAULT  '0',
 `country_id` smallint( 5  ) unsigned  DEFAULT NULL ,
 `type` tinyint( 1 )  unsigned NOT NULL  DEFAULT 1 ,
 `address1` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `address2` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `city` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `province` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `state` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `postcode` varchar( 255  )  COLLATE utf8_unicode_ci  DEFAULT NULL ,
 `default_address` tinyint( 1  )  NOT  NULL ,
 `creation_date` int( 10  )  unsigned NOT  NULL DEFAULT  '0',
 `revision_date` int( 10  )  unsigned NOT  NULL DEFAULT  '0',
 `status` varchar(32)  COLLATE utf8_unicode_ci NOT  NULL DEFAULT  'Active',
 PRIMARY  KEY (  `id`  ) ,
 KEY  `country_id` (  `country_id`  ) ,
 KEY  `status` (  `status`  )  ) ENGINE  = InnoDB  DEFAULT CHARSET  = utf8 COLLATE  = utf8_unicode_ci ROW_FORMAT  =  DYNAMIC ;

ALTER TABLE user_addresses CHANGE status `status` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active';
DROP TABLE IF EXISTS addresses;

-- Replace company roles by ints
UPDATE companies SET type = 1 WHERE type = 'Distributor';
UPDATE companies SET type = 2 WHERE type = 'Individual Retailer';
UPDATE companies SET type = 3 WHERE type = 'Mail Order';
UPDATE companies SET type = 4 WHERE type = 'Manufacturer';
UPDATE companies SET type = 5 WHERE type = 'Retailer Chain';
UPDATE companies SET type = 6 WHERE type = 'Trading Company';
UPDATE companies SET type = 7 WHERE type = 'Other' OR type = '-- Select One --';
ALTER TABLE  `companies` CHANGE  `type`  `company_type` tinyint( 2 ) UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE  `companies` CHANGE  `company_id`  `id` SMALLINT( 5 ) UNSIGNED NOT NULL AUTO_INCREMENT;

UPDATE companies SET role = 1 WHERE role = 'Customer';
UPDATE companies SET role = 2 WHERE role = 'Enquirer';
UPDATE companies SET role = 3 WHERE role = 'Supplier';
ALTER TABLE  `companies` CHANGE  `role`  `role` tinyint( 2 ) UNSIGNED NOT NULL DEFAULT 1;

-- Change capability names to reflect new subsystem names --
-- Create a temporary copy of the capabilities table --
CREATE TABLE  `capabilities_copy` (
`id` INT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
 `name` VARCHAR( 128 ) COLLATE utf8_unicode_ci NOT NULL ,
 `description` VARCHAR( 255 ) COLLATE utf8_unicode_ci NOT NULL ,
 `type` ENUM(  'read',  'write' ) COLLATE utf8_unicode_ci NOT NULL ,
 `dependson` INT( 3 ) UNSIGNED DEFAULT NULL ,
 `creation_date` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
 `revision_date` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '0',
 `status` VARCHAR( 32 ) COLLATE utf8_unicode_ci NOT NULL DEFAULT  'Active',
PRIMARY KEY (  `id` ) ,
UNIQUE KEY  `name` (  `name` ) ,
KEY  `dependson` (  `dependson` )
) ENGINE = MYISAM DEFAULT CHARSET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = COMPACT AUTO_INCREMENT =268;

INSERT INTO  `capabilities_copy`
SELECT *
FROM  `capabilities` ;

UPDATE capabilities SET name = REPLACE(name, 'chinasavvy', 'enquiries');
UPDATE capabilities SET name = REPLACE(name, 'exchange', 'commodities');
UPDATE capabilities SET name = 'site:viewcompanies' WHERE name = 'enquiries:viewcompanies';
UPDATE capabilities SET name = 'site:editcompanies', dependson = (SELECT id FROM capabilities_copy WHERE name = 'site:doanything') WHERE name = 'enquiries:editcompanies';
UPDATE capabilities SET name = 'site:deletecompanies', dependson = (SELECT id FROM capabilities_copy WHERE name = 'site:doanything') WHERE name = 'enquiries:deletecompanies';
UPDATE capabilities SET name = 'site:writecompanies' WHERE name = 'enquiries:writecompanies';
UPDATE capabilities SET name = 'site:sendemails', dependson = (SELECT id FROM capabilities_copy WHERE name = 'site:doanything') WHERE name = 'enquiries:sendemails';

DROP TABLE capabilities_copy;

-- New capabilities
INSERT INTO capabilities (name, description, type, dependson) VALUES ('enquiries:assignabletoenquiries', 'Can be assigned to an enquiry (considered staff)', 'write', 127); -- id 268
INSERT INTO roles_capabilities (role_id, capability_id) VALUES (30, (SELECT id FROM capabilities WHERE name = 'enquiries:assignabletoenquiries'));
INSERT INTO capabilities (name, description, type, dependson) VALUES ('qc:editprocedures', 'Can edit QC Procedures', 'write', 209); -- id 269 depends on qc:doanything
INSERT INTO capabilities (name, description, type, dependson) VALUES ('qc:viewprocedures', 'Can view QC Procedures', 'read', 269); -- id 270
INSERT INTO capabilities (name, description, type, dependson) VALUES ('qc:deleteprocedures', 'Can delete QC Procedures', 'write', 269); -- id 271
INSERT INTO capabilities (name, description, type, dependson) VALUES ('qc:writeprocedures', 'Can create QC Procedures', 'write', 269); -- id 272
INSERT INTO capabilities (name, description, type, dependson) VALUES ('qc:editprocedurefiles', 'Can edit photos/files for QC Procedures', 'write', 209); -- id 273

-- Change commodity category to ints
UPDATE exchange_commodities SET category = 1 WHERE category = 'metals';
UPDATE exchange_commodities SET category = 2 WHERE category = 'plastics';
ALTER TABLE  `exchange_commodities` CHANGE  `category`  `category` tinyint( 1 ) UNSIGNED NOT NULL DEFAULT 1;

-- Update paths and URLs for files --
UPDATE enquiries_files SET location = REPLACE(location, 'enquiries', 'enquiry');

-- Add info fields to file tables
ALTER TABLE  `enquiries_files` ADD  `file_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `url` ,
ADD  `raw_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `file_type` ,
ADD  `file_extension` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `raw_name` ,
ADD  `file_size` FLOAT( 16, 2 ) UNSIGNED NULL DEFAULT NULL AFTER  `file_extension` ,
ADD  `is_image` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `file_size` ,
ADD  `image_width` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `is_image` ,
ADD  `image_height` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_width` ,
ADD  `image_type` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_height` ,
ADD  `image_size_str` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_type`;

ALTER TABLE  `qc_project_files` ADD  `file_type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `description` ,
ADD  `raw_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `file_type` ,
ADD  `file_extension` VARCHAR( 12 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER  `raw_name` ,
ADD  `file_size` FLOAT( 16, 2 ) UNSIGNED NULL DEFAULT NULL AFTER  `file_extension` ,
ADD  `is_image` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0' AFTER  `file_size` ,
ADD  `image_width` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `is_image` ,
ADD  `image_height` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_width` ,
ADD  `image_type` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_height` ,
ADD  `image_size_str` VARCHAR( 64 ) NULL DEFAULT NULL AFTER  `image_type`;

ALTER TABLE  `qc_spec_photos`
ADD  `image_width` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_height` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_size_str` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE  `qc_job_photos`
ADD  `image_width` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_height` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_type` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
ADD  `image_size_str` VARCHAR( 64 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

-- Update ints in revision data strings
UPDATE `qc_revisions` SET data = REPLACE(data, '"language":"en"', '"language":"1"');
UPDATE `qc_revisions` SET data = REPLACE(data, '"language":"ch"', '"language":"2"');
UPDATE `qc_revisions` SET data = REPLACE(data, '"datatype":"int"', '"datatype":"1"');
UPDATE `qc_revisions` SET data = REPLACE(data, '"datatype":"string"', '"datatype":"2"');
UPDATE `qc_revisions` SET data = REPLACE(data, '"datatype":"file"', '"datatype":"3"');

-- Create new tables for QC Procedures
DROP TABLE IF EXISTS `qc_procedures`;
CREATE TABLE IF NOT EXISTS `qc_procedures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(10) unsigned NOT NULL DEFAULT '1001',
  `title` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `summary` text COLLATE utf8_unicode_ci NOT NULL,
  `version` smallint(5) unsigned NOT NULL,
  `updated_by` smallint(5) unsigned NOT NULL,
  `equipment` text COLLATE utf8_unicode_ci NOT NULL,
  `equipment_ch` text COLLATE utf8_unicode_ci NOT NULL,
  `revision_date` int(10) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `number` (`number`),
  KEY `updated_by` (`updated_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `qc_procedure_files`
--

DROP TABLE IF EXISTS `qc_procedure_files`;
CREATE TABLE IF NOT EXISTS `qc_procedure_files` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `procedure_id` int(10) unsigned NOT NULL,
  `file` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `file_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `raw_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_extension` varchar(12) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file_size` float(16,2) unsigned DEFAULT NULL,
  `is_image` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `image_width` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_height` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_type` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_size_str` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` int(10) unsigned NOT NULL DEFAULT '0',
  `revision_date` int(10) unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `procedure_id` (`procedure_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT COMMENT='Links to PDF files with added data for a project' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `qc_procedure_items`
--

DROP TABLE IF EXISTS `qc_procedure_items`;
CREATE TABLE IF NOT EXISTS `qc_procedure_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procedure_id` int(10) unsigned NOT NULL,
  `number` int(10) unsigned NOT NULL,
  `item` text COLLATE utf8_unicode_ci NOT NULL,
  `item_ch` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  `revision_date` int(10) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`),
  KEY `procedure_id` (`procedure_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `qc_specs_procedures`
--

DROP TABLE IF EXISTS `qc_specs_procedures`;
CREATE TABLE IF NOT EXISTS `qc_specs_procedures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `procedure_id` int(10) unsigned NOT NULL,
  `spec_id` int(10) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  `revision_date` int(10) unsigned NOT NULL,
  `status` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE  `qc_specs_procedures` ADD UNIQUE ( `procedure_id` , `spec_id`);

ALTER TABLE  `qc_projects` ADD  `has_updated_procedures` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';

-- CHANGE ALL PKS AND FKS TO INT(10)!!
ALTER TABLE companies MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE company_addresses MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE company_addresses MODIFY company_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE codes_parts MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE codes_projects MODIFY company_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries MODIFY enquiry_product_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_enquiry_notes MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_enquiry_notes MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_enquiry_notes MODIFY enquiry_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_enquiry_products MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_enquiry_staff MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE enquiries_enquiry_staff MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_enquiry_staff MODIFY enquiry_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_outbound_quotations MODIFY enquiry_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_files MODIFY id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE enquiries_files MODIFY document_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE exchange_currency_dailyrates MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE exchange_dailyvalues MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_jobs MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_jobs MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_jobs MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_jobs MODIFY supplier_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_job_photos MODIFY job_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_projects MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_projects MODIFY part_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_project_files MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_project_files MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_project_parts MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_project_related MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_project_related MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_project_related MODIFY related_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_revisions MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_revisions MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_revisions MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_spec_revisions MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_specs MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs MODIFY job_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs MODIFY english_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs MODIFY part_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs MODIFY file_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs_results MODIFY specs_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_specs_results MODIFY job_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_spec_photos MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_spec_photos MODIFY spec_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_project_parts MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_project_parts MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_spec_revisions MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE qc_spec_revisions MODIFY project_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE qc_spec_revisions MODIFY revision_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE users MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE users MODIFY company_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE users_logins MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE users_roles MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE users_roles MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE user_contacts MODIFY id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE user_contacts MODIFY user_id INT(10) UNSIGNED NOT NULL DEFAULT 0;


-- Don't forget to encrypt user passwords using CodeIgniter. Go to login/encrypt_user_passwords/[encryption_key] Find the encryption key in config/config.php --
