<?php
namespace DevTools;
use phppo\system\systemProcessing as systemProcessing;
use phppo\command\plugincommand\addcommand as addcommand;
$pluginAddCommand = new addcommand;
$pluginAddCommand -> addcommand("DevTools","makephar","plugin","Compress the directory to the phar","<Path|PluginName> / <system>");
// $pluginAddCommand -> addcommand("DevTools","mktmp","plugin","Create PHPPO Plugin template.","(wizard)");
// $pluginAddCommand -> addcommand("DevTools","extractphar","plugin","Extract phar archive.","<Archive Path> / <Archive Path> <To Path>");
$pluginAddCommand -> addcommand("DevTools","vardump","plugin","View the contents of the variables defined in the system.","<Variable Name> / <Variable Name> <Class Name>");

/**
*
*/
class DevTools extends systemProcessing{

	function __construct(){

	}

	public function onLoad(){
		$this->addlog("Plugin loaded.");
	}

	public function onCommand(){
		global $baseCommand;
		switch ($baseCommand) {
			case 'makephar':
				$this->makephar();
				break;

			case 'mktmp':
				# code...
				break;

			case 'extractphar':
				# code...
				break;

			case 'vardump':
				$this->vardump();
				break;

			default:
				# code...
				break;
		}
	}

	private function makephar(){
		global $aryTipeTxt;
		global $version;
		global $buildnumber;
		global $raw_input;
		global $currentdirectory;
		global $poPath;
		global $plugindata;
		$myPhar = new \phppo\system\myPhar;
		$messageCount = count($aryTipeTxt);
		if ($messageCount <= 1) {
			$this->info("パラメーターが不足しています。");
			return false;
		}else{
			$aryTipeTxt[1] = trim($aryTipeTxt[1]);
			if ($aryTipeTxt[1] == "system") {
				$Confirm = $this->input("\x1b[38;5;203mAre you sure you want to compose the source of PHPPO that are currently running to the phar archive ?(y):");
				if ($Confirm == "y") {
					$fp = $poPath . "/src/buildlog.log";
					if (!is_file($fp)) {
						touch($fp);
						$this->info("\x1b[38;5;83mBuild log file created.\x1b[38;5;145m:" . $fp);
						$buildnumber = "1";
					}else {
						// $buildnumber = substr_count($file, PHP_EOL);
						$data = file_get_contents($fp);
						$data = explode( "\n", $data );
						$buildnumber = count( $data );
						// echo $buildnumber;
					}
					$fp = fopen($fp, "a");
					fwrite($fp, "[" . date("\'y.m.d h:i:s") . "] PHP Prompt OS " . $version . " built. No. #" . $buildnumber . PHP_EOL);
					fclose($fp);
					$this->info("\x1b[38;5;231m" .  "[" . date("\'y.m.d h:i:s") . "] PHP Prompt OS " . $version . " built. No. #" . $buildnumber);
					$this->info("\x1b[38;5;227mCreateing...");
					$pharpath = $poPath . "\PHPPO-{$version}_#{$buildnumber}.phar";
					$phar = new \Phar($pharpath, 0, 'PHPPO.phar');
					$phar->buildFromDirectory($poPath  . "\src");
					$pharstat = stat($pharpath);
					$this->info("\x1b[38;5;83mSuccess. \x1b[38;5;145m:" . $pharpath);
					$this->info("File size:" . $pharstat["size"] . "byte");
				}
			}else{
				$allpath = substr($raw_input,9);
				$allpath = rtrim($allpath,"\"");
				$allpath = ltrim($allpath,"\"");
				if (file_exists($allpath)) {
					$Confirm = $this->input("\x1b[38;5;203m指定されたパス\x1b[38;5;145m({$allpath})\x1b[38;5;203mからのPharアーカイブの作成を行いますか？(y):");
					if ($Confirm == "y"|| $Confirm == "Y") {
						// $this->info("");
						if (is_dir($allpath)) {
							$filename = basename($allpath);
							$this->info("\x1b[38;5;227mCreateing...");
							$pharpath = $currentdirectory . "\\{$filename}.phar";
							$phar = new \Phar($pharpath, 0, "{$filename}.phar");
							$phar->buildFromDirectory($allpath);
							$pharstat = stat($pharpath);
							$this->info("\x1b[38;5;83mSuccess. \x1b[38;5;145m:" . $pharpath);
							$this->info("File size:" . $pharstat["size"] . "byte");
						}else {
							$this->throwError("指定したパスはディレクトリではありません。");
							return false;
						}
					}
				}else{
					if (array_key_exists($aryTipeTxt[1],$plugindata)) {
						$pluginName = $aryTipeTxt[1];
						$allpath = $currentdirectory;
						$filename = $plugindata[$pluginName]["name"];
						// echo $aryTipeTxt[1] . PHP_EOL;//////////////////////////////////
						// echo $filename . PHP_EOL;//////////////////////////////////
						// echo $allpath . PHP_EOL;//////////////////////////////////
						$this->info("\x1b[38;5;227mCreateing...");
						$pharpath = $currentdirectory . "/{$filename}.phar";
						// echo $pharpath . PHP_EOL;//////////////////////////////////
						$phar = new \Phar($pharpath, 0, "{$filename}.phar");
						try {
							$phar->buildFromDirectory($allpath);
						} catch (Exception $e) {
							$this->info("作成に失敗しました。:{$e}","critical");
						}
						$pharstat = stat($pharpath);
						$this->info("\x1b[38;5;83mSuccess. \x1b[38;5;145m:" . $pharpath);
						$this->info("File size:" . $pharstat["size"] . "byte");
					}else{
						// echo $aryTipeTxt[1];//////////////////////////////////
						$this->throwError("指定されたパスにディレクトリやファイルは存在しないか、指定した名前のプラグインは存在しません。");
						return false;
					}
				}
				// $dir = '';
				// $dirCount = "";
				// for ($i=1; $i < $dirCount; $i++) {
				// 	$dir .= $aryTipeTxt[$i] . " ";
				// }
				// $myPhar->compose($dir,"");
			}
		}
	}

	private function vardump(){
		global $aryTipeTxt;
		$messageCount = count($aryTipeTxt);
		if ($messageCount <= 1) {
			$this->info("パラメーターが不足しています。");
			return false;
			$this->info("vardumpコマンドの使用法:");
			$this->info("vardump <変数名> / <指定したクラス内の変数名> <変数を表示するクラス>:システム処理が継承した'vardump'クラス内から呼び出せるパブリック変数、及びメイン処理におけるグローバル変数の内容を表示します。");
			$this->info("第二引数を指定していない場合はメイン処理におけるグローバル変数の内容を表示します。");
		}else{
			if ($messageCount <= 2) {
				$var_name = trim($aryTipeTxt[1]);
				global $$var_name;
				$this->info("メイン処理におけるグローバル化変数'" . $var_name . "'の内容を表示します。" . PHP_EOL);
				$this->info(var_export($$var_name,true));
			}else{
				$var_name = trim($aryTipeTxt[1]);
				$var_inclass = trim($aryTipeTxt[2]);
				if ($var_inclass == "this") {
					$this->info("thisクラスは指定不可能です。","error");
				}else{
					$this->info("'$var_inclass'クラス内のパブリック変数'$var_name'の内容を表示します。");
					$var_inclass = new $var_inclass;
					$this->info(var_export($var_inclass->$var_name),true);
				}
			}
		}
	}
}
