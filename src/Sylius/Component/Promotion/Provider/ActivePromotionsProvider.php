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

namespace Sylius\Component\Promotion\Provider;

use Sylius\Component\Promotion\Model\PromotionSubjectInterface;
use Sylius\Component\Promotion\Repository\PromotionRepositoryInterface;

final class ActivePromotionsProvider implements PreQualifiedPromotionsProviderInterface
{
    private PromotionRepositoryInterface $promotionRepository;

    public function __construct(PromotionRepositoryInterface $promotionRepository)
    {
        $this->promotionRepository = $promotionRepository;
    }

    public function getPromotions(PromotionSubjectInterface $subject): array
    {
        return $this->promotionRepository->findActive();
    }
}
