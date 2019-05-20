<?php
setcookie("zorgpuntcheck", $_GET["zorgpuntid"] , time()+14400);
if(array_key_exists("goto", $_GET))
    header("Location: ".$_GET["goto"]);
else
    header("Location: index.php");
exit;
