<?php

namespace MCPH\UltimatePvP;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
  
  public $config;
  
  public function onEnable()
  {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getLogger()->info("UltimatePvP has been enabled.");
    $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
  }
  
  public function onDisable()
  {
    $this->getLogger()->info("UltimatePvP has been disabled.");
  }
  
  public function onDeath(PlayerDeathEvent $e)
  {
    $player = $e->getEntity();
    if($player instanceof Player) {
      $cause = $p->getLastDamageCause();
      if($cause instanceof EntityDamageByEntityEvent) {
        $light = new AddEntityPacket();
        $light->type = 93;
        $light->eid = Entity::$entityCount++;
        $light->metadata = array();
        $light->speedX = 0;
        $light->speedY = 0;
        $light->speedZ = 0;
        $light->x = $p->x;
        $light->y = $p->y;
        $light->z = $p->z;
        $player->dataPacket($light);
        
        $damagerhealth = $damager->getHealth();
        $weapon = $killer->getInventory()->getItemInHand()->getName();
        $player->sendMessage(TextFormat::RED. $damagername .TextFormat::GOLD." killed you with " .TextFormat::LIGHT_PURPLE.$killer->getHealth().TextFormat::GOLD." hearts left and while using ".TextFormat::BLUE.$killer->getInventory()->getItemInHand()->getName().TextFormat::GOLD."!");
        
        $damager = $cause->getDamager();
        if($damager instanceof Player) {
          $damagername = strtolower($damager->getName());
          $this->config->setNested($damagername . ".kills", $this->config->getNested($damagername . ".kills") + 1);
          $this->config->save(); // Important!
        }  
      }
    }  
  }
  
  public function onCommand(CommandSender $sender, Command $command, $label, array $args)
  {
    if(strtolower($command->$getName())== "kills") {
      if($sender instanceof Player) {
        if($sender->hasPermission("stats.kills")) {
          $kills = $this->config->getNested($playername . ".kills");
          $sender->sendMessage(TextFormat::GREEN . "Kills:" . TextFormat::AQUA . "$kills");
        }
        else {
          $sender->sendMessage(TextFormat::RED . "You don't have permissions to use this command.");
        }  
      }
      else {
        $sender->sendMessage(TextFormat::RED . "Please run this command in-game!");
      }  
    }
  } 
}  
