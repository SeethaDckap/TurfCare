<?php
/**
 * *
 *   LeanSwift eConnect Extension
 *
 *   NOTICE OF LICENSE
 *
 *   This source file is subject to the LeanSwift eConnect Extension License
 *   that is bundled with this package in the file LICENSE.txt located in the Connector Server.
 *
 *   DISCLAIMER
 *
 *  This extension is licensed and distributed by LeanSwift. Do not edit or add to this file
 *   if you wish to upgrade Extension and Connector to newer versions in the future.
 *   If you wish to customize Extension for your needs please contact LeanSwift for more
 *   information. You may not reverse engineer, decompile,
 *   or disassemble LeanSwift Connector Extension (All Versions), except and only to the extent that
 *   such activity is expressly permitted by applicable law not withstanding this limitation.
 *
 * @category  LeanSwift
 * @package   LeanSwift_EconnectSXE
 * @copyright Copyright (c) 2019 LeanSwift Inc. (http://www.leanswift.com)
 * @license   http://www.leanswift.com/license/connector-extension
 */

namespace Turfcare\EconnectSXE\Model\Soap\Client;

use LeanSwift\EconnectSXE\Api\LoggerInterface;
use LeanSwift\EconnectSXE\Api\Soap\RequestInterface;
use LeanSwift\EconnectSXE\Helper\Data;
use LeanSwift\EconnectSXE\Helper\Erpapi;
use LeanSwift\EconnectSXE\Helper\Xpath;
use Monolog\Logger;
use SoapVarFactory;
use Magento\Framework\Exception\ConfigurationMismatchException;
use LeanSwift\EconnectSXE\Model\Soap\Client\CommonFactory;

class Connect extends \LeanSwift\EconnectSXE\Model\Soap\Client\Connect implements RequestInterface
{
    protected $_callConnectionParams;
    protected $_baseNameSpace;
    protected $_helperData;
    protected $_callConnectionString;
    protected $_requestNamespace;
    protected $_requestBody;
    protected $_uri;
    protected $_locationURL;
    protected $_connectionRequest;
    protected $_removeString;
    protected $_response = '';
    protected $_commonFactory;
    protected $_loggerEnablePath = '';
    protected $_logger;
    protected $_api;
    protected $_soapParams = [];
    protected $i = 0;
    protected $j = 0;
    protected $_canParse = false;
    protected $flag = 0;
    protected $_keys = '';
    protected $_soapVar;
    protected $timeout = 90;

    public function __construct(
        $locationURL = '',
        $baseNameSpace = '',
        $callConnectionString = '',
        $callConnectionParams = [],
        $removeString = [],
        Data $helper,
        Logger $logger,
        $loggerEnablePath = '',
        CommonFactory $commonFactory,
        $api = '',
        SoapVarFactory $soapVar
    ) {
        $this->_locationURL = $locationURL;
        $this->_baseNameSpace = $baseNameSpace;
        $this->_callConnectionParams = $callConnectionParams;
        $this->_helperData = $helper;
        $this->_callConnectionString = $callConnectionString;
        $this->_removeString = $removeString;
        $this->_commonFactory = $commonFactory;
        $this->setLogger($logger);
        $this->_logger = $logger;
        $this->setEnablePath($loggerEnablePath);
        $this->_loggerEnablePath = $loggerEnablePath;
        $this->_api = $api;
        $this->_soapVar = $soapVar;
        $this->_requestNamespace = $this->_baseNameSpace . '.' . $this->_api;
    }

    /**
     * Set enabled path
     *
     * @param $path
     * @return mixed|void
     */
    public function setEnablePath($path)
    {
        $this->_loggerEnablePath = $path;
    }

    public function setKeys($keys)
    {
        $this->_keys = $keys;
    }

    public function formRequest($request, $nameSpace = false)
    {
        $stdClass = new \StdClass();
        if (!$nameSpace) {
            $nameSpace = $this->getNameSpace();
        }
        return $this->addRequestToStdClass($stdClass, $request, $nameSpace);
    }

    public function getNameSpace()
    {
        return $this->_requestNamespace;
    }

    public function addRequestToStdClass($stdClass, $request, $nameSpace)
    {
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                $this->addRequestToStdClass($stdClass, $value, $nameSpace);
            }
            $data = [
                'data' => $value,
                'encoding' => XSD_STRING,
                'type_name' => null,
                'type_namespace' => $nameSpace,
                'node_name' => $key,
                'node_namespace' => $nameSpace
            ];
            $stdClass->{$key} = $this->_soapVar->create($data);
        }
        return $stdClass;
    }

    public function formWrapper($key, $wrapper, $nameSpace)
    {
        $outerWrapper = new \StdClass();
        if (!$nameSpace) {
            $nameSpace = $this->getNameSpace();
        }
        $data = [
            'data' => $wrapper,
            'encoding' => SOAP_ENC_OBJECT,
            'type_name' => null,
            'type_namespace' => null,
            'node_name' => $key,
            'node_namespace' => $nameSpace
        ];
        $outerWrapper->{$key} = $this->_soapVar->create($data);
        return $outerWrapper;
    }

    public function removeString($string = [], $remove = false)
    {
        if ($remove) {
            $this->_removeString = $string;
        } else {
            $this->_removeString = array_merge_recursive($this->_removeString, $string);
        }
    }

    public function setPostValues($postValues)
    {
        $this->_postValues = $postValues;
    }

    /**
     * Request to location is formed here
     */

    public function send()
    {
        $this->sendRequest();
    }

    /**
     * Send CURL request
     */
    public function sendRequest()
    {
        // @codingStandardsIgnoreStart
        try {
            $client = $this->formRequestMap();
            $multiCurl = [];
            $result = [];
            $mh = curl_multi_init();
            $finalOutput = [];
            $i = $count = 0;
            foreach ($client->getRequestMap() as $request) {
                $this->writeLog($request);
                if ($this->_keys) {
                    $i = $this->_keys[$count];
                }
                //multiRequest
                $multiCurl[$i] = curl_init();
                curl_setopt($multiCurl[$i], CURLOPT_URL, $client->getLocation());
                curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($multiCurl[$i], CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($multiCurl[$i], CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($multiCurl[$i], CURLOPT_CONNECTTIMEOUT, 60);
                curl_setopt($multiCurl[$i], CURLOPT_TIMEOUT, $this->getTimeOut());
                $headers = $client->getLastRequestHeaders();
                if ($request != null) {
                    curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, "$request");
                    array_push($headers, "Content-Length: " . strlen($request));
                }
                curl_setopt($multiCurl[$i], CURLOPT_POST, true);
                curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, $headers);
                curl_multi_add_handle($mh, $multiCurl[$i]);
                if (!$this->_keys) {
                    $i = $i + 1;
                }
                $count = $count + 1;
            }
            $index = null;
            do {
                curl_multi_exec($mh, $index);
            } while ($index > 0);
            // get content and remove handles
            foreach ($multiCurl as $k => $ch) {
                if(curl_errno($ch)){
                    throw new \Exception(curl_error($ch));
                }
                $finalBody = '';
                $httpResponseCode =  curl_getinfo($ch, CURLINFO_HTTP_CODE);
                //if no http response code received
                if($httpResponseCode == 0) {
                    throw new ConfigurationMismatchException(__('Verify the SX.e Location URL'));
                }
                elseif($httpResponseCode == 100) {
                    throw new ConfigurationMismatchException(__('Verify the SX.e Connection string'));
                }
                elseif($httpResponseCode == 404) {
                    throw new ConfigurationMismatchException(__('Verify the SX.e Location URL or SX.e Connection string'));
                }
                $result[$k] = curl_multi_getcontent($ch);
                if($httpResponseCode == 200) {
                    $finalBody = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>" . $result[$k];
                    $this->writeLog($finalBody);
                }
                //server error
                elseif(substr((string)$httpResponseCode,0,1) == '5') {
                    $this->writeLog($result[$k], true);
                }
                //client errors
                elseif(substr((string)$httpResponseCode,0,1) == '4') {
                    $this->writeLog($result[$k], true);
                }
                curl_multi_remove_handle($mh, $ch);
                $finalOutput[$k] = $finalBody;
            }
            curl_multi_close($mh);

            if (!empty($finalOutput)) {
                $json = \Zend_Json::encode($finalOutput, true);
                $responseArray = \Zend_Json::decode($json);
                $this->_response = $responseArray;
            }
        }
        catch (ConfigurationMismatchException $e) {
            $this->writeLog($e->getMessage(),true);
        }
        catch (\Exception $e) {
            $this->writeLog($e->getMessage(),true);
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Form the SOAP client Request
     *
     * @return Common
     */
    public function formRequestMap()
    {
        $client = $this->_commonFactory->create(['options' => $this->getOptions()]);
        /** Setting the remove string from the request */
        $client->setRemoveString($this->getRemoveString());
        /** Set the API to the soap client */
        $client->setAPI($this->getAPI());
        $finalBody = $this->_requestBody;
        foreach ($finalBody as $requestBody) {
            $this->_requestBody = $requestBody;
            $client->__call($this->getAPI(), $this->formRequestBody());
        }
        return $client;
    }

    /**
     * Get the SOAP options
     *
     * @return mixed
     */
    public function getOptions()
    {
        $locationURL = $this->getLocationURL();
        if(!$locationURL) {
            throw new ConfigurationMismatchException(__('SOAP Location URL field has to be configured'));
            return "";
        }
        $options['trace'] = 1;
        $options['location'] = $this->getLocationURL();
        $options['uri'] = $this->getURI();
        $options['encoding'] = 'UTF-8';
        $options['style'] = SOAP_RPC;
        $options['connection_timeout'] = $this->getTimeOut();
        return $options;
    }

    /**
     * Get the location URL
     *
     * @return mixed
     */
    public function getLocationURL()
    {
        return $this->_helperData->getDataValue($this->_locationURL);
    }

    /**
     * Set the location URL
     *
     * @param $url
     * @return mixed|void
     */
    public function setLocationURL($url)
    {
        $this->_locationURL = $url;
    }

    public function getURI()
    {
        return $this->_uri ? $this->_uri : Erpapi::TEMP_NAMESPACE;
    }

    public function setURI($uri)
    {
        $this->_uri = $uri;
    }

    public function getRemoveString()
    {
        return $this->_removeString;
    }

    public function getAPI()
    {
        return $this->_api;
    }

    public function setTimeOut($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getTimeOut()
    {
        return $this->timeout;
    }

    public function setAPI($api)
    {
        $this->_api = $api;
        $this->_requestNamespace = $this->_baseNameSpace . '.' . $this->_api;
    }

    /**
     * Form the Connection Parameter
     *
     * @return array
     */
    public function formRequestBody()
    {
        if (!empty($this->_callConnectionParams)) {
            //this will not happen twice in a page
            if(!$this->_connectionRequest) {
                $param = [];
                foreach ($this->_callConnectionParams as $key => $value) {
                    $pathvalue = '';
                    //if the header param required is set true
                    if($value['required']) {
                        $pathvalue = $this->validateEmpty($key, $value);
                    }
                    $namespace = '';
                    if (isset($value['api'])) {
                        $namespace = $this->_baseNameSpace . '.' . $value['api'];
                    }
                    if (isset($value['pathvalue'])) {
                        if(!$pathvalue) {
                            $pathvalue = $this->_helperData->getDataValue($value['pathvalue']);
                        }
                        $param[] = new \SoapVar($pathvalue, XSD_ANYTYPE, "", "",
                            $key, $namespace);
                    } elseif (isset($value['value'])) {
                        $param[] = new \SoapVar($value['value'], XSD_ANYTYPE, "", "", $key, $namespace);
                    } else {
                        continue;
                    }
                }
                $wrapper = new \SoapVar($param, SOAP_ENC_OBJECT, "", null, null, $this->getURI());
                $this->_connectionRequest = [new \SoapParam($wrapper, $this->_callConnectionString)];
            }
            return array_merge($this->_connectionRequest, $this->addRequest());
        }
    }

    public function validateEmpty($key, $value) {
        $value = $this->_helperData->getDataValue($value['pathvalue']);
        if(!$value){
            throw new ConfigurationMismatchException(__(sprintf('SX.e %s field has to be configured',$key)));
        }
        return $value;
    }

    /**
     * Add Request body
     *
     * @return array
     */
    public function addRequest()
    {
        if (!empty($this->getRequestBody())) {
            //if($this->_isProcessed) {
            $param = $this->getRequestBody();
            $wrapper = new \SoapVar($param, SOAP_ENC_OBJECT, null, "", null, $this->getURI());
            return [new \SoapParam($wrapper, 'request')];
        }
    }

    public function getRequestBody()
    {
        return $this->_requestBody;
    }

    public function setRequestBody($requestBody)
    {
        $this->_requestBody = $requestBody;
    }

    public function writeLog($message, $sendEmail = false, $storeId = null)
    {
        $this->_helperData->writeLogInfo($this->getLogger(), $this->getEnablePath(), $message, $sendEmail, $storeId);
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function setLogger($logger)
    {
        $this->_logger = $logger;
    }

    public function getEnablePath()
    {
        return $this->_loggerEnablePath;
    }

    public function getResponse()
    {
        return $this->parseResponse($this->_response);
    }

    public function parseResponse($response)
    {
        return $response;
    }

    public function getValue($response, $key, $nameSpace = '')
    {
        $nameSpace = ($nameSpace) ? $nameSpace : $this->_requestNamespace;
        return Xpath::parseResponse($response, $nameSpace, $key);
    }

    public function getErrorMessage($response, $nameSpace = '')
    {
        $nameSpace = ($nameSpace) ? $nameSpace : $this->_requestNamespace;
        return Xpath::getErrorMessage($response, $nameSpace);
    }

    public function getDefaultPath()
    {
        // TODO: Implement getDefaultPath() method.
    }
}