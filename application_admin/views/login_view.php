<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <title>CHINASAVVY</title>
        <base href="<?=$base_url?>" />

        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="content-language" content="english" />
        <meta name="description" content="Admin" />
        <meta name="keywords" content="admin" />
        <meta name="author" content="Chinasavvy UK Ltd" />
        <meta name="copyright" content="Chinasavvy UK Ltd" />
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <link rel="stylesheet" href="/css/admin_style.css" type="text/css" />

        <!--[if IE]>
          <link rel="stylesheet" type="text/css" href="/css/ie.css" />
        <![endif]-->
    </head>
    <body id="login">
        <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

        <div class="formContainer">
        <?php echo form_open('login') ?>

        <div class="title">Chinasavvy Intranet</div>
        <?php if (!empty($message)) { ?>
            <div class="message"><?=$message?></div>
        <?php } ?>
        <span class="required">*</span><?php echo form_label('Username', 'username') ?>
        <?php echo form_input(array('name' => 'username', 'id' => 'username')) ?>
        <br />
        <span class="required">*</span><?php echo form_label('Password', 'password') ?>
        <?php echo form_password(array('name' => 'password', 'id' => 'password')) ?>
        <br />
        <?php echo form_submit('submit', 'Login', 'id="submit"') ?>
            <br />
            <span style="font-size:80%; color:#ff0000;">*</span>
            <span style="font-size:80%;">denotes required field</span>
        <?php echo form_close() ?>
        </div>
    </body>
</html>
