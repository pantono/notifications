<?php

declare(strict_types=1);

use Pantono\Database\Migration\Base\BasePantonoMigration;

final class WebPushMigration extends BasePantonoMigration
{
    public function change(): void
    {
        $this->table('web_push_subscription')
            ->addLinkedColumn('user_id', 'user', 'id', ['null' => true, 'signed' => false])
            ->addColumn('endpoint', 'text')
            ->addColumn('endpoint_hash', 'string', ['limit' => 64])
            ->addColumn('public_key', 'text')
            ->addColumn('auth_token', 'string')
            ->addColumn('content_encoding', 'string', ['default' => 'aes128gcm'])
            ->addColumn('enabled', 'boolean', ['default' => true])
            ->addColumn('created_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->addIndex(['endpoint_hash'], ['unique' => true])
            ->create();

        $this->table('web_push_notification')
            ->addLinkedColumn('subscription_id', 'web_push_subscription', 'id', ['signed' => false])
            ->addColumn('date_created', 'datetime')
            ->addColumn('date_sent', 'datetime', ['null' => true])
            ->addColumn('title', 'string')
            ->addColumn('body', 'text')
            ->addColumn('url', 'string', ['default' => '/'])
            ->addColumn('status', 'string', ['default' => 'pending'])
            ->addColumn('response', 'text', ['null' => true])
            ->addColumn('attempt_count', 'integer', ['default' => 0])
            ->addColumn('last_attempt_at', 'datetime', ['null' => true])
            ->addColumn('next_attempt_at', 'datetime', ['null' => true])
            ->addColumn('icon', 'string', ['default' => '/icon.png'])
            ->addColumn('badge', 'string', ['default' => '/icon.png'])
            ->addIndex('status')
            ->addIndex('next_attempt_at')
            ->create();
    }
}
