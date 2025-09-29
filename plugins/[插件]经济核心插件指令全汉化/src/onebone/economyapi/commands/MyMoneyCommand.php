<?php

namespace onebone\economyapi\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class MyMoneyCommand extends EconomyAPICommand{
	private $plugin;
	
	public function __construct(EconomyAPI $api, $cmd = "mymoney"){
		parent::__construct($cmd, $api);
		$this->setUsage("/$cmd");
		$this->setDescription("显示自己身上口袋里的金钱");
		$this->setPermission("economyapi.command.mymoney");
	}
	
	public function execute(CommandSender $sender, $label, array $args){
		if(!$this->getPlugin()->isEnabled()){
			return false;
		}
		if(!$this->testPermission($sender)){
			return false;
		}
		
		if(!$sender instanceof Player){
			$sender->sendMessage("请在游戏里运行该命令");
			return true;
		}
		$username = $sender->getName();
		$result = $this->getPlugin()->myMoney($username);
		$sender->sendMessage($this->getPlugin()->getMessage("mymoney-mymoney", $sender->getName(), array($result, "%2", "%3", "%4")));
	}
}