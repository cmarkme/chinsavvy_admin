<?php
/**
 * @package helpers
 */
/**
 * Method to produce the dynamic admin title element.
 * (displays csv and pdf link)
 *
 * Options are :
 *  - title   : The page's title
 *  - help    : The text to put in the help mouseover
 *  - expand  : The id of the div to show/hide
 *  - icons   : An array of icons to display (possible: add, save, pdf, email)
 *  - add_url : The url for adding an element, defaults to javascript(add)
 *  - pdf_url : The url of the page generating and serving the pdf file
 *  - csv_url : The url of the page generating and serving the csv file
 *  - email_url : The url of the page for sending an email
 *  - report_url : The url of the page for printing a report
 *  - *_url_params : If you want to pass named parameters to the URL using POST, put them in this array. The icons will be embedded in a form with hidden inputs
 *  - extra   : Extra html to show before the icons
 *  - level : Level 1 is top heading, higher levels simply get the CSS class 'subtitle'
 *
 * <code>
 * $options['title']   = 'Driver Admin';
 * $options['help']    = 'Driver Admin';
 * $options['expand']  = 'page';
 * $options['icons']   = array('save');
 * $options['csv_url'] = 'include/csv_export.php';
 * $options['id'] = 'tableid';
 * echo admin::getHTMLTitle($options);
 * </code>
 *
 * @param array $options See above
 * @return string HTML
 */
function get_title($options) {
    $id = '';
    if (!empty($options['id'])) {
        $id = "id=\"{$options['id']}\"";
    }
    $class = 'title';
    if (!empty($options['level']) && $options['level'] > 1) {
        $class = 'subtitle';
    }
    $return = '
        <table class="tbl gap" '.$id.' summary="Title">
          <tr>
            <td class="'.$class.'">' . $options['title'] . '</td>';

    // If extra html is given, add it before the icons (like a drop-down)
    if (isset($options['extra'])) {
        $return .= $options['extra'];
    }

    // Only show icons if help or expand are in the array
    if (array_key_exists('help', $options) || array_key_exists('expand', $options)) {
        $return .= '<td class="heading_corner">';
        if (array_key_exists('help', $options)) {
            $return .= img(array('src' => 'images/admin/icons/help_16.gif',
                           'title' => $options['help'],
                           'height' => 16,
                           'width' => 16,
                           'alt' => 'Help',
                           'class' => 'icon help'));
        }

        if (isset($options['expand'])) {

            $return .= '<div id="arrow_' . $options['expand'].'" class="title_arrow" style="background: url(/images/admin/icons/arrow-up_16.gif) center top no-repeat;"
                    title="Display of hide this block"
                    onclick="blocking(\'' . $options['expand'] . '\', \'block\'); return false;"></div>';
        }

        if (isset($options['icons'])) {

            if (in_array('add', $options['icons'])) {

                if (isset($options['add_url'])) {
                    $add_url = $options['add_url'];
                } else {
                    $add_url = 'javascript:add();';
                }

                $onclick = "window.location='$add_url';";
                if (preg_match('/javascript/', $add_url)) {
                    $onclick = $add_url;
                }

                $return .= img(array('src' => 'images/admin/icons/add_16.gif',
                               'onclick' => $onclick,
                               'height' => 16,
                               'width' => 16,
                               'title' => 'Add',
                               'alt' => 'Add',
                               'class' => 'icon add'));
            }

            if (!isset($options['csv_url'])) {
                $options['csv_url'] = '/include/csv_export.php';
            }
            if (!isset($options['xml_url'])) {
                $options['xml_url'] = 'serve_file.php';
            }
            $return .= make_post_icon('csv', $options, 'save_16.gif', 'export_to_csv', 'Export this table in CSV format');
            $return .= make_post_icon('xml', $options, 'xml_16.gif', 'export_to_xml', 'Export this table in XML format');
            $return .= make_post_icon('pdf', $options, 'pdf_16.gif', 'export_to_pdf', 'Export this table in PDF format');
            $return .= make_post_icon('email', $options, 'email.png');
            $return .= make_post_icon('report', $options, 'report.png');
        }

        $return .= '</td>';
    }



    $return .= '  </tr>
                </table>';

    return $return;
}

function make_post_icon($name, $options, $icon=null, $id=null, $title=null) {
    $return = '';
    if (in_array($name, $options['icons'])) {
        if (is_null($id)) {
            $id = "view_$name";
        }

        if (is_null($icon)) {
            $icon = "$name.png";
        }

        if (is_null($title)) {
            $title = ucfirst($name);
        }

        $return .= form_open($options[$name.'_url'], array('id' => $id));

        if (!empty($options[$name.'_url_params'])) {
            $return .= form_hidden($options[$name.'_url_params']);
        }
        $return .= img(array('src' => "images/admin/icons/$icon",
                       'onclick' => '$(this).parent().submit();',
                       'height' => 16,
                       'width' => 16,
                       'title' => $title,
                       'alt' => $title,
                       'class' => "icon $name"));
        $return .= form_close();
    }
    return $return;
}

