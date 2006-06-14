<?
include("common.php");

function inPlaylist($md5) {
    $query="SELECT * FROM `playlist` WHERE `md5`='$md5' AND playlist=1";
    $result=mysql_query($query) or die("query failed: $query");
    return mysql_fetch_array($result, MYSQL_ASSOC);
    
}
?>
<html>

<head>
<style type="text/css" title="currentStyle" media="screen">
        @import "style.css";
</style>
</head>

<body>

<?
$query = "SELECT * FROM `playlist` WHERE playlist=1 ORDER BY `order` DESC LIMIT 1";
$result=mysql_query($query) or die("query failed: $query");
$lastEntry=mysql_fetch_array($result, MYSQL_ASSOC);
$num=$lastEntry['order']+1;

if(array_key_exists('up', $_GET)) {
    $query = "SELECT * FROM `playlist` WHERE playlist=1 AND `md5`='".$_GET['up']."'";
    $result=mysql_query($query) or die("query failed: $query");
    $order=mysql_fetch_array($result, MYSQL_ASSOC);

    if($order['order']>0) {
        // move one above it down
        $query = "UPDATE `playlist` SET `order`='".$order['order']."' WHERE `order`='".($order['order']-1)."'";
        mysql_query($query) or die("query failed: $query");

        // move this one up
        $query = "UPDATE `playlist` SET `order`='".($order['order']-1)."' WHERE `order`='".$order['order']."' AND `md5`='".$order['md5']."'";
        mysql_query($query) or die("query failed: $query");
    } 

} else if(array_key_exists('down', $_GET)) {
    $query = "SELECT * FROM `playlist` WHERE playlist=1 AND `md5`='".$_GET['down']."'";
    $result=mysql_query($query) or die("query failed: $query");
    $order=mysql_fetch_array($result, MYSQL_ASSOC);

    if($order['order']<$num-1) {
        // move one above it down
        $query = "UPDATE `playlist` SET `order`='".$order['order']."' WHERE `order`='".($order['order']+1)."'";
        mysql_query($query) or die("query failed: $query");

        // move this one up
        $query = "UPDATE `playlist` SET `order`='".($order['order']+1)."' WHERE `order`='".$order['order']."' AND `md5`='".$order['md5']."'";
        mysql_query($query) or die("query failed: $query");
    } 

}

if(array_key_exists('add', $_POST)) {

    unset($_POST['add']);
    foreach($_POST as $md5 => $on) {
        if(!(inPlaylist($md5))) {
            $query = "INSERT INTO `playlist` (`md5`, `order`, `playlist`) VALUES".
                     "('$md5', '".$num++."', '1')";
            mysql_query($query) or die("query failed: $query");
        }
    }
} else if(array_key_exists('del', $_POST)) {
    unset($_POST['del']);
    foreach($_POST as $md5 => $on) {
        $query = "DELETE FROM `playlist` WHERE `playlist`=1 AND `md5`='$md5'";
        mysql_query($query) or die("query failed: $query");
    }
    //reorder
    $query = "SELECT * FROM `playlist` WHERE playlist=1 ORDER BY `order`";
    $result=mysql_query($query) or die("query failed: $query");
    $num=0;
    while($playlist=mysql_fetch_array($result, MYSQL_ASSOC)) {
        $query = "UPDATE `playlist` SET `order`='".$num++."' WHERE `md5`='".$playlist['md5']."'";
        mysql_query($query) or die("query failed: $query");
    }
}

?>

<div id="playlist">
<form action="playlist.php" method=POST>
<input type="submit" name="del" value="Remove from Playlist"><br>
<table cellpadding=3 cellspacing=0 width=100%> 
<tr class="songHeader"><td align="center">Order</td>
<td align="center">Title</td>
<td align="center">Artist</td>
<td align="center">Album</td>
<td align="center">Length</td>
<td align="center">Genre</td>
<td align="center">Plays</td>
<td align="center">Rejects</td>
</tr>
<?php
$num=0;

$query="SELECT * FROM `playlist` WHERE `playlist`=1 ORDER BY `order`";

$result=mysql_query($query) or die("query failed: $query");
while($track=mysql_fetch_array($result, MYSQL_ASSOC)) {
    $query="SELECT * FROM tracks WHERE `md5`='".$track['md5']."'";
    $trackRes=mysql_query($query) or die("query failed: $query");
    $mp3info=mysql_fetch_array($trackRes, MYSQL_ASSOC);
    $num=++$num%2;

?>
    <tr class="songInfo" id="song<? echo $num ?>">
        <td><? echo $track['order']+1 ?>
        <a href="<? echo $_SERVER['PHP_SELF'] ?>?up=<? echo $mp3info['md5'] ?>">+</a>/
        <a href="<? echo $_SERVER['PHP_SELF'] ?>?down=<? echo $mp3info['md5'] ?>">-</a>
        <td><input type="checkbox" name="<? echo $mp3info['md5'] ?>">
            <? echo $mp3info['title'] ?></td>
        <td><? echo $mp3info['artist'] ?> </td>
        <td><? echo $mp3info['album'] ?> </td>
        <td><? echo secToHMS($mp3info['len']) ?></td>
        <td><? echo $mp3info['genre'] ?></td>
        <td> <? echo $mp3info['plays'] ?></td>
        <td> <? echo $mp3info['rejects'] ?> </td>
    </tr>
<?
}
mysql_free_result($result);
?>
</table>
<input type="submit" name="del" value="Remove from Playlist"><br>
</form>

</div>

</body>
</html>
