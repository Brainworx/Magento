<?php
//$pieces = explode('|', $_GET["sessionId"]);
setcookie("bitcheck",  $_GET["sessionId"] , time()+3600);
//setcookie("frontend",  $pieces[0], time()+3600);
header("Location: index.php");
//echo "<a href='http://localhost/magento/'>click</a>";
exit;