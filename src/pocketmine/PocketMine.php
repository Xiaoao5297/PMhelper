<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/
//XiaoaoServer完全遵守GPL3规则
namespace {
	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL;
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_integer($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_int($var):
					echo str_repeat("  ", $cnt) . "int(" . $var . ")" . PHP_EOL;
					break;
				case is_float($var):
					echo str_repeat("  ", $cnt) . "float(" . $var . ")" . PHP_EOL;
					break;
				case is_bool($var):
					echo str_repeat("  ", $cnt) . "bool(" . ($var === true ? "true" : "false") . ")" . PHP_EOL;
					break;
				case is_string($var):
					echo str_repeat("  ", $cnt) . "string(" . strlen($var) . ") \"$var\"" . PHP_EOL;
					break;
				case is_resource($var):
					echo str_repeat("  ", $cnt) . "resource() of type (" . get_resource_type($var) . ")" . PHP_EOL;
					break;
				case is_object($var):
					echo str_repeat("  ", $cnt) . "object(" . get_class($var) . ")" . PHP_EOL;
					break;
				case is_null($var):
					echo str_repeat("  ", $cnt) . "NULL" . PHP_EOL;
					break;
			}
		}
	}

	function dummy(){

	}
}

namespace pocketmine {
	use pocketmine\utils\Binary;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Utils;
	use pocketmine\wizard\Installer;

	const VERSION = "1.6dev";
	const API_VERSION = "2.1.0";
	const CODENAME = "XiaoaoServer 0.3.1!";
	const MINECRAFT_VERSION = "v0.14.x alpha";
	const MINECRAFT_VERSION_NETWORK = "0.14";
	const GENISYS_API_VERSION = '1.7.3';

	/*
	 * Startup code. Do not look at it, it may harm you.
	 * Most of them are hacks to fix date-related bugs, or basic functions used after this
	 * This is the only non-class based file on this project.
	 * Enjoy it as much as I did writing it. I don't want to do it again.
	 */

	if(\Phar::running(true) !== ""){
		@define('pocketmine\PATH', \Phar::running(true) . "/");
	}else{
		@define('pocketmine\PATH', \getcwd() . DIRECTORY_SEPARATOR);
	}

	if(version_compare("7.0", PHP_VERSION) > 0){
		echo "[提示] 请使用 php 7.0 ~ 7.4 ！" . PHP_EOL;
		echo "[提示] 请使用主页上的 php 安装器" . PHP_EOL;
		exit(1);
	}
    if(version_compare("7.4", PHP_VERSION) < 0){
		echo "[提示] 请使用 php 7.0 ~ 7.4 ！" . PHP_EOL;
		echo "[提示] 请使用主页上的 php 安装器" . PHP_EOL;
		exit(1);
	}
	if(!extension_loaded("pthreads")){
        echo "[提示] 找不到pthreads扩展。" . PHP_EOL;
		echo "[提示] 请使用主页的安装器" . PHP_EOL;
		exit(1);
	}

	if(!class_exists("ClassLoader", false)){
		require_once(\pocketmine\PATH . "src/spl/ClassLoader.php");
		require_once(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
		require_once(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
	}

	$autoloader = new CompatibleClassLoader();
	$autoloader->addPath(\pocketmine\PATH . "src");
	$autoloader->addPath(\pocketmine\PATH . "src" . DIRECTORY_SEPARATOR . "spl");
	$autoloader->register(true);


	set_time_limit(0); //Who set it to 30 seconds?!?!

	gc_enable();
	error_reporting(-1);
	ini_set("allow_url_fopen", 1);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	ini_set("default_charset", "utf-8");

	ini_set("memory_limit", -1);
	define('pocketmine\START_TIME', microtime(true));

	$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-profiler"]);

	define('pocketmine\DATA', isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR);
	define('pocketmine\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

	Terminal::init();

	define('pocketmine\ANSI', Terminal::hasFormattingCodes());

	if(!file_exists(\pocketmine\DATA)){
		mkdir(\pocketmine\DATA, 0777, true);
	}

	//Logger has a dependency on timezone, so we'll set it to UTC until we can get the actual timezone.
	date_default_timezone_set("UTC");

	$logger = new MainLogger(\pocketmine\DATA . "server.log", \pocketmine\ANSI);
    /*
        卡在这里的人可以在start.sh里改成
        "$PHP_BINARY" -c ./php.ini "$POCKETMINE_FILE" $@
        中间加上-c ./php.ini或者 -c <你的php.ini位置>即可修复
    */
	if(!ini_get("date.timezone")){
		if(($timezone = detect_system_timezone()) and date_default_timezone_set($timezone)){
			//Success! Timezone has already been set and validated in the if statement.
			//This here is just for redundancy just in case some program wants to read timezone data from the ini.
			ini_set("date.timezone", $timezone);
		}else{
			//If system timezone detection fails or timezone is an invalid value.
			if($response = Utils::getURL("http://ip-api.com/json")
				and $ip_geolocation_data = json_decode($response, true)
				and $ip_geolocation_data['status'] != 'fail'
				and date_default_timezone_set($ip_geolocation_data['timezone'])
			){
				//Again, for redundancy.
				ini_set("date.timezone", $ip_geolocation_data['timezone']);
			}else{
			    //实在不行改成Asia/Shanghai
				ini_set("date.timezone", "UTC");
				date_default_timezone_set("UTC");
				$logger->warning("无法自动确定时区。不正确的时区将导致控制台日志上的时间戳不正确。默认情况下，它被设置为 \"UTC\"。您可以在php.ini文件中更改它。");
			}
		}
	}else{
		/*
		 * This is here so that people don't come to us complaining and fill up the issue tracker when they put
		 * an incorrect timezone abbreviation in php.ini apparently.
		 */
		$timezone = ini_get("date.timezone");
		if(strpos($timezone, "/") === false){
			$default_timezone = timezone_name_from_abbr($timezone);
			ini_set("date.timezone", $default_timezone);
			date_default_timezone_set($default_timezone);
		} else {
			date_default_timezone_set($timezone);
		}
	}

	function detect_system_timezone(){
		switch(Utils::getOS()){
			case 'win':
				$regex = '/(UTC)(\+*\-*\d*\d*\:*\d*\d*)/';

				/*
				 * wmic timezone get Caption
				 * Get the timezone offset
				 *
				 * Sample Output var_dump
				 * array(3) {
				 *	  [0] =>
				 *	  string(7) "Caption"
				 *	  [1] =>
				 *	  string(20) "(UTC+09:30) Adelaide"
				 *	  [2] =>
				 *	  string(0) ""
				 *	}
				 */
				exec("wmic timezone get Caption", $output);

				$string = trim(implode("\n", $output));

				//Detect the Time Zone string
				preg_match($regex, $string, $matches);

				if(!isset($matches[2])){
					return false;
				}

				$offset = $matches[2];

				if($offset == ""){
					return "UTC";
				}

				return parse_offset($offset);
				break;
			case 'linux':
				// Ubuntu / Debian.
				if(file_exists('/etc/timezone')){
					$data = file_get_contents('/etc/timezone');
					if($data){
						return trim($data);
					}
				}

				// RHEL / CentOS
				if(file_exists('/etc/sysconfig/clock')){
					$data = parse_ini_file('/etc/sysconfig/clock');
					if(!empty($data['ZONE'])){
						return trim($data['ZONE']);
					}
				}

				//Portable method for incompatible linux distributions.

				$offset = trim(exec('date +%:z'));

				if($offset == "+00:00"){
					return "UTC";
				}

				return parse_offset($offset);
				break;
			case 'mac':
				if(is_link('/etc/localtime')){
					$filename = readlink('/etc/localtime');
					if(strpos($filename, '/usr/share/zoneinfo/') === 0){
						$timezone = substr($filename, 20);
						return trim($timezone);
					}
				}

				return false;
				break;
			default:
				return false;
				break;
		}
	}

	/**
	 * @param string $offset In the format of +09:00, +02:00, -04:00 etc.
	 *
	 * @return string
	 */
	function parse_offset($offset){
		//Make signed offsets unsigned for date_parse
		if(strpos($offset, '-') !== false){
			$negative_offset = true;
			$offset = str_replace('-', '', $offset);
		}else{
			if(strpos($offset, '+') !== false){
				$negative_offset = false;
				$offset = str_replace('+', '', $offset);
			}else{
				return false;
			}
		}

		$parsed = date_parse($offset);
		$offset = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

		//After date_parse is done, put the sign back
		if($negative_offset == true){
			$offset = -abs($offset);
		}

		//And then, look the offset up.
		//timezone_name_from_abbr is not used because it returns false on some(most) offsets because it's mapping function is weird.
		//That's been a bug in PHP since 2008!
		foreach(timezone_abbreviations_list() as $zones){
			foreach($zones as $timezone){
				if($timezone['offset'] == $offset){
					return $timezone['timezone_id'];
				}
			}
		}

		return false;
	}

	if(isset($opts["enable-profiler"])){
		if(function_exists("profiler_enable")){
			\profiler_enable();
			$logger->notice("正在分析执行情况");
		}else{
			$logger->notice("未找到探查器。请安装https://github.com/krakjoe/profiler");
		}
	}

	function kill($pid){
		switch(Utils::getOS()){
			case "win":
				exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
				break;
			case "mac":
			case "linux":
			default:
				if(function_exists("posix_kill")){
					posix_kill($pid, SIGKILL);
				}else{
					exec("kill -9 " . ((int)$pid) . " > /dev/null 2>&1");
				}
		}
	}

	/**
	 * @param object $value
	 * @param bool   $includeCurrent
	 *
	 * @return int
	 */
	function getReferenceCount($value, $includeCurrent = true){
		ob_start();
		debug_zval_dump($value);
		$ret = explode("\n", ob_get_contents());
		ob_end_clean();

		if(count($ret) >= 1 and preg_match('/^.* refcount\\(([0-9]+)\\)\\{$/', trim($ret[0]), $m) > 0){
			return ((int) $m[1]) - ($includeCurrent ? 3 : 4); //$value + zval call + extra call
		}
		return -1;
	}

	function getTrace($start = 1, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . (is_array($value) ? "Array()" : Utils::printable(@strval($value)))) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable(substr($params, 0, -2)) . ")";
		}

		return $messages;
	}

	function cleanPath($path){
		return rtrim(str_replace(["\\", ".php", "phar://", rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PATH), "/"), rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PLUGIN_PATH), "/")], ["/", "", "", "", ""], $path), "/");
	}

	$errors = 0;

	if(php_sapi_name() !== "cli"){
		$logger->critical("您必须使用CLI运行PocketMine-MP。");
		++$errors;
	}

	if(!extension_loaded("sockets")){
		$logger->critical("找不到套接字扩展。");
		++$errors;
	}

	$pthreads_version = phpversion("pthreads");
	if(substr_count($pthreads_version, ".") < 2){
		$pthreads_version = "0.$pthreads_version";
	}
	if(version_compare($pthreads_version, "3.1.5") < 0){
		$logger->critical("pthreads 的等级需要 >= 3.1.5，而您的版本是 $pthreads_version 。");
		++$errors;
	}

	if(!extension_loaded("uopz")){
		//$logger->notice("Couldn't find the uopz extension. Some functions may be limited");
	}

	if(extension_loaded("pocketmine")){
		if(version_compare(phpversion("pocketmine"), "0.0.1") < 0){
			$logger->critical("您拥有本机PocketMine扩展，但您的版本低于0.0.1。");
			++$errors;
		}elseif(version_compare(phpversion("pocketmine"), "0.0.4") > 0){
			$logger->critical("您拥有本机PocketMine扩展，但您的版本高于0.0.4。");
			++$errors;
		}
	}

	if(!extension_loaded("curl")){
		$logger->critical("找不到cURL扩展。");
		++$errors;
	}

	if(!extension_loaded("yaml")){
		$logger->critical("找不到YAML扩展。");
		++$errors;
	}

	if(!extension_loaded("sqlite3")){
		$logger->critical("找不到SQLite3扩展。");
		++$errors;
	}

	if(!extension_loaded("zlib")){
		$logger->critical("找不到Zlib扩展。");
		++$errors;
	}

	if($errors > 0){
		$logger->critical("请使用主页上提供的安装程序，或者重新编译PHP。");
		$logger->shutdown();
		$logger->join();
		exit(1); //Exit with error
	}

	if(file_exists(\pocketmine\PATH . ".git/refs/heads/master")){ //Found Git information!
		define('pocketmine\GIT_COMMIT', strtolower(trim(file_get_contents(\pocketmine\PATH . ".git/refs/heads/master"))));
	}else{ //Unknown :(
		define('pocketmine\GIT_COMMIT', str_repeat("00", 20));
	}

	@define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
	@define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);
	@ini_set("opcache.mmap_base", bin2hex(Utils::getRandomBytes(8, false))); //Fix OPCache address errors

	$lang = "unknown";
	if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
		$inst = new Installer();
		$lang = $inst->getDefaultLang();
	}

	/*if(\Phar::running(true) === ""){
		$logger->warning("Non-packaged PocketMine-MP installation detected, do not use on production.");
	}*/

	ThreadManager::init();
	$server = new Server($autoloader, $logger, \pocketmine\PATH, \pocketmine\DATA, \pocketmine\PLUGIN_PATH, $lang);

	$logger->info("[提示]正在停止其他进程");

	foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
		$logger->debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
		$thread->quit();
	}

	$killer = new ServerKiller(8);
	$killer->start();

	$logger->shutdown();
	$logger->join();

	echo "[提示]服务器已关闭" . Terminal::$FORMAT_RESET . "\n";

	exit(0);

}

