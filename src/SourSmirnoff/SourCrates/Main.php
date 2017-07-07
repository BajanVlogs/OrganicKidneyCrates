<?php

namespace SourSmirnoff\SourCrates;

use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\Player;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	public function onEnable(){
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getLogger()->info("§aSourCrates by SourSmirnoff was loaded!");
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(strtolower($command->getName()) == "cratekey"){
			if($sender->hasPermission("cratekey") || $sender->hasPermission("cratekey.give")){
				if($this->getConfig()->get("PlayerGive") == false && $sender instanceof Player){
					$sender->sendMessage("§cYour not aloud to do that on SourServers");
					return true;
				}else{
					if(isset($args[0])){
						$player = $this->getServer()->getPlayer($args[0]);
						$keyid = $this->getConfig()->get("KeyID:");
						if($player instanceof Player){
							$name = $player->getName();
							$item = Item::get($keyid);
							$player->getInventory()->addItem($item);
							$commands = $this->getConfig()->get("Commands");
							foreach($commands as $i){
								$this->getServer()->dispatchCommand(new ConsoleCommandSender, str_replace(array("{PLAYER}", "{NAME}"), $player, $player->getName()));
							}
							$sender->sendMessage("§aGave ".$player->getName()." a CrateKey");
							if($sender instanceof Player){
								$this->getLogger()->info("§a" . $sender->getName()." gave ".$name." a CrateKey");
							}
							return true;
						}else{
							$sender->sendMessage("§cThat player isn't online!");
							return true;
						}
					}else{
						$sender->sendMessage("§cYou need to specify a player!");
						return false;
					}
				}
			}else{
				$sender->sendMessage("§cYour not aloud to do that on SourServers");
				return true;
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event){
		$prefix = $this->getConfig()->get("Prefix");
		$player = $event->getPlayer();
		$heldItem = $player->getInventory()->getItemInHand();
		$block = $event->getBlock();
		$level = $block->getLevel()->getName();
		$keyid = $this->getConfig()->get("KeyID");
		if($block->getId() === 54 || $block->getId() === 146) {
			if($block->getX() === $this->getConfig()->get("X") && $block->getY() === $this->getConfig()->get("Y") && $block->getZ() === $this->getConfig()->get("Z") && $level === $this->getConfig()->get("World")){
				if($heldItem->getId() === $keyid){
					$event->setCancelled();
					$player->getInventory()->removeItem(Item::get($keyid, 0, 1));
					for($i = 1; $i <= $this->getConfig()->get("Number"); $i++){
						$rand = $this->getConfig()->get("Items");
						$random = explode(":",$rand[mt_rand(0, count($rand) - 1)]);
						$player->getInventory()->addItem(Item::get($random[0], $random[1], $random[2]));
					}
					$player->sendMessage($prefix . "You opened the SourCrate and got rewards!");
				}else{
					$event->setCancelled();
					$player->sendMessage($prefix . "You need to be holding a CrateKey to get CrateChest rewards!");
				}
			}
		}
	}
}
