<?php
include("config.php");

$database=mysql_connect($dbhost, $dbuser, $dbpass)
    or die("Could not connect to database on $dbhost");
mysql_select_db($dbname);

function secToHMS($sec) {
    if(($sec/3600)>=1) {
        return sprintf("%2d:%02d:%02d", $sec/3600, ($sec%3600)/60, ($sec%60));
    } else {
        return sprintf("%2d:%02d", ($sec%3600)/60, ($sec%60));
    }
}

function getCurrentSong($num) {
    $query="SELECT * FROM current WHERE `id`='$num'";
    $result=mysql_query($query) or die("query failed: $query");
    return mysql_fetch_array($result, MYSQL_ASSOC);
}

function getTrackInfo($md5) {
    $query="SELECT * FROM tracks WHERE `md5`='$md5'";
    $result=mysql_query($query) or die("query failed: $query");
    return mysql_fetch_array($result, MYSQL_ASSOC);
}

function getCurrentTrackInfo() {
    $curSong=getCurrentSong(1);
    $curTrack=getTrackInfo($curSong['md5']);
    return array_merge($curSong, $curTrack);
}

function getPreviousTrackInfo() {
    $curSong=getCurrentSong(2);
    $curTrack=getTrackInfo($curSong['md5']);
    return array_merge($curSong, $curTrack);
}

// if there's another song playing, count this reject, else increment the play count
function endCurrentSong() {
    // get info on the currently playing song
    $currentTrack=getCurrentTrackInfo();
    $currentMD5=$currentTrack['md5'];
    $currentStart=$currentTrack['starttime'];
    $currentLen=$currentTrack['len'];
    $currentRejects=$currentTrack['rejects'];
    $currentPlays=$currentTrack['plays'];

    if(!strcmp($currentSong['title'],"") && (time()-$currentStart)<$currentLen) {
        // the song is still playing
        $currentRejects++;
    } else {
        // the song has ended
        $currentPlays++;
    }

    $query="UPDATE `tracks` SET `rejects`='$currentRejects', `plays`='$currentPlays' WHERE `md5`='$currentMD5' LIMIT 1";
    $result=mysql_query($query) or die ("update of current song failed: $query");

    //record the last song played
    $query="UPDATE `current` SET `md5`='$currentMD5', `starttime`='".$currentTrack['starttime']."', `whodidit`='".$currentTrack['whodidit']."' WHERE `id`='2' LIMIT 1";    
    $result=mysql_query($query) or die ("update of current song failed: $query");

    // erase the current song
    $query="UPDATE `current` SET `md5`='', `starttime`='0', `whodidit`='".$currentTrack['whodidit']."' WHERE `id`='1' LIMIT 1";    
    $result=mysql_query($query) or die ("update of current song failed: $query");
}

?>
