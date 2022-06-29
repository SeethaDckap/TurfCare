<?php
namespace Itoris\SmartFormerGold\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class SubmissionActions extends Column
{

    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_registry = $this->_objectManager->get('Magento\Framework\Registry');
        $directoryList = $this->_objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
        $this->_filesPath = $directoryList->getPath('media').'/sfg/files/';
    }

    public function prepareDataSource(array $dataSource)
    {
        $form = $this->_registry->registry('sfg_current_form');
        $formId = $form->getId();
        if (isset($dataSource['data']['items'])) {
            $fileElements = [];
            foreach($form->getAllElements() as $element) {
                if ($element->getAttribute('type') == 'file' || $element->getTag() == 'canvas') $fileElements[] = $element;
            }

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'smartformergold/submissions/edit',
                        ['formid'=> $formId, 'id' => $item['id']]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
                
                $item[$this->getData('name')]['pdf'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'smartformergold/submissions/pdf',
                        ['formid'=> $formId, 'id' => $item['id']]
                    ),
                    'label' => __('PDF'),
                    'hidden' => false,
                ];
                
                $item[$this->getData('name')]['csv'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'smartformergold/submissions/csv',
                        ['formid'=> $formId, 'id' => $item['id']]
                    ),
                    'label' => __('CSV'),
                    'hidden' => false,
                ];
                
                foreach($fileElements as $element) {
                    $fieldName = $element->getName();
                    if (isset($item[$fieldName]) && $item[$fieldName]) {
                        $fileName = substr($item[$fieldName], 64);
                        if (file_exists($this->_filesPath.$item[$fieldName])) {
                            $url = $this->urlBuilder->getUrl('smartformergold/submissions/getFile').'?fileName='.urlencode($item[$fieldName]);
                            $item[$fieldName] = '<a href="javascript://" onclick="document.location=\''.htmlspecialchars($url).'\'; event.stopPropagation()">'.htmlspecialchars($fileName).'</a>';
                        } else {
                            $item[$fieldName] = htmlspecialchars($fileName);
                        }
                    }
                }
                
            }
            
        }

        return $dataSource;
    }
}
