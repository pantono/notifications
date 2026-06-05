<?php

namespace Pantono\Notifications\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Pantono\Notifications\Model\WebPushSubscription;

class AbstractWebPushSubscriptionSaveEvent extends Event
{
    private WebPushSubscription $current;
    private ?WebPushSubscription $previous = null;

    public function getCurrent(): WebPushSubscription
    {
        return $this->current;
    }

    public function setCurrent(WebPushSubscription $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?WebPushSubscription
    {
        return $this->previous;
    }

    public function setPrevious(?WebPushSubscription $previous): void
    {
        $this->previous = $previous;
    }
}
