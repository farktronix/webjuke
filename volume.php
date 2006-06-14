<html>
<head>
<style type="text/css" title="currentStyle" media="screen">
        @import "style.css";
</style>
</head>
<body>
<div height="60px" width="100%" style="position:absolute;left:0;top:0;">
<?php
$volumeRaw=exec('amixer get \'Master\' | tail -n 1');
$volumeRaw=preg_replace("/.*\[(\d+)%\].*/", "\$1", $volumeRaw);

if(strchr(getenv(REMOTE_ADDR), "129.65.18")!=FALSE)
    $local=TRUE;

if($local) {
    if($_POST['volup']) {
        if($volumeRaw<=85)
            $volumeRaw+=5;
    } elseif($_POST['voldown']) {
        if($volumeRaw>=5)
            $volumeRaw-=5;
    } elseif($_POST['phone']) {
        $volumeRaw=55;
    }
    exec('amixer set \'Master\' '.$volumeRaw.'%');
} ?>
<div style="font-size:12px">Volume: <? echo $volumeRaw ?>%</div>
<?
if($local) {
?>


<form action="volume.php" method="post" style="display:inline;float:left;">
<input name="volup" value="1" type="hidden">
<input value="up" class="volbutton" type="submit">
</form>

<form action="volume.php" method="post" style="display:inline;float:left;">
<input name="voldown" value="1" type="hidden">
<input value="down" class="volbutton" type="submit">
</form>

<form action="volume.php" method="post" style="display:inline;float:left;">
<input name="phone" value="1" type="hidden">
<input value="phone!" class="volbutton" type="submit"></form>


<?
if($_POST['volup'] && $volumeRaw>85) {
    echo "<font color=\"red\">Hobble thinks it's too loud!</font>";
}
}
?>
</div>
</body>
</html>
