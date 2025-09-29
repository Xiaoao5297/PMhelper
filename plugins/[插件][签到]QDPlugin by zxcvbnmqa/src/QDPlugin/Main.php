<?php
namespace QDPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;

class Main extends PluginBase {

    private $signRecord = [];

    public function onEnable() {
        $this->getLogger()->info(TextFormat::GREEN . "QDPlugin enabled!");
        @mkdir($this->getDataFolder());
        $this->signRecord = new Config($this->getDataFolder() . "signRecord.yml", Config::YAML, []);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if (strtolower($command->getName()) === "qd") {
            if ($sender instanceof Player) {
                $playerName = $sender->getName();
                $today = date("Y-m-d");

                if ($this->signRecord->exists($playerName) && $this->signRecord->get($playerName) === $today) {
                    $sender->sendMessage(TextFormat::RED . "§4▎§f你今天已经签到过了！");
                    return true;
                }

                $economy = EconomyAPI::getInstance();
                $randomFloat = round(mt_rand(1500, 2600) / 10, 1);
                $economy->addMoney($sender, $randomFloat);
                $this->signRecord->set($playerName, $today);
                $this->signRecord->save();
                $sender->sendMessage(TextFormat::GREEN . "§2▎签到成功！你获得了 " . $randomFloat . " 元。");
                return true;
            } else {
                $sender->sendMessage(TextFormat::RED . "§e[§6!§e]§6➢§f请在游戏内使用此命令。");
                return true;
            }
        }
        return false;
    }

    public function onDisable() {
        $this->getLogger()->info(TextFormat::RED . "QDPlugin disabled!");
    }
}