<?php

// may only work if this controller is not in a subfolder

class File extends MY_Controller {

    var $roots = array(
        'file' => 'files'
    );
    public function __construct()
    {
        parent::__construct();
    }

    function Browse()
    {
        // FAL check here
    }

    function _remap()
    {
        $segment_array = $this->uri->segment_array();

        // first and second segments are our controller and the 'virtual root'
        //$ConFolder = array_shift( $segment_array );
        $controller = array_shift( $segment_array );
        $virtual_root = array_shift( $segment_array );

        if( empty( $this->roots )) exit( 'no roots defined' );

        // let's check if a virtual root is choosen
        // if this controller is the default controller, first segment is 'index'
        if ( $controller == 'index' OR $virtual_root == '' ) show_404();

        // let's check if a virtual root matches
        if ( ! array_key_exists( $virtual_root, $this->roots )) show_404();

        // build absolute path
        $path_in_url = '';
        foreach ( $segment_array as $segment ) $path_in_url.= $segment.'/';
        $absolute_path = $this->roots[ $virtual_root ].'/'.$path_in_url;
        $absolute_path = rtrim( $absolute_path ,'/' );

        // is it a directory or a file ?
        if ( is_dir( $absolute_path ))
        {
            // we'll need this to build links
            $this->load->helper('url');

            $dirs = array();
            $files = array();
            // let's traverse the directory
            if ( $handle = @opendir( $absolute_path ))
            {
                while ( false !== ($file = readdir( $handle )))
                {
                    if (( $file != "." AND $file != ".." ))
                    {
                        if ( is_dir( $absolute_path.'/'.$file ))
                        {
                            $dirs[]['name'] = $file;
                        }
                        else
                        {
                            $files[]['name'] = $file;
                        }
                    }
                }
                closedir( $handle );
                sort( $dirs );
                sort( $files );


            }
            // parent directory
            // here to ensure it's available and the first in the array
            if ( $path_in_url != '' )
                array_unshift ( $dirs, array( 'name' => '..' ));

            // send the view
            $data = array(
                'controller' => $controller,
                'virtual_root' => $virtual_root,
                'path_in_url' => $path_in_url,
                'dirs' => $dirs,
                'files' => $files,
            );
            $this->load->view( 'vault/browse', $data );
        }
        else
        {
            // it's not a directory, but is it a file ?
            if ( is_file( $absolute_path ))
            {
                // let's serve the file
                header ('Cache-Control: no-store, no-cache, must-revalidate');
                header ('Cache-Control: pre-check=0, post-check=0, max-age=0');
                header ('Pragma: no-cache');

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                // header('Content-Length: ' . filesize( ".$absolute_path." ));  // Modified
                header('Content-Length: ' . filesize( $absolute_path ));
                header('Content-Disposition: attachment; filename=' . basename( $absolute_path ));

                @readfile( $absolute_path );
            }
            else
            {
                show_404();
            }
        }
    }
}
