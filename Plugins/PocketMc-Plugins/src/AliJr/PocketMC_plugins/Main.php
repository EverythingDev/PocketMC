<?php


//PocketMC - Powerful Security Plugin!
//Dont change codes!

declare(strict_types=1);

namespace AliJr\PocketMC_plugins;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\permission\ServerOperator;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\Player;
use pocketmine\world\particle\HappyVillagerParticle;

class Main extends PluginBase implements Listener{

	public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		if(!file_exists($this->getDataFolder() . "auth")){
			mkdir($this->getDataFolder() . "auth");

			$this->getLogger()->notice("Auth Folder is not created Creating Folder...");
			$this->getLogger()->notice("Auth folder created, dont delete this folder!");
		}

		if(!file_exists($this->getDataFolder() . "bot")){
			mkdir($this->getDataFolder() . "bot");

			$this->getLogger()->notice("Bot Folder is not created Creating Folder...");
			$this->getLogger()->notice("Bot folder created, dont delete this folder!");
		}


		$this->getLogger()->info(C::GREEN . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=");
		$this->getLogger()->info(C::GREEN . "-=-=-=-=-=PocketMC SoftWare-=-=--=-=-=");
		$this->getLogger()->info(C::GREEN . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=");
	}

	public function onDisable(){

		$this->getLogger()->info(C::RED . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=");
		$this->getLogger()->info(C::RED . "-=-=-=-=-=PocketMC SoftWare-=-=--=-=-=");
		$this->getLogger()->info(C::RED . "-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=");

	}
//-=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=-
//Auth Plugin codes
//-=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=-
	public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
		if($command->getName() === "pocketmcpanel"){
			if($player instanceof player){
				$this->adminPanel($player);
			}
		}

		if($command->getName() === "changepassword"){
			if($player instanceof player){
				$this->changepassForm($player);
			}
		}
		return true;
	}

	public function onJoin(PlayerJoinEvent $event){

		$player = $event->getPlayer();
		if(!file_exists($this->getDataFolder() . "auth/" . strtolower($player->getName()) . ".yml")){
			new Config($this->getDataFolder() . "auth/" . strtolower($player->getName()) . ".yml", Config::YAML, [
				"password" => 0,
				"registered" => 0,
				"email" => 0,
				"number" => 0
			]);
		}

		$this->authForm($player);
	}

	public function authForm(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);

			$name = $player->getName();

			if($data === null){
				$player->kick(C::RED . "(Security-System) You trying to return Auth Form!");
				return false;
			}

			if($result->getNested("registered") == 0){
				$player->getLevel()->addSound(new EndermanTeleportSound($player));
				$result->set("registered", 1);
				$result->set("password", $data[1]);
				$result->set("number", $data[2]);
				$result->set("email", $data[3]);
				$result->save();

				$player->sendMessage(C::GREEN . "-=-=-=-=-=-=--=-=-=-=-=-\n* $name * Your account registered\nYour Password: $data[1]\nYour Number: $data[2]\n-=-=-=-=-=-=-=-=-=-=-");
			}

			if($result->getNested("registered") == 1){
				if($result->getNested("password") === $data[1]){
					$player->getLevel()->addSound(new EndermanTeleportSound($player));
					$player->sendMessage(C::GREEN . "You are logged in!");
					return true;
				}else{
					$this->authForm($player);
				}

			}

		});

		$name = $player->getName();
		$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);
		$config = new Config($this->getDataFolder() . "resources/" . strtolower("config" . ".yml"), Config::YAML);

		if($result->getNested("registered") == 0){
			$player->getLevel()->addSound(new BlazeShootSound($player));
			$form->setTitle($config->getNested("registertitle"));
			$form->addLabel($config->getNested("registerlabel"));
			$form->addInput($config->getNested("registermsg"), "Type here...");
			$form->addInput($config->getNested("numbermsg"), "Type here...");
			$form->addInput($config->getNested("emailmsg"), "Type here...");
			$form->sendToPlayer($player);
		}

		if($result->getNested("registered") == 1){
			$player->getLevel()->addSound(new DoorCrashSound($player));
			$form->setTitle($config->getNested("logintitle"));
			$form->addLabel("* $name * " . $config->getNested("loginlabel"));
			$form->addInput($config->getNested("loginmsg"), "Type here...");
			$form->sendToPlayer($player);
		}


	}

	public function changepassForm(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			if($data === null){
				return true;
			}

			switch($data){

				case 0:
					$this->changewithNumber($player);
					break;

				case 1:
					$this->changewithEmail($player);
					break;

				case 2:
					$this->changewitholdPass($player);
					break;


			}

		});

		$form->setTitle(C::DARK_RED . "Change Your account password");
		$form->setContent(C::WHITE . "Please select a option:");
		$form->addButton(C::GREEN . "Change With Your Number Phone");
		$form->addButton(C::GREEN . "Change With Your Email");
		$form->addButton(C::GREEN . "Change With Your Old Password");
		$form->sendToPlayer($player);


	}

	public function changewithNumber(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);

			if($data === null){
				return true;
			}


			if($result->getNested("number") == $data[1]){
				$this->changepassForm2($player);
			}else{
				$player->sendMessage(C::RED . "Incorrect Number!");
				return true;
			}


		});

		$form->setTitle(C::DARK_RED . "Change Password");
		$form->addLabel(C::GRAY . "Please type requested information to Change your password");
		$form->addInput(C::WHITE . "Your Number:", "Type here...");
		$form->sendToPlayer($player);

	}

	public function changewithEmail(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);

			if($data === null){
				return true;
			}


			if($result->getNested("email") == $data[1]){
				$this->changepassForm2($player);
			}else{
				$player->sendMessage(C::RED . "Incorrect Email!");
				return true;
			}


		});

		$form->setTitle(C::DARK_RED . "Change Password");
		$form->addLabel(C::GRAY . "Please type requested information to Change your password");
		$form->addInput(C::WHITE . "Your Email:", "Type here...");
		$form->sendToPlayer($player);

	}

	public function changewitholdPass(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);

			if($data === null){
				return true;
			}


			if($result->getNested("password") == $data[1]){
				$this->changepassForm2($player);
			}else{
				$player->sendMessage(C::RED . "Incorrect Password!");
				return true;
			}


		});

		$form->setTitle(C::DARK_RED . "Change Password");
		$form->addLabel(C::GRAY . "Please type requested information to recovery your password");
		$form->addInput(C::WHITE . "Your Old Password:", "Type here...");
		$form->sendToPlayer($player);

	}

	public function changepassForm2(player $player){


		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($player->getName() . ".yml"), Config::YAML);

			$name = $player->getName();
			$oldpass = $result->getNested("password");

			$player->sendMessage(C::GREEN . "-=-=-=-=-=-=-=-=-=-=-=-\nYour Account Password has Been Changed\nAccount Name : $name\nold password : $oldpass\nNew Password: $data[1]\n-=-=-=-=-=-=-=-=-=-=-=-");
			$result->set("password", $data[1]);
			$result->save();
			$this->authForm($player);

			if($data === null){
				return true;
			}


		});

		$form->setTitle(C::DARK_RED . "Change Password");
		$form->addLabel(C::GRAY . "Done, type your new password here");
		$form->addInput(C::WHITE . "Your New password:", "Type here...");
		$form->sendToPlayer($player);
	}


	public function adminPanel(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			$result = $data;

			if($result === null){
				return true;
			}

			switch($result){

				case 0:
					$this->playerinfoAuth($player);
					break;

				case 1:
					$this->changeplayerPassword($player);
					break;
			}

		});

		$name = $player->getName();
		$form->setTitle(C::YELLOW . "PocketMC Admin Panel");
		$form->setContent(C::WHITE . "Hi * $name * , Please select a button:");
		$form->addButton(C::GRAY . "Player Information");
		$form->addButton(C::RED . "Change Player Password");
		$form->sendToPlayer($player);

	}

	public function playerinfoAuth(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($data[1] . ".yml"), Config::YAML);

			$password = $result->getNested("password");
			$number = $result->getNested("number");
			$email = $result->getNested("email");

			$player->sendMessage(C::GREEN . "-=-=-=-=-=-=-=-=-=-=-=-=-\n* $data[1] * Auth Information\nPassword : $password\nNumber : $number\nEmail : $email\n-=-=-=-=-=-=-=-=-=-=-=-=-");


			if($data === null){
				return true;
			}


		});

		$name = $player->getName();

		$form->setTitle(C::YELLOW . "Recvive Player Information");
		$form->addLabel(C::WHITE . "Hi * $name * , Type player name on down input to recvive auth information:");
		$form->addInput(C::GRAY . "PLAYER NAME:", "Type here...");
		$form->sendToPlayer($player);
	}


	public function changeplayerPassword(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "auth/" . strtolower($data[1] . ".yml"), Config::YAML);

			$oldpass = $result->getNested("password");

			$player->sendMessage(C::GREEN . "-=-=-=-=-=-=-=-=-=-=-=-\nPlayer Name : $data[1]\nOld Password: $oldpass\nNew Password: $data[2]\n-=-=-=-=-=-=-=-=-=-=-=-");
			$result->set("password", $data[2]);
			$result->save();


			if($data === null){
				return true;
			}


		});

		$name = $player->getName();

		$form->setTitle(C::YELLOW . "Change Player Password");
		$form->addLabel(C::WHITE . "Hi * $name * , Type player name on down input to change player password:");
		$form->addInput(C::GRAY . "PLAYER NAME:", "Type here...");
		$form->addInput(C::GRAY . "New Password", "Type here...");
		$form->sendToPlayer($player);
	}

	//Auth plugin
//-=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=-
//-=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=-
	//AntiBOT Plugin

	public function botJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();

		$bot = new Config($this->getDataFolder() . "bot/" . strtolower($player->getName() . ".yml"), Config::YAML);

		if(!file_exists($this->getDataFolder() . "bot/" . strtolower($player->getName()) . ".yml")){
			new Config($this->getDataFolder() . "bot/" . strtolower($player->getName()) . ".yml", Config::YAML, [
				"bot" => 0
			]);
		}
		if($bot->getNested("bot") == 0 & 1){
			$this->botChecker($player);
		}

	}

	public function botChecker(DataPacketReceiveEvent $event){

		$player = $event->getPlayer();

		$bot = new Config($this->getDataFolder() . "bot/" . strtolower($player->getName() . ".yml"), Config::YAML);

		if($bot->getNested("bot") === 0){

			$player->close("", C::RED . "[ANTIBOT] We Are Analyzing Your Playing | Please Rejoin");
			$event->setCancelled(true);
			$bot->set("bot", 1);
			$bot->save();

		}

		if($bot->getNested("bot") === 1){

			$name = $player->getName();

			$this->getLogger()->notice("[AntiBOT] Player * $name * Player Passed Bot Testing and joined to your server!");

			$bot->set("bot", 2);
			$bot->save();

		}

	}

//AntiBOT Plugin





}
