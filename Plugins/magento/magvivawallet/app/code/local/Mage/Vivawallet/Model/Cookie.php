<?php

class Mage_Vivawallet_Model_Cookie extends Mage_Core_Model_Cookie
{
    const XML_PATH_COOKIE_DOMAIN    = 'web/cookie/cookie_domain';
    const XML_PATH_COOKIE_PATH      = 'web/cookie/cookie_path';
    const XML_PATH_COOKIE_LIFETIME  = 'web/cookie/cookie_lifetime';
    const XML_PATH_COOKIE_HTTPONLY  = 'web/cookie/cookie_httponly';

    /**#@+
     * Constants for Cookie manager.
     * RFC 2109 - Page 15
     * http://www.ietf.org/rfc/rfc6265.txt
     */
    const MAX_NUM_COOKIES = 50;
    const MAX_COOKIE_SIZE = 4096;
    const EXPIRE_NOW_TIME = 1;
    const EXPIRE_AT_END_OF_SESSION_TIME = 0;
    /**#@-*/

    const KEY_EXPIRES = 'expires';
    const KEY_PATH = 'path';
    const KEY_DOMAIN = 'domain';
    const KEY_SECURE = 'secure';
    const KEY_HTTP_ONLY = 'httponly';
    const KEY_SAME_SITE = 'samesite';

    /**#@+
     * Constant for metadata array key
     */
    const KEY_EXPIRE_TIME = 'expiry';

    protected $_lifetime;

    /**
     * Store object
     *
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * Set Store object
     *
     * @param mixed $store
     * @return Mage_Core_Model_Cookie
     */
    public function setStore($store)
    {
        $this->_store = Mage::app()->getStore($store);
        return $this;
    }

    /**
     * Retrieve Store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        if (is_null($this->_store)) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    /**
     * Retrieve Request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }

    /**
     * Retrieve Response object
     *
     * @return Mage_Core_Controller_Response_Http
     */
    protected function _getResponse()
    {
        return Mage::app()->getResponse();
    }

    /**
     * Retrieve Domain for cookie
     *
     * @return string
     */
    public function getDomain()
    {
        $domain = $this->getConfigDomain();
        if (empty($domain)) {
            $domain = $this->_getRequest()->getHttpHost();
        }
        return $domain;
    }

    /**
     * Retrieve Config Domain for cookie
     *
     * @return string
     */
    public function getConfigDomain()
    {
        return (string)Mage::getStoreConfig(self::XML_PATH_COOKIE_DOMAIN, $this->getStore());
    }

    /**
     * Retrieve Path for cookie
     *
     * @return string
     */
    public function getPath()
    {
        $path = Mage::getStoreConfig(self::XML_PATH_COOKIE_PATH, $this->getStore());
        if (empty($path)) {
            $path = $this->_getRequest()->getBasePath();
        }
        return $path;
    }

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getLifetime()
    {
        if (!is_null($this->_lifetime)) {
            $lifetime = $this->_lifetime;
        } else {
            $lifetime = Mage::getStoreConfig(self::XML_PATH_COOKIE_LIFETIME, $this->getStore());
        }
        if (!is_numeric($lifetime)) {
            $lifetime = 3600;
        }
        return $lifetime;
    }

    /**
     * Set cookie lifetime
     *
     * @param int $lifetime
     * @return Mage_Core_Model_Cookie
     */
    public function setLifetime($lifetime)
    {
        $this->_lifetime = (int)$lifetime;
        return $this;
    }

    /**
     * Retrieve use HTTP only flag
     *
     * @return bool
     */
    public function getHttponly()
    {
        $httponly = Mage::getStoreConfig(self::XML_PATH_COOKIE_HTTPONLY, $this->getStore());
        if (is_null($httponly)) {
            return null;
        }
        return (bool)$httponly;
    }

    /**
     * Is https secure request
     * Use secure on adminhtml only
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->getStore()->isAdmin()) {
            return $this->_getRequest()->isSecure();
        }
        return false;
    }

    /**
     * Set cookie
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param bool $httponly
     * @return Mage_Core_Model_Cookie
     */
    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        /**
         * Check headers sent
         */
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if ($period === true) {
            $period = 3600 * 24 * 365;
        } elseif (is_null($period)) {
            $period = $this->getLifetime();
        }

        if ($period == 0) {
            $expire = 0;
        }
        else {
            $expire = time() + $period;
        }
        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        $userAgent = Mage::helper('core/http')->getHttpUserAgent();
        $validator = new Mage_Vivawallet_Model_Validator();
        $sameSite = $validator->shouldSendSameSiteNone($userAgent);

        $version = PHP_VERSION_ID;
        if ($version >= 70300) {
            $options = [
                self::KEY_EXPIRES => $expire,
                self::KEY_PATH => $path,
                self::KEY_DOMAIN => $domain,
                self::KEY_SECURE => $secure,
                self::KEY_HTTP_ONLY => $httponly
            ];

            if ($sameSite) {
                //$options = array_merge($options, [self::KEY_SAME_SITE => 'None']);
				$options = array_merge($options, [self::KEY_SAME_SITE => 'None', self::KEY_SECURE => true]);
            }

            $phpSetcookieSuccess = setcookie(
                $name,
                $value,
                $options
            ); 

        }else{ 
            if ($sameSite) {
                //$path .= '; SameSite=None';
				$path .= '; SameSite=None; Secure=true';
            }
            setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }

        return $this;
    }

    /**
     * Postpone cookie expiration time if cookie value defined
     *
     * @param string $name The cookie name
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @return Mage_Core_Model_Cookie
     */
    public function renew($name, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if (($period === null) && !$this->getLifetime()) {
            return $this;
        }
        $value = $this->_getRequest()->getCookie($name, false);
        if ($value !== false) {
            $this->set($name, $value, $period, $path, $domain, $secure, $httponly);
        }
        return $this;
    }

    /**
     * Retrieve cookie or false if not exists
     *
     * @param string $neme The cookie name
     * @return mixed
     */
    public function get($name = null)
    {
        return $this->_getRequest()->getCookie($name, false);
    }

    /**
     * Delete cookie
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param int|bool $httponly
     * @return Mage_Core_Model_Cookie
     */
    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        /**
         * Check headers sent
         */
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, null, null, $path, $domain, $secure, $httponly);
        return $this;
    }
}
