<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to support@postcodeservice.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact support@postcodeservice.com for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
namespace TIG\Postcode\Controller\Address;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use TIG\Postcode\Webservices\Endpoints\GetAddress;
use TIG\Postcode\Webservices\Endpoints\GetBePostcode;
use TIG\Postcode\Webservices\Endpoints\GetBeStreet;
use TIG\Postcode\Services\Converter\Factory;

class Service extends Action
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var Factory
     */
    private $converter;

    /**
     * @var GetAddress
     */
    private $getAddress;

    /**
     * @var GetBePostcode
     */
    private $getBePostcode;

    /**
     * @var GetBeStreet
     */
    private $getBeStreet;

    /**
     * Service constructor.
     *
     * @param Context        $context
     * @param JsonFactory    $jsonFactory
     * @param Factory        $converterFactory
     * @param GetAddress     $getAddress
     * @param GetBePostcode  $getBePostcode
     * @param GetBeStreet    $getBeStreet
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Factory $converterFactory,
        GetAddress $getAddress,
        GetBePostcode $getBePostcode,
        GetBeStreet $getBeStreet
    ) {
        parent::__construct($context);

        $this->jsonFactory    = $jsonFactory;
        $this->converter      = $converterFactory;
        $this->getAddress     = $getAddress;
        $this->getBePostcode  = $getBePostcode;
        $this->getBeStreet    = $getBeStreet;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $country = $this->getCountry($params);
        $method = $this->getMethod($params, $country);

        $endpoint = $this->getEndpoint($country, $method);
        $params = $this->converter->convert('request', $params, $endpoint->getRequestKeys());
        if (!$params) {
            return $this->returnFailure(__('Request validation failed'));
        }

        $endpoint->setRequestData($params);
        $result = $endpoint->call();
        if (!$result) {
            return $this->returnFailure(__('Response validation failed'));
        }

        return $this->returnJson($result);
    }

    /**
     * @param $params
     * @param $country
     *
     * @return string
     */
    private function getMethod($params, $country)
    {
        if (isset($params[$country])) {
            return $params[$country];
        }

        return 'postcodecheck';
    }

    /**
     * @param $params
     *
     * @return string
     */
    private function getCountry($params)
    {
        if (key($params) == 'be') {
            return key($params);
        }

        return 'nl';
    }

    /**
     * @param string $error
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function returnFailure($error)
    {
        return $this->returnJson([
            'success' => false,
            'error'   => $error
        ]);
    }

    /**
     * @param $data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    private function returnJson($data)
    {
        $response = $this->jsonFactory->create();
        return $response->setData($data);
    }

    private function getEndpoint($country, $method)
    {
        if ($country == 'be' && $method == 'getpostcode') {
            return $this->getBePostcode;
        }

        if ($country == 'be' && $method == 'getstreet') {
            return $this->getBeStreet;
        }

        return $this->getAddress;
    }
}
