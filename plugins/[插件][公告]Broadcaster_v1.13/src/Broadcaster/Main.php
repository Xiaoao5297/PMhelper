<?php

/*
 * Broadcaster (v1.13) by EvolSoft
 * Developer: EvolSoft (Flavius12)
 * Website: http://www.evolsoft.tk
 * Date: 30/11/2014 05:22 PM (GMT)
 * Copyright & License: (C) EvolSoft. All Rights Reserved.
 */

namespace Broadcaster;

use pocketmine\Player;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{
	
	//About Plugin Const
	const PRODUCER = "EvolSoft";
	const VERSION = "1.12";
	const MAIN_WEBSITE = "http://www.evolsoft.tk";
	//Other Const
	//Prefix
	const PREFIX = "&9[&eBroadcaster&9] ";
	
    public $cfg;

    public function translateColors($symbol, $message){
    
    	$message = str_replace($symbol."0", TextFormat::BLACK, $message);
    	$message = str_replace($symbol."1", TextFormat::DARK_BLUE, $message);
    	$message = str_replace($symbol."2", TextFormat::DARK_GREEN, $message);
    	$message = str_replace($symbol."3", TextFormat::DARK_AQUA, $message);
    	$message = str_replace($symbol."4", TextFormat::DARK_RED, $message);
    	$message = str_replace($symbol."5", TextFormat::DARK_PURPLE, $message);
    	$message = str_replace($symbol."6", TextFormat::GOLD, $message);
    	$message = str_replace($symbol."7", TextFormat::GRAY, $message);
    	$message = str_replace($symbol."8", TextFormat::DARK_GRAY, $message);
    	$message = str_replace($symbol."9", TextFormat::BLUE, $message);
    	$message = str_replace($symbol."a", TextFormat::GREEN, $message);
    	$message = str_replace($symbol."b", TextFormat::AQUA, $message);
    	$message = str_replace($symbol."c", TextFormat::RED, $message);
    	$message = str_replace($symbol."d", TextFormat::LIGHT_PURPLE, $message);
    	$message = str_replace($symbol."e", TextFormat::YELLOW, $message);
    	$message = str_replace($symbol."f", TextFormat::WHITE, $message);
    
    	$message = str_replace($symbol."k", TextFormat::OBFUSCATED, $message);
    	$message = str_replace($symbol."l", TextFormat::BOLD, $message);
    	$message = str_replace($symbol."m", TextFormat::STRIKETHROUGH, $message);
    	$message = str_replace($symbol."n", TextFormat::UNDERLINE, $message);
    	$message = str_replace($symbol."o", TextFormat::ITALIC, $message);
    	$message = str_replace($symbol."r", TextFormat::RESET, $message);
    
    	return $message;
    }
    
    public function onEnable(){
	    @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
        $this->getCommand("sendmessage")->setExecutor(new Commands\SendMessage($this));
        $this->getCommand("broadcaster")->setExecutor(new Commands\Commands($this));
        $time = intval($this->cfg["time"]) * 20;
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new Task($this), $time);
    }
    
	public function broadcast($conf, $message){
		$message = str_replace("{PREFIX}", $conf["prefix"], $message);
		$message = str_replace("{SUFFIX}", $conf["suffix"], $message);
		$message = str_replace("{TIME}", date($conf["datetime-format"]), $message);
		return $message;
	}

	public function messagebyPlayer(Player $player, $conf, $message){
	    $format = $conf["sendmessage-format"];
		$format = str_replace("{MESSAGE}", $message, $format);
		$format = str_replace("{PREFIX}", $conf["prefix"], $format);
		$format = str_replace("{SENDER}", $player->getName(), $format);
		$format = str_replace("{SUFFIX}", $conf["suffix"], $format);
		$format = str_replace("{TIME}", date($conf["datetime-format"]), $format);
		return $format;
	}
	
	public function messagebyConsole(CommandSender $player, $conf, $message){
		$format = $conf["sendmessage-format"];
		$format = str_replace("{MESSAGE}", $message, $format);
		$format = str_replace("{PREFIX}", $conf["prefix"], $format);
		$format = str_replace("{SENDER}", $player->getName(), $format);
		$format = str_replace("{SUFFIX}", $conf["suffix"], $format);
		$format = str_replace("{TIME}", date($conf["datetime-format"]), $format);
		return $format;
	}
	
	public function getMessagefromArray($array){
		unset($array[0]);
		return implode(' ', $array);
	}
	
}
?>
