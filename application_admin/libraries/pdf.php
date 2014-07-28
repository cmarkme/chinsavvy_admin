<?php

define('K_TCPDF_EXTERNAL_CONFIG', true);

/**
 * TCPDF system constants that map to settings in our config file
 *
 * @var array
 */
$cfg_constant_map = array(
	'K_PATH_MAIN'	=> 'base_directory',
	'K_PATH_URL'	=> 'base_url',
	'K_PATH_FONTS'	=> 'fonts_directory',
	'K_PATH_CACHE'	=> 'cache_directory',
	'K_PATH_IMAGES'	=> 'image_directory',
	'K_BLANK_IMAGE' => 'blank_image',
	'K_SMALL_RATIO'	=> 'small_font_ratio',
	'K_CELL_HEIGHT_RATIO'	=> 'cell_height_ratio',
	'PDF_FONT_NAME_MAIN'	=> 'page_font',
);

# load the config file
require(APPPATH.'config/tcpdf.php');

# set the TCPDF system constants
foreach($cfg_constant_map as $const => $cfgkey) {
	if(!defined($const)) {
		define($const, $tcpdf[$cfgkey]);
		// echo sprintf("Defining: %s = %s\n<br />", $const, $tcpdf[$cfgkey]);
	} else {
		// echo "$const already defined as " . constant($const) . ".<br>";
	}
}
unset($cfg_constant_map, $tcpdf, $const, $cfgkey);


/************************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/
class pdf extends TCPDF {

    /**
     * @var float $base_font_size
     */
    public $base_font_size = 11;

    /**
     * @var array $basefont Info about overall font used in this report (font name, style and size)
     */
    public $basefont = array();

	/**
	 * Settings from our APPPATH/config/tcpdf.php file
	 *
	 * @var array
	 * @access private
	 */
	private $_config = array();


	/**
	 * Initialize and configure TCPDF with the settings in our config file
	 *
	 */
	function __construct($params) {
        $ci = get_instance();

		# load the config file
		require(APPPATH.'config/tcpdf.php');
		$this->_config = $tcpdf;
		unset($tcpdf);

        foreach ($params as $key => $value) {
            $this->_config[$key] = $value;
        }

		# initialize TCPDF
		parent::__construct(
			$this->_config['page_orientation'],
			$this->_config['page_unit'],
			$this->_config['page_format'],
			$this->_config['unicode'],
			$this->_config['encoding'],
			$this->_config['enable_disk_cache']
		);


		# language settings
		if(is_file($this->_config['language_file'])) {
			include($this->_config['language_file']);
			$this->setLanguageArray($l);
			unset($l);
		}

		# margin settings
		$this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);

		# header settings
        $lang = $ci->input->post('lang');
        $this->setBaseFont(PDF_FONT_NAME_MAIN, '', 8);

        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$this->print_header = $this->_config['header_on'];
		#$this->print_header = FALSE;

		$this->setHeaderMargin($this->_config['header_margin']);
		$this->SetHeaderData(
			$this->_config['header_logo'],
			$this->_config['header_logo_width'],
			strip_tags($this->_config['header_title']),
			strip_tags($this->_config['header_string'])
		);
        $this->setBaseFont(PDF_FONT_NAME_MAIN, '', 8);

        $this->setLineWidth(0.17);

		# footer settings
		$this->print_footer = $this->_config['footer_on'];
		$this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
		$this->setFooterMargin($this->_config['footer_margin']);

		# page break
		$this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);

		# cell settings
		$this->cMargin = $this->_config['cell_padding'];
		$this->setCellHeightRatio($this->_config['cell_height_ratio']);

		# document properties
		$this->author = $this->_config['author'];
		$this->creator = $this->_config['creator'];

		# font settings
		$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);

		# image settings
		$this->imgscale = $this->_config['image_scale'];
        $this->addPage();
        $title = (empty($this->_config['page_title'])) ? $this->_config['header_title'] : $this->_config['page_title'];
        $this->title($title);
	}

    function setEncoding($encoding='UTF-8') {
        $this->_config['encoding'] = $encoding;
    }

    function setUnicode($value=true) {
        $this->_config['unicode'] = $value;
    }

    function moveY($value=1) {
        $this->setY($this->getY() + $value);
    }

    function call_method($method, $params=array()) {
        return '<tcpdf method="'.$method.'" params="' . urlencode(serialize($params)) . '" />';
    }

    function horizontal_table($data, $thwidth, $tdwidth, $cellpadding=8, $border=1) {
        $table = '<table cellpadding="'.$cellpadding.'" border="'.$border.'">';

        foreach ($data as $label => $value) {
            $table .= '<tr>
                <th width="'.$thwidth.'" style="text-align: right"><strong>'.$label.'</strong></th>
                <td width="'.$tdwidth.'">'.$value.'</td>
            </tr>';
        }

        $table .= '</table>';

        return $table;
    }

    function setThead($thead) {
        $this->thead = $thead;
    }

    /**
     * Sets up the array $this->basefont, and sets the fpdf object's font variables to these values.
     */
    function setBaseFont($font=PDF_FONT_NAME_MAIN, $style='U', $size=14) {
        $this->basefont = array('font'=>$font, 'style'=> $style, 'size'=> $size);
        $this->SetFont($font, $style, $size);
    }
    /**
     * prints the page title.
     * @param string $string
     */
    function title($string) {
        $this->Ln(3);
        $titlestring = "<h1 style=\"margin: 30px 0px 10px 0px; font-size: 18; font-weight: bold; text-align: center;\">$string</h1><br />";
        $this->writeHTML($titlestring);
    }
}
