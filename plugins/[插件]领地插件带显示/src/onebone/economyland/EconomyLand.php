<?php

namespace onebone\economyland;

use onebone\economyland\event\LandAddedEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\scheduler\CallbackTask;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\event\EventPriority;
use pocketmine\plugin\MethodEventExecutor;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockUpdateEvent;

use onebone\economyapi\EconomyAPI;
use onebone\economyland\database\SQLiteDatabase;
use onebone\economyland\database\YamlDatabase;

class EconomyLand extends PluginBase implements Listener{
public $server=null;
	/**
	 * @var \onebone\economyland\database\Database;
	 */
	private $db;
	/**
	 * @var Config
	 */
	private $config, $lang;
	private $start, $end,$set;
	private $expire;
	private static $instance;
	
public function tip(){
	$players=$this->server->getOnlinePlayers();

	foreach($players as $p){
		
		$x=$p->x;
	$z=$p->z;
		$info = $this->db->getByCoord($x, $z, $p->getLevel()->getFolderName());
if($info!==false){
$ps=array_keys($info["invitee"]);
$str="§9";
foreach($ps as $pn){
	$str.=$pn." ";
}
	$p->sendTip("§4§l[§3领地 :§9#".$info["ID"]." §3属于:§6§l".$info["owner"]."§4 |".$str."§d§l]");
}
		
	}
	
						
}
	public function onEnable(){
$this->server=$this->getServer();
		if(!static::$instance instanceof EconomyLand){
			static::$instance = $this;
		}
		
		@mkdir($this->getDataFolder());
		if(!is_file($this->getDataFolder()."Expire.dat")){
			file_put_contents($this->getDataFolder()."Expire.dat", serialize(array()));
		}
		$this->expire = unserialize(file_get_contents($this->getDataFolder()."Expire.dat"));

		$this->createConfig();

		$now = time();
		foreach($this->expire as $landId => &$time){
			$time[1] = $now;
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "expireLand"), array($landId)), ($time[0] * 20));
		}

		//$this->land = new \SQLite3($this->getDataFolder()."Land.sqlite3");
		//$this->land->exec(stream_get_contents($this->getResource("sqlite3.sql")));
		switch(strtolower($this->config->get("database-type"))){
			case "yaml":
			case "yml":
				$this->db = new YamlDatabase($this->getDataFolder()."Land.yml", $this->config, $this->getDataFolder()."Land.sqlite3");
				break;
		/*	case "sqlite3":
			case "sqlite":
				$this->db = new SQLiteDatabase($this->getDataFolder()."Land.sqlite3", $this->config, $this->getDataFolder()."Land.yml");
				break;*/
			default:
				$this->db = new YamlDatabase($this->getDataFolder()."Land.yml", $this->config, $this->getDataFolder()."Land.sqlite3");
				$this->getLogger()->alert("Specified database type is unavailable. Database type is YAML.");
		}

		$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\block\\BlockPlaceEvent", $this, EventPriority::HIGHEST, new MethodEventExecutor("onPlaceEvent"), $this);
		$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\block\\BlockBreakEvent", $this, EventPriority::HIGHEST, new MethodEventExecutor("onBreakEvent"), $this);
	$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\entity\\EntityDamageEvent", $this, EventPriority::LOWEST, new MethodEventExecutor("onHurt"), $this);
		$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\player\\PlayerInteractEvent", $this, EventPriority::LOWEST, new MethodEventExecutor("onAct"), $this);
	//$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\player\PlayerMoveEvent", $this, EventPriority::HIGHEST, new MethodEventExecutor("onMove"), $this);
	
	$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"tip"]),20);
	}

	public function onHurt(EntityDamageEvent $ev){
				//if ($ev->isCancelled()) return;
				if(!($ev instanceof EntityDamageByEntityEvent)) return;


		if (!(($pl = $ev->getEntity()) instanceof Player
				&& ($p = $ev->getDamager()) instanceof Player)) return;

		$x=$pl->x;
		$z=$pl->z;
	$info = $this->db->getByCoord($x, $z, $pl->getLevel()->getFolderName());
				if($info !== false){
					
			       $p->sendMessage("§f[§2系统信息§f]§4§l你不能处于领地中的人~ §8[领地:#".$info["ID"].",属于:".$info["owner"]."]");
					$ev->setCancelled();
				}
				
	
		
		
		
	}
	public function expireLand($landId){
		if(!isset($this->expire[$landId])) return;
		$landId = (int)$landId;
		//$info = $this->land->query("SELECT * FROM land WHERE ID = $landId")->fetchArray(SQLITE3_ASSOC);
		//if(is_bool($info)) return;
		$info = $this->db->getLandById($landId);
		if($info === false) return;
		$player = $info["owner"];
		if(($player = $this->getServer()->getPlayerExact($player)) instanceof Player){
			$player->sendMessage("§e▎§f你的领地#${landId}已到期");
		}
		//$this->land->exec("DELETE FROM land WHERE ID = $landId");
		$this->db->removeLandById($landId);
		unset($this->expire[$landId]);
		$this->saveLands();
		return;
	}

	public function onDisable(){
		$this->saveLands();
	}
	public function saveLands(){
		$now = time();
		foreach($this->expire as $landId => $time){
			$this->expire[$landId][0] -= ($now - $time[1]);
		}
		file_put_contents($this->getDataFolder()."Expire.dat", serialize($this->expire));
		$this->db->close();
	}

	/**
	 * @return EconomyLand
	 */
	public static function getInstance(){
		return static::$instance;
	}
	
	public function onCommand(CommandSender $sender, Command $cmd, $label, array $param){
		//$this->saveLands();
		switch($cmd->getName()){
			case "setp":
			if(!$sender instanceof Player){
				$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
				return true;
			}
			if(isset($this->set[$sender->getName()]))
			{
				$this->set[$sender->getName()] = !$this->set[$sender->getName()];
			}
			else
			{
				$this->set[$sender->getName()]=true;
			}
			if($this->set[$sender->getName()])
			{
				$sender->sendMessage("§e▎§f已进入选择模式 ,放置方块选择第一个点 ,破坏方块选择第二个点");
			}
			else
			{
				$sender->sendMessage("§e▎§f已退出选择模式");
			}
			return true;
			case "startp":
			if(!$sender instanceof Player){
				$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
				return true;
			}
			$x = (int) $sender->x;
			$z = (int) $sender->z;
			$level = $sender->getLevel()->getFolderName();
			$this->start[$sender->getName()] = array("x" => $x, "z" => $z, "level" => $level);
			$sender->sendMessage($this->getMessage("first-position-saved"));
			return true;
			case "endp":
			if(!$sender instanceof Player){
				$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
				return true;
			}
			if(!isset($this->start[$sender->getName()])){
				$sender->sendMessage($this->getMessage("set-first-position"));
				return true;
			}
			if($sender->getLevel()->getFolderName() !== $this->start[$sender->getName()]["level"]){
				$sender->sendMessage($this->getMessage("cant-set-position-in-different-world"));
				return true;
			}
			
			$startX = $this->start[$sender->getName()]["x"];
			$startZ = $this->start[$sender->getName()]["z"];
			$endX = (int) $sender->x;
			$endZ = (int) $sender->z;
			$this->end[$sender->getName()] = array(
				"x" => $endX,
				"z" => $endZ
			);
			if($startX > $endX){
				$temp = $endX;
				$endX = $startX;
				$startX = $temp;
			}
			if($startZ > $endZ){
				$temp = $endZ;
				$endZ = $startZ;
				$startZ = $temp;
			}
			$startX--;
			$endX++;
			$startZ--;
			$endZ++;
			$price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("price-per-y-axis");
			$sender->sendMessage($this->getMessage("confirm-buy-land", array($price, "%2", "%3")));
			return true;
			case "land":
			case "l":
			$sub = array_shift($param);
			switch($sub){
				case "buy":
				if(!$sender->hasPermission("economyland.command.land.buy")){
					return true;
				}
				if(!$sender instanceof Player){
					$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
					return true;
				}
			//	$result = $this->land->query("SELECT * FROM land WHERE owner = '{$sender->getName()}'");
				$cnt = count($this->db->getLandsByOwner($sender->getName()));

				if(is_numeric($this->config->get("player-land-limit"))){
					if($cnt >= $this->config->get("player-land-limit")){
						$sender->sendMessage($this->getMessage("land-limit", array($cnt, $this->config->get("player-land-limit"))));
						return true;
					}
				/*	while($result->fetchArray(SQLITE3_ASSOC) !== false){
						++$cnt;
						if($cnt >= $this->config->get("player-land-limit")){
							$sender->sendMessage($this->getMessage("land-limit", array($cnt, $this->config->get("player-land-limit"))));
							return true;
						}
					}*/

				}
				if(!isset($this->start[$sender->getName()])){
					$sender->sendMessage($this->getMessage("set-first-position"));
					return true;
				}elseif(!isset($this->end[$sender->getName()])){
					$sender->sendMessage($this->getMessage("set-second-position"));
					return true;
				}
				$l = $this->start[$sender->getName()];
				$endp = $this->end[$sender->getName()];
				$startX = (int) $l["x"];
				$endX = (int) $endp["x"];
				$startZ = (int) $l["z"];
				$endZ = (int) $endp["z"];
				if($startX > $endX){
					$backup = $startX;
					$startX = $endX;
					$endX = $backup;
				}
				if($startZ > $endZ){
					$backup = $startZ;
					$startZ = $endZ;
					$endZ = $backup;
				}
				$startX--;
				$endX++;
				$startZ--;
				$endZ++;
				/*$result = $this->land->query("SELECT * FROM land WHERE startX <= $endX AND endX >= $endX AND startZ <= $endZ AND endZ >= $endZ AND level = '{$sender->getLevel()->getFolderName()}'")->fetchArray(SQLITE3_ASSOC);
				if(!is_bool($result)){
					$sender->sendMessage($this->getMessage("land-around-here", array($result["owner"], "", "")));
					return true;
				}*/
				$result = $this->db->checkOverlap($startX, $endX, $startZ, $endZ, $sender->getLevel()->getFolderName());
				if($result){
					$sender->sendMessage($this->getMessage("land-around-here", array($result["owner"], "", "")));
					return true;
				}
				$price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("price-per-y-axis");
				if(EconomyAPI::getInstance()->reduceMoney($sender, $price, true, "EconomyLand") === EconomyAPI::RET_INVALID){
					$sender->sendMessage($this->getMessage("no-money-to-buy-land"));
					return true;
				}
			//	$this->land->exec("INSERT INTO land (startX, endX, startZ, endZ, owner, level, price, invitee) VALUES ($startX, $endX, $startZ, $endZ, '{$sender->getName()}', '{$this->start[$sender->getName()]["level"]}', $price, ',')");
				$this->db->addLand($startX, $endX, $startZ, $endZ, $sender->getLevel()->getFolderName(), $price, $sender->getName());
				unset($this->start[$sender->getName()], $this->end[$sender->getName()]);
				$sender->sendMessage($this->getMessage("bought-land", array($price, "", "")));
				$this->saveLands();
				break;
				case "list":
				if(!$sender->hasPermission("economyland.command.land.list")){
					return true;
				}
				$page = isset($param[0]) ? (int) $param[0] : 1;
				
				$land = $this->db->getAll();
				$output = "";
				$max = ceil(count($land) / 5);
				$pro = 1;
				$page = (int)$page;
				$output .= $this->getMessage("land-list-top", array($page, $max, ""));
				$current = 1;
				foreach($land as $l){
					$cur = (int) ceil($current / 5);
					if($cur > $page) 
						continue;
					if($pro == 6) 
						break;
					if($page === $cur){
						$output .= $this->getMessage("land-list-format", array($l["ID"], ($l["endX"] - $l["startX"]) * ($l["endZ"] - $l["startZ"]), $l["owner"]));
						$pro++;
					}
					$current++;
				}
				$sender->sendMessage($output);
				break;
				case "whose":
				if(!$sender->hasPermission("economyland.command.land.whose")){
					return true;
				}
				$player = array_shift($param);
				$alike = true;
				if(str_replace(" ", "", $player) === ""){
					$player = $sender->getName();
					$alike = false;
				}
			///	$result = $this->land->query("SELECT * FROM land WHERE owner ".($alike ? "LIKE '%".$player."%'" : "= '".$player."'"));
				if($alike){
					$lands = $this->db->getLandsByKeyword($player);
				}else{
					$lands = $this->db->getLandsByOwner($player);
				}
				$sender->sendMessage("Results from query : $player\n");
			//	while(($info = $result->fetchArray(SQLITE3_ASSOC)) !== false){
				foreach($lands as $info)
					$sender->sendMessage($this->getMessage("land-list-format", array($info["ID"], ($info["endX"] - $info["startX"]) * ($info["endZ"] - $info["startZ"]), $info["owner"])));
				//}
				break;
				case "move":
				case "m":
				if(!$sender instanceof Player){
					$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
					return true;
				}
				/*
				if(!$sender->hasPermission("economyland.command.land.move")){
					return true;
				}*/
				$num = array_shift($param);
				if(trim($num) == ""){
					$sender->sendMessage("使用方法 : /land move <领地ID>");
					return true;
				}
				if(!is_numeric($num)){
					$sender->sendMessage("使用方法 : /land move <领地ID>");
					return true;
				}
				//$result = $this->land->query("SELECT * FROM land WHERE ID = $num");

			//	$info = $result->fetchArray(SQLITE3_ASSOC);
				$info = $this->db->getLandById($num);
				if($info === false){
					$sender->sendMessage($this->getMessage("no-land-found", array($num, "", "")));
					return true;
				}
				$level = $this->getServer()->getLevelByName($info["level"]);
				if(!$level instanceof Level){
					$sender->sendMessage($this->getMessage("land-corrupted", array($num, "", "")));
					return true;
				}
				$x = (int) $info["startX"] + (($info["endX"] - $info["startX"]) / 2);
				$z = (int) $info["startZ"] + (($info["endZ"] - $info["startZ"]) / 2);
				$cnt = 0;
				for($y = 1;; $y++){
					if($level->getBlock(new Vector3($x, $y, $z))->getID() === 0){
						break;
					}
					if($cnt === 5){
						break;
					}
					if($y > 255){
						++$cnt;
						++$x;
						--$z;
						$y = 1;
						continue;
					}
				}
				$sender->teleport(new Position($x, $y, $z, $level));
				$sender->sendMessage($this->getMessage("success-moving", array($num, "", "")));
				return true;
				case "give":
				if(!$sender instanceof Player){
					$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
					return true;
				}
				if(!$sender->hasPermission("economyland.command.land.give")){
					return true;
				}
				$player = array_shift($param);
				$landnum = array_shift($param);
				if(trim($player) == "" or trim($landnum) == "" or !is_numeric($landnum)){
					$sender->sendMessage("使用方法 : /$cmd give <玩家ID> <领地ID>");
					return true;
				}
				$username = $player;
				$player = $this->getServer()->getPlayer($username);
				if(!$player instanceof Player){
					$sender->sendMessage($this->getMessage("player-not-connected", [$username, "%2", "%3"]));
					return true;
				}
			//	$info = $this->land->query("SELECT * FROM land WHERE ID = $landnum")->fetchArray(SQLITE3_ASSOC);
				$info = $this->db->getLandById($landnum);
				if($info === false){
					$sender->sendMessage($this->getMessage("no-land-found", array($landnum, "", "")));
					return true;
				}
				if($sender->getName() !== $info["owner"] and !$sender->hasPermission("economyland.land.give.others")){
					$sender->sendMessage($this->getMessage("not-your-land", array($landnum, "", "")));
				}else{
					if($sender->getName() === $player->getName()){
						$sender->sendMessage($this->getMessage("cannot-give-land-myself"));
					}else{
					//	$this->land->exec("UPDATE land SET owner = '{$player->getName()}' WHERE ID = {$info["ID"]}");
						$this->db->setOwnerById($info["ID"], $player->getName());
						$sender->sendMessage($this->getMessage("gave-land", array($landnum, $player->getName())));
						$player->sendMessage($this->getMessage("got-land", array($sender->getName(), $landnum)));
					}
				}
				$this->saveLands();
				return true;
				case "save":
				$this->saveLands();
				$sender->sendMessage("§2▎§f数据保存完毕");
				return true;
				
				case "invite":
					$landnum = array_shift($param);
					$player = array_shift($param);
					if(trim($player) == "" or trim($landnum) == ""){
						$sender->sendMessage("使用方法 : /land <invite> [领地ID] [(r:)玩家ID]");
						return true;
					}
					if(!is_numeric($landnum)){
						$sender->sendMessage($this->getMessage("land-num-must-numeric", array($landnum, "", "")));
						return true;
					}
					//$result = $this->land->query("SELECT * FROM land WHERE ID = $landnum");
					//$info = $result->fetchArray(SQLITE3_ASSOC);
					$info = $this->db->getLandById($landnum);
					if($info === false){
						$sender->sendMessage($this->getMessage("no-land-found", array($landnum, "", "")));
						return true;
					}elseif($info["owner"] !== $sender->getName()){
						$sender->sendMessage($this->getMessage("not-your-land", array($landnum, "", "")));
						return true;
					}elseif(substr($player, 0, 2) === "r:"){
						$player = substr($player, 2);

						//$this->land->exec("UPDATE land SET invitee = '".str_replace($player.",", "", $info["invitee"])."' WHERE ID = {$info["ID"]};");
						$result = $this->db->removeInviteeById($landnum, $player);
						if($result === false){
							$sender->sendMessage($this->getMessage("not-invitee", array($player, $landnum, "")));
							return true;
						}
						$sender->sendMessage($this->getMessage("removed-invitee", array($player, $landnum, "")));
					}else{
						/*if(strpos($info["invitee"], ",".$player.",") !== false){
							$sender->sendMessage($this->getMessage("already-invitee", array($player, "", "")));
							return true;
						}
						$this->land->exec("UPDATE land SET invitee = '".$info["invitee"].$player.",' WHERE ID = {$info["ID"]};");*/
						$result = $this->db->addInviteeById($landnum, $player);
						if($result === false){
							$sender->sendMessage($this->getMessage("already-invitee", array($player, "", "")));
							return true;
						}
						$sender->sendMessage($this->getMessage("success-invite", array($player, $landnum, "")));
					}
					$this->saveLands();
					return true;
				case "invitee":
					$landnum = array_shift($param);
					if(trim($landnum) == "" or !is_numeric($landnum)){
						$sender->sendMessage("使用方法 : /land invitee <领地ID>");
						return true;
					}
					
					$info = $this->db->getInviteeById($landnum);
					if($info === false){
						$sender->sendMessage($this->getMessage("no-land-found", array($landnum, "", "")));
						return true;
					}
					$output = "Invitee of land #$landnum : \n";
					$output .= implode(", ", $info);
					$sender->sendMessage($output);
					$this->saveLands();
					return true;
				case "here":
				if(!$sender instanceof Player){
					$sender->sendMessage("§e▎§f请在游戏里使用这个指令");
					return true;
				}
				$x = $sender->x;
				$z = $sender->z;
				
				$info = $this->db->getByCoord($x, $z, $sender->getLevel()->getFolderName());
				if($info === false){
					$sender->sendMessage($this->getMessage("no-one-owned"));
					return true;
				}
				$sender->sendMessage($this->getMessage("here-land", array($info["ID"], $info["owner"], "%3")));
				return true;
				default:
				$sender->sendMessage("使用方法 : ".$cmd->getUsage());
			}
			return true;
			case "landsell":
			switch ($param[0]){
			case "here":
				if(!$sender instanceof Player){
					$sender->sendMessage("Please run this command in-game.");
					return true;
				}
				$x = $sender->x;
				$z = $sender->z;
				//$result = $this->land->query("SELECT * FROM land WHERE (startX < $x AND endX > $x) AND (startZ < $z AND endZ > $z) AND level = '{$sender->getLevel()->getFolderName()}'");
				//$info = $result->fetchArray(SQLITE3_ASSOC);
				$info = $this->db->getByCoord($x, $z, $sender->getLevel()->getFolderName());
				if($info === false){
					$sender->sendMessage($this->getMessage("no-one-owned"));
					return true;
				}
				if($info["owner"] !== $sender->getName()){
					$sender->sendMessage($this->getMessage("not-my-land"));
				}else{
					EconomyAPI::getInstance()->addMoney($sender, $info["price"] / 2);
					$sender->sendMessage($this->getMessage("sold-land", array(($info["price"] / 2), "", "")));
					//$this->land->exec("DELETE FROM land WHERE ID = {$info["ID"]}");
					$this->db->removeLandById($info["ID"]);
				}
				$this->saveLands();
				return true;
			default:
				$p = $param[0];
				if(is_numeric($p)){
					//$info = $this->land->query("SELECT * FROM land WHERE ID = $p")->fetchArray(SQLITE3_ASSOC);
					$info = $this->db->getLandById($p);
					if($info === false){
						$sender->sendMessage("Usage: /landsell <here|land number>");
						return true;
					}
					if($info["owner"] === $sender->getName() or $sender->hasPermission("economyland.landsell.others")){
						EconomyAPI::getInstance()->addMoney($sender, ($info["price"] / 2), true, "EconomyLand");
						$sender->sendMessage($this->getMessage("sold-land", array(($info["price"] / 2), "", "")));
						//$this->land->exec("DELETE FROM land WHERE ID = $p");
						$this->db->removeLandById($p);
					}else{
						$sender->sendMessage($this->getMessage("not-your-land", array($p, $info["owner"], "")));
					}
				}else{
					$sender->sendMessage($this->getMessage("no-land-found", array($p, "", "")));
				}
				$this->saveLands();
			}
			$this->saveLands();
			return true;
		}
		return false;
	}
	

	public function onPlaceEvent(BlockPlaceEvent $event){
		$this->permissionCheck($event);
		if($event->isCancelled()){return;};
		$sender=$event->getPlayer();
		if(isset($this->set[$sender->getName()]) && $this->set[$sender->getName()])
		{
			$x = (int) $sender->x;
			$z = (int) $sender->z;
			$level = $sender->getLevel()->getFolderName();
			$this->start[$sender->getName()] = array("x" => $x, "z" => $z, "level" => $level);
			$sender->sendMessage($this->getMessage("first-position-saved"));
			$event->setCancelled();
		}
	}
	
	public function onBreakEvent(BlockBreakEvent $event){
		$this->permissionCheck($event);
		if($event->isCancelled()){return;};
		$sender=$event->getPlayer();
		if(isset($this->set[$sender->getName()]) && $this->set[$sender->getName()])
		{
			if(!$sender instanceof Player){
				$sender->sendMessage("§e▎§f请在游戏内使用这个指令");
				return true;
			}
			if(!isset($this->start[$sender->getName()])){
				$sender->sendMessage($this->getMessage("set-first-position"));
				return true;
			}
			if($sender->getLevel()->getFolderName() !== $this->start[$sender->getName()]["level"]){
				$sender->sendMessage($this->getMessage("cant-set-position-in-different-world"));
				return true;
			}
			
			$startX = $this->start[$sender->getName()]["x"];
			$startZ = $this->start[$sender->getName()]["z"];
			$endX = (int) $sender->x;
			$endZ = (int) $sender->z;
			$this->end[$sender->getName()] = array(
				"x" => $endX,
				"z" => $endZ
			);
			if($startX > $endX){
				$temp = $endX;
				$endX = $startX;
				$startX = $temp;
			}
			if($startZ > $endZ){
				$temp = $endZ;
				$endZ = $startZ;
				$startZ = $temp;
			}
			$startX--;
			$endX++;
			$startZ--;
			$endZ++;
			$price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("price-per-y-axis");
			$sender->sendMessage($this->getMessage("confirm-buy-land", array($price, "%2", "%3")));
			$event->setCancelled();
		}
	}
	public function onAct(PlayerInteractEvent $e){

		
		$iteminhand=$e->getItem();
		$i=$iteminhand->getID();
		$d=$iteminhand->getDamage();
		$p=$e->getPlayer();
		if($i==259){
			$b=$e->getBlock();
		$x=$b->getX();$z=$b->getZ();
		$levname=$b->getLevel()->getFolderName();
		if($this->db->canTouch($x,$z,$levname,$p)!==true){
			$p->sendMessage("§4[§5领地保护§4]§7您没有在此领地中使用打火石的权限.");
			$e->setCancelled();
		}
		}else
if($i==325&&($d==8||$d==10)){
	$player=$p->getName();
		$b=$e->getBlock();
		$x=$b->getX();$z=$b->getZ();
		$levname=$b->getLevel()->getFolderName();
		
		if(!$this->db->canPutWater($x,$z,$levname,$player)){
			$p->sendMessage("§4[§5领地保护§4]§7附近10格范围内有禁止访问的领地，您可以向对方协商邀请你进入领地。此处禁止放置水源。");
			$e->setCancelled();
		}
		
}

	}

	
	public function permissionCheck(BlockEvent $event){
		/** @var $player Player */
		$player = $event->getPlayer();
		$block = $event->getBlock();
		
		$x = $block->getX();
		$z = $block->getZ();
		$level = $block->getLevel()->getFolderName();
		
		if(in_array($level, $this->config->get("non-check-worlds"))){
			return false;
		}
		
		$exist = false;
		//$result = $this->land->query("SELECT owner,invitee FROM land WHERE level = '$level' AND endX > $x AND endZ > $z AND startX < $x AND startZ < $z");
		//if(!is_array($info)) goto checkLand;
		$info = $this->db->canTouch($x, $z, $level, $player);
		if($info === -1){
			if($this->config->get("white-world-protection")){
				if(in_array($level, $this->config->get("white-world-protection")) and !$player->hasPermission("economyland.land.modify.whiteland")){
					$player->sendMessage($this->getMessage("not-owned"));
					$event->setCancelled(true);
					return false;
				}
			}
		}elseif($info !== true){
			$player->sendMessage($this->getMessage("no-permission", array($info["owner"], "", "")));
			$event->setCancelled(true);
			return false;
		}

	}

	public function addLand($player, $startX, $startZ, $endX, $endZ, $level, $expires = null){
		if($level instanceof Level){
			$level = $level->getFolderName();
		}
		if($player instanceof Player){
			$player = $player->getName();
		}
		if($startX > $endX){
			$tmp = $startX;
			$startX = $endX;
			$endX = $tmp;
		}
		if($startZ > $endZ){
			$tmp = $startZ;
			$startZ = $endZ;
			$endZ = $tmp;
		}
		$startX--;
		$endX++;
		$startZ--;
		$endZ++;
	//	$result = $this->land->query("SELECT * FROM land WHERE startX <= $endX AND endX >= $endX AND startZ <= $endZ AND endZ >= $endZ AND level = '$level'")->fetchArray(SQLITE3_ASSOC);
		$result = $this->db->checkOverlap($startX, $endX, $startZ, $endZ, $level);
		if($result){
			return false;
		}
		$price = (($endX - $startX) - 1) * (($endZ - $startZ) - 1) * $this->config->get("price-per-y-axis");
	//	$this->land->exec("INSERT INTO land (startX, endX, startZ, endZ, owner, level, price, invitee".($expires === null?"":", expires").") VALUES ($startX, $endX, $startZ, $endZ, '$player', '$level', $price, ','".($expires === null ? "":", $expires").")");
		$id = $this->db->addLand($startX, $endX, $startZ, $endZ, $level, $price, $player, $expires);
		if($expires !== null){
			//$info = $this->land->query("SELECT seq FROM sqlite_sequence")->fetchArray(SQLITE3_ASSOC);
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "expireLand"), [$id]), $expires * 1200);
			$this->expire[$id] = array(
				$expires * 60,
				time()
			);
		}
		return true;
	}
	
	public function getMessage($key, $value = array("%1", "%2", "%3")){
		if($this->lang->exists($key)){
			return str_replace(array("%1", "%2", "%3", "\\n"), array($value[0], $value[1], $value[2], "\n"), $this->lang->get($key));
		}
		return "无法找到ID为 \"$key\" 的消息";
	}
	
	private function createConfig(){
		$this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array(
			"handler-priority" => 10,
			"white-world-protection" => array(),
			"non-check-worlds" => array(),
			"player-land-limit" => "NaN",
			"price-per-y-axis" => 100,
			"database-type" => "yaml"
		));
		
		$this->lang = new Config($this->getDataFolder()."language.properties", Config::PROPERTIES, array(
			"sold-land" => "§2▎§f已将领地以%1节操的价格卖出",
			"not-my-land" => "§4▎§f你并没有领地",
			"no-one-owned" => "§e▎§f没人拥有这块地方",
			"not-your-land" => "§e▎§fID为%1的领地不是你的",
			"no-land-found" => "§4▎§f没有ID为%1的领地",
			"land-corrupted" => "§4▎§f领地编号%1错误",
			"fail-moving" => "§4▎§f移动到领地%1失败",
			"success-moving" => "§2▎§f已移动到领地%1",
			"land-list-top" => "领地列表 (%1/%2)\\n",
			"land-list-format" => "#%1 面积 :%2方块 主人 :%3\\n",
			"here-land" => "§e▎§f#%1是%2的领地",
			"land-num-must-numeric" => "§e▎§f§4▎§f领地ID必须是数字",
			"not-invitee" => "§4▎§f你还没有领地%1的使用权限",
			"already-invitee" => "§2▎§f%1已被授权使用你的领地",
			"removed-invitee" => "§2▎§f已取消领地%2里%1的使用权限",
			"success-invite" => "§2▎§f成功授权%1使用你的领地",
			"player-not-connected" => "§e▎§f玩家%1不在线",
			"cannot-give-land-myself" => "§4▎§f不能把自己的领地送给自己",
			"gave-land" => "§2▎§f成功把领地%1送给%2",
			"got-land" => "§e▎§f%1把领地%2送给了你",
			"land-limit" => "§9▎§f你有%1个领地 ,最多可以拥有%2个",
			"set-first-position" => "§9▎§f请先选择第一个点",
			"set-second-position" => "§9▎§f请先选择第二个点",
			"land-around-here" => "§4▎§f%1的领地在周围 ,不能覆盖",
			"no-money-to-buy-land" => "§4▎§f你没有足够的节操来圈地",
			"bought-land" => "§2▎§f成功花费%1节操来圈地",
			"first-position-saved" => "§2▎§f已设置第一个点",
			"second-position-saved" => "§2▎§f已设置第二个点",
			"cant-set-position-in-different-world" => "§4▎§f不能在不同的世界里设置两个点",
			"confirm-buy-land" => "§e▎§f领地价格 :%1节操 ,输入 /land buy 来创建领地",
			"no-permission" => "§e▎§f这是%1的领地 ,你没有权限使用这块领地",
			"not-owned" => "§e▎§f你必须先购买这块领地才能进行建筑"
		));
	}
}
