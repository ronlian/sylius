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

namespace Sylius\Bundle\CoreBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Processor\ProductCatalogPromotionsProcessorInterface;
use Sylius\Component\Core\Event\ProductUpdated;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class ProductUpdateListener
{
    private ProductRepositoryInterface $productRepository;

    private ProductCatalogPromotionsProcessorInterface $productCatalogPromotionsProcessor;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductCatalogPromotionsProcessorInterface $productCatalogPromotionsProcessor,
        EntityManagerInterface $entityManager
    ) {
        $this->productRepository = $productRepository;
        $this->productCatalogPromotionsProcessor = $productCatalogPromotionsProcessor;
        $this->entityManager = $entityManager;
    }

    public function __invoke(ProductUpdated $event): void
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $event->code]);
        if ($product === null) {
            return;
        }

        $this->productCatalogPromotionsProcessor->process($product);

        $this->entityManager->flush();
    }
}
