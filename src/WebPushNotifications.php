<?php

namespace Pantono\Notifications;

use Pantono\Notifications\Repository\WebPushNotificationRepository;
use Pantono\Contracts\Config\ConfigInterface;
use Pantono\Core\Application\Exception\ApiException;
use Pantono\Notifications\Filter\WebPushNotificationFilter;
use Minishlink\WebPush\WebPush;
use Pantono\Hydrator\Hydrator;
use Pantono\Notifications\Model\WebPushSubscription;
use Pantono\Notifications\Model\WebPushNotification;
use Pantono\Notifications\Event\PreWebPushSubscriptionSaveEvent;
use Pantono\Notifications\Event\PostWebPushSubscriptionSaveEvent;
use League\Container\Event\EventDispatcher;
use Pantono\Notifications\Event\PreWebPushNotificationSaveEvent;
use Pantono\Notifications\Event\PostWebPushNotificationSaveEvent;
use Pantono\Authentication\Model\User;

class WebPushNotifications
{
    private const MAX_DELIVERY_ATTEMPTS = 3;
    private const RETRY_DELAY_SECONDS = 300;

    private WebPushNotificationRepository $repository;
    private Hydrator $hydrator;
    private ConfigInterface $config;
    private EventDispatcher $dispatcher;

    public function __construct(WebPushNotificationRepository $repository, Hydrator $hydrator, ConfigInterface $config, EventDispatcher $dispatcher)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param WebPushNotificationFilter $filter
     * @return WebPushSubscription[]
     */
    public function getSubscriptionsByFilter(WebPushNotificationFilter $filter): array
    {
        return $this->hydrator->hydrateSet(WebPushSubscription::class, $this->repository->getSubscriptionsByFilter($filter));
    }

    public function getSubscriptionById(int $id): ?WebPushSubscription
    {
        return $this->hydrator->lookupRecord(WebPushSubscription::class, $id);
    }

    public function getSubscriptionByEndpointHash(string $hash): ?WebPushSubscription
    {
        return $this->hydrator->hydrate(WebPushSubscription::class, $this->repository->getSubscriptionByEndpointHash($hash));
    }

    public function createOrUpdateSubscription(string $endpoint, string $publicKey, string $auth, ?User $user = null, string $contentEncoding = 'aes128gcm'): WebPushSubscription
    {
        $hash = hash('sha256', $endpoint);
        $sub = $this->getSubscriptionByEndpointHash($hash);
        if (!$sub) {
            $sub = new WebPushSubscription();
            $sub->setCreatedAt(new \DateTime);
        }
        $sub->setUpdatedAt(new \DateTime);
        $sub->setEnabled(true);
        $sub->setEndpoint($endpoint);
        $sub->setPublicKey($publicKey);
        $sub->setEndpointHash($hash);
        $sub->setAuthToken($auth);
        $sub->setContentEncoding($contentEncoding);
        if ($user) {
            $sub->setUser($user);
        }
        $this->saveSubscription($sub);
        return $sub;
    }

    public function sendNotificationToFilter(WebPushNotificationFilter $filter, string $title, string $body, string $url = '/', string $icon = '/icon.png', string $badge = '/icon.png'): void
    {
        foreach ($this->getSubscriptionsByFilter($filter) as $subscription) {
            $this->sendNotificationToSubscription($subscription, $title, $body, $url, $icon, $badge);
        }
    }

    public function sendNotificationToUserId(int $id, string $title, string $body, string $url = '/'): void
    {
        $filter = new WebPushNotificationFilter();
        $filter->setUserId($id);
        $this->sendNotificationToFilter($filter, $title, $body, $url);
    }

    public function sendNotificationToSubscription(WebPushSubscription $subscription, string $title, string $body, string $url = '/', string $icon = '/icon.png', string $badge = '/icon.png'): void
    {
        $notification = new WebPushNotification();
        $notification->setDateCreated(new \DateTime);
        $notification->setStatus('pending');
        $notification->setSubscription($subscription);
        $notification->setBody($body);
        $notification->setTitle($title);
        $notification->setUrl($url);
        $notification->setIcon($icon);
        $notification->setBadge($badge);
        $this->saveNotification($notification);
    }

    public function doSendNotification(WebPushNotification $notification): void
    {
        $this->recordAttempt($notification);

        if (!$notification->getSubscription()) {
            $this->markNotificationFailed($notification, 'Notification has no subscription');
            $this->saveNotification($notification);
            return;
        }

        $auth = $this->getConfig();
        $webPush = new WebPush([
            'VAPID' => $auth
        ], ['TTL' => 3600, 'urgency' => 'normal']);
        $webPush->setReuseVAPIDHeaders(true);

        try {
            $payload = json_encode($notification, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->markNotificationFailed($notification, 'Unable to encode notification payload: ' . $exception->getMessage());
            $this->saveNotification($notification);
            return;
        }

        $result = $webPush->sendOneNotification($notification->getSubscription()->toWebPushSubscription(), $payload);
        if ($result->isSuccess()) {
            $notification->setDateSent(new \DateTime);
            $notification->setNextAttemptAt(null);
        }
        $notification->setResponse($result->getReason());
        if ($result->isSubscriptionExpired()) {
            $notification->getSubscription()->setEnabled(false);
            $this->saveSubscription($notification->getSubscription());
        }
        if ($result->isSuccess()) {
            $notification->setStatus('sent');
        } elseif ($result->isSubscriptionExpired() || $notification->getAttemptCount() >= self::MAX_DELIVERY_ATTEMPTS) {
            $notification->setStatus('failed');
            $notification->setNextAttemptAt(null);
        } else {
            $notification->setStatus('retry');
            $notification->setNextAttemptAt((new \DateTimeImmutable())->modify('+' . self::RETRY_DELAY_SECONDS . ' seconds'));
        }
        $this->saveNotification($notification);
    }

    private function recordAttempt(WebPushNotification $notification): void
    {
        $notification->setAttemptCount($notification->getAttemptCount() + 1);
        $notification->setLastAttemptAt(new \DateTimeImmutable());
    }

    private function markNotificationFailed(WebPushNotification $notification, string $response): void
    {
        $notification->setStatus('failed');
        $notification->setResponse($response);
        $notification->setNextAttemptAt(null);
    }

    public function saveSubscription(WebPushSubscription $subscription): void
    {
        $previous = $subscription->getId() ? $this->hydrator->lookupRecord(WebPushSubscription::class, $subscription->getId()) : null;
        $event = new PreWebPushSubscriptionSaveEvent();
        $event->setCurrent($subscription);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);

        $this->repository->saveModel($subscription);

        $event = new PostWebPushSubscriptionSaveEvent();
        $event->setCurrent($subscription);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);
    }

    public function saveNotification(WebPushNotification $notification): void
    {
        $previous = $notification->getId() ? $this->hydrator->lookupRecord(WebPushNotification::class, $notification->getId()) : null;
        $event = new PreWebPushNotificationSaveEvent();
        $event->setCurrent($notification);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);

        $this->repository->saveModel($notification);

        $event = new PostWebPushNotificationSaveEvent();
        $event->setCurrent($notification);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);
    }

    public function getPublicKey(): string
    {
        return $this->getConfig()['publicKey'];
    }

    /**
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        $config = $this->config->getApplicationConfig()->getValue('web_push.vapid');
        if (!$config || !is_array($config)) {
            throw new \RuntimeException('VAPID configuration is missing or invalid');
        }

        $subject = $config['subject'] ?? '';
        $publicKey = $config['public_key'] ?? '';
        $privateKey = $config['private_key'] ?? '';
        if (!$subject || !$publicKey || !$privateKey) {
            throw new ApiException('PWA VAPID keys are not configured');
        }

        return [
            'subject' => $subject,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey,
        ];
    }
}
