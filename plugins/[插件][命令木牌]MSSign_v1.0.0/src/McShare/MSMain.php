<?php

namespace McShare;


use pocketmine\plugin\Plugin;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\level\Level;


class MSMain extends PluginBase implements Listener
{


	public function onEnable()
	{

		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		
		$this->getLogger()->info("<McShare>命令木牌开始加载…");
		
		@mkdir($this->getDataFolder(),0777,true);
		//白名单
		$this->player = new Config($this->getDataFolder()."white-list.yml",Config::YAML,array(
		"#开启后只有列表内的玩家可以创建木牌，无视OP权限",
		"White-List"=>false,
		"List"=>[]));
		//放置权限与破坏权限同步
		
		//木牌
		$this->sign = new Config($this->getDataFolder()."Sign.yml",Config::YAML);
		//格式->"XYZLEVEL"=>"Command"
		
		$this->co = new Config($this->getDataFolder()."Config.yml",Config::YAML,array(
		"#木牌上显示的文字",
		"Prefix"=>"§7||§bCMD§d命令木牌§7||",
		
		"#是否在木牌上显示指令",
		"Command"=>false,
		
		
		));
		
		
		$this->getLogger()->info("<McShare>命令木牌加载完成");
		
		
		}
		
		
public function onCommand(CommandSender $pl, Command $command, $label, array $args)
    {
  if ($command->getName() == "mssign") {
  
  if (count($args) === 0) return false;
  
  if ($args[0] == "help") {
  
  $pl->sendMessage("§7########§aMcShare§7########");
  $pl->sendMessage("§e/mssign help  §7|查看帮助");
  $pl->sendMessage("§e/mssign whitelist §7|开启/关闭白名单功能");
  $pl->sendMessage("§e/mssign whitelist <ID>  §7|添加/删除白名单玩家");
  $pl->sendMessage("§c格式>>§1[第一行(mss)]§2[第二行(介绍)]§3[第三行(命令)]§5[第四行(第三行写不下写第四行)]");
  $pl->sendMessage("§7########§aMcShare§7########");
  return true;

  } else
  
    if ($args[0] == "whitelist") {
     if (!isset($args[1])) {
      if ($this->player->get("White-List") == true) {
      $this->player->set("White-List",false);
      $pl->sendMessage("§a关闭白名单功能成功");
      
      } else {
      
      $this->player->set("White-List",true);
      $pl->sendMessage("§a开启白名单功能成功");

      }
     
     } else {
     $na = $args[1];
     if (in_array($na,$this->player->get("List"))) {
     //删除
     $list = $this->player->get("List");
    $search = array_search($na,$list);
    $search = array_splice($list,$search,1);
    $this->player->set("List",$list);
    $pl->sendMessage("§a玩家§e $na §a已被移除");

     } else {
     $list = $this->player->get("List");
     $list[] = $args[1];
     $this->player->set("List",$list);
     $pl->sendMessage("§a玩家§e $args[1] §a已被添加");
     }
     
    }
     $this->player->save();
     return true;
    
   }
  }
 }


public function SignChange(SignChangeEvent $ev)
{

  $pl = $ev->getPlayer();
  $g1 = $ev->getLine(0);
  $na = $pl->getName();
  
  if ($g1 == "mss") {
   if ($this->player->get("White-List") == true) {
    if (!in_array($na,$this->player->get("List"))) {
    $pl->sendMessage("§c你没有权限创建命令木牌");
    return;
    }
   } else {
    if (!$pl->isOp()) {
      $pl->sendMessage("§c你没有权限创建命令木牌");
    return;
     }
    }
    
    $g2 = $ev->getLine(1);
    $g3 = $ev->getLine(2).$ev->getLine(3);
    $bl = $ev->getBlock();
    
    $ev->setLine(0,$this->co->get("Prefix"));
    $ev->setLine(1,"§7<<<<<<>>>>>>");
    $ev->setLine(2,"§e".$g2);
    if ($this->co->get("Command") == true) {
    $ev->setLine(3,"§d".$g3);
    } else {
    $ev->setLine(3,"§7<<<<<<>>>>>>");
    }
   $info = $bl->x.$bl->y.$bl->z.$bl->getLevel()->getFolderName();
   $this->sign->set($info,$g3);
   $this->sign->save();
   $pl->sendTip("§a成功创建命令木牌");
  
  }


}


public function BlockBreak(BlockBreakEvent $ev)
 {
 $pl = $ev->getPlayer();
 $bl = $ev->getBlock();
 $id = $bl->getId();
 $na = $pl->getName();
  
  if ($id == 323 or $id == 63 or $id == 68) {
  $info = $bl->x.$bl->y.$bl->z.$bl->getLevel()->getFolderName();
  if ($this->sign->exists($info)) {
   if ($this->player->get("White-List") == true) {
    if (!in_array($na,$this->player->get("List"))) {
    $pl->sendMessage("§4▎[§c✘§4]§c➢§4你没有破坏命令木牌的权限");
    $ev->setCancelled();
    return;
        } 
       } else {
   if (!$pl->isOp()) {
    $pl->sendMessage("§4▎[§c✘§4]§c➢§4你没有破坏命令木牌的权限");
    $ev->setCancelled();
    return;

     }
    }
    
    $this->sign->remove($info);
    $this->sign->save();
    $pl->sendMessage("§2▎[§a✔§2]➢§3移除成功");
   }
  }
 }
 
 
 
public function Interact(PlayerInteractEvent $ev)
 {
 $pl = $ev->getPlayer();
 $bl = $ev->getBlock();
 $id = $bl->getId();
 $na = $pl->getName();
  
  if ($id == 323 or $id == 63 or $id == 68) {
  $info = $bl->x.$bl->y.$bl->z.$bl->getLevel()->getFolderName();
  if ($this->sign->exists($info)) {
  
  $this->getServer()->dispatchCommand($pl,$this->sign->get($info));

 
    }
    
   }
   
  }
  
 }
 
?>