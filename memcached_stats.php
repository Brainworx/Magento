<?php
$m = new Memcached();
$m->addServer('com-linmemcached001', 9043);

print_r($m->getStats());
?>