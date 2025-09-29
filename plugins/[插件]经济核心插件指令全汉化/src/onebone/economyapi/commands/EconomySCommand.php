<?php

namespace onebone\economyapi\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class EconomySCommand extends EconomyAPICommand{
	private $plugin;
	
	public function __construct(EconomyAPI $plugin, $cmd = "economys"){
		parent::__construct($cmd, $plugin);
		$this->plugin = $plugin;
		$this->setPermission("economyapi.command.economys");
		$this->setDescription("显示跟经济核心插件相关联的插件列表");
		$this->setUsage("/$cmd");
	}
	
	public function execute(CommandSender $sender, $label, array $params){
		if(!$this->getPlugin()->isEnabled()){
			return false;
		}
		if(!$this->testPermission($sender)){
			return false;
		}
		$output = "经济相关插件列表如下 :\n";
		foreach($this->getPlugin()->getList() as $plugin){
			$output .= $plugin.", ";
		}
		$output = substr($output, 0, -2);
		$sender->sendMessage($output);
		return true;
	}
}