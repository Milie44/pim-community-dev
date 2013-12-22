<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;

/**
 * Attribute manager
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeManager implements ProductAttributeManagerInterface
{
    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var AttributeTypeFactory
     */
    protected $factory;

    /**
     * Constructor
     *
     * @param string               $attributeClass Attribute class
     * @param string               $productClass   Product class
     * @param ProductManager       $productManager Product manager
     * @param LocaleManager        $localeManager  Locale manager
     * @param AttributeTypeFactory $factory        Attribute type factory
     */
    public function __construct(
        $attributeClass,
        $productClass,
        ProductManager $productManager,
        LocaleManager $localeManager,
        AttributeTypeFactory $factory
    ) {
        $this->attributeClass = $attributeClass;
        $this->productClass   = $productClass;
        $this->productManager = $productManager;
        $this->localeManager  = $localeManager;
        $this->factory        = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttribute($type = null)
    {
        return $this->productManager->createAttribute($type);
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeOption()
    {
        return $this->productManager->createAttributeOption();
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeOptionValue()
    {
        return $this->productManager->createAttributeOptionValue();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeClass()
    {
        return $this->attributeClass;
    }

    /**
     * {@inheritdoc}
     */
    public function createAttributeFromFormData($data)
    {
        if ($data instanceof ProductAttributeInterface) {
            return $data;
        }

        if (gettype($data) === 'array' && isset($data['attributeType'])) {
            return $this->productManager->createAttribute($data['attributeType']);
        } elseif (gettype($data) === 'array' && isset($data['id'])) {
            return $this->productManager->getAttributeRepository()->find($data['id']);
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareFormData($data)
    {
        $optionTypes = array(
            'pim_catalog_multiselect',
            'pim_catalog_simpleselect'
        );

        // If the attribute type can have options but no options have been created,
        // create an empty option to render the corresponding form fields
        if (in_array($data['attributeType'], $optionTypes) && !isset($data['options'])) {
            $option = array(
                'optionValues' => array()
            );

            foreach ($this->localeManager->getActiveLocales() as $locale) {
                $option['optionValues'][] = array(
                    'locale' => $locale->getCode()
                );
            }

            $data['options'] = array($option);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypes()
    {
        $types = $this->factory->getAttributeTypes($this->productClass);
        $choices = array();
        foreach ($types as $type) {
            $choices[$type] = $type;
        }
        asort($choices);

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareBackendProperties(ProductAttributeInterface $attribute)
    {
        $baseAttribute = $this->productManager->createAttribute($attribute->getAttributeType());

        $attribute->setBackendType($baseAttribute->getBackendType());
        $attribute->setBackendStorage($baseAttribute->getBackendStorage());

        return $attribute;
    }
}
