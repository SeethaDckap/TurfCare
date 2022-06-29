<?php
namespace Itoris\SmartFormerGold\Ui\DataProvider\Submission;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class SubmissionDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $addFieldStrategies;
    protected $addFilterStrategies;
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_request = $this->_objectManager->get('Magento\Framework\App\RequestInterface');
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $formId = $this->_request->getParam('formid');
        if (!$formId) {
            $referer = $this->_request->getServer('HTTP_REFERER');
            $pos = strpos($referer, '/formid/');
            if ($pos !== false) $formId = intval(substr($referer, $pos + 8));
        }
        $form = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Form')->load($formId);
        $this->_registry->unregister('sfg_current_form');
        $this->_registry->register('sfg_current_form', $form);
        
        $this->collection = $this->_objectManager->get('Itoris\SmartFormerGold\Model\Submission')->getCollection();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

    /*public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        
        $items = $this->getCollection()->toArray();
        return $items;
        print_r($items); exit;

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }*/

    /*public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (isset($this->addFilterStrategies[$filter->getField()])) {
            $this->addFilterStrategies[$filter->getField()]
                ->addFilter(
                    $this->getCollection(),
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
        } else {
            parent::addFilter($filter);
        }
    }*/
}
