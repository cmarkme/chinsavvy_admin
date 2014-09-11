<h1><?=$virtual_root?></h1>
<h2><?='/'.$path_in_url?></h2>

<?php
$prefix = $controller.'/'.$virtual_root.'/'.$path_in_url;
if (!empty($dirs)) foreach( $dirs as $dir )
    echo 'dir <a href="'.$file['name'].'">'.  $dir['name'].'</a><br>';

if (!empty($files)) foreach( $files as $file )
    echo '<a href="'.$file['name'].'">'. $file['name'].'</a><br>';
?>
