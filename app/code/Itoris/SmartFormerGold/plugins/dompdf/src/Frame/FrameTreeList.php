<?php
namespace Itoris\SmartFormerGold\plugins\dompdf\src\Frame;

use IteratorAggregate;
use Itoris\SmartFormerGold\plugins\dompdf\src\Frame;

/**
 * Pre-order IteratorAggregate
 *
 * @access private
 * @package dompdf
 */
class FrameTreeList implements IteratorAggregate
{
    /**
     * @var \Itoris\SmartFormerGold\plugins\dompdf\src\Frame
     */
    protected $_root;

    /**
     * @param \Itoris\SmartFormerGold\plugins\dompdf\src\Frame $root
     */
    public function __construct(Frame $root)
    {
        $this->_root = $root;
    }

    /**
     * @return FrameTreeIterator
     */
    public function getIterator()
    {
        return new FrameTreeIterator($this->_root);
    }
}
