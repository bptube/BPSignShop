<?php

namespace bpsys\BPSignShop;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\utils\Config;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as col;

// BPSignShop is a creation of BPsysGamer
// Wiki: https://github.com/bptube/BPSignShop/wiki
// Thanks for using this pluggins

class Main extends PluginBase implements Listener{

    private $config;

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->notice(col::GREEN." BPSignShop is working correctly ");
        $this->api = EconomyAPI::getInstance();
        @mkdir($this->getDataFolder());
        @mkdir($this->getDataFolder()."bpbuy");
        @mkdir($this->getDataFolder()."bpsell");
        @mkdir($this->getDataFolder()."bprandom");
        @mkdir($this->getDataFolder()."bpfree");
     }
    
 public function onCommand(CommandSender $sender,Command $cmd, string $label, array $args) : bool{
        if ($cmd->getName() === "bphelp"){
        	if(!$sender instanceof Player){
            $sender->sendMessage(col::GREEN . "To know how it works visit: https://github.com/bptube/BPSignShop/wiki" ); 
			return true;
		     }else{
			$player = $sender->getPlayer();
        	if($player->hasPermission("bpsignshop.bphelp")){
            $sender->sendMessage(col::GREEN . "To know how it works visit: https://github.com/bptube/BPSignShop/wiki");
            }else{
            $sender->sendMessage(col::RED . "You do not have permission to use this command");
           }
          }
        }
        if ($cmd->getName() === "bpitem"){
        	if(!$sender instanceof Player){
            $sender->sendMessage(col::GREEN . "This command only works in the game" ); 
			return true;
		     }else{
			$player = $sender->getPlayer();
        	if($player->hasPermission("bpsignshop.bpitem")){
        	$item = $sender->getInventory()->getItemInHand();
            $name = $item->getName();
            $iid = $item ->getID();
            $sender->sendMessage(col::BOLD.col::BLUE."************************");
            $sender->sendMessage(col::RED . "Information: ");
            $sender->sendMessage(col::RED . "Name: ".col::BOLD.$name);
            $sender->sendMessage(col::RED . "id: ".col::BOLD.$iid);
            $sender->sendMessage(col::BOLD.col::BLUE."************************");
            }else{
            $sender->sendMessage(col::RED . "You do not have permission to use this command");
           }
          }
        }
        return true;
    }
    
    public function onSign(SignChangeEvent $event){
        $player = $event->getPlayer();
        if($player->hasPermission("bpsignshop.createshop")){
            switch($event->getLine(0)){
            	case "[BPRD]":
                    if(is_numeric((int) $event->getLine(1))){
                        $level = $event->getBlock()->getLevel()->getName();
                        $xx = $event->getBlock()->getX();
                        $yy = $event->getBlock()->getY();
                        $zz = $event->getBlock()->getZ();
                        $x = (int) $xx;
                        $y = (int) $yy;
                        $z = (int) $zz;
                        if($event->getLine(1) >= 1){
                            $pos = $level.$x.$y.$z;
                             
                             $shopcfg = new Config($this->getDataFolder() . "bprandom/".$pos.".yml", Config::YAML);
                             $shopcfg->set($pos, ["iID1"=>"0", "iM1"=>"0", "iID2" => "0", "iM2" => "0", "iID3"=>"0", "iM3"=>"0", "iID4" => "0", "iM4" => "0", "iID5"=>"0", "iM5"=>"0", "price" =>$event->getLine(1)]);
                             $shopcfg->save();
                                                                                                   
                            
                            $event->setLine(3, "$".$event->getLine(1));
                            $event->setLine(1, col::BOLD."try your");
                            $event->setLine(2, col::BOLD."luck");
                           
                            $player->sendMessage(col::GREEN."Shop created!");
                            break;
                        } }
                 case "[BPFree]":
                    if(is_numeric((int) $event->getLine(1))){
                        $level = $event->getBlock()->getLevel()->getName();
                        $xx = $event->getBlock()->getX();
                        $yy = $event->getBlock()->getY();
                        $zz = $event->getBlock()->getZ();
                        $x = (int) $xx;
                        $y = (int) $yy;
                        $z = (int) $zz;
                        $id = $event->getLine(1);
                        if(strpos($id, ":") !== false){
                            $item = explode(':', $id);
                        }else{
                            $item = array($id, "0");
                        }
                        if($event->getLine(1) >= 1 && $event->getLine(2) >= 1){
                          $itemName = Item::get($item[0], $item[1])->getName();
                            if($itemName === "Unknown" || $itemName === "Air"){
                            	$event->setLine(0, col::BOLD.col::RED."ERROR");
                                $event->setLine(1, col::RED."incorrect id");
                                $player->sendMessage(col::RED."The store was not created");
                                break;
                             } else {
                          
                            $pos = $level.$x.$y.$z;

                            $shopcfg = new Config($this->getDataFolder() . "bpfree/".$pos.".yml", Config::YAML);
                            $shopcfg->set($pos, ["itemID"=>$item[0], "itemMeta"=>$item[1], "amount"=>$event->getLine(2)]);
                            $shopcfg->save();
                            
                            $num = strlen($itemName);
                            if($num >= 13){
                                $itemName = str_replace(" ", "", $itemName);
                            }
                            $num = strlen($itemName);
                            while($num >= 13){
                                $lastLetter = substr($itemName, -1);
                                $itemName = str_replace($lastLetter, "", $itemName);
                                $num = $num - 1;
                            }
                           
                            $event->setLine(1, col::BOLD."Get Free");
                            $event->setLine(2, col::BOLD.$itemName);
                           
                            $player->sendMessage(col::GREEN."Shop created!");
                            break;
                        } }}
                case "[BPBuy]":
                    if(is_numeric((int) $event->getLine(1)) && is_numeric((int) $event->getLine(2)) && is_numeric((int) $event->getLine(3))){
                        $level = $event->getBlock()->getLevel()->getName();
                        $xx = $event->getBlock()->getX();
                        $yy = $event->getBlock()->getY();
                        $zz = $event->getBlock()->getZ();
                        $x = (int) $xx;
                        $y = (int) $yy;
                        $z = (int) $zz;
                        $id = $event->getLine(1);
                        if(strpos($id, ":") !== false){
                            $item = explode(':', $id);
                        }else{
                            $item = array($id, "0");
                        }
                        if($event->getLine(2) >= 1 && $event->getLine(3) > 0){
                        	$itemName = Item::get($item[0], $item[1])->getName();
                             
                             if($itemName === "Unknown" || $itemName === "Air"){
                            	$event->setLine(0, col::BOLD.col::RED."ERROR");
                                $event->setLine(1, col::RED."incorrect id");
                                $player->sendMessage(col::RED."The store was not created");
                                break;
                             }else{
                             
                            $pos = $level.$x.$y.$z;

                            $shopcfg = new Config($this->getDataFolder() . "bpbuy/".$pos.".yml", Config::YAML);
                            $shopcfg->set($pos, ["itemID"=>$item[0], "itemMeta"=>$item[1], "amount"=>$event->getLine(2), "price"=>$event->getLine(3)]);
                            $shopcfg->save();

                            $num = strlen($itemName);
                            if($num >= 13){
                                $itemName = str_replace(" ", "", $itemName);
                            }
                            $num = strlen($itemName);
                            while($num >= 13){
                                $lastLetter = substr($itemName, -1);
                                $itemName = str_replace($lastLetter, "", $itemName);
                                $num = $num - 1;
                            }
                            
                            $event->setLine(1, col::BOLD.$itemName);
                            $event->setLine(3, "$".$event->getLine(3));
                            $player->sendMessage(col::GREEN."Shop created!");
                            break;
                        }
                    }else{
                        break;
                    }}
                case "[BPSell]":
                    if(is_numeric((int) $event->getLine(1)) && is_numeric((int) $event->getLine(2)) && is_numeric((int) $event->getLine(3))){
                        $level = $event->getBlock()->getLevel()->getName();
                        $xx = $event->getBlock()->getX();
                        $yy = $event->getBlock()->getY();
                        $zz = $event->getBlock()->getZ();
                        $x = (int) $xx;
                        $y = (int) $yy;
                        $z = (int) $zz;
                        $id = $event->getLine(1);
                        if(strpos($id, ":") !== false){
                            $item = explode(':', $id);
                        }else{
                            $item = array($id, "0");
                        }
                        if($event->getLine(2) >= 1 && $event->getLine(3) > 0){
                        	$itemName = Item::get($item[0], $item[1])->getName();
                             
                             if($itemName === "Unknown" || $itemName === "Air"){
                            	$event->setLine(0, col::BOLD.col::RED."ERROR");
                                $event->setLine(1, col::RED."incorrect id");
                                $player->sendMessage(col::RED."The store was not created");
                                break;
                             }else{
                            $pos = $level.$x.$y.$z;

                           $shopcfg= new Config($this->getDataFolder() . "bpsell/".$pos.".yml", Config::YAML);
                            $shopcfg->set($pos, ["itemID"=>$item[0], "itemMeta"=>$item[1], "amount"=>$event->getLine(2), "price"=>$event->getLine(3)]);
                            $shopcfg->save();
                            
                            $num = strlen($itemName);
                            if($num >= 13){
                                $itemName = str_replace(" ", "", $itemName);
                            }
                            $num = strlen($itemName);
                            while($num >= 13){
                                $lastLetter = substr($itemName, -1);
                                $itemName = str_replace($lastLetter, "", $itemName);
                                $num = $num - 1;
                            }
                           
                            $event->setLine(1, col::BOLD.$itemName);
                            $event->setLine(3, "$".$event->getLine(3));
                            $player->sendMessage(col::GREEN."Shop created!");
                            break;
                        } 
                    }}
                }
            }
        }
        public function onTouch(PlayerInteractEvent $event){
        	  
        if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
        	
        $level = $event->getBlock()->getLevel()->getName();
        $xx = $event->getBlock()->getX();
        $yy = $event->getBlock()->getY();
        $zz = $event->getBlock()->getZ();
        $x = (int) $xx;
        $y = (int) $yy;
        $z = (int) $zz;
        $pos = $level.$x.$y.$z;
        
        $fbuy = is_file($this->getDataFolder() . "bpbuy/" .$pos.".yml");
        $fsell = is_file($this->getDataFolder() . "bpsell/" .$pos.".yml");
        $frand = is_file($this->getDataFolder() . "bprandom/" .$pos.".yml");
        $ffree = is_file($this->getDataFolder() . "bpfree/" .$pos.".yml");
       
        if($fbuy === true){
        $config = new Config($this->getDataFolder() . "bpbuy/" .$pos.".yml", Config::YAML);
        $info = $config->get($pos);
        $id = $info["itemID"];
            $meta = $info["itemMeta"];
            $amount = $info["amount"];
            $price = $info["price"];
            $name = Item::get($id, $meta)->getName();
            $player = $event->getPlayer();
             
             if (EconomyAPI::getInstance()->myMoney($player) >= $price) {
             	EconomyAPI::getInstance()->reduceMoney($player->getName(), $price);
                $player->getInventory()->addItem(Item::get($id, $meta, $amount));
                $player->sendMessage(col::GREEN."Successfully bought ".col::YELLOW.$amount." ".$name.col::GREEN." for ".col::YELLOW."$".$price);
            }else{
                $player->sendMessage(col::RED."You don't have enough money to buy ".$name);
            }
        }elseif($fsell === true){
        $config = new Config($this->getDataFolder() . "bpsell/" .$pos.".yml", Config::YAML);
        $info = $config->get($pos);
        $id = $info["itemID"];
            $meta = $info["itemMeta"];
            $amount = $info["amount"];
            $price = $info["price"];
            $name = Item::get($id, $meta)->getName();
            $player = $event->getPlayer();
            $inventory = $player->getInventory();
            if($inventory->contains(Item::get($id, $meta, $amount))){
                $player->getInventory()->removeItem(Item::get($id, $meta, $amount));
                EconomyAPI::getInstance()->addMoney($player, $price);
                $player->sendMessage(col::GREEN."Successfully sold ".col::YELLOW.$amount." ".$name.col::GREEN." for ".col::YELLOW."$".$price);
            }else{
                $player->sendMessage(col::RED."You don't have enough ".$name);
            } 
         }elseif($frand === true){
        $config = new Config($this->getDataFolder() . "bprandom/" .$pos.".yml", Config::YAML);
        $info = $config->get($pos);
             $randitem = rand(1, 5);
             $randamount = rand(0, 64);
             if($randitem === 1) {
             	$id = $info["iID1"];
                 $meta = $info["iM1"];
              }elseif($randitem === 2){
              	$id = $info["iID2"];
                 $meta = $info["iM2"];
              }elseif($randitem === 3){
              	$id = $info["iID3"];
                 $meta = $info["iM3"];
             }elseif($randitem === 4){
              	$id = $info["iID4"];
                 $meta = $info["iM4"];
             }elseif($randitem === 5){
              	$id = $info["iID5"];
                 $meta = $info["iM5"];
             }
            $price = $info["price"];
            $name = Item::get($id, $meta)->getName();
            $player = $event->getPlayer();
            if (EconomyAPI::getInstance()->myMoney($player) >= $price) {
             	EconomyAPI::getInstance()->reduceMoney($player->getName(), $price);
                $player->getInventory()->addItem(Item::get($id, $meta, $randamount));
                $player->sendMessage(col::GREEN."Successfully bought ".col::YELLOW.$randamount." ".$name.col::GREEN." for ".col::YELLOW."$".$price);
            }else{
                $player->sendMessage(col::RED."You don't have enough money to buy ".$name);
            }
        }elseif($ffree === true){
        $config = new Config($this->getDataFolder() . "bpfree/" .$pos.".yml", Config::YAML);
        $info = $config->get($pos);
        $id = $info["itemID"];
            $meta = $info["itemMeta"];
            $amount = $info["amount"];
            $name = Item::get($id, $meta)->getName();
            $player = $event->getPlayer();
          
            $player->getInventory()->addItem(Item::get($id, $meta, $amount));
            $player->sendMessage(col::GREEN."They have given you ".col::YELLOW.$amount." ".$name);
        }
        
        } 
    }
    
    public function onBreak(BlockBreakEvent $event){
    	if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
        $player = $event->getPlayer();
        $level = $event->getBlock()->getLevel()->getName();
        $xx = $event->getBlock()->getX();
        $yy = $event->getBlock()->getY();
        $zz = $event->getBlock()->getZ();
        $x = (int) $xx;
        $y = (int) $yy;
        $z = (int) $zz;
        $pos = $level.$x.$y.$z;

        $fbuy = is_file($this->getDataFolder() . "bpbuy/" .$pos.".yml");
        $fsell = is_file($this->getDataFolder() . "bpsell/" .$pos.".yml");
        $frand = is_file($this->getDataFolder() . "bprandom/" .$pos.".yml");
        $ffree = is_file($this->getDataFolder() . "bpfree/" .$pos.".yml");

        if($fbuy === true){
            if($player->hasPermission("bpsignshop.deleteshop")){
                @unlink($this->getDataFolder() . "bpbuy/" .$pos.".yml");
                $player->sendMessage(col::GREEN."Shop deleted!");
            }else{
                $event->setCancelled();
                $player->sendMessage(col::RED."You are not allowed to delete shops.");
            }
        }elseif($frand === true){
            if($player->hasPermission("bpsignshop.deleteshop")){
               @unlink($this->getDataFolder() . "bprandom/" .$pos.".yml");
                $player->sendMessage(col::GREEN."Shop deleted!");
            }else{
                $event->setCancelled();
                $player->sendMessage(col::RED."You are not allowed to delete shops.");
            }
         }elseif($ffree === true){
            if($player->hasPermission("bpsignshop.deleteshop")){
             @unlink($this->getDataFolder() . "bpfree/" .$pos.".yml");
                $player->sendMessage(col::GREEN."Shop deleted!");
            }else{
                $event->setCancelled();
                $player->sendMessage(col::RED."You are not allowed to delete shops.");
            }
        }elseif($fsell === true){
            if($player->hasPermission("bpsignshop.deleteshop")){
                @unlink($this->getDataFolder() . "bpsell/" .$pos.".yml");
                $player->sendMessage(col::GREEN."Shop deleted!");
            }else{
                $event->setCancelled();
                $player->sendMessage(col::RED."You are not allowed to delete shops.");
            }}
        }
    }
}