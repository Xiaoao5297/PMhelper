<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine;

use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;

/**
 * Handles the achievement list and a bit more
 */
abstract class Achievement{
	/**
	 * @var array[]
	 */
	public static $list = [
		/*"openInventory" => array(
			"name" => "Taking Inventory",
			"requires" => [],
		),*/
		"mineWood" => [
			"name" => "获得木头",
			"requires" => [ //"openInventory",
			],
		],
		"buildWorkBench" => [
			"name" => "这是，工作台？",
			"requires" => [
				"mineWood",
			],
		],
		"buildPickaxe" => [
			"name" => "下矿时间到!",
			"requires" => [
				"buildWorkBench",
			],
		],
		"buildFurnace" => [
			"name" => "“热”门话题",
			"requires" => [
				"buildPickaxe",
			],
		],
		"acquireIron" => [
			"name" => "来硬的",
			"requires" => [
				"buildFurnace",
			],
		],
		"buildHoe" => [
			"name" => "耕种时间到！",
			"requires" => [
				"buildWorkBench",
			],
		],
		"makeBread" => [
			"name" => "烤面包",
			"requires" => [
				"buildHoe",
			],
		],
		"bakeCake" => [
			"name" => "蛋糕是个谎言",
			"requires" => [
				"buildHoe",
			],
		],
		"buildBetterPickaxe" => [
			"name" => "获得升级",
			"requires" => [
				"buildPickaxe",
			],
		],
		"buildSword" => [
			"name" => "出击时间到!",
			"requires" => [
				"buildWorkBench",
			],
		],
		"diamonds" => [
			"name" => "钻石!",
			"requires" => [
				"acquireIron",
			],
		],

	];


	public static function broadcast(Player $player, $achievementId){
		if(isset(Achievement::$list[$achievementId])){
			$translation = new TranslationContainer("chat.type.achievement", [$player->getDisplayName(), TextFormat::GREEN . Achievement::$list[$achievementId]["name"]]);
			if(Server::getInstance()->getConfigString("announce-player-achievements", true) === true){
				Server::getInstance()->broadcastMessage($translation);
			}else{
				$player->sendMessage($translation);
			}

			return true;
		}

		return false;
	}

	public static function add($achievementId, $achievementName, array $requires = []){
		if(!isset(Achievement::$list[$achievementId])){
			Achievement::$list[$achievementId] = [
				"name" => $achievementName,
				"requires" => $requires,
			];

			return true;
		}

		return false;
	}


}
