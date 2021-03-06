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
use Lexik\Bundle\JWTAuthenticationBundle\Exception\MissingTokenException;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\AdminUserInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @experimental */
final class AddressesExtension implements ContextAwareQueryCollectionExtensionInterface
{
    private UserContextInterface $userContext;

    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ): void {
        if (!is_a($resourceClass, AddressInterface::class, true)) {
            return;
        }

        $user = $this->userContext->getUser();
        if ($user === null) {
            throw new MissingTokenException('JWT Token not found');
        }

        if ($user instanceof ShopUserInterface && in_array('ROLE_USER', $user->getRoles(), true)) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder
                ->innerJoin(sprintf('%s.customer', $rootAlias), 'customer')
                ->andWhere(sprintf('%s.customer = :customer', $rootAlias))
                ->setParameter('customer', $user->getCustomer())
            ;

            return;
        }

        if ($user instanceof AdminUserInterface && in_array('ROLE_API_ACCESS', $user->getRoles(), true)) {
            return;
        }

        throw new AccessDeniedHttpException('Requested method is not allowed.');
    }
}
