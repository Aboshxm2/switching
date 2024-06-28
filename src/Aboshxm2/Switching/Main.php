<?php

declare(strict_types=1);

namespace Aboshxm2\Switching;

use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener
{
    private array $entityTicks = [];

    protected function onEnable(): void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * @priority HIGHEST
     */
    public function onDamage(EntityDamageByEntityEvent $event): void
    {
        $living = $event->getEntity();
        if(!$living instanceof Living) return;

        $ticks = $living->ticksLived;
        $cooldown = ($this->getConfig()->get('attack-cooldown', -1) >= 0) ? $this->getConfig()->get('attack-cooldown') : $event->getAttackCooldown();

        if(
            isset($this->entityTicks[$living->getId()]) &&
            ($ticks - $this->entityTicks[$living->getId()]) < $cooldown
        ) {
            if($living->getLastDamageCause() !== null && $living->getLastDamageCause()->getBaseDamage() >= $event->getBaseDamage()) {
                $event->cancel();
            }
        }else {
            $this->entityTicks[$living->getId()] = $ticks;
        }

        $event->setAttackCooldown(0);
    }
}