<?php

namespace Pantono\Notifications\Repository;

use Pantono\Database\Repository\DefaultRepository;
use Pantono\Notifications\Filter\WebPushNotificationFilter;

class WebPushNotificationRepository extends DefaultRepository
{
    /**
     * @param WebPushNotificationFilter $filter
     * @return array<int,mixed>
     */
    public function getSubscriptionsByFilter(WebPushNotificationFilter $filter): array
    {
        $select = $this->getDb()->select('p.*')->from('web_push_subscription', 'p');

        if ($filter->getUserId()) {
            $select->andWhere('p.user_id = :id')
                ->setParameter('id', $filter->getUserId());
        }
        if ($filter->getEnabled() !== null) {
            $select->andWhere('p.enabled = :enabled')
                ->setParameter('enabled', $filter->getEnabled() ? 1 : 0);
        }

        $this->applyCountAndLimit($select, $filter);

        return $this->getDb()->fetchAll($select);
    }

    /**
     * @return array<int,mixed>
     */
    public function getSubscriptionByEndpointHash(string $hash): ?array
    {
        return $this->selectSingleRow('web_push_subscription', 'endpoint_hash', $hash);
    }
}
