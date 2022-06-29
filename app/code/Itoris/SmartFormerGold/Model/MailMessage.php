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
namespace Itoris\SmartFormerGold\Model;

use Zend\Mime\Mime;
use Zend\Mime\Part;

class MailMessage extends \Magento\Framework\Mail\Message {
    
    protected $attachments = [];
    
    private $zendMessage;

    private $messageType = self::TYPE_TEXT;

    public function __construct($charset = 'utf-8')
    {
        $this->zendMessage = new \Zend\Mail\Message();
        $this->zendMessage->setEncoding($charset);
    }

    public function setMessageType($type)
    {
        $this->messageType = $type;
        return $this;
    }

    public function setBody($body)
    {
        $body = $this->createHtmlMimeFromString($body);
        $this->zendMessage->setBody($body);
        return $this;
    }

    public function setSubject($subject)
    {
        $this->zendMessage->setSubject($subject);
        return $this;
    }

    public function getSubject()
    {
        return $this->zendMessage->getSubject();
    }

    public function getBody()
    {
        return $this->zendMessage->getBody();
    }

    public function setFrom($fromAddress)
    {
        $this->setFromAddress($fromAddress, null);
        return $this;
    }

    public function setFromAddress($fromAddress, $fromName = null)
    {
        $this->zendMessage->setFrom($fromAddress, $fromName);
        return $this;
    }

    public function addTo($toAddress)
    {
        $this->zendMessage->addTo($toAddress);
        return $this;
    }

    public function addCc($ccAddress)
    {
        $this->zendMessage->addCc($ccAddress);
        return $this;
    }

    public function addBcc($bccAddress)
    {
        $this->zendMessage->addBcc($bccAddress);
        return $this;
    }

    public function setReplyTo($replyToAddress)
    {
        $this->zendMessage->setReplyTo($replyToAddress);
        return $this;
    }

    public function getRawMessage()
    {
        return $this->zendMessage->toString();
    }

    public function createHtmlMimeFromString($htmlBody) {
        $htmlPart = new Part($htmlBody);
        $htmlPart->setCharset($this->zendMessage->getEncoding());
        $htmlPart->setType(Mime::TYPE_HTML);
        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->addPart($htmlPart);
        foreach($this->attachments as $attachment) $mimeMessage->addPart($attachment);
        return $mimeMessage;
    }

    public function setBodyHtml($html)
    {
        $this->setMessageType(self::TYPE_HTML);
        return $this->setBody($html);
    }

    public function setBodyText($text)
    {
        $this->setMessageType(self::TYPE_TEXT);
        return $this->setBody($text);
    }
    
    public function createAttachment($body, $mimeType, $disposition, $encoding, $filename){
        $attachment = new Part($body);
        $attachment->setType($mimeType);
        $attachment->setDisposition($disposition);
        $attachment->setEncoding($encoding);
        $attachment->setFileName($filename);
        $this->attachments[] = $attachment;
        return $this;
    }    

}
