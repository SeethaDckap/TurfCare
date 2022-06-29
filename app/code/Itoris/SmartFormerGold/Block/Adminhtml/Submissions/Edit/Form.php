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
namespace Itoris\SmartFormerGold\Block\Adminhtml\Submissions\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    protected function _prepareForm()
    {
        $formId = (int)$this->getRequest()->getParam('formid');
        $submissionId = (int)$this->getRequest()->getParam('id');
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->_filesPath = $directoryList->getPath('media').'/sfg/files/';
        $this->urlBuilder = $this->_objectManager->get('Magento\Framework\UrlInterface');
        $sfgForm = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Form')->load($formId);
        $config = $sfgForm->getConfig();
        $this->_registry->register('sfg_current_form', $sfgForm);
        $submission = $this->_objectManager->create('Itoris\SmartFormerGold\Model\Submission')->load($submissionId);
        $aliases = $sfgForm->getAllAliases();
        
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'smartformergold/submissions/save',
                        [
                            'id' => $this->getRequest()->getParam('id'),
                            'formid' => $this->getRequest()->getParam('formid')
                        ]
                    ),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'submission_details',
            ['legend' => __('Submission Details'), 'class' => 'fieldset-wide']
        );
        
        $aliases = array_merge($aliases, ['system_customer' => __('Customer'), 'system_created' => __('Created At'), 'system_updated' => __('Updated At'), 'id' => 'Submission ID']);
        $res = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $con = $res->getConnection('read');
        $additional = $con->fetchRow("select * from `{$res->getTableName('itoris_sfg_submission_index')}` where `form_id`={$formId} and `submission_id`={$submission->getId()}");
        $customerLink = 'n/a';
        if ((int) $additional['customer_id']) {
            $customer = $this->_objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface')->getById($additional['customer_id']);
            if ((int) $customer->getId()) {
                $customerLink = '<a href="'.htmlspecialchars($this->getUrl('customer/index/edit', ['id' => $additional['customer_id']])).'">'.$customer->getLastname().', '.$customer->getFirstname().'</a>';
            }
        }
        $data = array_merge(
            ['system_customer' => $additional['customer_id'] ? $customerLink : 'n/a',
            'system_created' => $additional['created'] ? $additional['created'] : 'n/a',
            'system_updated' => $additional['updated'] ? ($additional['updated'] != '0000-00-00 00:00:00' ? $additional['updated'] : __('Never')) : 'n/a'],
            $submission->getData()
        );
        $submission->setData($data);

        foreach($submission->getData() as $key => $value) {
            $element = $sfgForm->getElementByDbName($key);
            if (in_array($key, ['id', 'system_customer', 'system_created', 'system_updated'])) {
                $fieldset->addField(
                    $key,
                    'note',
                    ['label' => __(isset($aliases[$key]) ? $aliases[$key] : $key), 'required' => false, 'name' => $key, 'text' => $submission->getData($key)]
                );
            } else {
                $type = 'text';
                $options = [];
                $text = '';
                if ($element !== null) {
                    if ($element->getTag() == 'textarea') $type = "textarea";
                    if ($element->getTag() == 'select' && empty($element->getContentPHP())) {
                        $type = "select";
                        $_options = $element->getOptions();
                        foreach($_options as $_option) $options[$_option['value']] = $_option['text'];
                    }
                    if ($element->getAttribute('type') == 'file') {
                        $type = "note";
                        if ($value) {
                            $fileName = substr($value, 64);
                            if (file_exists($this->_filesPath.$value)) {
                                $url = $this->urlBuilder->getUrl('smartformergold/submissions/getFile').'?fileName='.urlencode($value);
                                $text = '<a href="javascript://" onclick="document.location=\''.htmlspecialchars($url).'\'; event.stopPropagation()">'.htmlspecialchars($fileName).'</a>';
                            } else {
                                $text = htmlspecialchars($fileName);
                            }
                        }
                    }
                    if ($element->getTag() == 'canvas') {
                        $type = "note";
                        if ($value) {
                            $fileName = substr($value, 64);
                            if (file_exists($this->_filesPath.$value)) {
                                $png = 'data:image/png;base64,'.base64_encode(file_get_contents($this->_filesPath.$value));
                                $text = '<img class="sfg-canvas" src="'.$png.'" alt="image" />';
                            }
                        }
                    }
                }
                $fieldset->addField(
                    $key,
                    $type,
                    ['label' => __(isset($aliases[$key]) ? $aliases[$key] : $key), 'required' => false, 'name' => $key, 'options' => $options, 'text' => $text]
                );
            }

        }
        $form->setUseContainer(true);
        $form->setValues($submission->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
    
}
