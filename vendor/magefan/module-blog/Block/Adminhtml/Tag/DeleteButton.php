<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Block\Adminhtml\Tag;

/**
 * Class DeleteButton
 */
class DeleteButton extends \Magefan\Community\Block\Adminhtml\Edit\DeleteButton
{
    /**
     * @return array|string
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed("Magefan_Blog::tag_delete")) {
            return [];
        }
        return parent::getButtonData();
    }
}