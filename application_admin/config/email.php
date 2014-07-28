<?php
$url = $_SERVER['HTTP_HOST'];
$domain = substr($url, 0, 3) == 'www' ? substr($url, 4) : $url;

if ($domain == 'admin.chinasavvy.com') {
    $config['smtp_host'] = 'mail.chinasavvy.com';
$config['smtp_user'] = 'cpd+chinasavvy.com';
$config['smtp_pass'] = 'smuDge1946;';
$config['smtp_port'] = '587';
} else {
    $config['smtp_host'] = 'mail.connault.com.au';
    $config['smtp_user'] = 'nicolas@connault.com.au';
    $config['smtp_pass'] = 'NLD7opc3i0KUk';
    $config['smtp_port'] = '587';
}
$config['protocol'] = 'smtp';
$config['wordwrap'] = true;
$config['wrapchars'] = 76;
$config['mailtype'] = 'html';
$config['charset'] = 'utf-8';
$config['validate'] = true;
$config['crlf'] = "\r\n";
$config['newline'] = "\r\n";

/*

$config['smtp_host'] = 'mail.chinasavvy.com';
$config['smtp_user'] = 'cpd+chinasavvy.com';
$config['smtp_pass'] = 'smuDge1946;';
$config['smtp_port'] = '587';
*/