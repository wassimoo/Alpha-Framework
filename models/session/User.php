<?php
/**
 * Created by PhpStorm.
 * User: wassim
 * Date: 27/02/18
 * Time: 16:29
 */

namespace Session;

/**
 * Class User
 * @package Session
 */
class User
{
    private $ipAddress;
    private $userAgent;
    private $country;
    private $os;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->ipAddress = $this->extractIpAddress();
        $this->userAgent = $this->extreactUserAgent();
        $this->country = $this->extractCountry();
        $this->os = $this->extractOs();
    }


    /**
     * @return string
     */
    private function extractIpAddress(){

    }
    /****************** Getters & Setters *********************/
    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * @param string $os
     */
    public function setOs($os)
    {
        $this->os = $os;
    }
    /***********************************************************/
}
