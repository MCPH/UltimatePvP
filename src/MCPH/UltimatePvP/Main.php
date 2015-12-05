<?php

namespace MCPH\UltimatePvP;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
  
  public $config;
  public $tasks = array();
  public $interval = 10;
  private $players = array();
  
  public function onEnable()
  {
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getLogger()->info("UltimatePvP has been enabled.");
    $this->config = new Config($this->getDataFolder()."kills.yml", Config::YAML);
    $this->interval = $this->getConfig()->get("interval");
  }
  
  public function onDisable()
  {
    $this->getLogger()->info("UltimatePvP has been disabled.");
  }
  
  /**
   * @param EnityDamageEvent $event
   *
   * @priority LOW
   * @ignoreCancelled true
   */
  public function onDamage(EntityDamageEvent $event)
  {
    if($event instanceof EntityDamageByEntityEvent){
      if($event->getDamager() instanceof Player){
        foreach(array($event->getDamager(),$event->getEntity()) as $players){
          $this->setTime($players);
        }
      }
    }
  }
  
  private function setTime(Player $player)
  {
    if(isset($this->players[$player->getName()])){
      if((time() - $this->players[$player->getName()]) > $this->interval){
        $player->sendMessage(TextFormat::RED . "You are in PvP! Do not log out!");
      }
      if(isset($this->tasks[$player->getName()])){
        $this->getServer()->getScheduler()->cancelTask($this->tasks[$player->getName()]);
      }
      $this->tasks[$player->getName()] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeMsg($this, $player), 20)->getTaskId();
      }else{
        $player->sendMessage(TextFormat::RED . "You are in PvP! Do not log out!");
        $this->tasks[$player->getName()] = $this->getServer()->getScheduler()->scheduleRepeatingTask(new TimeMsg($this, $player), 20)->getTaskId();
      }
    $this->players[$player->getName()] = time();
  }
  
  public function onDeath(PlayerDeathEvent $e)
  {
    if(isset($this->players[$event->getEntity()->getName()])){
      unset($this->players[$event->getEntity()->getName()]);
      if(isset($this->tasks[$event->getEntity()->getName()])) $this->getServer()->getScheduler()->cancelTask($this->tasks[$event->getEntity()->getName()]);unset($this->tasks[$event->getEntity()->getName()]);
    }
    
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
        $player->sendMessage(TextFormat::RED. $damagername .TextFormat::GOLD." killed you with " .TextFormat::LIGHT_PURPLE. $damagerhealth .TextFormat::GOLD." hearts left and while using ".TextFormat::BLUE. $weapon .TextFormat::GOLD."!");
        
        $damager = $cause->getDamager();
        if($damager instanceof Player) {
          $damagername = strtolower($damager->getName());
          $this->config->setNested($damagername . ".kills", $this->config->getNested($damagername . ".kills") + 1);
          $this->config->save(); // Important!
        }  
      }
    }  
  }
  
  /**
   * @param PlayerQuitEvent $event
   *  
   * @priority HIGH
   * @ignoreCancelled true
   */
  public function PlayerQuitEvent(PlayerQuitEvent $event)
  {
    if(isset($this->players[$event->getPlayer()->getName()])){
      $player = $event->getPlayer();
      if((time() - $this->players[$player->getName()]) < $this->interval){
        $player->kill();
      }
    unset($this->players[$player->getName()]);
    if(isset($this->tasks[$player->getName()])) $this->getServer()->getScheduler()->cancelTask($this->tasks[$player->getName()]);unset($this->tasks[$player->getName()]);
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
