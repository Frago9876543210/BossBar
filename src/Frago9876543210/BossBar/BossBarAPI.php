<?php

declare(strict_types=1);

namespace Frago9876543210\BossBar;

use pocketmine\bossbar\BossBar;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use function spl_object_id;

class BossBarAPI extends PluginBase implements Listener{
	/** @var BossBar[][] */
	private static $bossBars = [];

	protected function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public static function addBossBar(Player $player, BossBar $bossBar) : void{
		self::$bossBars[spl_object_id($player)][spl_object_id($bossBar)] = $bossBar;
	}

	public static function removeBossBar(Player $player, BossBar $bossBar) : void{
		$playerId = spl_object_id($player);
		$bossBarId = spl_object_id($bossBar);
		if(isset(self::$bossBars[$playerId][$bossBarId])){
			unset(self::$bossBars[$playerId][$bossBarId]);
		}
	}

	/**
	 * @param PlayerQuitEvent $e
	 * @priority MONITOR
	 */
	public function onQuit(PlayerQuitEvent $e) : void{
		$player = $e->getPlayer();

		$id = spl_object_id($player);
		if(isset(self::$bossBars[$id])){
			foreach(self::$bossBars[$id] as $bossBar){
				$bossBar->removeViewer($player, false);
			}
			unset(self::$bossBars[$id]);
		}
	}
}