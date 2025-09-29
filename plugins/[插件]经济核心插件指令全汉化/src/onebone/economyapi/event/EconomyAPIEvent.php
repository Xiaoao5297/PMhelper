<?php

namespace onebone\economyapi\event;

use pocketmine\plugin\Plugin;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;

use onebone\economyapi\EconomyAPI;

class EconomyAPIEvent extends PluginEvent implements Cancellable{
	protected $issuer;
	private $plugin;

	public function __construct(EconomyAPI $plugin, $issuer){
		$this->plugin = $plugin;
	}
	
	public function getIssuer(){
		return $this->issuer;
	}
}