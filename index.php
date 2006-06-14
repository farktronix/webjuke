<?php
include("common.php");

session_start();

if(array_key_exists('sortBy', $_GET)) {
    if($_SESSION['sortBy']!=$_GET['sortBy'])
        $_SESSION['order']=0;
    else
        $_SESSION['order']=!$_SESSION['order'];

    $_SESSION['sortBy']=$_GET['sortBy'];
    ?> <meta http-equiv="refresh" content="0;url=<? echo $_SERVER['PHP_SELF'] ?>"><?
    exit;
}

if(array_key_exists('search', $_GET)) {
    $_SESSION['search']=$_GET['search'];
} else {
    $_SESSION['search']="";
}

?>
<html>

<head>

<style type="text/css" title="currentStyle" media="screen">
        @import "style.css";
</style>

<script language="JavaScript">
function popUp(URL) {
    day = new Date();
    id = day.getTime();
    eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=610,height=175');");
}

function submitSearch() {
    document.songSearch.submit();
}
function clearSearch() {
    document.getElementById("searchBox").value="";
    document.songSearch.submit();
}

function playlist() {
    
}
</script>

</head>

<body>

<div id="titleShadow1">
webjuke<sup>2</sup>
</div> 
<div id="title">
<a href="<? echo $_SERVER['PHP_SELF'] ?>" style="color:#dff073;text-decoration:none">&nbsp;webjuke<sup>2</sup></a>
<div id="curSong">
<?  // get currently playing song info
$currentTrack=getCurrentTrackInfo();

if($currentTrack['md5']=="")
    $currentTrack['title']="[No song playing]";

if($currentTrack['md5']!=""
   && (time()-$currentTrack['starttime'])>$currentTrack['len'])
{
    endCurrentSong();
} 
?>
<p>Current song:</p>
<a title="Played by: <? echo $currentTrack['whodidit'] ?>">
<p class="title"><b><? echo $currentTrack['title'] ?></b></p>
<?  if($currentTrack['artist']) { ?>
<p class="artist"> - <? echo $currentTrack['artist'] ?></p>
<? } ?>
<?  if($currentTrack['album']) { ?>
<p class="album"> - <? echo $currentTrack['album'] ?></p>
<? } ?>
</a>
<br>
<?  // get previous song info
$currentTrack=getPreviousTrackInfo();

if($currentTrack['md5']=="")
    $currentTrack['title']="[No previous song]";
?>
<p>Previous song: </p>
<a title="Played by: <? echo $currentTrack['whodidit'] ?>">
<p class="title"><b><? echo $currentTrack['title'] ?></b></p>
<?  if($currentTrack['artist']) { ?>
<p class="artist"> - <? echo $currentTrack['artist'] ?></p>
<? } ?>
<?  if($currentTrack['album']) { ?>
<p class="album"> - <? echo $currentTrack['album'] ?></p>
<? } ?>
</a>
</div>
</div> 

<div id="container">
<div id="toolbar">
    <p class="update"><a href="updatedb.php">Update Database</a></p>
    <p class="stop"><a href="play_mp3.php?track=stop">Stop Current Song</a></p>
    <p class="playlist"><a href="playlist.php">Show Playlist</a></p>
</div>

<iframe src="volume.php" frameborder="no" height="60px" width="100%" scrolling="no" style="display:inline;float:right;"></iframe>

<br>

<div id="search">
<form action="<? echo $_SERVER['PHP_SELF'] ?>" name="songSearch" METHOD=GET>
<input type="text" name="search" id="searchBox" value="<? echo $_SESSION['search'] ?>" size=20>
<a href="javascript: submitSearch()">Search</a> | <a href="javascript: clearSearch()">Clear</a>
</form>
</div>

<div id="sidebar">
<a href="<? echo $_SERVER['PHP_SELF'] ?>?search=" style="text-decoration:none">Artists:</a><br>
<?
$query="SELECT DISTINCT artist FROM tracks WHERE 1 ORDER BY artist";
$result=mysql_query($query) or die("Query failed: $query");
while($artist=mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
    &nbsp;&nbsp;&nbsp;<a href="<?echo $_SERVER['PHP_SELF'] ?>?search=<? echo $artist['artist'] ?>" style="text-decoration:none">
    <? echo $artist['artist'] ?></a><br>
<? } ?>
<br>
<a href="<? echo $_SERVER['PHP_SELF'] ?>?search=" style="text-decoration:none">Albums:</a><br>
<?
$query="SELECT DISTINCT album FROM tracks WHERE 1 ORDER BY album";
$result=mysql_query($query) or die("Query failed: $query");
while($album=mysql_fetch_array($result, MYSQL_ASSOC)) { ?>
    &nbsp;&nbsp;&nbsp;<a href="<?echo $_SERVER['PHP_SELF'] ?>?search=<? echo $album['album'] ?>" style="text-decoration:none">
    <? echo $album['album'] ?></a><br>
<? } ?>
</div>

<div id="songList">
<b><a href="<? echo $_SERVER['PHP_SELF'] ?>">Webjuke</a><?if($_SESSION['search']) { ?>
:: <? echo $_SESSION['search']; } ?></b>


<form action="playlist.php" method=POST>
<input type="submit" name="add" value="Add to Playlist"><input type="submit" name="del" value="Remove from Playlist"><br>
<table cellpadding=3 cellspacing=0 width=80%> 
<tr class="songHeader"><td></td>
<? if($_SESSION['order']==0) {
    $sortImg="sortUp.gif";
} else {
    $sortImg="sortDown.gif";
} ?>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=title">Title<?
if($_SESSION['sortBy']=="title")
    echo "<img src=\"$sortImg\" border=0>";
?></a>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=artist">Artist<?
if($_SESSION['sortBy']=="artist")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=album">Album<?
if($_SESSION['sortBy']=="album")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=len">Length<?
if($_SESSION['sortBy']=="len")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=genre">Genre<?
if($_SESSION['sortBy']=="genre")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=plays">Plays<?
if($_SESSION['sortBy']=="plays")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
<td align="center"><a href="<? echo $_SERVER['PHP_SELF'] ?>?sortBy=rejects">Rejects<?
if($_SESSION['sortBy']=="rejects")
    echo "<img src=\"$sortImg\" border=0>";
?></a></td>
</tr>
<?php

$num=0;

$query="SELECT * FROM tracks WHERE ";
if($_SESSION['search']!="") {
    $query .= "`title` LIKE '%".$_SESSION['search']."%' ";
    $query .= "OR `album` LIKE '%".$_SESSION['search']."%' ";
    $query .= "OR `artist` LIKE '%".$_SESSION['search']."%' ";
    $query .= "OR `genre` LIKE '%".$_SESSION['search']."%' ";
} else {
    $query .= "1 ";
}
if($_SESSION['sortBy']) 
    $query .= "ORDER BY ".$_SESSION['sortBy']." ";
else
    $query .= "ORDER BY artist ";

if($_SESSION['order']==0)
    $query .= "ASC ";
else
    $query .= "DESC ";

$result=mysql_query($query) or die("query failed: $query");
while($mp3info=mysql_fetch_array($result, MYSQL_ASSOC)) {
    $num=++$num%2;

?>
    <tr class="songInfo" id="song<? echo $num ?>">
        <td><img src="download.gif" border=0></a>
            <a href="javascript:popUp('edit.php?md5=<? echo $mp3info['md5'] ?>')"><img src="edit.gif" border=0></a></td>
        <td><input type="checkbox" name="<? echo $mp3info['md5'] ?>">
            <a href="play_mp3.php?track=<? echo $mp3info['md5'] ?>">
            <? echo $mp3info['title'] ?></a></td>
        <td>
        <a href="<? echo $_SERVER['PHP_SELF'] ?>?search=<? echo $mp3info['artist']?>"><? echo $mp3info['artist'] ?></a>
        </td>
        <td> 
        <a href="<? echo $_SERVER['PHP_SELF'] ?>?search=<? echo $mp3info['album']?>"><? echo $mp3info['album'] ?></a>
        <td> <? echo secToHMS($mp3info['len']) ?></td>
        <td> 
        <a href="<? echo $_SERVER['PHP_SELF'] ?>?search=<? echo $mp3info['genre']?>"><? echo $mp3info['genre'] ?></a>
        </td>
        <td> <? echo $mp3info['plays'] ?></td>
        <td> <? echo $mp3info['rejects'] ?> </td>
    </tr>
<?
}
mysql_free_result($result);
?>
</table>
<input type="submit" name="add" value="Add to Playlist"><input type="submit" name="del" value="Remove from Playlist">
</form>
</div>

You are: <? if(getenv('REMOTE_HOST')) echo getenv('REMOTE_HOST'); else echo getenv('REMOTE_ADDR'); ?>
</div>

</body>
</html>
