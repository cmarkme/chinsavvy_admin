<?php
if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Extends CI's pagination class (http://codeigniter.com/user_guide/libraries/pagination.html)
 * It sets some variables for configuration of the pagination class dynamically,
 * depending on the URI, so we don't have to substract the offset from the URI,
 * or set $config['base_url'] and $config['uri_segment'] manually in the controller
 *
 * Here is what is set by this extension class:
 * 1. $this->offset - the current offset
 * 2. $this->uri_segment - the URI segment to be used for pagination
 * 3. $this->base_url - the base url to be used for pagination
 * (where $this refers to the pagination class)
 *
 * The way this works is simple:
 * Drop this library in folder application/libraries
 * If we use pagination, it must ALWAYS follow the following syntax and be
 * located at the END of the URI:
 * PAGINATION_SELECTOR/offset
 * E.g. http://www.example.com/controller/action/Page/2
 *
 * The PAGINATION_SELECTOR is a special string which we know will ONLY be in the
 * URI when paging is set. Let's say the PAGINATION_SELECTOR is 'Page' (since most
 * coders never use any capitals in the URI, most of the times any string with
 * a single capital character in it will suffice). The PAGINATION_SELECTOR is
 * set in the general config file,
 * in the following index: $this->config->item('pagination_selector')
 *
 * Example use (in controller):
 * // Set pagination and get pagination HTML
 * $this->data['pagination'] = $this->pagination->get_pagination($this->db->count_all_results('my_table'), 10);
 *
 * // Retrieve paginated results, using the dynamically determined offset
 * $this->db->limit($config['per_page'], $this->pagination->offset);
 * $query = $this->db->get('my_table');
 *
 * @name MY_Pagination.php
 * @version 1.0
 * @author Joost van Veen
 */
class MY_Pagination extends CI_Pagination
{

    /**
     * Pagination offset.
     * @var integer
     */
    public $offset = 0;

    /**
     * Opening HTML tag for pagination string
     * @var string
     */
    public $cur_tag_open = '<strong>';

    /**
     * Opening HTML tag for pagination string
     * @var unknown_type
     */
    public $cur_tag_close = '</strong>&nbsp;|&nbsp;';

    /**
     * Text for link to first page
     * @var string
     */
    public $first_link = 'First';

    /**
     * Text for link to last page
     * @var string
     */
    public $last_link = 'Last';

    /**
     * Text for link to next page
     * @var string
     */
    public $next_link = '&gt;&gt;';

    /**
     * Text for link to previous page
     * @var string
     */
    public $prev_link = '&lt;&lt;&nbsp;|&nbsp;';

    /**
     * Closing tag for digits
     * @var string
     */
    public $num_tag_close = '&nbsp;|&nbsp;';

    /**
     * Closing tag for digits
     * @var string
     */
    public $num_tag_open = '';

    /**
     * Number of links to show in pagination
     * @var integer
     */
    public $num_links = 8;

    /**
     * Pagination selector to be used in URI. Make sure to set this to a value
     * that is never used elsewhere in the URI.
     * @var string
     */
    public $pagination_selector = 'Page';

    function __construct() {
        parent::__construct();

        log_message('debug', "MY custom Pagination Class Initialized");

        if ($this->config->item('pagination_selector') == '') {
            show_error('config->item(\'pagination_selector\') is not set. Set config->item(\'pagination_selector\') in a config file, or $this->pagination->$this->pagination_selector');
        }
        else {
            $this->pagination_selector = $this->config->item('pagination_selector');
        }

        $this->_set_pagination_offset();
    }

    /**
     * Rturn HTML for pagination, based on count ($total_rows) and limit ($per_page)
     * @param integer $total_rows
     * @param integer $per_page
     * @return string
     */
    public function get_pagination ($total_rows, $per_page) {
        if ($total_rows > $per_page) {
            $cur_page = 1;
            if ($this->config->item('enable_query_strings') === TRUE OR $this->page_query_string === TRUE) {
                if ($this->input->get($this->query_string_segment) != 0) {
                    $cur_page = $this->input->get($this->query_string_segment);

                    // Prep the current page - no funny business!
                    $cur_page = (int) $cur_page;
                }
            } else {
                if ($this->uri->segment($this->uri_segment) != 0) {
                    $cur_page = $this->uri->segment($this->uri_segment);

                    // Prep the current page - no funny business!
                    $cur_page = (int) $cur_page;
                }
            }

            $cur_page = floor(($cur_page/$per_page) + 1);
            $total_pages = ceil($total_rows/$per_page);
            $lower_boundary = ($cur_page-1) * $per_page;
            $higher_boundary = ($cur_page == $total_pages) ? $total_rows : $cur_page * $per_page;

            $config = array('total_rows' => $total_rows, 'per_page' => $per_page,
                    'full_tag_open' => '<table class="tbl pagination"><tr>
                        <td class="resultsfound">'.$total_rows.' Result found</td>
                        <td class="currentpage">(Page '.$cur_page.'/'.$total_pages.')</td>
                        <td class="links">',
                    'full_tag_close' => '</td>
                        <td class="recordsshown">Displaying records '.$lower_boundary.' to '.$higher_boundary.'</td></tr></table>');
            $this->initialize($config);
            return $this->create_links();
        }
    }

    /**
     * Set dynamic pagination variables in $this->data['pagvars']
     * @return void
     */
    private function _set_pagination_offset ()
    {
        // Store pagination offset if it is set
        if (strstr($this->uri->uri_string(), $this->pagination_selector)) {

            // Get the segment offset for the pagination selector
            $segments = $this->uri->segment_array();

            // Loop through segments to retrieve pagination offset
            foreach ($segments as $key => $value) {

                // Find the pagination_selector and work from there
                if ($value == $this->pagination_selector) {

                    // Store pagination offset
                    $this->offset = $this->uri->segment($key + 1);

                    // Store pagination segment
                    $this->uri_segment = $key + 1;

                    // Set base url for paging. This only works if the
                    // pagination_selector and paging offset are AT THE END of
                    // the URI!
                    $uri = $this->uri->uri_string();
                    $pos = strpos($uri, $this->pagination_selector);
                    $this->base_url = $this->config->item('base_url') . substr($uri, 0, $pos + strlen($this->pagination_selector));
                }
            }
        }
        else {
            // Pagination selector was not found in URI string. So offset is 0
            $this->offset = 0;
            $this->uri_segment = 0;
            $this->base_url = $this->config->item('base_url') . substr($this->uri->uri_string(), 1) . '/' . $this->pagination_selector;
        }
    }
}
?>
