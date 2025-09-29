<?php

namespace onebone\economyapi\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class ReturnDebtCommand extends EconomyAPICommand{
	private $plugin, $cmd;
	
	public function __construct(EconomyAPI $plugin, $cmd = "returndebt"){
		parent::__construct($cmd, $plugin);
		$this->plugin = $plugin;
		$this->cmd = $cmd;
		$this->setUsage("/$cmd <amount>");
		$this->setDescription("向银行还债或还贷");
		$this->setPermission("economyapi.command.returndebt");
	}
	
	public function execute(CommandSender $sender, $label, array $params){
		if(!$this->plugin->isEnabled() or !$this->testPermission($sender)){
			return false;
		}
		
		if(!$sender instanceof Player){
			$sender->sendMessage("请在游戏里运行该命令.");
			return true;
		}
		
		$amount = array_shift($params);
		
		if(trim($amount) === "" or (!is_numeric($amount) and $amount !== "all")){
			$sender->sendMessage("用法: /".$this->cmd." <金钱>");
			return true;
		}
		
		if($amount === "all"){
			$amount = $this->plugin->myDebt($sender);
		}
		if($amount <= 0){
			$sender->sendMessage($this->plugin->getMessage("returndebt-must-bigger-than-zero", $sender->getName()));
			return true;
		}
		
		if($this->plugin->myMoney($sender) < $amount){
			$sender->sendMessage($this->plugin->getMessage("returndebt-dont-have-money", $sender->getName(), array($amount, $this->plugin->myMoney($sender), "%3", "%4")));
			return true;
		}
		
		$result = $this->plugin->reduceDebt($sender, $amount, false, "ReturnDebtCommand");
		switch($result){
			case EconomyAPI::RET_INVALID:
			$sender->sendMessage($this->plugin->getMessage("returndebt-dont-have-debt", $sender->getName(), array($amount, $this->plugin->myDebt($sender), "%3", "%4")));
			break;
			case EconomyAPI::RET_CANCELLED:
			$sender->sendMessage($this->plugin->getMessage("returndebt-failed", $sender->getName()));
			break;
			case EconomyAPI::RET_SUCCESS:
			$sender->sendMessage($this->plugin->getMessage("returndebt-returndebt", $sender->getName(), array($amount, $this->plugin->myDebt($sender), "%3", "%4")));
			break;
		}
		return true;
	}
}