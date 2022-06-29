<?php
namespace Itoris\SmartFormerGold\Ui\Component;

class ColumnFactory
{
    protected $componentFactory;

    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
    ];

    public function __construct(\Magento\Framework\View\Element\UiComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    public function create($attributeCode, $context, array $config = [])
    {
        $columnName = $attributeCode;
        $config = array_merge([
            'label' => $attributeCode,
            'dataType' => 'text',
            'add_field' => true,
            'visible' => 'text',
            'filter' => 'text',
        ], $config);

        /*if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
        }*/
        
        $config['component'] = $this->getJsComponent($config['dataType']);
        
        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];
 
        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /*protected function getDataType($attribute)
    {
        return isset($this->dataTypeMap[$attribute->getFrontendInput()])
            ? $this->dataTypeMap[$attribute->getFrontendInput()]
            : $this->dataTypeMap['default'];
    }

    protected function getFilterType($frontendInput)
    {
        $filtersMap = ['date' => 'dateRange'];
        $result = array_replace_recursive($this->dataTypeMap, $filtersMap);
        return isset($result[$frontendInput]) ? $result[$frontendInput] : $result['default'];
    }*/
}
