<?php

namespace Pantono\Notifications\Model;

use Pantono\Authentication\Model\User;
use Pantono\Contracts\Attributes\Database\OneToOne;
use Pantono\Contracts\Attributes\FieldName;
use Pantono\Contracts\Application\Interfaces\SavableInterface;
use Pantono\Database\Traits\SavableModel;
use Pantono\Contracts\Attributes\DatabaseTable;
use Minishlink\WebPush\Subscription;

#[DatabaseTable('web_push_subscription')]
class WebPushSubscription implements SavableInterface
{
    use SavableModel;

    private ?int $id = null;
    #[OneToOne(targetModel: User::class), FieldName('user_id')]
    private ?User $user = null;
    private string $endpoint;
    private string $endpointHash;
    private string $publicKey;
    private string $authToken;
    private string $contentEncoding;
    private bool $enabled;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    public function getEndpointHash(): string
    {
        return $this->endpointHash;
    }

    public function setEndpointHash(string $endpointHash): void
    {
        $this->endpointHash = $endpointHash;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getAuthToken(): string
    {
        return $this->authToken;
    }

    public function setAuthToken(string $authToken): void
    {
        $this->authToken = $authToken;
    }

    public function getContentEncoding(): string
    {
        return $this->contentEncoding;
    }

    public function setContentEncoding(string $contentEncoding): void
    {
        $this->contentEncoding = $contentEncoding;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function toWebPushSubscription(): Subscription
    {
        return Subscription::create([
            'endpoint' => $this->getEndpoint(),
            'publicKey' => $this->getPublicKey(),
            'authToken' => $this->getAuthToken(),
            'contentEncoding' => $this->getContentEncoding() ?: 'aes128gcm'
        ]);
    }
}
