<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_SMARTFORMER_GOLD
 * @copyright  Copyright (c) 2017 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */
namespace Itoris\SmartFormerGold\Block\Adminhtml\Forms;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected  function _construct() {
        parent::_construct();
        $this->setId('smartformergold_forms_grid');
        $this->setDefaultSort('form_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection() {
        $collection = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->getCollection();
        $this->setCollection($collection);

        return $this;
    }

    protected function _prepareColumns() {

        $this->addColumn('form_id', [
            'header' => $this->escapeHtml(__('#')),
            'type' => 'range',
            'index' => 'form_id'
        ]);
        
        $this->addColumn('name', [
            'header' => $this->escapeHtml(__('Form Name')),
            'index' => 'name',
        ]);
        
        $this->addColumn('description', [
            'header' => $this->escapeHtml(__('Description')),
            'index' => 'description',
            'sortable'  => false,
        ]);
        
        $this->addColumn('submissions', [
            'header' => $this->escapeHtml(__('Submissions')),
            'index' => 'submissions',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'Itoris\SmartFormerGold\Block\Adminhtml\Forms\Renderer\SubmissionsUrl',
        ]);  
        
        $this->addColumn('status', [
            'header' => $this->escapeHtml(__('Status')),
            'index' => 'status',
            'type' => 'options',
            'options' => [0 => __('Disabled'), 1 => __('Enabled')],
        ]);   
        
        $this->addColumn('actions',
            [
                'header'    =>  $this->escapeHtml(__('Actions')),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => [
                    [
                        'caption'   => $this->escapeHtml(__('Edit')),
                        'url'       => ['base'=> '*/*/editform'],
                        'field'     => 'id'
                    ],
                    [
                        'caption'   => $this->escapeHtml(__('Delete')),
                        'url'       => ['base'=> '*/*/deleteForm'],
                        'confirm'   => $this->escapeHtml(__('Are you sure want to delete the form?')),
                        'field'     => 'id'
                    ],
                    [
                        'caption'   => $this->escapeHtml(__('Clone')),
                        'url'       => ['base'=> '*/*/cloneForm'],
                        'field'     => 'id'
                    ],
                    [
                        'caption'   => $this->escapeHtml(__('Backup')),
                        'url'       => ['base'=> '*/*/backupForm'],
                        'field'     => 'id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            ]);
            
        $this->addColumn('url', [
            'header' => $this->escapeHtml(__('Direct URL to the form')),
            'index' => 'url',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'Itoris\SmartFormerGold\Block\Adminhtml\Forms\Renderer\FormUrl',
        ]);  
        
        parent::_prepareColumns();
        return $this;
    }

    protected function _prepareMassaction() {
        $this->setMassactionIdField('form_id');
        $this->getMassactionBlock()->setFormFieldName('smartformergold_forms_grid');

        $this->getMassactionBlock()->addItem('delete', [
            'label'    => $this->escapeHtml(__('Delete')),
            'url'      => $this->getUrl('*/*/massDelete'),
            'confirm'  => $this->escapeHtml(__('Are you sure want to delete selected forms?'))
        ]);
        $this->getMassactionBlock()->addItem(
            'update_status',
            [
                'label' => __('Update Status'),
                'url' => $this->getUrl('*/*/massUpdateStatus'),
                'additional' => [
                    'status' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => [0 => 'Disabled', 1 => 'Enabled'],
                    ],
                ]
            ]
        );
        $this->getMassactionBlock()->addItem('clone', [
            'label'    => $this->escapeHtml(__('Clone')),
            'url'      => $this->getUrl('*/*/massClone'),
            'confirm'  => $this->escapeHtml(__('Are you sure want to clone selected forms?'))
        ]);
        $this->getMassactionBlock()->addItem('backup', [
            'label'    => $this->escapeHtml(__('Backup')),
            'url'      => $this->getUrl('*/*/massBackup')
        ]);
        return $this;
    }

    /**
     * Retrieve row click URL
     *
     * @param \Magento\Framework\Object $row
     *
     * @return string
     */
    public function getRowUrl($row) {
        return $this->getUrl('*/*/editform', ['id' => $row->getId()]);
    }

}