<?php

namespace onebone\economyapi\commands;

use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class SeeMoneyCommand extends EconomyAPICommand{
	private $plugin, $cmd;
	
	public function __construct(EconomyAPI $plugin, $cmd = "seemoney"){
		parent::__construct($cmd, $plugin);
		$this->cmd = $cmd;
		$this->setUsage("/$cmd <player>");
		$this->setDescription("查询某个玩家身上口袋里的金钱");
		$this->setPermission("economyapi.command.seemoney");
	}
	
	public function execute(CommandSender $sender, $label, array $args){
		if(!$this->getPlugin()->isEnabled()){
			return false;
		}
		if(!$this->testPermission($sender)){
			return false;
		}
		
		$player = array_shift($args);
		if(trim($player) === ""){
			$sender->sendMessage("用法: /".$this->cmd." <玩家>");
			return true;
		}
		
		//  Player finder  //
		$server = Server::getInstance();
		$p = $server->getPlayer($player);
		if($p instanceof Player){
			$player = $p->getName();
		}
		// END //
		$result = $this->getPlugin()->myMoney($player);
		if($result === false){
			$sender->sendMessage($this->getPlugin()->getMessage("player-never-connected", $sender->getName(), array($player, "%2", "%3", "%4")));
			return true;
		}else{
			$sender->sendMessage($this->getPlugin()->getMessage("seemoney-seemoney", $sender->getName(), array($player, $result, "%3", "%4")));
			return true;
		}
	}
}