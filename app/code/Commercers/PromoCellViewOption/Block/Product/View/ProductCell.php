<?php

namespace Commercers\PromoCellViewOption\Block\Product\View;

use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Framework\Locale\Format;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

class ProductCell extends \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    protected $arrayUtils;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    protected $allowAttributes = [];

    /**
     * @param Template\Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param ArrayUtils $arrayUtils
     * @param PriceHelper $priceHelper
     * @param array $data
     */

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\ConfigurableProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Product $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        array $data = [],
        Format $localeFormat = null,
        Session $customerSession = null,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Variations\Prices $variationPrices = null,
        ProductRepositoryInterface $productRepository,
        PriceHelper $priceHelper
    )
    {
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $helper, $catalogProduct, $currentCustomer, $priceCurrency, $configurableAttributeData, $data, $localeFormat, $customerSession, $variationPrices);
    }

    public function getAttributeData($product) {
        $return = '';
        $attributes = $this->getAllowAttributes();
        foreach ($attributes as $attribute) {
            $return .= 'attr-' . $attribute->getAttributeId() . '="' . $product->getData($attribute->getProductAttribute()->getAttributeCode()) . '" ';
        }
        return $return;
    }

    public function getSimpleProduct()
    {
        $configurableProduct = $this->getParentProduct();
        return $configurableProduct->getTypeInstance()->getUsedProducts($configurableProduct);
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes()
    {
        if (empty($this->allowAttributes)) {
            $configurableProduct = $this->getParentProduct();
            // $_children = $configurableProduct->getTypeInstance()->getUsedProductCollection($configurableProduct);
            // echo "<pre>";
            // var_dump($_children->getData());exit;
            $this->allowAttributes = $configurableProduct->getTypeInstance()->getConfigurableAttributes($configurableProduct);
        }
        return $this->allowAttributes;
    }

    public function prepareOptions($options)
    {
        $result = [];
		$result['Normal'] = [];
        foreach ($options as $opt) {
            if ($opt['label'] && strpos($opt['label'], '|') !== false) {
                $separates = explode('|', $opt['label']);
                $opt['label'] = $separates[1];
                $result[$separates[0]][] = $opt;
            } else {
	            $result['Normal'][] = $opt;
            }
        }

		if (empty($result['Normal'])) {
			unset($result['Normal']);
		}

        return $result;
    }

    public function getParentProduct() {
        $params = $this->getRequest()->getParams();
        return $this->productRepository->getById($params['id']);
    }

    public function getAttributeValue($productId, $code) {
        return $this->productRepository->getById($productId)->getData($code);
    }

    /**
     * Decorate a plain array of arrays or objects
     *
     * @param array $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return array
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        return $this->arrayUtils->decorateArray($array, $prefix, $forceSetAll);
    }

    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    public function getAttributeValueOfProduct($product)
    {
        $allowAttrs = $this->getAllowAttributes();
        $values = [];
        foreach ($allowAttrs as $attr) {
            $values[] = $product->getCustomAttribute($attr->getProductAttribute()->getAttributeCode())->getValue();
        }
        return implode('-', $values);
    }

    public function getNewArrayAttributeOptionSortByPosition($dataConfigProductSimple){
        $newChildren = [];
        foreach ($dataConfigProductSimple as $key => $child){
            $productChild = $this->productRepository->getById($child["product_id"]);
            if(!$productChild->getPositionConfigProduct()){
                $positionConfigProductChild = 99999;
            }else{
                $positionConfigProductChild = $productChild->getPositionConfigProduct();
            }
            $newChildren[$key]["position_config_product"] = intval($positionConfigProductChild);
            $newChildren[$key]["value_index"] = $child["value_index"];
            $newChildren[$key]["label"] = $child["label"];
            $newChildren[$key]["product_super_attribute_id"] = $child["product_super_attribute_id"];
            $newChildren[$key]["default_label"] = $child["default_label"];
            $newChildren[$key]["store_label"] = $child["store_label"];
            $newChildren[$key]["use_default_value"] = $child["use_default_value"];
            $newChildren[$key]["product_id"] = $child["product_id"];
        }
        $sortArrayNewChildren = $this->sortArrayOfArray($newChildren, 'position_config_product');
        return $sortArrayNewChildren;
    }
    private function sortArrayOfArray($array, $subfield)
    {
        $sortarray = array();
        foreach ($array as $key => $row)
        {
            $sortarray[$key] = $row[$subfield];
        }
        array_multisort($sortarray, SORT_ASC, $array);
        return $array;
    }
}
