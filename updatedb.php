<?php
include("config.php");

require_once('getid3/getid3.php');
$getID3=new getID3;

$database=mysql_connect($dbhost, $dbuser, $dbpass)
    or die("Could not connect to database on $dbhost");
mysql_select_db($dbname);

?>

<table class="trackList" border=1>
<tr><td>Hash</td><td>Title</td><td>Album</td><td>Artist</td><td>Length</td><td>Genre</td><td>Location</td></tr>
<?php

/* scan all of the songs in the temporary music folder, add the songs to the database,
   and copy them to the music directory.
*/
chdir($musictmp);
foreach(glob("*.mp3") as $mp3) {
    $duplicateSong=0;

    /* read the id3 tag */
    $mp3info=array();
    $mp3id3=$getID3->analyze($mp3);
    getid3_lib::CopyTagsToComments($mp3id3);

    /* gather the data into a cleaner structure */
    $mp3info['hash']=@$mp3id3['md5_data'];
    $len_sec=$mp3info['len']=intval(@$mp3id3['playtime_seconds']);

    $mp3info['title']=@$mp3id3['comments_html']['title'][0];
    if(!strlen($mp3info['title']))
        $mp3info['title']=@$mp3id3['comments_html']['title'][1];
    if(!strlen($mp3info['title'])) {
        /* if there's no track name we don't want it in the database */
        // NEWS FLASH: this is dumb.
        //print "<tr>No track name found: $mp3</tr>\n";
        //continue;
        $mp3info['title']=$mp3;
    }
    $mp3info['title']=addslashes($mp3info['title']);

    $mp3info['album']=@$mp3id3['comments_html']['album'][0];
    if(!strlen($mp3info['album']))
        $mp3info['album']=@$mp3id3['comments_html']['album'][1];
    if(!strlen($mp3info['album']))
        $mp3info['album']="Unknown Album";
    $mp3info['album']=addslashes($mp3info['album']);
        
    $mp3info['artist']=@$mp3id3['comments_html']['artist'][0];
    if(!strlen($mp3info['artist']))
        $mp3info['artist']=@$mp3id3['comments_html']['artist'][1];
    if(!strlen($mp3info['artist']))
        $mp3info['artist']="Unknown Artist";
    $mp3info['artist']=addslashes($mp3info['artist']);

    $mp3info['genre']=addslashes(@$mp3id3['comments_html']['genre'][0]);

    $mp3info['track']=@$mp3id3['comments_html']['track'][0];

    $mp3info['location']=$mp3info['artist']."/".$mp3info['album']."/".$mp3info['title'].".mp3";

    /* check for duplicates */
    $query="SELECT md5 FROM tracks WHERE md5 = '".$mp3info['hash']."'";
    $result=mysql_query($query) or die("query failed: $query");
    if(mysql_fetch_array($result, MYSQL_ASSOC))
        $duplicateSong=1;
    mysql_free_result($result);
    if(file_exists($musicroot.$mp3info['location']))
        $duplicateSong=1;

    /* print out song info */
    echo "<tr";
    if($duplicateSong)
        echo " bgcolor=\"salmon\"";
    echo "><td>";
    echo $mp3info['hash'];
    echo "</td><td>";
    echo $mp3info['title'];
    echo "</td><td>";
    echo $mp3info['album'];
    echo "</td><td>";
    echo $mp3info['artist'];
    echo "</td><td>";
    printf("%02d:%02d:%02d", $len_sec/3600, ($len_sec%3600)/60, ($len_sec%60));
    echo "</td><td>";
    echo $mp3info['genre'];
    echo "</td><td>";
    echo $mp3info['location'];
    echo "</td></tr>\n";

    if($duplicateSong)
        continue;

    $query="INSERT INTO `tracks`( `md5`, `title`, `album`, `artist`, `genre`, `tracknum`, `len`, `loc`, `plays`, `rejects`) VALUES ( '"
    .$mp3info['hash']."',\""
    .$mp3info['title']."\",\""
    .$mp3info['album']."\",\""
    .$mp3info['artist']."\",\""
    .$mp3info['genre']."\",'"
    .$mp3info['track']."','"
    .$mp3info['len']."',\""
    .$mp3info['location']."\",'0','0')";

    $result=mysql_query($query) or die("query failed: $query");

    if(!file_exists($musicroot.$mp3info['artist']))
        mkdir($musicroot.$mp3info['artist']);
    if(!file_exists($musicroot.$mp3info['artist']."/".$mp3info['album']))
        mkdir($musicroot.$mp3info['artist']."/".$mp3info['album']);

    if(!rename($musictmp.$mp3, $musicroot.$mp3info['location']))
        echo "error copying file ".$musictmp.$mp3." to ".$musicroot.$mp3info['location'];
}

?>
</table>

<?php
// clean out any songs in the database that have been deleted from the hard drive
$query="SELECT * FROM tracks WHERE 1 ORDER BY artist"; 
$result=mysql_query($query) or die("query failed: $query");
while($mp3info=mysql_fetch_array($result, MYSQL_ASSOC)) {
    if(!file_exists($musicroot.$mp3info['loc'])) {
        echo $mp3info['loc']." not found! Deleting from database...<br>\n";
        mysql_query("DELETE FROM tracks WHERE `md5`='".$mp3info['md5']."'");
    }
}
?>

<?php
mysql_close($database);
?>
