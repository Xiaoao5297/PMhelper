<?php
namespace Asign;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\tile\Tile;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat as TM;
use pocketmine\math\Vector3;
use pocketmine\command\ConsoleCommandSender;

class Asign extends PluginBase implements Listener{

    public function onEnable(){
   $this->getServer()->getPluginManager()->registerEvents($this,$this);
   $this->getLogger()->info(TM::GREEN."Hello!It's Sgien.Vebot"); 	
    }
   public function onDisable(){
     $this->getLogger()->info(TM::RED."|---Bye,bye---|");
   }
	
    public function playerBlockTouch(PlayerInteractEvent $event){
        if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
            $sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
            if(!($sign instanceof Sign)){
                return;
            }
            $sign = $sign->getText();
			$player = $event->getPlayer();
		 	   $s1 = $sign[1];
					$s2 = $sign[2];
					$s3 = $sign[3];
					$test = $s2.$s3;				
            switch($sign[0]){
				case TM::RED."§b[ 命令木牌 ] 管理型":
					if(!$player->isOp()){
			$this->opcommand($player, $test);
								}else{
			$this->command($player, $test);
								}
					return;
					break;
				case TM::GREEN."§b[ 命令木牌 ] 普通型":
			$this->command($player, $test);	
					return;
					break;
				case TM::WHITE."§b[ 世界传送 ]":
	 					if ($this->getServer()->isLevelLoaded($s1)) { 
	 					$player->sendMessage(TM::WHITE."§a[系统]你来到了§6$sign[1].");			
      						$player->teleport(Server::getInstance()->getLevelByName($s1)->getSafeSpawn());
            			}else{
     						$sender->sendMessage("§a[系统] 世界 ".$s1." 不存在.");
          				} 			
					return;
					break;
			    case TM::GRAY."§b[ 坐标传送 ]":
			$player->teleport(new Vector3($sign[1],$sign[2],$sign[3]));
				$p->sendMessage(TM::DARK_RED."§a[系统]成功抵达: \n $sign[1] \n $sign[2] \n $sign[3].");			
					return;
					break;
				case TM::GOLD."§b[ 命令木牌 ] 后台型":
			$this->ConCommand($player, $test);
					return;
					break;
                }
            }
        }
	public function OnBreak(BlockBreakEvent $event){
		$b = $event->getBlock();
		$p = $event->getPlayer();
        if($b->getID() == 323 || $b->getID() == 63 || $b->getID() == 68){
            $sign = $p->getLevel()->getTile($b);
            if(!($sign instanceof Sign)){
                return;
            }
            $sign = $sign->getText();	
			if(!$p->isOp()){
            switch($sign[0]){
				case TM::RED."§b[ 命令木牌 ] 管理型":
				$p->sendMessage(TM::DARK_RED."§a[系统]别乱动");				
				$event->setCancelled(true);
					return;
					break;
				case TM::GOLD."§b[ 命令木牌 ] 后台型":
				$p->sendMessage(TM::DARK_RED."§a[系统]别乱动");				
				$event->setCancelled(true);
					return;
					break;
                }			
		}}
	}
	public function onWrite(SignChangeEvent $event){
		$line=$event->getLines();
		switch($line[0]){
		case "op":
			if(!$event->getPlayer()->isOp()){
				$event->getPlayer()->sendMessage(TM::RED."§a[系统]你不是管理员不能执行此操作");
				$event->setCancelled(true);
			}else{
				if(!$line[2] == null){
				$event->getPlayer()->sendMessage(TM::AQUA."[系统]你成功建立了一个OP指令牌");
	        	$event->setLine(0,TM::RED."§b[ 命令木牌 ] 管理型");					
			}else{
				$event->getPlayer()->sendMessage(TM::RED."§b[系统]请填写指令");
				$event->setCancelled(true);
			  }
			}		
		return;
		break;
		case "cmd":
		if(!$line[2] == null){
		  $event->getPlayer()->sendMessage(TM::AQUA."[系统]你成功建立了一个cmd指令牌");
	        	$event->setLine(0,TM::GREEN."§b[ 命令木牌 ] 普通型");				  
		}else{
				$event->getPlayer()->sendMessage(TM::RED."§b[系统]请填写指令");
				$event->setCancelled(true);			
		}
		return;
		break;
		case "con":
			if(!$event->getPlayer()->isOp()){
				$event->getPlayer()->sendMessage(TM::RED."[系统]你不是管理员不能执行此操作");
				$event->setCancelled(true);
			}else{
				if(!$line[2] == null){
				$event->getPlayer()->sendMessage(TM::AQUA."[系统]你成功建立了一个Console指令牌");
	        	$event->setLine(0,TM::GOLD."§b[ 命令木牌 ] 后台型");								
			}else{
				$event->getPlayer()->sendMessage(TM::RED."[系统]请填写指令");
				$event->setCancelled(true);
			  }
			}	
		return;
		break;
		case "tp":
		if(!$line[1] == null and !$line[2] == null and !$line[3] == null){
			if(is_numeric($line[1]) or is_numeric($line[2]) or is_numeric($line[3])){
				$event->getPlayer()->sendMessage(TM::AQUA."[系统]你建立了一个TP坐标牌");
			    $event->setLine(0,TM::GRAY."§b[ 坐标传送 ]");					
			}else{
			$event->getPlayer()->sendMessage(TM::RED."[系统]坐标必须为数字!!");
				$event->setCancelled(true);					
		}}else{
				$event->getPlayer()->sendMessage(TM::RED."[系统]请填写完整的坐标");
				$event->setCancelled(true);			
		}
		return;
		break;
		case "w":
		if(!$line[1] == null){
		  $event->getPlayer()->sendMessage(TM::AQUA."[系统]你成功建立了一个世界传送牌");
		        $event->setLine(0,TM::WHITE."§b[ 世界传送 ]");	  		   
	      $event->setLine(1,"$line[1]");		  
		}else{
				$event->getPlayer()->sendMessage(TM::RED."[系统]请在第2行填写世界名称");
				$event->setCancelled(true);			
		}		
		return;
		break;		
		}
	}
    
	public function opcommand($player,$command){
		$pn = $player->getName();
		$tp = "@p";
		$p = array('@p'=>"$pn");		
		$this->getServer()->addOp($pn);
		if(strstr($command,$tp)){
			$command = strtr("$command",$p);
		}
		$this->getServer()->dispatchCommand($player, $command);
		$this->getServer()->removeOp($pn);
	}
	public function command($player,$command){
		$pn = $player->getName();
		$tp = "@p";
		$p = array('@p'=>"$pn");			
		if(strstr($command,$tp)){
			$command = strtr("$command",$p);
		}		
		$this->getServer()->dispatchCommand($player, $command);
	}
	public function ConCommand($player, $command){
		if($player->isOp()){
		$pn = $player->getName();
		$tp = "@p";
		$p = array('@p'=>"$pn");			
		if(strstr($command,$tp)){
			$command = strtr("$command",$p);
		}					
		$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $command);		
		}else{
			$player->sendMessage(TM::RED."[Asign]Not Op not Use.");
		}
	}
}
?>
