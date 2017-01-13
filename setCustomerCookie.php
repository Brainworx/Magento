<?php
setcookie("frontend",  $_GET["sessionId"] , time()+3600);
header("Location: index.php");
exit;