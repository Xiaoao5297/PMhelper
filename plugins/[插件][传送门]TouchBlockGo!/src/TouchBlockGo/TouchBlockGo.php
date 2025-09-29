<?php

/*
  __   __                                        ______              __
  \ \  \ \                                      / _____\            / /
   \ \__\ \  __    __  _____   _____    ____   / / ____    _____   / /         
    \  ___ \ \ \  / / / ___ \ / ___ \  / ___\ / / /___ \  / ___ \ /_/
     \ \  \ \ \ \/ / / /__/ // _____/ / /     \ \____/ / / /__/ / __
      \_\  \_\ \  / / _____/ \______//_/       \______/  \_____/ /_/
              _/ / / /
             /__/ /_/

                      HyperGo!|Copyright © 保留所有权利
                           Powered By HyperGo!
                            author HyperLife
*/

namespace TouchBlockGo;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;

class TouchBlockGo extends PluginBase implements Listener{

 public function onEnable(){
  $this->getServer()->getPluginManager()->registerEvents($this,$this);
  //插件启动提示
  $this->getServer()->getLogger()->warning("§fTouchBlockGo§7已成功运行在PHP版本为: §f".(PHP_VERSION)."§7的§f".(PHP_OS)."§7系统上.");
  
  @mkdir($this->getDataFolder(),0777,true);
  @mkdir($this->getDataFolder()."InternalData/",0777,true);
  
  $this->TBD=new Config($this->getDataFolder()."TouchBlockData.yml",Config::YAML,array("提示"=>"命令中请使用 名称 代替玩家名称"));
  $this->ID=new Config($this->getDataFolder()."/InternalData/InternalData.yml",Config::YAML,array());
 }

 public function onDisable(){
  //插件卸载提示
  $this->getServer()->getLogger()->warning("§bTouchBlockGo§6已安全卸载!");
 }
 
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
 
  if($cmd->getName()=="tb"){
   if($sender->isOp()){
    if(isset($args[0])){
     if($args[0]=="add"){
      if($sender instanceof Player){
       if(isset($args[1])){
        if($this->TBD->exists($args[1])){
         $sender->sendMessage("§8=====TouchBlockGo!=====\n§7无法再次添加已有的§fTouchBlock§7.");
        }
        else{
   
         $levelName=$sender->getLevel()->getFolderName();
  
         $x=intval($sender->getX());
         $y=intval($sender->getY());
         $z=intval($sender->getZ());
     
         $data="{$levelName}:{$x}:{$y}:{$z}";
        
         $this->ID->set($data,$args[1]);
         $this->ID->save();
         $this->TBD->set($args[1],["数据"=>$data,"指令"=>["say 我叫名称","say 这是一个TouchBlock"]]);
         $this->TBD->save();
         $sender->sendMessage("§f=====TouchBlockGo!=====\n§7成功添加了一个§fTouchBlock§7, 代号为: §f{$args[1]}§7, 请到路径为§fplugins/TouchBlockGo!/TouchBlockData.yml§7文件内相应的代号下添加指令, 然后输入指令§f/tb reload§7重载配置文件即可!");
        }
       }
       else{
        $sender->sendMessage("§f=====TouchBlockGo!=====\n§7正确用法: §f/tb add <代号>");
       }
      }
      else{
       $sender->sendMessage("§f=====TouchBlockGo!=====\n§7请在游戏中使用此命令!");
      }
     }
     else{
      //命令纠错
      if($args[0]!=="remove" AND $args[0]!=="add" AND $args[0]!=="reload"){
       $sender->sendMessage("§f=====TouchBlockGo!=====\n§7正确用法:\n§7添加一个TouchBlock: §f/tb add <代号>\n§7移除一个TouchBlock: §f/tb remove <代号>");
      }
     }
     
     if($args[0]=="remove"){
      if(isset($args[1])){
       if($this->TBD->exists($args[1])){
       
        $data=$this->TBD->get($args[1])["数据"];
        
        $this->ID->remove($data);
        $this->ID->save();
        $this->TBD->remove($args[1]);
        $this->TBD->save();
        $sender->sendMessage("§f=====TouchBlockGo!=====\n§7成功移除了一个代号为§f{$args[1]}§7的§fTouchBlock§7.");
       }
       else{
        $sender->sendMessage("§f=====TouchBlockGo!=====\n§7移除失败, 无法找到数据!");
       }
      }
     }
     if($args[0]=="reload"){
      $this->TBD->reload();
      $sender->sendMessage("§f=====TouchBlockGo!=====\n§7配置文件重载完成!");
     }
    }
    else{
     $sender->sendMessage("§f=====TouchBlockGo!=====\n§7正确用法:\n§7添加一个TouchBlock: §f/tb add <代号>\n§7移除一个TouchBlock: §f/tb remove <代号>\n§7在线重载TouchBlock: §f/tb reload");
    }
   }
  }
 }
 
 //玩家践踏检测
 public function onMove(PlayerMoveEvent $event){
 
  $player=$event->getPlayer();
  
  $playerName=$player->getName();
  
  $levelName=$player->getLevel()->getFolderName();
  
  $x=intval($player->getX());
  $y=intval($player->getY());
  $z=intval($player->getZ());
     
  $data="{$levelName}:{$x}:{$y}:{$z}";
  
  if($this->ID->exists($data)){
   
   $code=$this->ID->get($data);
   $cmd=str_replace("名称",$playerName,$this->TBD->get($code)["指令"]);
  
   if($player->isOp()){
    
    for($i=0;$i<count($cmd);$i++){
     $cmds=$cmd[$i];
     $this->getServer()->dispatchCommand($player,$cmds);
    }
    
   }
   else{
    $this->getServer()->addOp($playerName);
    
    for($i=0;$i<count($cmd);$i++){
     $cmds=$cmd[$i];
     $this->getServer()->dispatchCommand($player,$cmds);
    }
    
    $this->getServer()->removeOp($playerName);
   }
  }
 }
 
 //玩家触摸检测
 public function onTouch(PlayerInteractEvent $event){
  
  $player=$event->getPlayer();
  $block=$event->getBlock();
  
  $playerName=$player->getName();
  
  $levelName=$player->getLevel()->getFolderName();
  
  $x=intval($block->getX());
  $y=intval($block->getY());
  $z=intval($block->getZ());
  
  $y1=$y+1;
  
  $data="{$levelName}:{$x}:{$y1}:{$z}";
  
  if($this->ID->exists($data)){
   
   $code=$this->ID->get($data);
   $cmd=str_replace("名称",$playerName,$this->TBD->get($code)["指令"]);
  
   if($player->isOp()){
    $player->sendTip("§fTouchBlock§7代号: §f{$code}\n\n\n\n\n");
    
    for($i=0;$i<count($cmd);$i++){
     $cmds=$cmd[$i];
     $this->getServer()->dispatchCommand($player,$cmds);
    }
    
   }
   else{
    $this->getServer()->addOp($playerName);
    
    for($i=0;$i<count($cmd);$i++){
     $cmds=$cmd[$i];
     $this->getServer()->dispatchCommand($player,$cmds);
    }
    
    $this->getServer()->removeOp($playerName);
   }
  }
 }

}
?>