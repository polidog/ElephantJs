<?php

/**
 * JSでPHPを動かすアホクラス 
 * 
 * @author polidog
 * @version 0.1
 */
class ElephantJs {

	const VERSION = 0.1;

	/**
	 * デバッグモードフラグ
	 * @var boolean 
	 */
	public static $debug = false;

	/**
	 * V8Engine
	 * @var V8Js 
	 */
	protected $v8Engine = null;

	/**
	 * 起動用のJsのファイルを指定する
	 * @var string
	 */
	protected $bootjsPath = '';

	/**
	 * グローバルスコープの変数名
	 * @var array 
	 */
	protected $globalVarKeys = array(
		'argv', 'argc', '_POST', '_GET', '_COOKIE', '_FILES', '_ENV', '_REQUEST', '_SERVER'
	);

	/**
	 * PHPの変数データを格納する
	 * @var stdClass
	 */
	protected $phpVars = null;
	
	/**
	 * PHPオブジェクトに付け加えるjsのメソッドとか値とか
	 * @var array 
	 */
	protected $attachJs = array();

	/**
	 * コンストラクタ
	 * @param array $phpVars
	 * @throws Exception 
	 */
	public function __construct($phpVars = array()) {

		if (class_exists('V8Js') === false) {
			// v8jsがない場合は例外
			throw new Exception('V8Js class not found');
		}

		// 変数管理用にstdクラス使う
		$this->phpVars = new stdClass();

		// v8 engineを生成
		$this->v8Engine = new V8Js('__JSPHP');

		// グローバル変数のパース処理
		$this->parsePhpGrobalVars();

		// 変数をセットする
		$this->setPhpLocalVars($phpVars);
	}

	/**
	 * 実行したいJavascriptのstringを渡して実行する
	 * @param string $jsString  
	 */
	public function execute($jsString) {
		$_execJsString = $this->createJsObject();
		$_execJsString .= "\n" . $jsString;
		$this->v8Engine->executeString($_execJsString);
	}

	/**
	 * Jsファイルを実行する
	 * @param array $files 
	 */
	public function executeFile($fileNames, $basePath = null) {
		if (!is_null($fileNames)) {
			$fileNames = array($fileNames);
		}

		if (empty($fileNames)) {
			throw new ErrorException('file not found');
		}

		if (empty($basePath)) {
			$basePath = dirname(__FILE__);
		}

		foreach ($fileNames as $file) {
			$path = $basePath . DIRECTORY_SEPARATOR . $file;
			if (file_exists($path)) {
				$this->execute(file_get_contents($path));
			}
		}
	}
	
	
	/**
	 * JSを動的に追加する
	 * @param mixed $key array | string
	 * @param string $value 
	 */
	public function attachJs($key, $value = null) {
		if ( is_array($key) && is_null($value) ) {
			foreach ( $key as $k => $v ) {
				$this->attachJs($k,$v);
			}
		}
		else {
			$this->attachJs[$key] = $value;
		}
	}

	/**
	 * jsに受け渡すphp変数を取得する
	 * @return array 
	 */
	public function getPhpVars() {
		return $this->phpVars;
	}

	/**
	 * Javascriptに渡すためのJsオブジェクトを生成する
	 * @return string
	 */
	protected function createJsObject() {
		$js = 'var PHP = { varsion: "' . PHP_VERSION . '",vars:{global:%s,local:%s}};';
		$js .= 'PHP.getVars = function(key,type) { if ( this.vars[type][key] !== undefined ) { return this.vars[type][key]} };';

		if ( !empty( $this->attachJs ) ) {
			foreach ( $this->attachJs as $key => $value ) {
				$js .= "PHP.{$key} = {$value}";
			}
		}
		
		if (empty($this->phpVars->global)) {
			$global = "{}";
		} else {
			$global = json_encode($this->phpVars->global);
		}

		if (empty($this->phpVars->local)) {
			$local = "{}";
		} else {
			$local = json_encode($this->phpVars->local);
		}
		return sprintf($js, $global, $local);
	}

	/**
	 * PHPのグローバル変数JSに渡すために精査する
	 */
	protected function parsePhpGrobalVars() {

		// ない変数を補填する
		foreach ($this->globalVarKeys as $value) {
			if (!isset($phpVars[$value]) && isset($GLOBALS[$value])) {
				$phpVars[$value] = $GLOBALS[$value];
			}
		}
		$this->phpVars->global = $phpVars;
	}

	/**
	 * PHPの変数をセットする
	 * @param array $phpVars
	 * @return JsPhp
	 */
	protected function setPhpLocalVars(array $phpVars) {

		// いらないグローバル変数がついてた場合は削除する
		if (isset($phpVars['GLOBALS'])) {
			unset($phpVars['GLOBALS']);
		}

		foreach ($this->globalVarKeys as $value) {
			if (isset($phpVars[$value])) {
				unset($phpVars[$value]);
			}
		}

		$this->phpVars->local = $phpVars;
		return $this;
	}

	/**
	 * ファイルの中身を取得する
	 * @param string $path
	 * @return mixed [bool|string] 
	 */
	protected function getJsContent($path, $isEval = false, $params = array()) {

		extract($params);
		if (!file_exists($path)) {
			return false;
		}

		$content = file_get_contents($path);
		if ($isEval) {
			return eval($content);
		}
		return $content;
	}

	protected function debugLog($message) {
		$this->output($message, 'debug');
	}

	protected function output($message, $prefix, $html = false) {
		if (!$html) {
			echo "[{$prefix}]" . $message . "\n";
		}
	}

}
