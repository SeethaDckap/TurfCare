<?php

namespace LeanSwift\Turfcare\Controller\Customer;
use Magedelight\Orderbysku\Controller\Customer\Sampledata as CoreSampledata;

class Sampledata extends CoreSampledata
{

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $context;

    /**
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($fileFactory,$directoryList,$csvProcessor,$context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $content = [];
        $content[] = ["sku","qty","product_comment"];
        $content[] = ["120-1243-01","5","Your comment here"];
        $content[] = ["130-1265","1","Your comment here"];
        $content[] = ["40-0028","1","Your comment here"];
        $content[] = ["ST23752","1","Your comment here"];
        $content[] = ["211043","1","Your comment here"];

        $fileName = 'sampledata.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;
 
        $this->csvProcessor
            ->setDelimiter(',')
            ->setEnclosure('"')
            ->saveData(
                $filePath,
                $content
            );
 
        return $this->fileFactory->create(
            $fileName,
            [
                'type' => "filename",
                'value' => $fileName,
                'rm' => true,
            ],
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
            'application/octet-stream'
        );
    }
}
