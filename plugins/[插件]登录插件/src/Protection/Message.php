<?php

namespace Protection;

use pocketmine\utils\Config;

class Message{
	
	public function __construct($file){
		new Config($file."Question.yml", Config::YAML, array(
		   "4"=>array(
		   "a"=>"欢迎来到本服务器~\(≧▽≦)/~",
		   "b"=>"为防止恶意注册",
		   "c"=>"请直接输入你的游戏 ID 进行验证"),
		   "5"=>array(
		   "a"=>"✘✘✘✘请输入激活码✘✘✘✘",
		   "b"=>NULL,
		   "c"=>NULL ),
		   "3"=>array(
		   "a"=>"###现在开始设置你的密码###",
		   "b"=>"###现在开始设置你的密码###",
		   "c"=>"请直接输入,无须/register"),
		   "2"=>array(
		   "a"=>"请再次输入 你设置的密码",
		   "b"=>"输入 remove 可以返回上一步",
		   "c"=>NULL ),
		   "1"=>array(
		   "a"=>"♚你需要登入，请直接输入密码",
		   "b"=>"♚登入后2小时本IP不需要再次登入本账号",
		   "c"=>NULL )
		));
		
	}
}