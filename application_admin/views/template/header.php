<?php
$csstoload = (empty($csstoload)) ? array() : $csstoload;
$jstoload = (empty($jstoload)) ? array() : $jstoload;

$csstoload = array_unique(array_merge(array('admin_style', 'jquery.ui'), $csstoload));
$jstoload = array_unique(array_merge(array('jquery/jquery', 'jquery/jquery.ui', 'admin', 'constants'), $jstoload));
?>
<?php header('Content-type: text/html; charset=UTF-8'); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>ChinaSavvy - <?=$title?></title>
    <base href="<?=$this->config->item('base_url')?>" />
    <meta name="verify-a" value="7388b2656185a863039a">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="content-language" content="english" />
    <meta name="description" content="Admin" />
    <meta name="keywords" content="admin" />
    <meta name="author" content="Chinasavvy UK Ltd" />
    <meta name="copyright" content="Chinasavvy UK Ltd" />
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />

    <?php foreach ($csstoload as $cssfile): ?>
        <link rel="stylesheet" href="css/<?=$cssfile?>.css" type="text/css" />
    <?php endforeach; ?>
    <link rel="stylesheet" href="css/admin_print.css" type="text/css" media="print" />
    <!--[if IE]>
      <link rel="stylesheet" type="text/css" href="css/ie.css" />
    <![endif]-->
    <?php foreach ($jstoload as $jsfile): ?>
        <script type="text/javascript" src="<?=base_url()?>includes/js/<?=$jsfile?>.js"> /* <![CDATA[ */ /* ]]> */ </script>
    <?php endforeach; ?>
    <script type="text/javascript">
    /*<![CDATA[ */
    var caps = <?php echo json_encode($this->session->userdata('user_caps')) ?>;
    //]]>
    </script>
</head>
<body id="<?=(isset($body_id)) ? $body_id : "home"?>">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
