<?php
/**
 * Copyright © 2017 BORN . All rights reserved.
 */
namespace Commercers\CustomConfigProduct\Observer;

use Commercers\Backend\Ui\DataProvider\Product\Form\Modifier\InformationLabels;
use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SerializedPositionProductLabels implements ObserverInterface
{
    private $_productRepository;
    /**
     * @var SourceItemsProcessorInterface
     */
    private $sourceItemsProcessor;

    const POSITION_CONFIG_PRODUCT = 'position_config_product';
    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Constructor
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SourceItemsProcessorInterface $sourceItemsProcessor,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->_productRepository = $productRepository;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->request = $request;
    }
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return;
        }

        $configurableMatrix = $this->request->getParam('configurable-matrix-serialized', "[]");
        if ($configurableMatrix != '') {
            $productsData = json_decode($configurableMatrix, true);
        //    echo "<pre>";
        //    var_dump($productsData);exit;
            foreach ($productsData as $key => $productData) {
                $productRepository = $this->_productRepository->getById($productData["id"]);
                $productRepository->setPositionConfigProduct($productData["position_config_product"]);
                $this->_productRepository->save($productRepository);
            }
        }
    }
}
