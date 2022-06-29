<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Itoris\SmartFormerGold\Ui\Component\Listing;

class Columns extends \Magento\Ui\Component\Listing\Columns
{
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    protected $filterMap = [
        'default' => 'text',
        'select' => 'select',
        'boolean' => 'select',
        'multiselect' => 'select',
        'date' => 'dateRange',
    ];

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Itoris\SmartFormerGold\Ui\Component\ColumnFactory $columnFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->columnFactory = $columnFactory;
    }
    
    public function prepare() {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $form = $this->_registry->registry('sfg_current_form');
        
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('read');
        $formConfig = $form->getConfig();
        $columns = $con->fetchAll("SHOW COLUMNS FROM `{$formConfig->database->name}`");
        $aliases = $form->getAllAliases();
        
        $fileElementNames = [];
        foreach($form->getAllElements() as $element) {
            if ($element->getAttribute('type') == 'file' || $element->getTag() == 'canvas') $fileElementNames[] = $element->getName();
        }
            
        foreach($columns as $_column) {
            $columnCode = $_column['Field'];
            $config = [];
            if (!isset($this->components[$columnCode])) {
                $config['sortOrder'] = ++$columnSortOrder;
                $config['filter'] = $columnCode == 'id' ? 'textRange' : 'text';
                if ($columnCode == 'id') {
                    $config['label'] = __('ID');
                } else if (isset($aliases[$columnCode])) {
                    $config['label'] = $aliases[$columnCode];
                }
                if (in_array($columnCode, $fileElementNames)) $config['bodyTmpl'] = 'ui/grid/cells/html';
                $column = $this->columnFactory->create($columnCode, $this->getContext(), $config);
                $column->prepare();
                $this->addComponent($columnCode, $column);
            }
        }
        $bookmarkManagement = $this->_objectManager->get('Magento\Ui\Api\BookmarkManagementInterface');
        $bookmarks = $bookmarkManagement->loadByNamespace('submission_listing');
        foreach ($bookmarks->getItems() as $bookmark) {
            $config = $bookmark->getConfig();
            $config['current']['positions']['actions'] = 1000; //make actions the last column
            $bookmark->setConfig(json_encode($config))->save();
        }

        parent::prepare();
    }

    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }
}
