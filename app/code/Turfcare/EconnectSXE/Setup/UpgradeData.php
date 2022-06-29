<?php

namespace Turfcare\EconnectSXE\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
	/**
	 * @var Magento\Eav\Setup\EavSetupFactory
	 */
    protected $_eavSetupFactory;
	
	/**
	 * @var Magento\Eav\Model\Config
	 */
    protected $_eavConfig;

	/**
	 * @var Magento\Customer\Setup\CustomerSetupFactory
	 */
    protected $customerSetupFactory;

	/**
	 * @var Magento\Eav\Model\Entity\Attribute\SetFactory
	 */
    private $attributeSetFactory;

	/**
	 * @param EavSetupFactory $eavSetupFactory
	 * @param Magento\Eav\Model\Config $config
	 * @param CustomerSetupFactory $customerSetupFactory
	 * @param AttributeSetFactory $attributeSetFactory
	 */
    public function __construct(
		EavSetupFactory $eavSetupFactory, 
		\Magento\Eav\Model\Config $config, 
		CustomerSetupFactory $customerSetupFactory,
		AttributeSetFactory $attributeSetFactory
	)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_eavConfig = $config;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //customer attribute creation
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute('customer_address', 'delivery_method', [
            'type' => 'varchar',
            'label' => 'Delivery Method',
            'input' => 'text',
            'required' => false,
            'position' => 140,
            'system' => false,
            'visible' => true,
        ]);

        $addressAttr = 'delivery_method';

        $attributeAdid = $customerSetup->getEavConfig()->getAttribute('customer_address', $addressAttr)
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer_address'],
            ]);

        $attributeAdid->save();
    }
}