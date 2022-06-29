<?php

namespace Itoris\SmartFormerGold\Block\Adminhtml\Submissions;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    protected function _construct()
    {
        parent::_construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'Itoris_SmartFormerGold';
        $this->_controller = 'adminhtml_submissions';
        
        $this->buttonList->remove('reset');
        
        $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getUrl('smartformergold/submissions/index', ['formid' => $this->getRequest()->getParam('formid')]) . '\')');
        $confirmText = __('Are you sure you want to do this?');
        $this->buttonList->update('delete', 'onclick', "confirmSetLocation('{$confirmText}', '" . $this->getUrl('smartformergold/submissions/delete', ['formid' => $this->getRequest()->getParam('formid'), 'id' => $this->getRequest()->getParam('id')]) . "')");
        
        $this->buttonList->update('save', 'class', 'save');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => $this->escapeHtml(__('Save and Continue Edit')),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            null
        );
        
        $this->buttonList->add(
            'pdf',
            [
                'label' => $this->escapeHtml(__('PDF')),
                'onclick' => 'setLocation(\'' . $this->getUrl('smartformergold/submissions/pdf', ['formid' => $this->getRequest()->getParam('formid'), 'id' => $this->getRequest()->getParam('id')]) . '\')'
            ],
            null
        );
        
        $this->buttonList->add(
            'csv',
            [
                'label' => $this->escapeHtml(__('CSV')),
                'onclick' => 'setLocation(\'' . $this->getUrl('smartformergold/submissions/csv', ['formid' => $this->getRequest()->getParam('formid'), 'id' => $this->getRequest()->getParam('id')]) . '\')'
            ],
            null
        );
        
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
        $index = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Customer\Submission')
                        ->getCollection()
                        ->addFieldToFilter('form_id', $this->getRequest()->getParam('formid'))
                        ->addFieldToFilter('submission_id', $this->getRequest()->getParam('id'))
                        ->getFirstItem();
                        
        if ($index->getUniqueKey()) {
            $base = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
            $this->buttonList->add(
                'edit_full',
                [
                    'label' => $this->escapeHtml(__('Edit Full View')),
                    'onclick' => 'window.open(\'' . $base.'sfg/customer/edit/id/'.$index->getUniqueKey() . '\')'
                ],
                null
            );
        }
    }

}
