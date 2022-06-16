<?php

namespace Commercers\DisplayAsParentPrice\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ListProduct;

class Data extends AbstractHelper
{

    protected $_productFactory;

    protected $_blockListProduct;

    protected $priceCurrency;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        PriceCurrencyInterface $priceCurrency,
        ListProduct $blockListProduct
    )
    {
        $this->_productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->_blockListProduct = $blockListProduct;
        parent::__construct($context);
    }

    public function getPriceDisplayAsParentPrice($configProduct){
        $_children = $configProduct->getTypeInstance()->getUsedProductCollection($configProduct);
        $price = "";
        $newChildren = [];
        foreach ($_children as $child){
            $productChild = $this->_productRepository->getById($child->getId());
            if(!$productChild->getPositionConfigProduct()){
                $positionConfigProductChild = 99999;
            }else{
                $positionConfigProductChild = $productChild->getPositionConfigProduct();
            }
            $newChildren[$productChild->getEntityId()]["entity_id"] = $productChild->getEntityId();
            $newChildren[$productChild->getEntityId()]["position_config_product"] = intval($positionConfigProductChild);
            $newChildren[$productChild->getEntityId()]["price"] = $productChild->getPrice();
        }
        $sortArrayNewChildren = $this->sortArrayOfArray($newChildren, 'position_config_product');
        $firstEleOfSortArrNewChildren = array_shift($sortArrayNewChildren);
        $price = $firstEleOfSortArrNewChildren["price"];
        if($price){
            return $this->formatPrice($price);
        }
            return $this->_blockListProduct->getProductPrice($configProduct);
    }
    public function formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat($price, true);
    }

    private function sortArrayOfArray($array, $subfield)
    {
        $sortarray = array();
        foreach ($array as $key => $row)
        {
            $sortarray[$row["entity_id"]] = $row[$subfield];
        }
        array_multisort($sortarray, SORT_ASC, $array);
        return $array;
    }

}
