<?php

/*
 * Broadcaster (v1.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 09/11/2014 3:00 PM (GMT)
 * Copyright & License: (C) EvolSoft. All Rights Reserved.
 */

namespace Broadcaster\Commands;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use Broadcaster\Main;

class Commands extends PluginBase implements CommandExecutor{
	
	public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
    	$fcmd = strtolower($cmd->getName());
    	switch($fcmd){
    			case "broadcaster":
    				if(isset($args[0])){
    			   		$args[0] = strtolower($args[0]);
    			   		if($args[0]=="reload"){
    			   			if($sender->hasPermission("broadcaster.reload")) {
    			   				$this->plugin->reloadConfig();
    			   				$sender->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&aConfiguration Reloaded."));
    			   				return true;
    			   			}
    			   			else{
    			   				$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    			   				return true;
    			   			}
    			   		}
    			   		elseif($args[0]=="info"){
    			   			if($sender->hasPermission("broadcaster.info")) {
    			   				$sender->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2BroadCaster &9v" . Main::VERSION . " &2developed by&9 " . Main::PRODUCER));
    			   				$sender->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&2Website &9" . Main::MAIN_WEBSITE));
    			   				return true;
    			   			}
    			   			else{
    			   				$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    			   				return true;
    			   			}
    			   		}else{
    			   			if($sender->hasPermission("broadcaster")){
    			   				$sender->sendMessage($this->plugin->translateColors("&", Main::PREFIX . "&cSubcommand &9" . $args[0] . "&c not found. Use &9/bc &cto show available commands"));
    			   				break;
    			   			}
    			   			else{
    			   				$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    			   				break;
    			   			}
    			   			return true;
    			   		}
    			   	}
    			   	else{
    			   		if($sender->hasPermission("broadcaster")){
    			   			$sender->sendMessage($this->plugin->translateColors("&", "&2- &9Available Commands &2-"));
    			   			$sender->sendMessage($this->plugin->translateColors("&", "&9/bc info &2- &9Show info about this plugin"));
    			   			$sender->sendMessage($this->plugin->translateColors("&", "&9/bc reload &2- &9Reload the config"));
    			   			$sender->sendMessage($this->plugin->translateColors("&", "&9/sendmessage &2- &9Send message to specified player (* for all players)"));
    			   			break;
    			   		}else{
    			   			$sender->sendMessage($this->plugin->translateColors("&", "&cYou don't have permissions to use this command"));
    			   			break;
    			   			}
    			   		return true;
    			   	}
    		}
    	return true;
    }
    
}
    ?>