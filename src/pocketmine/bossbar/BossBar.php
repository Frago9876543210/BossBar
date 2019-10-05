<?php

declare(strict_types=1);

namespace pocketmine\bossbar;

use Frago9876543210\BossBar\BossBarAPI;
use pocketmine\entity\EntityFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\{AddActorPacket, BossEventPacket, types\entity\EntityLegacyIds};
use pocketmine\player\Player;
use function spl_object_id;

class BossBar{
	/** @var int */
	private $entityRuntimeId;
	/** @var string */
	private $title;
	/** @var float */
	private $healthPercent;
	/** @var Player[] */
	private $viewers = [];

	public function __construct(string $title = "", float $healthPercent = 1){
		if($healthPercent < 0 or $healthPercent > 1){
			throw new \InvalidArgumentException("healthPercent must be in range [0-1]");
		}
		$this->entityRuntimeId = EntityFactory::nextRuntimeId();
		$this->title = $title;
		$this->healthPercent = $healthPercent;
	}

	/**
	 * @return string
	 */
	public function getTitle() : string{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title) : void{
		$this->title = $title;

		$pk = BossEventPacket::title($this->entityRuntimeId, $this->title);
		foreach($this->viewers as $viewer){
			$viewer->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @return float
	 */
	public function getHealthPercent() : float{
		return $this->healthPercent;
	}

	/**
	 * @param float $healthPercent
	 */
	public function setHealthPercent(float $healthPercent) : void{
		if($healthPercent < 0 or $healthPercent > 1){
			throw new \InvalidArgumentException("healthPercent must be in range [0-1]");
		}
		$this->healthPercent = $healthPercent;

		$pk = BossEventPacket::healthPercent($this->entityRuntimeId, $this->healthPercent);
		foreach($this->viewers as $viewer){
			$viewer->getNetworkSession()->sendDataPacket($pk);
		}
	}

	/**
	 * @param Player $player
	 */
	public function addViewer(Player $player) : void{
		$pk = new AddActorPacket();
		$pk->entityRuntimeId = $this->entityRuntimeId;
		$pk->type = EntityLegacyIds::SLIME;
		$pk->position = new Vector3();
		$player->getNetworkSession()->sendDataPacket($pk);

		$player->getNetworkSession()->sendDataPacket(BossEventPacket::show($this->entityRuntimeId, $this->title, $this->healthPercent));

		BossBarAPI::addBossBar($player, $this);
		$this->viewers[spl_object_id($player)] = $player;
	}

	/**
	 * @param Player $player
	 * @param bool   $send
	 */
	public function removeViewer(Player $player, bool $send = true) : void{
		$id = spl_object_id($player);
		if(isset($this->viewers[$id])){
			if($send){
				BossBarAPI::removeBossBar($player, $this);
				$player->getNetworkSession()->sendDataPacket(BossEventPacket::hide($this->entityRuntimeId));
			}
			unset($this->viewers[$id]);
		}
	}
}