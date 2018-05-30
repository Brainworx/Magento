<?php
//$pieces = explode('|', $_GET["sessionId"]);
setcookie("zorgpuntcheck",  $_GET["zorgpuntid"] , time()+14400);
//setcookie("frontend",  $pieces[0], time()+4*60*60);
header("Location: index.php");
//echo "<a href='http://localhost/magento/'>click</a>";
exit;