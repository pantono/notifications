<?php

namespace Pantono\Notifications\Model;

use Pantono\Contracts\Attributes\DatabaseTable;
use Pantono\Contracts\Application\Interfaces\SavableInterface;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\Database\OneToOne;
use Pantono\Contracts\Attributes\FieldName;

#[DatabaseTable('web_push_notification')]
class WebPushNotification implements SavableInterface, \JsonSerializable
{
    use SavableModel;

    private ?int $id = null;
    #[OneToOne(targetModel: WebPushSubscription::class), FieldName('subscription_id')]
    private ?WebPushSubscription $subscription = null;
    private \DateTimeInterface $dateCreated;
    private ?\DateTimeInterface $dateSent = null;
    private string $title;
    private string $body;
    private string $url = '/';
    private string $icon = '/icon.png';
    private string $badge = '/icon.png';
    private string $status = 'pending';
    private ?string $response = null;
    private int $attemptCount = 0;
    private ?\DateTimeInterface $lastAttemptAt = null;
    private ?\DateTimeInterface $nextAttemptAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getSubscription(): ?WebPushSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?WebPushSubscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function getDateCreated(): \DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getDateSent(): ?\DateTimeInterface
    {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeInterface $dateSent): void
    {
        $this->dateSent = $dateSent;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }

    public function getBadge(): string
    {
        return $this->badge;
    }

    public function setBadge(string $badge): void
    {
        $this->badge = $badge;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): void
    {
        $this->response = $response;
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    public function setAttemptCount(int $attemptCount): void
    {
        $this->attemptCount = $attemptCount;
    }

    public function getLastAttemptAt(): ?\DateTimeInterface
    {
        return $this->lastAttemptAt;
    }

    public function setLastAttemptAt(?\DateTimeInterface $lastAttemptAt): void
    {
        $this->lastAttemptAt = $lastAttemptAt;
    }

    public function getNextAttemptAt(): ?\DateTimeInterface
    {
        return $this->nextAttemptAt;
    }

    public function setNextAttemptAt(?\DateTimeInterface $nextAttemptAt): void
    {
        $this->nextAttemptAt = $nextAttemptAt;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'url' => $this->getUrl(),
            'icon' => $this->getIcon(),
            'badge' => $this->getBadge(),
        ];
    }
}
