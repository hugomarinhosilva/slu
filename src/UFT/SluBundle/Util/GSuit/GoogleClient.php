<?php


namespace UFT\SluBundle\Util\GSuit;


class GoogleClient
{
    /**
     * @var Config
     */
    protected $config;
    /**
     * @var string
     */
    protected $googleClient;
    /**
     * Set config
     *
     * @param string $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
    /**
     * Get config
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * Set google client
     *
     * @param string $googleClient
     */
    public function setGoogleClient($googleClient)
    {
        $this->googleClient = $googleClient;
    }
    /**
     * Get google client
     *
     * @return string
     */
    public function getGoogleClient()
    {
        return $this->googleClient;
    }
    /**
     * Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $user_to_impersonate = 'devadm@uft.edu.br';

        $googleClient = new \Google_Client();
        $googleClient->setApplicationName($config->getApplicationName());
        $googleClient->setAuthConfig($config->getCredentialFile());
        $googleClient->setScopes(array(\Google_Service_Directory::ADMIN_DIRECTORY_USER, \Google_Service_Directory::ADMIN_DIRECTORY_GROUP));
        $googleClient->setSubject($user_to_impersonate);
        $this->setConfig($config);
        $this->setGoogleClient($googleClient);

    }
    /**
     * Get analytics service
     */
    public function analytics()
    {
        return new \Google_Service_AnalyticsReporting($this->getGoogleClient());
    }
    /**
     * Get analytics service
     */
    public function directory()
    {
        return new \Google_Service_Directory($this->getGoogleClient());
    }
    /**
     * Get shopping content service
     */
    public function shoppingContent()
    {
        return new \Google_Service_ShoppingContent($this->getGoogleClient());
    }
    /**
     * Get youtube service
     */
    public function youtube()
    {
        return new \Google_Service_YouTube($this->getGoogleClient());
    }
}