<?php
namespace LeanSwift\Turfcare\Model;
use Magento\Framework\Math\Random;
Class DefaultModel extends \Magento\Captcha\Model\DefaultModel {

    protected $formId;

    protected $resLogFactory;

    protected $keepSession = true;

    protected $session;

    private $randomMath;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Captcha\Helper\Data $captchaData,
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory,
                                                           $formId,
        Random $randomMath = null
    )
    {
        parent::__construct($session,$captchaData,$resLogFactory,$formId);
        $this->randomMath = $randomMath ?? \Magento\Framework\App\ObjectManager::getInstance()->get(Random::class);
        $this->setDotNoiseLevel(15);
        $this->setLineNoiseLevel(1);
    }
}