<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\ApiBundle\Doctrine\QueryCollectionExtension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Review\Model\ReviewInterface;

/** @experimental */
final class AcceptedProductReviewsExtension implements ContextAwareQueryCollectionExtensionInterface
{
    private string $productReviewClass;

    public function __construct(string $productReviewClass)
    {
        $this->productReviewClass = $productReviewClass;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ): void {
        if ($this->productReviewClass !== $resourceClass) {
            return;
        }

        if ($operationName !== 'shop_get') {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->andWhere(sprintf('%s.status = :status', $rootAlias))
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
        ;
    }
}
