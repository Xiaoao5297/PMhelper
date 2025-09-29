<?php
namespace Protection;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\CallbackTask;

use pocketmine\entity\Effect;

use Protection\Message;

class Protection extends PluginBase implements Listener{
	
	private $login,$newplayer,$qu,$playerslogin1,$playerslogin2;
	private $move,$x,$y,$z,$timeout=300;
	private $pper=array();
	
	public function onLoad(){
		$this->path = $this->getDataFolder();
		@mkdir($this->path);
		@mkdir($this->path."/Players");
		$this->newplayer=$this->path."/Players/";
	}
	public function onEnable(){ 
	    new Message($this->path);
		$this->sban = new Config($this->path."Sban-list.yml", Config::YAML);
		$this->qu = new Config($this->path."Question.yml", Config::YAML);
	    $this->keys = new Config($this->path."keys.yml", Config::YAML,array("switch"=>"off"));
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$url = "http://plugins.mcpe.cn/UpPlugins/upprotection.php";
		$this->getLogger()->info(TextFormat::BLUE."插件加载成功");
	
	}
	public function onJoin(PlayerJoinEvent $event){
	    $user = strtolower($event->getPlayer()->getName());
		$id = $event->getPlayer()->getName();
		$tf=$this->keys->get("switch");
		$ip=$event->getPlayer()->getAddress();
		date_default_timezone_set('Asia/Chongqing'); //系统时间差8小时问题
		if(!file_exists($this->newplayer."$user.yml")){
		$p = new Config($this->newplayer."$user.yml", Config::YAML, array(
			"username"=>$user,
			"address"=>null,
			"last-day"=>null,
			"last-hour"=>null,
			"password"=>null,
			"links"=>4
		));
		$p->save();				
	    $this->getServer()->getLogger()->info(TextFormat::YELLOW."$id ".TextFormat::BLUE."第一次加入服务器建立数据成功");	
		}
		$pp = new Config($this->newplayer."$user.yml", Config::YAML);
		$sip=$pp->get("address");
		$sday=$pp->get("last-day");
		$shour=$pp->get("last-hour");
		$ttt=$pp->get("links");
        $this->pper[$user] = "off";
		$this->playerslogin1[$user] = "true";
        $this->playerslogin2[$user] = "false";
		$day=date("d");
		$hour=date("H");
		if($ttt == "0"){
			if($sip == $ip and $day == $sday and ($hour < ($shour+2))){
				$event->getPlayer()->sendMessage(TextFormat::GOLD."☆自动登入成功");
				$this->playerslogin1[$user]="false";
				$this->getServer()->getLogger()->info(TextFormat::BLUE."▎$id 登入游戏成功");
				$event->getPlayer()->sendMessage(TextFormat::GOLD."▎欢迎回来，同志");
				$this->pper[$user]="on";
				// 解除失明缓慢
				$this->removeLoginEffects($event->getPlayer());
				$this->sendBottomTip($event->getPlayer(), "");
			}else{
				$pp->set("links",1);
				$this->addLoginEffects($event->getPlayer());
				$this->sendBottomTip($event->getPlayer(), "你需要登录，请在聊天框里登录\n同ip两小时内免登录");
			}
		}elseif($ttt > 1){
			$pp->set("links",4);
			if($tf == "on" and $ttt == "4"){
				$pp->set("links",5);}
			$this->getServer()->getLogger()->info(TextFormat::RED."[注册账号]$id 未注册账号");
			$this->addLoginEffects($event->getPlayer());
			$this->sendBottomTip($event->getPlayer(), "你需要登录，请在聊天框里登录\n同ip两小时内免登录");
		}
		$pp->save();
		$this->trust($event,$pp);
        $this->move[$user] = 0;
		$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"timeout"],[$event]), $this->timeout*20);
	}	

	public function onCmdandChat(PlayerCommandPreprocessEvent $event){
		$player = $event->getPlayer();
	    $user = strtolower($player->getName());
		$id = $player->getName();
		$m = $event->getMessage();
		$ip = $player->getAddress();
		date_default_timezone_set('Asia/Chongqing'); //系统时间差8小时问题
		$day=date("d");
		$hour=date("H");
		$pp =new Config($this->newplayer."$user.yml", CONFIG::YAML);
		$this->keys = new Config($this->path."keys.yml", Config::YAML);
		$ttt=$pp->get("links");
	    if($ttt !== 0){$event->setCancelled(true);}
		if($ttt==5){
			$keys = $this->keys->getall();
			if(isset($keys[$m])){
			$pp->set("username","$user");
			if($keys[$m] == 1){
			unset($keys[$m]);
			}else{
			$keys[$m]--;}
			$this->keys->setall($keys);
			$pp->set("links",$ttt-1);
			$pp->save();
		    $player->sendMessage(TextFormat::BLUE."Ψ恭喜你激活成功~！");
			$this->keys->save();
            $this->trust($event,$pp);
	        $this->getServer()->getLogger()->info(TextFormat::RED."▎$id 使用激活码$m 激活成功!");
			}else{
			$player->sendMessage(TextFormat::RED."▎对不起，此为无效激活码！");}
			}
        if($ttt==4){
		if($m==$id){
			$pp->set("links",$ttt-1);
			$pp->save();
			$player->sendMessage(TextFormat::BLUE."▎验证成功！我们开始注册吧 !");
            $this->trust($event,$pp);
			}else{
			$player->sendMessage(TextFormat::BLUE."▎请输入你的游戏ID >".TextFormat::RED." $id ");
			$player->sendMessage(TextFormat::BLUE."▎注意你的ID大小写 >".TextFormat::RED." $id");
		}}
		if($ttt==3){
		    $m=trim($m);
			$pp->set("password",$m);
			$pp->set("links",$ttt-1);
			$pp->save();
			$player->sendMessage(TextFormat::GOLD."☆☆你设置的密码为".TextFormat::BLUE." $m");
            $this->trust($event,$pp);
			}
        if($ttt==2){
			$re=$pp->get("password");
			if($m==$re){
			$time = date("y-m-d-H-i");
		    $pp->set("links",0);
			$pp->set("register-time","$time");
			$pp->save();
			$this->playerslogin1[$user]="false";
			$this->playerslogin2[$user]="true";
            $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"login2"],[$user]), 10*20);
	        $player->sendMessage(TextFormat::BLUE."▎恭喜注册成功,你可以开始游戏咯！");
			$this->pper[$user]="on";
            $this->trust($event,$pp);	
	        $this->removeLoginEffects($player);
			$this->sendBottomTip($player, "");
	        return $this->getServer()->getLogger()->info(TextFormat::RED."[玩家注册]$id 成功完成注册!");
			}elseif($m=="remove"){
			$pp->set("links",$ttt+1);
			$pp->save();
			$this->trust($event,$pp);
			}else{
			$player->sendMessage(TextFormat::RED."▎你输入的密码于上次不符");
            $this->trust($event,$pp);
			}
		}
        $reg = $pp->get("password");
        if($ttt==1){
			if(!$m == NULL){
			if($m == $reg){
			$pp->set("links",$ttt-1);
			$pp->set("last-day",$day);
			$pp->set("last-hour",$hour);
			$pp->set("address",$ip);
			$pp->save();
			$this->playerslogin1[$user]="false";
			$this->playerslogin2[$user]="true";
            $this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this,"login2"],[$user]), 10*20);
	        $this->getServer()->getLogger()->info(TextFormat::YELLOW."▎$id 登入游戏成功");	
			$this->pper[$user] = "on";
	        $player->sendMessage(TextFormat::GOLD."▎欢迎回来，同志");
	        $this->removeLoginEffects($player);
	        $this->sendBottomTip($player, "");
            }else{ 
			$player->sendMessage("▎对不起密码错误！");
			}}}
        if($this->playerslogin2[$user]=="true"){
        if($m == $reg){
        $player->sendMessage("§e▎".TextFormat::YELLOW."你差点透露密码哦！");
	    $event->setCancelled(true);
		}}
	}
	// 增加失明和缓慢效果
	private function addLoginEffects(Player $player){
		$blindness = Effect::getEffect(15); // 失明
		$slowness = Effect::getEffect(2); // 缓慢
		if($blindness !== null){
			$player->addEffect(new EffectInstance($blindness, 999999, 1, false));
		}
		if($slowness !== null){
			$player->addEffect(new EffectInstance($slowness, 999999, 3, false));
		}
	}
	// 移除失明和缓慢效果
	private function removeLoginEffects(Player $player){
		$player->removeEffect(15); // 失明
		$player->removeEffect(2);  // 缓慢
	}
	// 发送底部提示
	private function sendBottomTip(Player $player, $message){
		if(method_exists($player, "sendTip")){
			$player->sendTip($message);
		}
	}
	public function trust($event,$pp){
		$player=$event->getPlayer();
		$t=$pp->get("links");
		if($t > 0){
		$player->sendMessage($this->qu->get($t)["a"]);
		$player->sendMessage($this->qu->get($t)["b"]);
		$player->sendMessage($this->qu->get($t)["c"]);
		}
	}		
	public function timeout($event){
	$player=$event->getPlayer();
	$user=strtolower($player->getName());
    if($this->playerslogin1[$user] == "true"){
    if($player instanceof player){
		$player->kick("登入超时");
	}
	unset($this->playerslogin1[$user]);
	}}
	public function login2($user){
	$this->playerslogin2[$user] = "false";
	}	
	public function onPlayerPreLogin(PlayerPreLoginEvent $event){
		$player = $event->getPlayer();
		$user = strtolower($player->getName());
		$ip = $player->getAddress();
		$sban = $this->getSban($user,$ip);
		if($sban === true){
			$event->setCancelled(true);
			$player->kick("被Sban禁止加入");
			return;
		}
		if(isset($this->pper[$user])){
		if($this->pper[$user] == "off" ){
		    return;}
		foreach($this->getServer()->getOnlinePlayers() as $p){
			if($p !== $player and $user === strtolower($p->getName())){
				if($this->pper[$user] == "on"){
					$event->setCancelled(true);
					$player->kick("禁止重复登入");
					return;
				} 
			}
		}}
	}
	public function onPlayerInteract(PlayerInteractEvent $event){
	    $this->permission($event);
	}		
	public function onBlockBreak(BlockBreakEvent $event){
		$this->permission($event);
	}	
	public function onEntityDamage(EntityDamageEvent $event){
		if($event->getEntity() instanceof Player){
			$user  = strtolower($event->getEntity()->getName());
			if(isset($this->pper[$user])===false){$this->pper[$user]="off";}
		    if($this->pper[$user] == "off" ){	
			$event->setCancelled(true);}
		}
	}
	public function onBlockPlace(BlockPlaceEvent $event){
		$this->permission($event);
	}
	public function onPlayerDrop(PlayerDropItemEvent $event){
		$this->permission($event);
	}
	public function onInventoryOpen(InventoryOpenEvent $event){
		//$this->permission($event);
	}
	public function onPlayerItemConsume(PlayerItemConsumeEvent $event){
		$this->permission($event);
	}
	public function onPlayerMove(PlayerMoveEvent $event){
	    $player = $event->getPlayer();
	    $user = strtolower($player->getName());
		if(isset($this->pper[$user])===false){
			$this->pper[$user]="off";}
		if($this->pper[$user] == "off" ){
		$this->move[$user]++;
		if($this->move[$user] >= 2){
		$event->setCancelled(true);
		$event->getPlayer()->onGround = true;
		return false;
		}
		// 发送底部登录提示
		$this->sendBottomTip($player, "你需要登录，请在聊天框里登录\n同ip两小时内免登录");
		}
	}
	public function onPickupItem(InventoryPickupItemEvent $event){
		$player = $event->getInventory()->getHolder();
		$user = strtolower($player->getName());
		if(!isset($this->pper[$user])){$this->pper[$user]=="off";}
		if($this->pper[$user] == "off" ){$event->setCancelled(true);}
	}
	public function onPlayerQuit(PlayerQuitEvent $event){
	    $player = $event->getPlayer();
	    $user = strtolower($player->getName());
		unset($this->pper[$user]);
		unset($this->playerslogin2[$user]);
	}
	public function permission($event){
		$player = $event->getPlayer();
	    $user = strtolower($player->getName());
		if(isset($this->pper[$user]) === false){
			$this->pper[$user]="off";}
		if($this->pper[$user] == "off" ){$event->setCancelled(true);}
	}
	public function getSban($name,$ip){
		$banlist = $this->sban->getall();
		$name = strtolower($name);
		foreach($banlist as $bname=>$bip){
			$bname = strtolower($bname);
			if($name == $bname){
				$this->getServer()->getLogger()->info("[Sban] ".TextFormat::RED."$name 在黑名单列表，禁止加入");
				return true;
			}
			if($ip == $bip){
				$this->getServer()->getLogger()->info("[Sban] ".TextFormat::RED."$name 无法加入游戏,因：".TextFormat::YELLOW."于玩家$bname 同一IP,禁止加入");
				return true;
			}
		}
		return false;
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		$user = strtolower($sender->getName());
		switch($command->getName()){
			case "sban":
			    if(isset($args[0])){
				$s = $args[0];
				if($s == "list" or $s == "l"){
					$banlist = $this->sban->getall();
					$n = 1;
					$out = "-------被封禁的列表-------\n";
					foreach($banlist as $name=>$ip){
						$out .=TextFormat::RED."NO.$n ".TextFormat::BLUE.$name.TextFormat::WHITE." => ".TextFormat::YELLOW."$ip\n";
					}
					$sender->sendMessage($out);
				return true;}
				if(isset($args[1])){
				$user=$args[1];
				if($s == "add" or $s == "a"){
					if(file_exists($this->newplayer."$user.yml")){
						$pp = new Config($this->newplayer."$user.yml", CONFIG::YAML);
						$ip = $pp->get("address");
						if($ip == null){$ip = "未登入";}
						$p = $this->getServer()->getPlayer($user);
						if($p instanceof Player){
						$ip = $p->getAddress();
						$p->kick("被Sban踢出游戏");}
						$this->sban->set($user,$ip);
						$this->sban->save();
						$sender->sendMessage("[Sban] ".TextFormat::BLUE."成功封禁玩家: ".TextFormat::RED.$user.TextFormat::BLUE." 游戏IP: ".TextFormat::YELLOW.$ip);
				        return true;
					}
					$sender->sendMessage("[Sban] ".TextFormat::BLUE."$user 未加入过服务器");
				    return true;}
				elseif($s == "remove" or $s == "re"){
					$ban = $this->sban->get($user);
					if($ban != null){
						$banlist = $this->sban->getall();
						unset($banlist[$user]);
						$this->sban->setall($banlist);
						$this->sban->save();
						$sender->sendMessage("[Sban] ".TextFormat::BLUE."成功解除对玩家: ".TextFormat::RED.$user.TextFormat::BLUE." 的封禁");
				        return true;
					}
					$sender->sendMessage("[Sban] ".TextFormat::BLUE."$user 不存在封禁列表");
				    return true;}
				return false;
				}}
				return false;
			case "unregister":
			    if(isset($args[1])){
				$y=$args[0];
				$n=$args[1];
				$pp =new Config($this->newplayer."$user.yml", CONFIG::YAML);
				$reg=$pp->get("password");
				if($y==$reg){
				$pp->set("password",$n);
				$pp->save();
				$msg="▎恭喜你成功修改密码，新密码为 $n";
			    }else{
				$msg="▎对不起原密码输入错误！";}
				}elseif($sender instanceof Player){
				return false;
				}else{
				$msg="▎请在游戏中使用 ！";
				}
				$sender->sendMessage($msg);
				return true;
			case "keys":
			    if(isset($args[0])){
				$aa = $args[0];
				$this->keys = new Config($this->path."keys.yml", Config::YAML);
				switch ($aa){
					case "off":
					$this->keys->set("switch","off");
					$this->keys->save();
					$sender->sendMessage("▎激活码功能关闭");
					return true;
					case "on":
					$this->keys->set("switch","on");
					$this->keys->save();
					$sender->sendMessage("[提示] 激活码功能启用");
					return true;
				}
				date_default_timezone_set('Asia/Chongqing'); //系统时间差8小时问题
				$m=date("m");
				$day=date("d");
				$k=0;
				$out=$m.$day;
				while($k < 4){
				$a=chr(rand(97,122));
				$b=mt_rand(0,9);
				$c=mt_rand(1,2);
				if($c==1){$aa=$b;}else{$aa=$a;}
				$out .=$aa;
				$k=$k+1;}
				$keyc=mt_rand(1,2);
				$keys=$out;
				$this->keys->set("$keys",$keyc);
				$this->keys->save();
				$sender->sendMessage("[提示] 生成新激活码$keys 有效次数 $keyc");
				return true;
				}
		}
	}
}