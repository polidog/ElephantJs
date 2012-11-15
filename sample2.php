<?php
require 'ElephantJs.php';

$a = "hohoge";
$jsElephant = new ElephantJs(get_defined_vars());
$result = $jsElephant->executeFile('sample2.js');
var_dump($result);
