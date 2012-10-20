<?php
require 'ElephantJs.php';

$a = "ElephantJs samples string a";

// 普通に動かす
$jsElephant = new ElephantJs(get_defined_vars());
$jsElephant->execute('print(PHP.getVars("a","local") + "\n");');

// ファイルから実行する場合
$jsElephant->executeFile('sample.js');

// 動的にPHPオブジェクトにPHPを追加する
$jsElephant->attachJs(array(
	'println' => 'function(key) { print( key + "\n") };'
));
$jsElephant->execute('PHP.println("これはJavascriptです。うそではないです。");');

// 関数から使ってみる
function test($a = '引数1', $b = '引数2') {
	$c = "ローカル！";
	$jsElephant = new ElephantJs(get_defined_vars());
	$jsElephant->execute('print(PHP.getVars("a","local") + "\n")');
	$jsElephant->execute('print(PHP.getVars("b","local") + "\n")');
	$jsElephant->execute('print(PHP.getVars("c","local") + "\n")');
}

test();
