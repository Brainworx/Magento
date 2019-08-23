<?php
$m = new Memcached();
$m->addServer('com-linmemcached001', 9043);

// get all stored memcached items

$keys = $m->getAllKeys();
print_r($keys);
?>