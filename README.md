# ElephantJs

ElephantJsは、PHPからJavascritpを実行するためのライブラリです。  
使用するためにはV8Jsが必要です。


	pecl install v8js
  


### 使い方
----


	require 'ElephantJs.php';
	
	$a = "ElephantJs samples string a";

	// 普通に動かす
	$jsElephant = new ElephantJs(get_defined_vars());
	$jsElephant->execute('print(PHP.getVars("a","local") + "\n");');

これで実行すれば、以下のように出力されます  

	a

ファイルから実行したい場合は

	require 'ElephantJs.php';
	$jsElephant = new ElephantJs(get_defined_vars());
	$jsElephant->executeFile('sample.js');
	
とファイル名を指定すれば実行できます。  
実行したいディレクトリが別の場所の場合は指定することもできます。  

	require 'ElephantJs.php';
	$jsElephant = new ElephantJs(get_defined_vars());
	$jsElephant->executeFile('sample.js','/var/www/html/js');
	
_注意_ コンストラクタで必ずget_defined_vars()を引数として実行しないとローカルスコープの変数が取得できません。


#### Javascriptオブジェクトについて
----

ElephantJsで実行した場合にPHPという変数名でJavascript側にオブジェクトが渡されます。  
今のところメソッドは以下のものを用意しています。

	PHP.getVars('変数名','local'); // 第二引数はlocal or globalを指定する

PHP.getVars()を実行することによりPHP側の変数を取得する事ができます。 
あと実行しているPHPのバージョンを取得したい場合は、PHP.versionで取得できます。

ちなみに、メソッドが足りない場合にPHP側からメソッドを動的に増やす事も出来ます。

例:printlnを追加する  

	$jsElephant->attachJs(array(
		'println' => 'function(key) { print( key + "\n") };'
	));
	$jsElephant->execute('PHP.println("これはJavascriptです。うそではないです。");');


