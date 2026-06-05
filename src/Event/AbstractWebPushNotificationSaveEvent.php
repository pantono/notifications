<?php

namespace Pantono\Notifications\Event;

use Pantono\Notifications\Model\WebPushNotification;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractWebPushNotificationSaveEvent extends Event
{
    private WebPushNotification $current;
    private ?WebPushNotification $previous = null;

    public function getCurrent(): WebPushNotification
    {
        return $this->current;
    }

    public function setCurrent(WebPushNotification $current): void
    {
        $this->current = $current;
    }

    public function getPrevious(): ?WebPushNotification
    {
        return $this->previous;
    }

    public function setPrevious(?WebPushNotification $previous): void
    {
        $this->previous = $previous;
    }
}
