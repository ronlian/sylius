How to add a custom cart promotion action?
==========================================

Let's assume that you would like to have a cart promotion that gives **100% discount on the cheapest item in the cart**.

See what steps need to be taken to achieve that:

Create a new cart promotion action
----------------------------------

You will need a new class ``CheapestProductDiscountPromotionActionCommand``.

It will give a discount equal to the unit price of the cheapest item. That's why it needs to have the Proportional Distributor and
the Adjustments Applicator. The ``execute`` method applies the discount and distributes it properly on the totals.
This class needs also a ``isConfigurationValid()`` method which was omitted in the snippet below.

.. code-block:: php

    <?php

    namespace App\Promotion\Action;

    use Sylius\Component\Core\Promotion\Action\DiscountPromotionActionCommand;

    class CheapestProductDiscountPromotionActionCommand extends DiscountPromotionActionCommand
    {
        const TYPE = 'cheapest_item_discount';

        /**
         * @var ProportionalIntegerDistributorInterface
         */
        private $proportionalDistributor;

        /**
         * @var UnitsPromotionAdjustmentsApplicatorInterface
         */
        private $unitsPromotionAdjustmentsApplicator;

        /**
         * @param ProportionalIntegerDistributorInterface $proportionalIntegerDistributor
         * @param UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
         */
        public function __construct(
            ProportionalIntegerDistributorInterface $proportionalIntegerDistributor,
            UnitsPromotionAdjustmentsApplicatorInterface $unitsPromotionAdjustmentsApplicator
        ) {
            $this->proportionalDistributor = $proportionalIntegerDistributor;
            $this->unitsPromotionAdjustmentsApplicator = $unitsPromotionAdjustmentsApplicator;
        }

        /**
         * {@inheritdoc}
         */
        public function execute(PromotionSubjectInterface $subject, array $configuration, PromotionInterface $promotion)
        {
            if (!$subject instanceof OrderInterface) {
                throw new UnexpectedTypeException($subject, OrderInterface::class);
            }

            $items = $subject->getItems();

            $cheapestItem = $items->first();

            $itemsTotals = [];

            foreach ($items as $item) {
                $itemsTotals[] = $item->getTotal();

                $cheapestItem = ($item->getVariant()->getPrice() < $cheapestItem->getVariant()->getPrice()) ? $item : $cheapestItem;
            }

            $splitPromotion = $this->proportionalDistributor->distribute($itemsTotals, -1 * $cheapestItem->getVariant()->getPrice());
            $this->unitsPromotionAdjustmentsApplicator->apply($subject, $promotion, $splitPromotion);
        }

        /**
         * {@inheritdoc}
         */
        public function getConfigurationFormType()
        {
            return CheapestProductDiscountConfigurationType::class;
        }
    }

Prepare a configuration form type for the admin panel
-----------------------------------------------------

The new action needs a form type to be available in the admin panel, while creating a new cart promotion.

.. code-block:: php

    <?php

    namespace App\Form\Type\Action;

    use Symfony\Component\Form\AbstractType;

    class CheapestProductDiscountConfigurationType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function getBlockPrefix()
        {
            return 'app_promotion_action_cheapest_product_discount_configuration';
        }
    }

Register the action as a service
--------------------------------

In the ``config/services.yaml`` configure:

.. code-block:: yaml

    # config/services.yaml
    app.promotion_action.cheapest_product_discount:
        class: App\Promotion\Action\CheapestProductDiscountPromotionActionCommand
        arguments: ['@sylius.proportional_integer_distributor', '@sylius.promotion.units_promotion_adjustments_applicator']
        tags:
            - { name: sylius.promotion_action, type: cheapest_product_discount, form_type: App\Form\Type\Action\CheapestProductDiscountConfigurationType, label: Cheapest product discount }


Register the form type as a service
-----------------------------------

In the ``config/services.yaml`` configure:

.. code-block:: yaml

    # config/services.yaml
    app.form.type.promotion_action.cheapest_product_discount_configuration:
        class: App\Form\Type\Action\CheapestProductDiscountConfigurationType
        tags:
            - { name: form.type }

Create a new cart promotion with your action
--------------------------------------------

Go to the admin panel of your system. On the ``/admin/promotions/new`` url you can create a new promotion.

In its configuration you can choose your new "Cheapest product discount" action.

That's all. **Done!**

Learn more
----------

* :doc:`Customization Guide </customization/index>`
* :doc:`Cart Promotions Concept Documentation </book/orders/cart-promotions>`
