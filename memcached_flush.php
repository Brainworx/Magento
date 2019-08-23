<?php
$m = new Memcached();
$m->addServer('com-linmemcached001', 9043);

/* flush all items in 10 seconds */
$m->flush(5);
echo ('Flush will be done in 5 seconds.');
?>