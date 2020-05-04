<?php
/**
 * Magento session manager
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\VivaPayments\Session;

use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Session Manager
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class SessionManager extends \Magento\Framework\Session\Generic
{
    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var SessionStartChecker
     */
    private $sessionStartChecker;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Session\SessionStartChecker|null $sessionStartChecker
     * @throws \Magento\Framework\Exception\SessionException
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Session\SessionStartChecker $sessionStartChecker = null
    ) {
        $this->request = $request;
        $this->sidResolver = $sidResolver;
        $this->sessionConfig = $sessionConfig;
        $this->saveHandler = $saveHandler;
        $this->validator = $validator;
        $this->storage = $storage;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->appState = $appState;
        $this->sessionStartChecker = $sessionStartChecker ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Session\SessionStartChecker::class
        );
        $this->start();
    }

    /**
     * Configure session handler and start session
     *
     * @throws \Magento\Framework\Exception\SessionException
     * @return $this
     */
    public function start()
    {
        if ($this->sessionStartChecker->check()) {
            if (!$this->isSessionExists()) {
                \Magento\Framework\Profiler::start('session_start');

                try {
                    $this->appState->getAreaCode();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    throw new \Magento\Framework\Exception\SessionException(
                        new \Magento\Framework\Phrase(
                            'Area code not set: Area code must be set before starting a session.'
                        ),
                        $e
                    );
                }

                // Need to apply the config options so they can be ready by session_start
                $this->initIniOptions();
                $this->registerSaveHandler();
                if (isset($_SESSION['new_session_id'])) {
                    // Not fully expired yet. Could be lost cookie by unstable network.
                    session_commit();
                    session_id($_SESSION['new_session_id']);
                }
				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
				$productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface'); 
				$magversion = $productMetadata->getVersion();
				
				//tommod m235
				if (version_compare($magversion, '2.3.5', '<')) {
				 $sid = $this->sidResolver->getSid($this);
                 //potential custom logic for session id (ex. switching between hosts)
                 $this->setSessionId($sid);
				}
                
                session_start();
                if (isset($_SESSION['destroyed'])
                    && $_SESSION['destroyed'] < time() - $this->sessionConfig->getCookieLifetime()
                ) {
                    $this->destroy(['clear_storage' => true]);
                }

                $this->validator->validate($this);
				
				//tommod m235
				if (version_compare($magversion, '2.3.5', '<')) {
                 $this->renewCookie($sid);
				} else {
				 $this->renewCookie(null);
				}

                register_shutdown_function([$this, 'writeClose']);

                $this->_addHost();
                \Magento\Framework\Profiler::stop('session_start');
            }
            $this->storage->init(isset($_SESSION) ? $_SESSION : []);
        }
        return $this;
    }

    /**
     * Renew session cookie to prolong session
     *
     * @param null|string $sid If we have session id we need to use it instead of old cookie value
     * @return $this
     */
    private function renewCookie($sid)
    {
        if (!$this->getCookieLifetime()) {
            return $this;
        }
        //When we renew cookie, we should aware, that any other session client do not
        //change cookie too
        $cookieValue = $sid ?: $this->cookieManager->getCookie($this->getName()) ?: session_id();
        if ($cookieValue) {
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath($this->sessionConfig->getCookiePath());
            $metadata->setDomain($this->sessionConfig->getCookieDomain());
            $metadata->setDuration($this->sessionConfig->getCookieLifetime());
            $metadata->setSecure($this->sessionConfig->getCookieSecure());
            $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());

            $this->cookieManager->setPublicCookie(
                $this->getName(),
                $cookieValue,
                $metadata
            );
        }

        return $this;
    }

    /**
     * Performs ini_set for all of the config options so they can be read by session_start
     *
     * @return void
     */
    private function initIniOptions()
    {
        $result = ini_set('session.use_only_cookies', '1');
        if ($result === false) {
            $error = error_get_last();
            throw new \InvalidArgumentException(
                sprintf('Failed to set ini option session.use_only_cookies to value 1. %s', $error['message'])
            );
        }

        foreach ($this->sessionConfig->getOptions() as $option => $value) {
            if ($option=='session.save_handler') {
                continue;
            } else {
                $result = ini_set($option, $value);
                if ($result === false) {
                    $error = error_get_last();
                    throw new \InvalidArgumentException(
                        sprintf('Failed to set ini option "%s" to value "%s". %s', $option, $value, $error['message'])
                    );
                }
            }
        }
    }
}