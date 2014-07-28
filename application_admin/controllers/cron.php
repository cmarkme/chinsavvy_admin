<?php
/**
 * Contains the Currency Controller class
 * @package controllers
 */

/**
 * Cron Controller Class
 * @package controllers
 */
class Cron extends MY_Controller {
    public $restricted = false;

	function __construct() {
		parent::__construct();
		
    }
    function  test ()
    {

    	$this->load->library('email');
    	$this->load->model('autoemail_model');
    	$this->load->model('emaillog_model');
    	$this->load->model('setting_model');
    	$this->load->helper('date');
    	/*
    	 $config['smtp_host'] = 'mail.chinasavvy.com';
    	$config['smtp_user'] = 'cpd@chinasavvy.com';
    	$config['smtp_pass'] = 'smuDge1946;';
    	$config['smtp_port'] = '587';
    	$this->email->initialize($config);
    	*/
    	
    	$config['smtp_host'] = 'localhost';
    	$config['protocol'] = 'smtp';
    	$config['smtp_user'] = 'cpd+chinasavvy.com';
    	$config['smtp_pass'] = 'smuDge1946;';
    	$config['smtp_port'] = '587';
    	$this->email->initialize($config);
    	
    	$this->email->from('cmarkme@chinasavvy.com');
    	$this->email->subject('subject');
    	$this->email->message('message');
    	$this->email->to('freelance.webdev.iow@gmail.com');
    	$this->email->send();
    }

    function update_currency_values() {

        $this->load->model('exchange/currencyrate_model');
        $this->load->model('exchange/currency_model');

        $now = time();
        $date = date('d/m/Y', $now);
        $parts = explode('/', $date);
        $first_second = mktime(0, 0, 0, $parts[1], $parts[0], $parts[2]);
        $last_second = mktime(59, 59, 23, $parts[1], $parts[0], $parts[2]);

        // Loop through currencies and retrieve their rates based on the USD
        if ($currencies = $this->currency_model->get()) {
            foreach ($currencies as $currency) {
                $this->db->where("creation_date BETWEEN $first_second AND $last_second");
                $currency_dailyrate = $this->currencyrate_model->get(array('currency_id' => $currency->id));

                if (!$currency_dailyrate) {
                    $url = 'http://www.webservicex.net/CurrencyConvertor.asmx/ConversionRate?FromCurrency=USD&ToCurrency='.$currency->code;
                    $rate = simplexml_load_file($url);
                    if ($currency->code == 'USD') {
                        $rate[0] = 1;
                    }
                    $this->currencyrate_model->add(array('currency_id' => $currency->id, 'creation_date' => $first_second, 'value' => $rate[0]));
                }
            }
        }
    }

    public function backup_qc_specs() {

        $this->load->dbutil();
        $backup =& $this->dbutil->backup(array('tables' => array('qc_specs'), 'charset' => 'utf8', 'format' => 'gzip', 'filename' => 'qc_specs_backup_'.mktime().'.sql'));
        $this->load->helper('file');
        write_file('/home/adminch/public_html/sql/qc_specs_backup_'.mktime().'.sql.gz', $backup);
    }

    /**
     * For each auto-email set up in the DB, look up enquirer emails using associated conditions, and send
     * appropriate message
     */
    public function send_auto_emails() {
    	/*
    	$this->load->library('email');
    	
    	$config['smtp_host'] = 'localhost';
    	$config['smtp_user'] = 'cmarkme@chinasavvy.com';
    	$config['smtp_pass'] = 'kathleen01041972';
    	$config['smtp_port'] = '587';
    	$this->email->initialize($config);
    	
    	
    	
    	$this->email->from('cmarkme@chinasavvy.com', 'mark');
    	$this->email->to('freelance.webdev.iow@gmail.com');
    	$this->email->subject('Email Test');
    	$this->email->message('Testing the email class.');
    	
    	
    	
    	
    	$this->email->send();
    	echo $this->email->print_debugger();
    	*/
    	
     	$this->load->library('email');   
		$this->load->model('autoemail_model');
        $this->load->model('emaillog_model');
        $this->load->model('setting_model');
        $this->load->helper('date');
        /*
        $config['smtp_host'] = 'mail.chinasavvy.com';
        $config['smtp_user'] = 'cpd@chinasavvy.com';
        $config['smtp_pass'] = 'smuDge1946;';
        $config['smtp_port'] = '587';
        $this->email->initialize($config);
        */
        
        $config['smtp_host'] = 'localhost';
        $config['protocol'] = 'smtp';
        $config['smtp_user'] = 'cpd+chinasavvy.com';
        $config['smtp_pass'] = 'smuDge1946;';
        $config['smtp_port'] = '587';
        $this->email->initialize($config);
        
        
        
        
        
        //emails to send
        $autoemails = $this->autoemail_model->get(array('status' => 'Active'));
        
        
		//list of cc 'janny.li@chinasavvy.com, manuel.pilar@chinasavvy.com, cpd@chinasavvy.com, wing.xu@chinasavvy.com, donna@chinasavvy.com'
        $additional_bcc = $this->setting_model->get(array('name' => 'autoemail_bcc'), true)->value;
        
        //admin details
        $admin_users = $this->user_model->get_users_by_capability('enquiries:getoutboundnotifications');
        
        
        
        $admin_emails = array();
        
		/*
		 * 0 => string 'janny.li@chinasavvy.com' (length=23)
		  1 => string ' manuel.pilar@chinasavvy.com' (length=28)
		  2 => string ' cpd@chinasavvy.com' (length=19)
		  3 => string ' wing.xu@chinasavvy.com' (length=23)
		  4 => string ' donna@chinasavvy.com' (length=21)
		 */
       
        if (!empty($additional_bcc)) {
            $admin_emails = explode(',', $additional_bcc);
            
        }
        
        
					        /*
					         * array (size=9)
					  0 => string 'janny.li@chinasavvy.com' (length=23)
					  1 => string ' manuel.pilar@chinasavvy.com' (length=28)
					  2 => string ' cpd@chinasavvy.com' (length=19)
					  3 => string ' wing.xu@chinasavvy.com' (length=23)
					  4 => string ' donna@chinasavvy.com' (length=21)
					  5 => string 'cpd@chinasavvy.com' (length=18)
					  6 => string 'donna@chinasavvy.com' (length=20)
					  7 => string 'lizzie@chinasavvy.com' (length=21)
					  8 => string 'manuel.pilar@chinasavvy.com' (length=27)
					         */
 
        foreach ($admin_users as $admin_user) {
            $admin_emails[] = $this->user_contact_model->get_by_user_id($admin_user->id, USERS_CONTACT_TYPE_EMAIL, true, true, true);
           
        }
        

        
        
        
        
        foreach ($autoemails as $autoemail) {
            $emails = $this->autoemail_model->get_emails($autoemail->id); //$emails holds the customers id number
			
            // Problem: The same user may have submitted multiple enquiries in the same time period. He may then receive multiple emails
            // Solution: Check the email_log table for already sent emails
            if (empty($emails)) {
                continue;
            }

            foreach ($emails as $key => $email) {
                $needles = array( '[enquiry_id]', '[product_title]', '[enquiry_date]', '[enquirer]', '[first_name]');
                $replacements = array($email->enquiry_id, $email->product_title, unix_to_human($email->enquiry_date), $email->enquirer, $email->first_name);
                $subject = str_replace($needles, $replacements, $autoemail->subject);
                $message = str_replace($needles, $replacements, $autoemail->message);

                $emaillog_params = array(
                    'sender_table' => 'autoemails',
                    'sender_id' => $autoemail->id,
                    'receiver_table' => 'users',
                    'receiver_id' => $email->user_id,
                    'to_email' => $email->email,
                    'message' => $message,
                    'subject' => $subject);

                if ($this->emaillog_model->get($emaillog_params)) {
                    continue;
                }
				
                
                
                
                
                $emails[$key]->enquiry_date = unix_to_human($email->enquiry_date);
                

                $this->email->subject($subject);
                $this->email->message($message);
                $this->email->to($email->email);  // list of customers emails
                
                $this->email->bcc($admin_emails);

                $replyto = array('email' => 'cpd@chinasavvy.com', 'name' => 'Christopher Devereux');
                $replytoname = $this->setting_model->get(array('name' => 'autoemail_replyto_name'), true)->value;
                $replytoemail = $this->setting_model->get(array('name' => 'autoemail_replyto_email'), true)->value;

                if (!empty($replytoname) && !empty($replytoemail)) {
                    $replyto['email'] = $replytoemail;
                    $replyto['name'] = $replytoname;
                }

                $this->email->from($replyto['email'], $replyto['name']);
                $this->email->reply_to($replyto['email'], $replyto['name']);
                
               //echo $this->email->from($replyto['email'], $replyto['name'])."</br>";
                
		    	//$this->email->send();
		    	
                
                if ($this->email->send()) {
                	 
                    $emaillog_params['from_email'] = $replyto['email'];
                    $emaillog_params['fromname'] = $replyto['name'];
                    $emaillog_params['recipients'] = $email->email;
                    $emaillog_params['host'] = $_SERVER['HTTP_HOST'];
                    $emaillog_params['port'] = 25;
                    $emaillog_params['addreplyto'] = $replyto['email'];
                    
                    $this->emaillog_model->add($emaillog_params);
                } 
                else {
                	 echo $this->email->print_debugger();
                }               
            }
        }
        $this->email->from('cmarkme@chinasavvy.com');
        $this->email->subject('subject');
                $this->email->message('message');
                $this->email->to('freelance.webdev.iow@gmail.com');
                $this->email->send();
        //echo $this->email->print_debugger();
    }
    
   
}
