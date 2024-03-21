<?php


//PocketMC - Powerful Security Plugin!
//Dont change codes!

declare(strict_types=1);

namespace AliJr\PocketMC_plugins;

use onebone\economyapi\EconomyAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\level\sound\DoorCrashSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\item\Item;
use pocketmine\permission\ServerOperator;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\Player;

class Main extends PluginBase implements Listener{

	public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		if(!file_exists($this->getDataFolder() . "certificate")){
			mkdir($this->getDataFolder() . "certificate");

			$this->getLogger()->notice("Certificate Folder is not created Creating Folder...");
			$this->getLogger()->notice("Certificate folder created, dont delete this folder!");

		}

		if(!file_exists($this->getDataFolder() . "bank")){
			mkdir($this->getDataFolder() . "bank");

			$this->getLogger()->notice("Bank Folder is not created Creating Folder...");
			$this->getLogger()->notice("Bank folder created, dont delete this folder!");

		}

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
//Security Codes is here !
//Auth Plugin codes
//-=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=--=-=-=-=-=-=-
	//Commands Codes
	public function onCommand(CommandSender $player, Command $command, string $label, array $args) : bool{
		if($command->getName() === "pocketmcpanel"){
			if($player instanceof player){
				$this->adminPanel($player);
			}
		}

		if($player instanceof player){
			if($command->getName() === "changepassword"){
			$this->changepassForm($player);
			}
		}

		if($player instanceof player){
			if($command->getName() === "certificate"){
				$this->certificatefirstForm($player);
			}
		}
		return true;
	}

	//Commands Codes!

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

		if(!file_exists($this->getDataFolder() . "bot/" . strtolower($player->getName() . ".yml"))){
			new Config($this->getDataFolder() . "bot/" . strtolower($player->getName()) . ".yml", Config::YAML, [
				"bot" => 0
			]);
		}

		$this->onbotJoin($player);

	}

	public function onbotJoin(player $player){

		$bot = new Config($this->getDataFolder() . "bot/" . strtolower($player->getName() . ".yml"), Config::YAML);

		if($bot->getNested("bot") == 0){
			$this->botChecker();
		}

	}

	public function botChecker(DataPacketReceiveEvent $event){

		$player = $event->getPlayer();

		$bot = new Config($this->getDataFolder() . "bot/" . strtolower($player->getName() . ".yml"), Config::YAML);

		if($bot->getNested("bot") == 0){

			$player->close("", C::RED . "[ANTIBOT] We Are Analyzing Your Playing | Please Rejoin");
			$event->setCancelled(true);
			$bot->set("bot", 1);
			$bot->save();

		}

		if($bot->getNested("bot") == 1){

			$bot->set("bot", 2);
			$bot->save();

		}

	}

//AntiBOT Plugin
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
//-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=--=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
//RolePlay Codes is here !

//Birth Certificate

	public function onJoin2(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		if(!file_exists($this->getDataFolder() . "certificate/" . strtolower($player->getName()) . ".yml")){
			new Config($this->getDataFolder() . "certificate/" . strtolower($player->getName()) . ".yml", Config::YAML, [
				"age" => 0,
				"gender" => 0,
				"nationailty" => 0,
				"have" => 0
			]);
		}
	}


	public function certificatefirstForm(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			$result = new Config($this->getDataFolder() . "certificate/" . strtolower($player->getName()) . ".yml", Config::YAML);

			if($data === null){
				return true;
			}


			switch($data){
				case 0:

					$this->firstForm($player);

					break;

				case 1:

					if($result->getNested("have") === 0){
						$player->sendMessage(C::RED . "You dont have birth certificate card :(");
					}

					if($result->getNested("have") === 1){

						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

						if($economy->myMoney($player) >= 25000){

							$this->paperCertificate($player);

							EconomyAPI::getInstance()->reduceMoney($player, 25000);

							$player->sendMessage(C::GREEN . "I'm Copying On Your Birth Cetificate Card And Give to you. 25K money earned from your account");

						}

					}else{
						$player->sendMessage(C::RED . "You dont have money , You need 25K Money");
					}

					break;

				case 2:

					$this->cardForm($player);

					break;

			}
		});

		$form->setTitle("§b| §eCertificate");
		$form->addButton("§7[ §dCreate Certificate §7]\n§7> §dTap To Open");
		$form->addButton("§7[ §dCopy §7]\n§7> §dTap To Open");
		$form->addButton("§7[ §dCard §7]\n§7> §dTap To Open");
		$form->sendToPlayer($player);



	}

	public function firstForm(player $player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createCustomForm(function(player $player, array $data = null){

			$result = new Config($this->getDataFolder() . "certificate/" . strtolower($player->getName()) . ".yml", Config::YAML);

			if($data === null){

				return true;

			}

			$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");

			if($economy->myMoney($player) >= 50000){

				if($data[5] === true){

					$this->paperCertificate($player);

					EconomyAPI::getInstance()->reduceMoney($player, 50000);

					$result->set("age", $data[1]);
					$result->save();

					if($data[1] === true){
						$result->set("gender", 1);
						$result->save();
					}

					if($data[2] === true){
						$result->set("gender", 2);
						$result->save();
					}

					$result->set("have", 1);
					$result->save();

					if($data[3] === true){
						$result->set("nationailty", 1);
						$result->save();
					}

					if($data[4] === true){
						$result->set("nationailty", 2);
						$result->save();
					}

				}
			}


		});

		$form->setTitle(C::WHITE . "Certificate");
		$form->addInput("§7Type §eAge" , "§eType here...");
		$form->addLabel("§7Select §eGender");
		$form->addToggle("Male");
		$form->addToggle("Female");
		$form->addLabel("§7Select §eNationiality");
		$form->addToggle("§aIranian");
		$form->addToggle("§bAmerican");
		$form->addLabel("§7Verify Your Account");
		$form->addToggle("§7Verify §b^");
		$form->sendToPlayer($player);

	}

	public function paperCertificate($player){

		$paper = Item::get(Item::PAPER);
		$paper->setCustomName("§bCeriticate");
		$player->getInventory()->addItem($paper);

	}
	public function PlayerInteractpaperCertificate(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$paper = $event->getItem();
		if($player->getInventory()->getItemInHand()->getCustomName() === "§bCeriticate"){
			$this->showCertificate($player);
		}
	}

	public function showCertificate(player $player){

		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			if($data = null){
				return true;
			}

			switch($data){
				case 0:

					$player->sendMessage(C::RED . "Your Closed Menu");

					break;
			}

		});

		$result = new Config($this->getDataFolder() . "certificate/" . strtolower($player->getName()) . ".yml", Config::YAML);
		$name = $player->getName();

		if($result->getNested("gender") == 1){
			$form->setTitle("§bC§ee§br§et§bi§ef§ei§bc§ea§et§be");
			$form->setContent("§fName : §a$name §e| §fGender : §aMale");
			$form->addButton("§cClose");
			$form->sendToPlayer($player);
		}

		if($result->getNested("gender") == 2){
			$form->setTitle("§bC§ee§br§et§bi§ef§ei§bc§ea§et§be");
			$form->setContent("§fName : §a$name §e| §fGender : §aFemale");
			$form->addButton("§cClose");
			$form->sendToPlayer($player);
		}

	}

	public function onJoin3(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		if(!file_exists($this->getDataFolder() . "bank/" . $player->getName() . ".yml")){
			new Config($this->getDataFolder() . "bank/" . $player->getName() . ".yml", Config::YAML, array(
				"money" => 0,
				"money1" => 0,
				"money2" => 0,
				"loan" => 0,
				"loan2" => 0,
				"loan3" => 0
			));
		}
	}

	public function cardForm(player $player){
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){
			$name = $player->getName();
			$result = $data;

			if($result === null){
				return true;
			}

			switch($result){

				case 0:
					$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
					if($economy->myMoney($player) >= 5000){
						EconomyAPI::getInstance()->reduceMoney($player, 5000);
						$this->carditem($player);
					}

					break;

				case 1:
					$this->depositMoney($player);
					break;

				case 2:
					$this->withdrawMoney($player);
					break;

				case 3:;
					$this->loanForm($player);
					break;
			}
		});

		$form->setTitle("§e§lCard Menu");
		$form->setContent("§7§lFor using this option first create a Card!");
		$form->addButton("§e§lCreate Card\n§a§l5K");
		$form->addButton("§e§lDeposit Money");
		$form->addButton("§e§lWithdraw Money");
		if($player->hasPermission("loanform.use")){
			$form->addButton("§e§lEarn Loan");
		}else{
			$form->addButton("§4§lYou earned one loan!");
		}
		$form->sendToPlayer($player);

		return $form;
	}

	public function loanForm(player $player){
		if($player->hasPermission("loanform.use")){
			$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
			$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
			$name = $player->getName();
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loan1.use");
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loan2.use");
			$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loan3.use");

			$form = $api->createSimpleForm(function(player $player, $data = null){

				$result = $data;

				if($result === null){
					return true;
				}

				switch($result){

					case 0:
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						$name = $player->getName();
						if($player->hasPermission("loan1.use")){
							EconomyAPI::getInstance()->addMoney($player, 5000);
							$player->sendMessage("§2§l[ §aATM §2] §3> §aYou Earned 5K Loan from bank");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "unsetuperm $name loanform.use");
							$money->set("loan", 5000);
							$money->save();
						}

						break;

					case 1:
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						$name = $player->getName();
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						if($player->hasPermission("loan1.use")){
							EconomyAPI::getInstance()->addMoney($player, 10000);
							$player->sendMessage("§2§l[ §aATM §2] §3> §aYou Earned 10K Loan from bank");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "unsetuperm $name loanform.use");
							$money->set("loan2", 10000);
							$money->save();
						}

						break;

					case 2:
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						$name = $player->getName();
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						if($player->hasPermission("loan1.use")){
							EconomyAPI::getInstance()->addMoney($player, 20000);
							$player->sendMessage("§2§l[ §aATM §2] §3> §aYou Earned 20K Loan from bank");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "unsetuperm $name loanform.use");
							$money->set("loan3", 10000);
							$money->save();
						}

						break;
				}
			});

			$form->setTitle("§a§lEarn Loan");
			$form->setContent("§7Select a button:");
			$form->addButton("§2§l5K");
			$form->addButton("§2§l10K");
			$form->addButton("§2§l20K");
			$form->sendToPlayer($player);

		}
	}

	public function withdrawMoney(player $player){
		$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			$result = $data;

			if($result === null){
				return true;
			}

			switch($result){

				case 0:
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					if($money->getNested("money") == 5000){
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						EconomyAPI::getInstance()->addMoney($player, 5000);
						$player->sendMessage("§2§l[ §aATM §2] §a> §2You withdrawed 5K Money on your bank account!");
						$money->set("money", 0);
						$money->save();
					}

					break;

				case 1:
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					if($money->getNested("money1") == 10000){
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						EconomyAPI::getInstance()->addMoney($player, 10000);
						$player->sendMessage("§2§l[ §aATM §2] §a> §2You withdrawed 10K Money on your bank account!");
						$money->set("money1", 0);
						$money->save();
					}

					break;

				case 2:
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					if($money->getNested("money2") == 20000){
						$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
						$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
						EconomyAPI::getInstance()->addMoney($player, 20000);
						$player->sendMessage("§2§l[ §aATM §2] §a> §2You withdrawed 20K Money on your bank account!");
						$money->set("money2", 0);
						$money->save();
					}

					break;
			}
		});

		$form->setTitle("§a§LWithdraw Money");
		$form->setContent("§7Select a button:");
		$form->addButton("§2§l5K");
		$form->addButton("§2§l10K");
		$form->addButton("§2§l20K");
		$form->sendToPlayer($player);
	}

	public function depositMoney(player $player){
		$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
		$api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

		$form = $api->createSimpleForm(function(player $player, $data = null){

			$result = $data;

			if($result === null){
				return true;
			}

			switch($result){

				case 0:

					$name = $player->getName();
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
					if($economy->myMoney($player) >= 5000){
						if($money->getNested("loan2") == 0){
							$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
							EconomyAPI::getInstance()->reduceMoney($player, 5000);
							$money->set("money", 5000);
							$money->save();

							$player->sendMessage("§2§l[ §aATM §2] §aYou deposit money on your Bank account, you can earn loan!");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loanform.use");
						}
						if($money->getNested("loan") == 5000){
							EconomyAPI::getInstance()->reduceMoney($player, 5000);
							$player->sendMessage("§2§l[ §aATM §2] §a>§2 Your money earned by bank for earn loan!");
							$money->set("loan", 0);
							$money->save();
						}
					}else{
						$player->sendMessage("§c§l[ §4ATM §c] §4> §cYou Dont have Money!");
					}

					break;

				case 1:
					$name = $player->getName();
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
					if($economy->myMoney($player) >= 10000){
						if($money->getNested("loan2") == 0){
							$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
							EconomyAPI::getInstance()->reduceMoney($player, 10000);
							$money->set("money", 10000);
							$money->save();

							$player->sendMessage("§2§l[ §aATM §2] §aYou deposit money on your Bank account, you can earn loan!");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loanform.use");

						}
						if($money->getNested("loan2") == 10000){
							EconomyAPI::getInstance()->reduceMoney($player, 10000);
							$player->sendMessage("§2§l[ §aATM §2] §a>§2 Your money earned by bank for earn loan!");
							$money->set("loan2", 0);
							$money->save();
						}
					}else{
						$player->sendMessage("§c§l[ §4ATM §c] §4> §cYou dont have Money!");
					}

					break;

				case 2:

					$name = $player->getName();
					$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
					$economy = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
					if($economy->myMoney($player) >= 20000){
						if($money->getNested("loan3") == 0){
							$money = new Config($this->getDataFolder() . "bank/" . strtolower($player->getName()) . ".yml", Config::YAML);
							EconomyAPI::getInstance()->reduceMoney($player, 20000);
							$money->set("money", 20000);
							$money->save();
							$player->sendMessage("§2§l[ §aATM §2] §aYou deposit money on your Bank account, you can earn loan!");
							$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "setuperm $name loanform.use");
						}
						if($money->getNested("loan3") == 20000){
							EconomyAPI::getInstance()->reduceMoney($player, 20000);
							$player->sendMessage("§2§l[ §aATM §2] §a>§2 Your money earned by bank for earn loan!");
							$money->set("loan3", 0);
							$money->save();
						}
					}else{
						$player->sendMessage("§c§l[ §4ATM §c] §4> §cYou dont have money!");
					}

					break;
			}

		});

		$form->setTitle("§a§lDeposit Money");
		$form->setContent("§7Select a button:");
		$form->addButton("§2§l5K");
		$form->addButton("§2§l10K");
		$form->addButton("§2§l20K");
		$form->sendToPlayer($player);
	}

	public function carditem($player){
		$player->sendMessage("§a§l[Atm] Your Card Created!");
		$name1 = $player->getName();
		$card = Item::get(Item::PAPER);
		$card->setCustomName("§a§lCard");
		$player->getInventory()->addItem($card);
	}

	public function oninteract(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$item = $event->getItem();
		if ($player->getInventory()->getItemInHand()->getCustomName() === "§a§lCard") {
			$this->openatmpanel($player);
		}
	}


	public function openatmpanel(player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$player->getLevel()->addSound(new AnvilUseSound($player));
		$form = $api->createCustomForm(function (player $player, array $data = null){
			if($data === null){
				return true;
			}
			$target = $this->getServer()->getPlayer($data[1]);
			if($target !== null){
				if(strtolower($data[1]) === strtolower($player->getName())){
					$player->sendMessage("§cyou can not send money to your");
					return;
				}
				if($data === null){
				}
				if(!is_numeric($data[2])){
					$player->sendMessage("§cPlease type number");
					return;
				}

				if($data[2] <= 0){
					$player->sendMessage("§cDont use - = * & # ( )");
					return;
				}
				$namet = $target->getName();
				$name = $player->getName();
				$economy = $this->getServer()->getPluginManager()->getPlugin('EconomyAPI');
				if($economy->myMoney($player) >= $data[2]){
					EconomyAPI::getInstance()->reduceMoney($player, $data[2]);
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "givemoney $data[1] $data[2]");
					$player->sendMessage("§aYou Send to $data[1] Money: $data[2]");
					$target->sendMessage("§2$name Payed You: $data[2] \n §e§lDescription : §7§l $data[3]");
					$this->tarakonesh($player);
					$resid = Item::get(Item::PAPER);
					$resid->setCustomName("§a§lReceipt \n §e§l Name: $data[1] \n §e§l Money : $data[2] \n §e§lDescription : $data[3]\n §e§l-§a-§e-§a-§e-§a-§e-§a");
					$player->getInventory()->addItem($resid);
				}else{
					$player->sendMessage("§cShoma Pool Nadarid");
					$this->tarakoneshnamovafagh($player);
					$target->sendMessage(C::RED . "[Atm] Player $name is trying to send money $data[2] For you but dont have money!.");
				}
			}else{
				$player->sendMessage("§cPlayer $data[1] Offline!");
				$this->tarakoneshnamovafaghoffline($player);
			}

		});

		$form->setTitle("§6§lAber Bank");
		$form->addLabel("§e§lPlease type Required Information on down inputs:");
		$form->addInput("§f§lPlayer Name", "Type here...");
		$form->addInput("§f§lMoney", "§f§lType here...");
		$form->addInput("§f§lDescription", "Type here...");
		$form->sendToPlayer($player);
		return $form;
	}

	public function tarakonesh(player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function(Player $player, $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){

				case 0:
					$this->openatmpanel($player);
					break;

				case 1:
					$player->sendMessage(C::GREEN . "[ATM] You send money to this player!");
					break;

			}

		});

		$form->setTitle(C::GREEN . "Result");
		$form->setContent(C::GRAY . "You Have sended money to this player!");
		$form->addButton(C::WHITE ."Back To Pay Menu", 0, "textures/ui/refresh_light");
		$form->addButton(C::RED . "Close Menu", 0, "textures/ui/close_button_hover");
		$form->sendToPlayer($player);

		return $form;

	}

	public function tarakoneshnamovafagh(player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function(Player $player, $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){

				case 0:
					$this->openatmpanel($player);
					break;

				case 1:
					$player->sendMessage(C::RED . "[ATM] You dont have money!");
					break;

			}

		});

		$form->setTitle(C::RED . "Result");
		$form->setContent(C::GRAY . "Money is not send to this player, your dont have money!");
		$form->addButton(C::WHITE."Retry", 0, "textures/ui/refresh_light");
		$form->addButton(C::RED . "Close Menu", 0, "textures/ui/close_button_hover");
		$form->sendToPlayer($player);

		return $form;

	}

	public function tarakoneshnamovafaghoffline(player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function(Player $player, $data = null){
			$result = $data;
			if($result === null){
				return true;
			}
			switch($result){

				case 0:
					$this->openatmpanel($player);
					break;

				case 1:
					$player->sendMessage(C::RED . "[ATM] Player Offlined!");
					break;

			}

		});

		$form->setTitle(C::RED . "Result");
		$form->setContent(C::GRAY . "Your money is not send to this player, Player offlined!");
		$form->addButton(C::WHITE . "Retry", 0, "textures/ui/refresh_light");
		$form->addButton(C::RED . "Close Menu", 0, "textures/ui/close_button_hover");
		$form->sendToPlayer($player);

		return $form;

	}




}
