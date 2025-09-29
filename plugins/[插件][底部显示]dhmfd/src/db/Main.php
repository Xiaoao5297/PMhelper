<?php
namespace db;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

    private $message;
    private $config;

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // 每秒更新一次
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask($this, [$this, "updateDisplay"]), 20);

        @mkdir($this->getDataFolder());
        $this->message = new Config($this->getDataFolder() . "message.yml", Config::YAML, array(
            "qqq" => "未设置QQ群",
            "sn" => "我的服务器",
            "db" => "on"
        ));

        // 加载 config.yml
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
    }

    public function onJoin(PlayerJoinEvent $event) {
        // 玩家加入时不需要额外操作
    }

    public function onQuit(PlayerQuitEvent $event) {
        // 玩家退出时不需要额外操作
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "请在游戏内使用此命令！");
            return false;
        }

        switch ($command->getName()) {
            case "qqq":
                if (isset($args[0]) && $args[0] === "set") {
                    $this->message->set("qqq", isset($args[1]) ? $args[1] : "未设置");
                    $this->message->save();
                    $sender->sendMessage(TextFormat::GREEN . "QQ群已更新为: " . $args[1]);
                    return true;
                }
                break;

            case "sn":
                if (isset($args[0]) && $args[0] === "set") {
                    $this->message->set("sn", isset($args[1]) ? $args[1] : "未设置");
                    $this->message->save();
                    $sender->sendMessage(TextFormat::GREEN . "服务器名称已更新为: " . $args[1]);
                    return true;
                }
                break;
        }
        return false;
    }

    public function updateDisplay() {
        if ($this->message->get("db") !== "on") return;

        date_default_timezone_set('Asia/Shanghai');
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $name = $player->getName();
            $x = (int)$player->getX();
            $y = (int)$player->getY();
            $z = (int)$player->getZ();
            $item = $player->getInventory()->getItemInHand();
            $money = EconomyAPI::getInstance()->myMoney($name);
            $map = $player->getLevel()->getFolderName();
            $time = date("H:i:s");

            // 从 config.yml 中读取显示格式
            $format = $this->config->get("format", "<§2草§6方块§f服务器>\n§2金币: §6{money}§f   §2坐标: §6{x} §6{y} §6{z}§f\n§2物品: §6{item}§f   §2地图: §6{map}§f\n§2时间: §6{time}  §2QQ群: §6{qqq}");

            // 替换变量
            $info = str_replace(
                ["{x}", "{y}", "{z}", "{item}", "{money}", "{map}", "{time}", "{qqq}", "{sn}"],
                [$x, $y, $z, $item->getId() . ":" . $item->getDamage(), $money, $map, $time, $this->message->get("qqq"), $this->message->get("sn")],
                $format
            );

            // 发送到玩家右侧
            $player->sendPopup($info);
        }
    }
}