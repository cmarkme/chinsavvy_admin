<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$config['ckeditor_toolbar'] = array(
        array('name' => 'document', 'items' => array( 'Source','-','Save' )),
        array('name' => 'clipboard', 'items' => array( 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' )),
        array('name' => 'editing', 'items' => array( 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' )),
        '/',
        array('name' => 'basicstyles', 'items' => array( 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' )),
        array('name' => 'paragraph', 'items' => array( 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
        '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' )),
        array('name' => 'links', 'items' => array( 'Link','Unlink','Anchor' )),
        array('name' => 'insert', 'items' => array( 'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' )),
        '/',
        array('name' => 'styles', 'items' => array( 'Styles','Format','Font','FontSize' )),
        array('name' => 'colors', 'items' => array( 'TextColor','BGColor' )),
        array('name' => 'tools', 'items' => array( 'Maximize', 'ShowBlocks','-','About', '-', 'Signature'))
    );
$config['ckeditor_default_toolbar'] = array(
        array('name' => 'document', 'items' => array( 'Source','-','Save' )),
        array('name' => 'clipboard', 'items' => array( 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' )),
        array('name' => 'editing', 'items' => array( 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' )),
        '/',
        array('name' => 'basicstyles', 'items' => array( 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' )),
        array('name' => 'paragraph', 'items' => array( 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote',
        '-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' )),
        array('name' => 'links', 'items' => array( 'Link','Unlink','Anchor' )),
        array('name' => 'insert', 'items' => array( 'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' )),
        '/',
        array('name' => 'styles', 'items' => array( 'Styles','Format','Font','FontSize' )),
        array('name' => 'colors', 'items' => array( 'TextColor','BGColor' )),
        array('name' => 'tools', 'items' => array( 'Maximize', 'ShowBlocks','-','About'))
    );
$config['ckeditor_extraPlugins'] = 'signature';
$config['ckeditor_basePath'] = '/includes/js/ckeditor/';
