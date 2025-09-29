<?php

namespace onebone\economyapi\commands;

use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class PayCommand extends EconomyAPICommand{
	private $plugin;
	
	public function __construct(EconomyAPI $plugin, $cmd = "pay"){
		parent::__construct($cmd, $plugin);
		$this->setUsage("/$cmd <玩家> <金钱>");
		$this->setPermission("economyapi.command.pay");
		$this->setDescription("付款或给其他玩家钱");
	}
	
	public function execute(CommandSender $sender, $label, array $params){
		$plugin = $this->getPlugin();
		if(!$plugin->isEnabled()){
			return false;
		}
		if(!$this->testPermission($sender)){
			return false;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage("请在游戏里运行该命令");
			return true;
		}
		
		$player = array_shift($params);
		$amount = array_shift($params);
		
		if(trim($player) === "" or trim($amount) === "" or !is_numeric($amount)){
			$sender->sendMessage("用法: ".$this->getUsage());
			return true;
		}
		
		$server = Server::getInstance();
		//  Player finder  //
		$p = $server->getPlayer($player);
		if($p instanceof Player){
			$player = $p->getName();
		}
		// END //
		
		$result = $plugin->reduceMoney($sender, $amount);
		if($result !== EconomyAPI::RET_SUCCESS){
			$sender->sendMessage("支付失败 !");
			return true;
		}
		$result = $plugin->addMoney($player, $amount);
		if($result !== EconomyAPI::RET_SUCCESS){
			$sender->sendMessage("支付失败 !");
			$plugin->addMoney($sender, $amount, true);
			return true;
		}
		$sender->sendMessage("支付了 \$$amount 给 $player");
	}
}