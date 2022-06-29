<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Orderbysku
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */

namespace Magedelight\Orderbysku\Controller\Customer;

class Sampledata extends \Magento\Framework\App\Action\Action
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
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->directoryList = $directoryList;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $content = [];
        $content[] = ["sku,qty"];
        $content[] = ["MB-2025,5"];
        $content[] = ["24-MB04,1"];
        $content[] = ["24-MB03,1"];
        $content[] = ["24-MB05,1"];
        $content[] = ["24-MB06,1"];
        $content[] = ["24-MB02,1"];
        $content[] = ["24-UB02,1"];
        $content[] = ["24-WB01,1"];
        $content[] = ["24-WB02,1"];
        $content[] = ["24-WB05,1"];
        $content[] = ["24-WB06,1"];
        $content[] = ["24-WB03,1"];

        $fileName = 'sampledata.csv';
        $filePath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            . "/" . $fileName;
 
        $this->csvProcessor
            ->setDelimiter(';')
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
