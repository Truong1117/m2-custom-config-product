<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Commercers\CustomConfigProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\Ui\Component\Form;

/**
 * Data provider for Configurable panel.
 */
class PositionAttribute extends AbstractModifier
{
    const POSITION_CONFIG_PRODUCT = 'position_config_product';
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @param LocatorInterface $locator
     * @param ProductRepository $productRepository
     */
    public function __construct(
        LocatorInterface $locator,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->locator = $locator;
        $this->_productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {

        $productId = $this->locator->getProduct()->getId();

        if (isset($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX])) {
            foreach ($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX] as $key => $productArray) {
                $product =  $this->_productRepository->get($productArray[ProductInterface::SKU]);
                $positionConfigProduct = $product->getData(self::POSITION_CONFIG_PRODUCT);
                $data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX][$key][self::POSITION_CONFIG_PRODUCT] = $positionConfigProduct;
            }
        }
        return $data;
    }

    /**
     * Composes configuration for "position_config_product_container" component.
     *
     * @return array
     */
    private function getPositionConfigProductContainerConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => Form\Field::NAME,
                        'formElement' => Form\Element\Input::NAME,
                       'component' => 'Magento_Ui/js/form/element/abstract',
                       'elementTmpl' => 'ui/form/element/input',
                        // 'component' => 'Magento_Ui/js/form/element/text',
                        // 'elementTmpl' => 'ui/dynamic-rows/cells/text',
                        'dataType' => Form\Element\DataType\Text::NAME,
                        'dataScope' => self::POSITION_CONFIG_PRODUCT,
                        'disabled' => false,
                        'label' => __('Position'),
                        'sortOrder' => 0,
                        'additionalClasses' => 'position_config_product_custom',
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children']
        [ConfigurablePanel::CONFIGURABLE_MATRIX]['children']
        ['record']['children']['position_config_product_container'] = $this->getPositionConfigProductContainerConfig();
        return $meta;
    }
}