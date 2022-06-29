<?php

namespace LeanSwift\Turfcare\Block\Html;

use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\Template\FilterProvider;

class Topmenu extends \WeltPixel\NavigationLinks\Block\Html\Topmenu
{
    /**
     * @var BlockRepository
     */
    protected $staticBlockRepository;

    /**
     * @var FilterProvider
     */
    protected $_filterProvider;

    /**
     * Topmenu constructor.
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param BlockRepository $staticBlockRepository
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
        BlockRepository $staticBlockRepository,
        FilterProvider $filterProvider,
        array $data = []
    )
    {

        parent::__construct($context, $nodeFactory, $treeFactory, $staticBlockRepository, $filterProvider ,$data);
    }

    /**
     * Reset home page menu cache hours
     *
     * @return int
     */
    protected function getCacheLifetime()
    {
        return 0;
    }
}