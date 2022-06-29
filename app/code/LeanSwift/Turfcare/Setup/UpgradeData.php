<?php

namespace LeanSwift\Turfcare\Setup;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $catalogSetupFactory;

    /**
     * Customer setup factory
     *
     * @var Magento\Customer\Setup\CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory,CustomerSetupFactory $customerSetupFactory,AttributeSetFactory $attributeSetFactory)
    {
        $this->catalogSetupFactory = $categorySetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
	
	    /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
	    $catalogSetup = $this->catalogSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '2.3.1') < 0) {
            $catalogSetup->addAttribute(Category::ENTITY, 'is_active_for_logged_in_users', [
                'type' => 'int',
                'label' => 'Display only for logged in users',
                'input' => 'select',
                'required' => false,
                'sort_order' => 16,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'wysiwyg_enabled' => false,
                'is_html_allowed_on_front' => false,
                'group' => 'General Information',
                'default' => 0,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'visible' => true,
            ]);
        }

        //customer attribute creation
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        if (version_compare($context->getVersion(), '2.3.2') < 0) {
            $customerSetup->addAttribute(
                Customer::ENTITY, 'erp_customer_code', [
                    'type' => 'varchar',
                    'label' => 'Erp Customer Code',
                    'input' => 'text',
                    'required' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'position' => 150,
                    'system' => false,
                    'visible' => true,
                ]
            );

            $attributeErpCustomerCode = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY,
                'erp_customer_code')
                ->addData(
                    [
                        'attribute_set_id' => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms' => ['adminhtml_customer'],
                    ]
                );

            $attributeErpCustomerCode->save();
        }

        if (version_compare($context->getVersion(), '2.3.3') < 0) {
            $customerSetup->addAttribute(
                Customer::ENTITY, 'ship_via', [
                    'type' => 'varchar',
                    'label' => 'Ship Via',
                    'input' => 'text',
                    'required' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'position' => 150,
                    'system' => false,
                    'visible' => true,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.3.4') < 0) {
            $customerSetup->addAttribute(
                Customer::ENTITY, 'ship_via', [
                    'type' => 'varchar',
                    'label' => 'Ship Via',
                    'input' => 'text',
                    'required' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'position' => 150,
                    'system' => false,
                    'visible' => true,
                ]
            );

            $attributeShipViaCode = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY,
                'ship_via')
                ->addData(
                    [
                        'attribute_set_id' => $attributeSetId,
                        'attribute_group_id' => $attributeGroupId,
                        'used_in_forms' => ['adminhtml_customer'],
                    ]
                );

            $attributeShipViaCode->save();
        }

        if (version_compare($context->getVersion(), '2.3.5') < 0) {
            $entityAttributes = [
                'customer_address' => [
                    'lastname' => [
                        'is_required' => 0
                    ]
                ]
            ];
            $this->upgradeAttributes($entityAttributes, $customerSetup);
        }

        $setup->endSetup();
    }

    protected function upgradeAttributes(array $entityAttributes, \Magento\Customer\Setup\CustomerSetup $customerSetup)
    {
        foreach ($entityAttributes as $entityType => $attributes) {
            foreach ($attributes as $attributeCode => $attributeData) {
                $attribute = $customerSetup->getEavConfig()->getAttribute($entityType, $attributeCode);
                foreach ($attributeData as $key => $value) {
                    $attribute->setData($key, $value);
                }
                $attribute->save();
            }
        }
    }
}