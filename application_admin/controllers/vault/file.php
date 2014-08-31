<?php
/**
 * @package controllers
 */
class file extends MY_Controller {
    

function __construct() {
        parent::__construct();
   $this->defaultSkin = "hdrlabs";

//default sort
    $this->defaultSort = "name";
// default sort order
    $this->defSortDirection = "ascending";

// default time to show each image in a slideshow (in seconds)
    $this->defaultSSSpeed = 6;

// Show "send to Flickr" links
    $this->flickr = false;

// any files you don't want visible to the file browser add into this
// array...
    $this->ignoreFiles = array(	"index.php",
        "fComments.txt"
    );

// any folders you don't want visible to the file browser add into this
// array...
    $this->ignoreFolders = array("fileNice"
    );

// file type handling, add file extensions to these array to have the
// file types handled in certain ways
    $this->imgTypes 	= array("gif","jpg","jpeg","bmp","png");
    $this->embedTypes = array("mp3","mov","aif","aiff","wav","swf","mpg","avi","mpeg","mid","wmv");
    $this->htmlTypes 	= array("html","htm","txt","css","siblt");
    $this->phpTypes 	= array("php","php3","php4","asp","js");
    $this->miscTypes 	= array("pdf","doc","zip","sit","rar","rm","ram","ibl","siblt","gz","exe");

// date format - see http://php.net/date for details
    $this->dateFormat = "F d Y ";

}


    function add() {
       $this->load->view('vault/add');
    }


public function browse($category_id=null, $project_id=null, $supplier_id=null) {
    /*********************************************************************/
    /*                             fileNice                              */
    /*                                                                   */
    /*  Heirachical PHP file browser - http://filenice.com               */
    /*  Written by Andy Beaumont - http://andybeaumont.com               */
    /*                                                                   */
    /*  Send bugs and suggestions to stuff[a]fileNice.com                */
    /*                                                                   */
    /*                                                                   */
    /*********************************************************************/

    /*********************************************************************/
    /*                                                                   */
    /* User editable preferences are now stored in fileNice/prefs.php    */
    /* for easier maintenance and to assist with some fancy new features */
    /* in this and future versions.                                      */
    /*                                                                   */
    /*********************************************************************/

   // include(APPPATH."fileNice/prefs.php");

    /*********************************************************************/
    /*                                                                   */
    /*  Best not to touch stuff below here unless you know what you're   */
    /*  doing.                                                           */
    /*                                                                   */
    /*********************************************************************/

    $version = "1.1";

    $server = $_SERVER['HTTP_HOST'];
    $thisDir = dirname($_SERVER['PHP_SELF']);
    $pathToHere = "http://$server$thisDir";

    //$dir=isset($_GET['dir'])?$_GET['dir']:'';if(strstr($dir,'..'))$dir='';
    $dir=$this->uri->segment(5) ? $this->uri->segment(5):'';if(strstr($dir,'..'))$dir='';

    if($dir != ""){
        $titlePath = "http://$server/$dir";
        $path = $dir;
    }else{
        $titlePath = "http://$server$thisDir";
    }



    include "fileNice/fileNice.php";

// HANDLE THE PREFERENCES
    $names = array("showImg","showEmbed","showHtml","showScript","showMisc");
    if(isset($_POST['action']) && $_POST['action'] == "prefs"){
        // lets set the cookie values
        $varsArray = array();
        for($i=0; $i<count($names);$i++){
            if($_POST[$names[$i]] == "show"){
                $varsArray[$names[$i]] = "show";
            }else{
                $varsArray[$names[$i]] = "hide";
            }
            setcookie($names[$i],$varsArray[$names[$i]],time()+60*60*24*365);
            $$names[$i] = $varsArray[$names[$i]];
        }
        // set the skin
        setcookie("skin",$_POST['skin'],time()+60*60*24*365);
        $skin = $_POST['skin'];
        // set the slideshow speed
        setcookie("ssSpeed",$_POST['ssSpeed'],time()+60*60*24*365);
        $ssSpeed = $_POST['ssSpeed'] * 1000;
        // set the sortBy
        setcookie("sortBy",$_POST['sortBy'],time()+60*60*24*365);
        $sortBy = $_POST['sortBy'];
        // set the sortDir
        setcookie("sortDir",$_POST['sortDir'],time()+60*60*24*365);
        $sortDir = $_POST['sortDir'];
    }else{
        // retreive prefs
        for($i=0; $i<count($names);$i++){
            if(isset($_COOKIE[$names[$i]])){
                //echo("COOKIE[".$names[$i]."] = " . $_COOKIE[$names[$i]] . "<br />");
                if($_COOKIE[$names[$i]] != "show"){
                    $$names[$i] = "hide";
                }else{
                    $$names[$i] = "show";
                }
            }else{
                $$names[$i] = "show";
            }
        }
        // GET THE PREFERRED SKIN
        if(isset($_COOKIE['skin'])){
            $skin = $_COOKIE['skin'];
        }else{
            $skin = $this->defaultSkin;
        }
        // GET THE SLIDE SHOW SPEED
        if(isset($_COOKIE['ssSpeed'])){
            $ssSpeed = $_COOKIE['ssSpeed'] * 1000;
        }else{
            $ssSpeed = $this->defaultSSSpeed * 1000;
        }
        // GET THE SORT BY AND DIRECTION
        if(isset($_COOKIE['sortBy'])){
            $sortBy = $_COOKIE['sortBy'];
        }else{
            $sortBy = $this->defaultSort;
        }
        if(isset($_COOKIE['sortDir'])){
            $sortDir = $_COOKIE['sortDir'];
        }else{
            $sortDir = $this->defSortDirection ;
        }
    }





    if(isset($_GET['action']) && $_GET['action'] == "getFolderContents"){
        if(substr($_GET['dir'],0,2) != ".." && substr($_GET['dir'],0,1) != "/" && $_GET['dir'] != "./" && !stristr($_GET['dir'], '../')){
            $dir = $_GET['dir'];
            $list = new FNFileList;
            $list->getDirList($dir);
            exit;
        }else{
            // someone is poking around where they shouldn't be
            echo("Don't hack my shit yo.");
            exit;
        }
    }else if(isset($_GET['action']) && $_GET['action'] == "nextImage"){
        $out = new FNOutput;
        $tmp = $out->nextAndPrev($_GET['pic']);
        if($tmp[1] == ""){
            $nextpic = $tmp[2];
        }else{
            $nextpic = $tmp[1];
        }
        // get the image to preload
        $tmp2 = $out->nextAndPrev($nextpic);
        // get the image dimensions
        $imageDim = @getimagesize($nextpic);
        echo $nextpic."|".$imageDim[0]."|".$imageDim[1]."|".$tmp2[1];
        exit;
    }

$data = array('ssSpeed' => $ssSpeed,
    'skin' => $skin,
    'titlePath' => $titlePath,
    'sortBy' => $sortBy,
    'sortDir' => $sortDir,
    'dir' => $dir

);




    $this->load->view('vault/browse', $data);




}

}
