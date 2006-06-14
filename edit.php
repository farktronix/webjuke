<?php
include("common.php");

// print a single-line text box
function input_text($element_name, $values) {
     print '<input type="text" name="' . $element_name .'" value="';
     print htmlentities($values[$element_name]) . '">';
}

/*
if($_GET['update']) {
    $query="UPDATE `tracks` SET ".
    "`title`='".$_POST['title']."',".
    "`album`='".$_POST['album']."',".
    "`artist`='".$_POST['artist']."',".
    "`genre`='".$_POST['genre']."',".
    "`tracknum`='".$_POST['tracknum']."'".
    " WHERE `md5`='".$_POST['md5']."' LIMIT 1";

    echo $query;
} else {

    if(!($trackInfo=getTrackInfo($_GET['md5']))) {
        die("Error reading track information from database!".
            "(md5: ".$_GET['md5'].")");
    }

    print_r($trackInfo);
}
*/

require_once 'HTML/QuickForm.php';

$form = new HTML_QuickForm('editTrack');

$trackInfo=getTrackInfo($_GET['md5']);
$form->setDefaults($trackInfo);

$form->addElement('header', null, 'Edit Track Info');
$form->addElement('text', 'title', 'Track Name:', array('size' => 60, 'maxlength' => 255));
$form->addElement('text', 'album', 'Album:', array('size' => 60, 'maxlength' => 255));
$form->addElement('text', 'artist', 'Artist:', array('size' => 60, 'maxlength' => 255));
$form->addElement('text', 'genre', 'Genre:', array('size' => 60, 'maxlength' => 255));
$form->addElement('hidden', 'md5', 'md5');
$form->addElement('submit', null, 'Submit');

$form->applyFilter('title', 'trim');
$form->applyFilter('album', 'trim');
$form->applyFilter('artist', 'trim');
$form->applyFilter('genre', 'trim');

if($form->validate()) {
    $query="UPDATE `tracks` SET ".
    "`title`='".addslashes($form->exportValue('title'))."',".
    "`album`='".addslashes($form->exportValue('album'))."',".
    "`artist`='".addslashes($form->exportValue('artist'))."',".
    "`genre`='".addslashes($form->exportValue('genre'))."',".
    "`tracknum`='".addslashes($form->exportValue('tracknum'))."'".
    " WHERE `md5`='".addslashes($form->exportValue('md5'))."' LIMIT 1";
    mysql_query($query) or die ("update of track information failed: $query");
    ?> <body onLoad="JavaScript:window.opener.location.reload();window.close()"> <?
    //echo "<meta http-equiv=\"Refresh\" content=\"0;url=index.php\">";
    exit;
}

$form->display();
?>
