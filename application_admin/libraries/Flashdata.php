<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class FlashData {

    function FlashData()
    { /* construct */
        $this->_load();
    } /* END construct */

    var $data;

	function get_info($key)
	{
		$data = $this->get($key);
		if ($data == false)
		{
			return false;
		} else {
			return '<div name="message" id="message" style="border: 1px dotted #999999; padding: 3px; margin-bottom: 10px;"><img src="/images/icons/information.png" width="16" height="16" align="absmiddle" style="margin-right: 3px;" />' . $data . '</div>';
		}
	}

	function get_error($key)
	{
		$data = $this->get($key);
		if ($data == false)
		{
			return false;
		} else {
			return '<div name="message" id="message" style="border: 1px dotted #999999; padding: 3px; margin-bottom: 10px;"><img src="/images/icons/error.png" width="16" height="16" align="absmiddle" style="margin-right: 3px;" />' . $data . '</div>';
		}
	}

    function get($key)
    { /* @public: Get a flash */
        return ( (isset($this->data->$key))
            ? substr($this->data->$key, 5)
            : false );
    } /* END get */

    function set($key, $val = null)
    { /* @public: Set a flash */
        $key = ( is_array($key) )
            ? $key
            : array($key => $val);

        foreach( $key as $_key => $_val )
        {
            $this->data->$_key = ":new:$_val";
        }

        $this->_write();
    } /* END set */

    function keep($key)
    { /* @public: Renew a flash */
        if( $value = $this->get($key) )
        {
            $this->set($key, $value);
        }
    } /* END keep */

    function delete($key)
    { /* @public: Delete a flash */
        if( isset($this->data->$key) )
        {
            unset($this->data->$key);
            $this->_write();
        }
    } /* END delete */

    function _load()
    { /* @private: Load flash data from session */
        $ci =& get_instance();
        $data = $ci->session->userdata('FlashData');

        $this->data = ( $data != false )
            ? unserialize($data)
            : (object)null;

        foreach( get_object_vars($this->data) as $key => $val )
        {
            if( substr($val, 0, 5) == ':old:' )
            {
                unset($this->data->$key);
            }

            else
            {
                $this->data->$key = ':old:'.substr($val, 5);
            }
        }

        $this->_write();
    } /* END _load */

    function _write()
    { /* @private: Write flash data to session */
        $ci =& get_instance();
        $ci->session->set_userdata('FlashData', serialize($this->data));
    } /* END _write */
}

?>