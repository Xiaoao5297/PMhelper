<?php

namespace onebone\economyapi\commands;

use onebone\economyapi\EconomyAPI;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;

class BankCommand extends EconomyAPICommand{
	private $plugin, $cmd;
	
	public function __construct(EconomyAPI $plugin, $cmd = "bank"){
		parent::__construct($cmd, $plugin);
		$this->cmd = $cmd;
		$this->plugin = $plugin;
		$this->setPermission("economyapi.command.bank");
		$this->setDescription("玩家通过该命令<存款|取款|查询|余额>管理银行账户里的金钱");
		$this->setUsage("/$cmd <存款|取款|查询|余额>");
	}
	
	public function execute(CommandSender $sender, $label, array $params){
		if(!$this->testPermission($sender) or !$this->plugin->isEnabled()){
			return false;
		}
		
		$sub = array_shift($params);
		$amount = array_shift($params);
		
		switch($sub){
			case "存款":
			if(trim($amount) === "" or !is_numeric($amount)){
				$sender->sendMessage("用法: /".$this->getName()." 存款 <金钱>");
				return true;
			}
			if(!$sender instanceof Player){
				$sender->sendMessage("请在游戏里运行该命令");
				return true;
			}
			
			$money = $this->plugin->myMoney($sender->getName());
			
			if($money < $amount){
				$sender->sendMessage($this->plugin->getMessage("bank-deposit-dont-have-money", $sender->getName(), array($amount, $money, "%3", "%4")));
				return true;
			}
			
			$this->plugin->reduceMoney($sender->getName(), $amount, true); // Reduce money in force
			$result = $this->plugin->addBankMoney($sender->getName(), $amount, true);
			if($result === EconomyAPI::RET_SUCCESS){
				$sender->sendMessage($this->plugin->getMessage("bank-deposit-success", $sender->getName(), array($amount, "%2", "%3", "%4")));
			}else{
				$sender->sendMessage($this->plugin->getMessage("bank-deposit-failed", $sender->getName()));
			}
			break;
			case "取款":
			if(trim($amount) === "" or !is_numeric($amount)){
				$sender->sendMessage("用法: /".$this->getName()." 取款 <金钱>");
				return true;
			}
			if(!$sender instanceof Player){
				$sender->sendMessage("请在游戏里运行该命令");
				return true;
			}
			
			$money = $this->plugin->myBankMoney($sender->getName());
			
			if($money < $amount){
				$sender->sendMessage($this->plugin->getMessage("bank-withdraw-lack-of-credit", $sender->getName(), array($amount, $money, "%3", "%4")));
				return true;
			}else{
				$this->plugin->reduceBankMoney($sender->getName(), $amount, true);
				$this->plugin->addMoney($sender->getName(), $amount, true);
				$sender->sendMessage($this->plugin->getMessage("bank-withdraw-success", $sender->getName(), array($amount, "%2", "%3", "%4")));
				return true;
			}
			break;
			case "查询":
			if(trim($amount) === ""){
				$sender->sendMessage("用法: /".$this->getName()." 查询 <玩家>");
				return true;
			}
			
			//  Player finder  //
			$server = Server::getInstance();
			$p = $server->getPlayer($amount);
			if($p instanceof Player){
				$player = $p->getName();
			}
			// END //
			
			$money = $this->plugin->myBankMoney($amount);
			if($money === false){
				$sender->sendMessage($this->plugin->getMessage("player-never-connected", $sender->getName(), array($amount, "%2", "%3", "%4")));
			}else{
				$sender->sendMessage($this->plugin->getMessage("bank-hismoney", $sender->getName(), array($amount, $money, "%3", "%4")));
			}
			return true;
			case "余额":
			$money = $this->plugin->myBankMoney($sender);
			$sender->sendMessage($this->plugin->getMessage("bank-mymoney", $sender->getName(), array($money, "%2", "%3", "%4")));
			break;
			default:
			$sender->sendMessage("用法: /".$this->cmd." <存款|取款|查询|余额>");
		}
	}
}