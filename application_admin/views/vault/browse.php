<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>

	<title>fileNice&trade; - [<?php echo $titlePath ?>]</title>
	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="content-language" content="en" />
	<meta name="robots" content="none" />
	<meta name="author" content="Andy Beaumont - http://andybeaumont.com" />
	<meta name="generator" content="the fantabulous mechanical eviltwin code machine" />
	<meta name="MSSmartTagsPreventParsing" content="true" />
	
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<meta http-equiv="Content-Style-Type" content="text/css" />
	<link rel="stylesheet" type="text/css" href="/css/vault/skins/<?php echo $skin; ?>/fileNice.css" />
	<link rel="stylesheet" type="text/css" href="/css/vault/skins/<?php echo $skin; $r = rand(99999,99999999); echo "/icons.php?r=$r\"" ?> />
	
<script language="javascript" type="text/javascript">
var ssSpeed = <?php echo $ssSpeed; ?>;
</script>
	
	<script src="http://dev.adminch.com/includes/js/jquery/fileNice.js" type="text/javascript"></script>
		
</head>

<body>
<!-- busy indicators -->
<div id="overDiv">&nbsp;</div>
<div id="busy">&nbsp;</div>
<!-- main -->
<div id="container">
	<div id="header">
	<form name="search" id="search" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"><input type="text" name="sstring" id="sstring" value="<?php if(isset($_POST['sstring'])) echo $_POST['sstring']; ?>" /><input type="button" name="search" value="search" id="searchButton" onclick="validateSearch();" /></form>
		<!-- please leave the word fileNice visible on the page, it's only polite really isn't it. -->
		<h1><a href="#" title="About fileNice&trade;" class="logo about_filenice">fileNice&#8482;</a></h1>
		<h2>Files in [<a href="<?php echo $titlePath; ?>" title="reset"><?php echo $titlePath; ?></a>]</h2>
		<h3><a href="#" title="edit prefs" class="expander preferences">(?)</a></h3>
	</div>
<div id="about_filenice">
fileNice&trade; <?php echo $version; ?><br />
<!-- please leave the word fileNice and the link to fileNice.com in the about, it's only polite really isn't it. I didn't do all this work just for you to try to pass it off as your own. -->
Free open source file browser available from <a href="http://fileNice.com" title="fileNice.com">fileNice.com</a><br />
Created by <a href="http://andybeaumont.com" title="andybeaumont.com">Andy Beaumont</a><br />
Enhanced by Christian Bloch<br />
<?php
if(file_exists("fileNice/skins/$skin/about.txt")){
	echo "<br /><br />
Skin:<br />";
	echo file_get_contents("fileNice/skins/$skin/about.txt");
}
?>
</div>

<form name="prefs" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="preferences">
Preferences:<br /><br />
<fieldset>
<legend>Sort by</legend>
<input type="radio" name="sortBy" id="name" value="name" <?php if($sortBy == "name") echo"checked=\"checked\""; ?> />
<label for="name">file name</label><br />
<input type="radio" name="sortBy" id="date" value="date" <?php if($sortBy == "date") echo"checked=\"checked\""; ?> />
<label for="date">date modified</label>
</fieldset>

<fieldset>
<legend>Sort direction</legend>
<input type="radio" name="sortDir" id="ascending" value="ascending" <?php if($sortDir == "ascending") echo"checked=\"checked\""; ?> />
<label for="ascending">ascending</label><br />
<input type="radio" name="sortDir"  id="descending" value="descending" <?php if($sortDir == "descending") echo"checked=\"checked\""; ?> />
<label for="descending">descending</label>
</fieldset>

<fieldset>
<legend>Filetypes to display</legend>
<input type="hidden" name="action" value="prefs" />
<?php if($showImg != "show") $checked = "";  else  $checked = "checked=\"checked\"";?>
<input type="checkbox" name="showImg" id="showImg" value="show" <?php echo $checked; ?> />
<label for="showImg">Show image type files</label><br />

<?php if($showEmbed  != "show") $checked = "";  else  $checked = "checked=\"checked\"";?> 
<input type="checkbox" name="showEmbed" id="showEmbed" value="show" <?php echo $checked; ?> />
<label for="showEmbed"> Show embed type files</label><br />

<?php if($showHtml != "show") $checked = "";  else  $checked = "checked=\"checked\"";?> 
<input type="checkbox" name="showHtml" id="showHtml" value="show" <?php echo $checked; ?> />
<label for="showHtml">Show html/text files</label><br />

<?php if($showScript != "show") $checked = "";  else  $checked = "checked=\"checked\"";?> 
<input type="checkbox" name="showScript" id="showScript" value="show" <?php echo $checked; ?> />
<label for="showScript">Show script files</label><br />

<?php if($showMisc != "show") $checked = "";  else  $checked = "checked=\"checked\"";?> 
<input type="checkbox" name="showMisc" id="showMisc" value="show" <?php echo $checked; ?> />
<label for="showMisc" >Show misc files</label><br />

<br />
</fieldset>
<fieldset>
<legend>Skin</legend>
<select name="skin" id="skin_select">
<?php
if(
$hook = opendir("/fileNice/skins/"))
{
while (false !== ($file = readdir($hook))){
	if($file != "." && $file != ".."){
		if($file == $skin){
			echo("<option value=\"$file\" selected=\"selected\">$file</option>");	
		}else{
			echo("<option value=\"$file\">$file</option>");	
		}
	}
}
}
closedir($hook);
?>
</select><br />
</fieldset>
<fieldset>
<legend>Slideshow speed</legend>
<input type="text" maxlength="2" name="ssSpeed" id="slideshow_speed" value="<?php echo $ssSpeed/1000; ?>" style="width:30px;" /> seconds per image<br />
</fieldset>
<input type="submit" name="Save" id="prefSave" value="Save" />
</form>


<?php
if(isset($_GET['view'])){
	if(substr($_GET['view'],0,2) != ".." && substr($_GET['view'],0,1) != "/" && $_GET['view'] != "./" && !stristr($_GET['view'], '../')){
		$out = new FNOutput;
		$out->viewFile(secCheck($_GET['view']));
	}else{
		// someone is poking around where they shouldn't be
		echo("Don't hack my shit yo.");
		exit;	
	}
}else if(isset($_GET['src'])){
	if(substr($_GET['src'],0,2) != ".." && substr($_GET['src'],0,1) != "/" && $_GET['src'] != "./" && !stristr($_GET['src'], '../')){
		$out = new FNOutput;
		$out->showSource(secCheck($_GET['src']));
	}else{
		// someone is poking around where they shouldn't be
		echo("Don't hack my shit yo.");
		exit;	
	}
}

?> <ul id="root"> <?php

// show file list
$list = new FNFileList;

if(isset($_POST['sstring'])){
	$t = htmlspecialchars($_POST['sstring'], ENT_QUOTES, 'UTF-8');
	$sstring = preg_replace("/[\'\")(;|`,<>]/", "", $t);
	$list->search($sstring);
}

if($dir != ""){
		$list->getDirList(secCheck($dir));
}else{
		$list->getDirList("./");
}





?>
</ul>
</div>


<!-- send to Flickr form -->
<form name="flickr" action="http://www.flickr.com/tools/sendto.gne" method="get">
<input type="hidden" name="url" />
</form>
</body>
<!-- script for applying the javascript events -->
<script type="text/javascript">
var o = new com.filenice.actions;
o.setFunctions();
</script>
</html>

