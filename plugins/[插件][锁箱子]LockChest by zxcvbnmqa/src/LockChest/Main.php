<?php
namespace LockChest;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent; // 新增事件
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\Player;

class Main extends PluginBase implements Listener {
    
    /** @var Config|null */
    private $lockedChests = null;
    
    public function onEnable() {
        $this->getLogger()->info("§aLockChest 插件正在加载...");
        
        // 注册事件监听器
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        // 创建插件数据文件夹
        @mkdir($this->getDataFolder());
        
        // 初始化 locked.yml 配置文件
        $this->lockedChests = new Config($this->getDataFolder() . "locked.yml", Config::YAML, []);
        if ($this->lockedChests === null) {
            $this->getLogger()->error("§c无法加载 locked.yml 文件！插件将禁用。");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        
        $this->getLogger()->info("§aLockChest 插件加载完成！");
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args) {
        if (!($sender instanceof Player)) {
            $sender->sendMessage("§c请在游戏内使用此命令！");
            return true;
        }
        
        $block = $sender->getTargetBlock(10);
        if (!$block || $block->getId() !== Block::CHEST) {
            $sender->sendMessage("§9▎§f请看向一个箱子！");
            return true;
        }
        
        $pos = $block->x . ":" . $block->y . ":" . $block->z . ":" . $sender->getLevel()->getName();
        
        switch (strtolower($cmd->getName())) {
            case "锁":
                if (isset($this->lockedChests->getAll()[$pos])) {
                    $sender->sendMessage("§4▎§f个箱子已经被锁定了！");
                    return true;
                }
                $this->lockedChests->set($pos, [
                    "owner" => $sender->getName(),
                    "created" => time()
                ]);
                $this->lockedChests->save();
                $sender->sendMessage("§2▎§f成功锁定箱子！");
                return true;
                
            case "开锁":
                $data = $this->lockedChests->getAll();
                if (!isset($data[$pos])) {
                    $sender->sendMessage("§9▎§f个箱子没有被锁定！");
                    return true;
                }
                if ($data[$pos]["owner"] !== $sender->getName() && !$sender->isOp()) {
                    $sender->sendMessage("§e▎§f你不是这个箱子的主人！");
                    return true;
                }
                $this->lockedChests->remove($pos);
                $this->lockedChests->save();
                $sender->sendMessage("§2▎§f已解除箱子锁定！");
                return true;
        }
        return false;
    }
    
    public function onInteract(PlayerInteractEvent $event) {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        
        if ($block->getId() === Block::CHEST) {
            $pos = $block->x . ":" . $block->y . ":" . $block->z . ":" . $block->getLevel()->getName();
            $data = $this->lockedChests->getAll();
            
            if (isset($data[$pos])) {
                if ($data[$pos]["owner"] !== $player->getName() && !$player->isOp()) {
                    $player->sendMessage("§4▎§f这个箱子已被锁定！");
                    $event->setCancelled();
                }
            }
        }
    }
    
    public function onPlace(BlockPlaceEvent $event) {
        $block = $event->getBlock();
        if ($block->getId() === Block::CHEST) {
            $player = $event->getPlayer();
            $item = $event->getItem();
            if ($item->getId() === Block::CHEST && $player->hasPermission("lockchest.autolock")) {
                $pos = $block->x . ":" . $block->y . ":" . $block->z . ":" . $block->getLevel()->getName();
                $this->lockedChests->set($pos, [
                    "owner" => $player->getName(),
                    "created" => time()
                ]);
                $this->lockedChests->save();
                $player->sendMessage("§2▎§f已自动锁定新放置的箱子！");
            }
        }
    }
    
    // 新增：禁止非箱子主人破坏箱子
    public function onBreak(BlockBreakEvent $event) {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        
        if ($block->getId() === Block::CHEST) {
            $pos = $block->x . ":" . $block->y . ":" . $block->z . ":" . $block->getLevel()->getName();
            $data = $this->lockedChests->getAll();
            
            if (isset($data[$pos])) {
                if ($data[$pos]["owner"] !== $player->getName() && !$player->isOp()) {
                    $player->sendMessage("§4▎§c这个箱子已被锁定，你不能破坏它！");
                    $event->setCancelled();
                }
            }
        }
    }
    
    public function onDisable() {
        $this->getLogger()->info("§cLockChest 插件正在禁用...");
        if ($this->lockedChests !== null) {
            $this->lockedChests->save();
            $this->getLogger()->info("§alocked.yml 已保存！");
        } else {
            $this->getLogger()->warning("§clockedChests 为 null，无法保存！");
        }
        $this->getLogger()->info("§cLockChest 插件已禁用！");
    }
}