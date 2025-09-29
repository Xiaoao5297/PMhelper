<?php
namespace db;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;

class CallbackTask extends PluginTask {

    /** @var callable */
    private $callable;

    /** @var array */
    private $args;

    /**
     * CallbackTask 构造函数。
     *
     * @param Plugin $owner 插件主类实例
     * @param callable $callable 可调用的函数或方法
     * @param array $args 传递给回调函数的参数
     */
    public function __construct(Plugin $owner, callable $callable, array $args = []) {
        parent::__construct($owner);
        $this->callable = $callable;
        $this->args = $args;
    }

    /**
     * 任务运行时调用的方法。
     *
     * @param int $currentTick 当前服务器 tick
     */
    public function onRun($currentTick) {
        call_user_func_array($this->callable, $this->args);
    }
}