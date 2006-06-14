<?php
include("common.php");

$md5=$_GET['track'];

if(!$md5) {
    die("No track specified. You shouldn't be here.");
}

if($md5=="stop") {
    endCurrentSong();
    system("killall mpg123");
} else {

/* make sure the song exists */
$query="SELECT * FROM tracks WHERE md5 = '".$md5."'";
$result=mysql_query($query) or die("query failed: $query");
if(!($track=mysql_fetch_array($result, MYSQL_ASSOC)))
    die("Song not found in database. Go back and try again.");

if(!file_exists($musicroot.$track['loc']))
    die($musicroot.$track['loc']."<br>Song not found in directory. Please check that the file exists or re-upload the file.");

endCurrentSong();

if($remote_user=getenv('REMOTE_HOST')) ;
else 
    $remote_user=getenv('REMOTE_ADDR');

$query="UPDATE `current` SET `md5`='".$md5."', `starttime`='".time()."', `whodidit`='".$remote_user."' WHERE `id`='1'LIMIT 1";
$result=mysql_query($query) or die ("update of current song failed: $query");


//print("Playing ".$track['artist']." - ".$track['title']."<br>");
system("killall mpg123");
system($mpg123." \"".$musicroot.$track['loc']."\" > /dev/null &");
}
?>
<meta http-equiv="Refresh" content="0;url=index.php">
