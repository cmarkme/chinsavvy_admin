<?php
/**
 * @package helpers
 */
/**
 * Method to show a title bar for a navigation menu box.
 *
 * @access public
 * @static
 * @param string $title the title of the box
 *
 * @return string HTML output
 *
 */
function get_nav_title($title) {
    $nav_id = get_nav_id(false);

    return '<strong' . ($nav_id === 0 ? ' class="first"' : '') . '>' . $title . '</strong>';
}

function get_nav_id($increment=true) {
    static $nav_id = 0;
    if ($increment) {
        return $nav_id++;
    } else {
        return $nav_id;
    }
}

abstract class nav_element {
    public $title;
    /**
     * @var boolean $allowdoanything Whether or not admins can ignore the caps. True means that they can.
     */
    public $allowdoanything = true;
    public $caps = array();

    public function __construct($title, $caps=array()) {
        $this->caps = $caps;
        $this->title = $title;
    }

    public function matches_user_caps() {
        foreach ($this->caps as $capname) {
            if (!has_capability($capname, null, $this->allowdoanything)) {
                return false;
            }
        }
        return true;
    }

    abstract function render();
}

class nav_menu extends nav_element {
    public $links = array();

    public function __construct($title, $links, $caps=array()) {
        parent::__construct($title, $caps);

        $this->allowdoanything = false;

        foreach ($links as $array) {
            $this->links[] = new nav_link($array[0], $array[1], $array[2]);
        }
    }

    public function render() {
        $retval = '';

        $nav_id = get_nav_id(true);
        if ($this->matches_user_caps()) {
            $retval .= get_nav_title($this->title);
            $retval .= '<ul id="nav_' . $nav_id . '">'."\n";
            $linksoutput = '';

            foreach ($this->links as $navlink) {
                $linksoutput .= $navlink->render();
            }

            if (empty($linksoutput)) {
                return '';
            }

            $retval .= $linksoutput . '</ul>'."\n";
        }
        return $retval;
    }
}

class nav_link extends nav_element {
    public $link;

    public function __construct($title, $link, $caps=array()) {
        parent::__construct($title, $caps);
        $this->link = $link;
    }

    public function render() {
        if ($this->matches_user_caps()) {
            return '<li><a href="/' . str_replace('&', '&amp;', $this->link) . '" title="' . $this->title . '">' . $this->title . '</a></li>'."\n";
        } else {
            return false;
        }
    }
}

function get_dynamic_nav() {
    $nav = '';
    $navmenu = new nav_menu('Enquiry &amp; Quotation',
        array(array('New Enquiry', "enquiries/enquiry/add", array('enquiries:writeenquiries')),
              array('New Quotation', "enquiries/outbound/add", array('enquiries:writeoutbound')),
              array('Enquiry report', "enquiries/enquiry/browse", array('enquiries:viewenquiries')),
              array('Quotation report', "enquiries/outbound/browse", array('enquiries:viewoutbound')),
              array('Assigned enquiries', "enquiries/enquiry/assigned_enquiries", array('enquiries:viewassignedenquiries')),
              array('Additional Reports', "enquiries/report", array('enquiries:viewreports')),
              array('Quotation files', "enquiries/quotation_files", array('enquiries:doanything')),
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Commodities',
        array(
              // array('Supplier Reports', "enquiries/inbound/browse", array('enquiries:viewinbound')),
              array('Data entry', "exchange/dailyvalues/add", array('exchange:writedailyvalues')),
              array('Metal values', "exchange/dailyvalues/metals", array('exchange:viewmetals')),
              array('Plastic values', "exchange/dailyvalues/plastics", array('exchange:viewplastics'))
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Estimates',
        array(
              array('New Estimate', "estimates/estimate/add", array('estimates:user')),
              array('Estimate Report', "estimates/estimate/browse", array('estimates:user')),
              array('Material Types', "estimates/material_type/browse", array('estimates:doanything')),
              array('Material Costs', "estimates/material_cost/browse", array('estimates:doanything')),
              array('Process Types', "estimates/process_type/browse", array('estimates:doanything')),
              array('Process Costs', "estimates/process_cost/browse", array('estimates:doanything')),
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Documents vault',
        array(array('View Files', "vault/file/browse", array('vault:viewfiles')),
              array('Upload a file', "vault/file/add", array('vault:writefiles')),
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Administration',
        array(array('User Management', "users/user", array('users:viewusers')),
              array('Roles', "users/role/browse", array('users:editroles')),
              array('Company Management', "company", array('site:editcompanies')),
              // array('Public Files Management', "enquiries/files", array('enquiries:editfiles')),
              array('Commodities', "exchange/commodity", array('exchange:editexchange')),
              array('Exchange Markets', "exchange/market", array('exchange:editmarkets')),
              array('Verification codes', "verification", array('site:doanything')),
              array('Settings', "setting", array('site:doanything')),
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Communication',
        array(array('Email form', "email", array('site:sendemails')),
              array('Auto-emails', "autoemails", array('site:editautoemails')),
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('Codes',
        array(array('Divisions', "codes/division/browse", array('codes:editdivisions')),
              array('Customers', "codes/customer/browse", array('codes:editcustomers')),
              array('Projects', "codes/project/browse", array('codes:editprojects')),
              array('Products', "codes/part/browse", array('codes:editparts')),
              array('Suppliers', "codes/supplier/browse", array('codes:editsuppliers')),
              array('Processes', "codes/process/browse", array('codes:editprocesses')),
              array('QC', "codes/qc/browse", array('codes:editqc'))
        ));
    $nav .= $navmenu->render();

    $navmenu = new nav_menu('QC',
        array(array('Projects', "qc/project/browse", array('qc:viewprojects')),
              array('Procedures', "qc/procedure", array('qc:viewprocedures')),
              array('Documents', "qc/document", array('qc:viewprojects')),
              array('Email', "qc/email", array('qc:viewprojects')),
              array('Sample Sizes', "qc/sample_size", array('site:doanything')),
        ));
    $nav .= $navmenu->render();

    return $nav;
}

?>
