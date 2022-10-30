<?php

//how use script

include("sqlite_dump.php");

$myDB = "myDB.sqlite"; //path to myDB
$myFile = "myFile.sql"; //path to myFile
$sql = $sqlite_dump->dump_sqlite($myDB);
file_put_content($myFile, $sql);

?>
