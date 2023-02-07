<?php


namespace UFT\UserBundle\Security\Service;


use LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\SamlConstants;

class AppEntityDescriptor extends SimpleEntityDescriptorBuilder
{
    /** @var array|string */
    protected $acsUrl;

    /**
     * @param string          $entityId
     * @param array|string    $acsUrl
     * @param string          $ssoUrl
     * @param string          $ownCertificate
     * @param string[]        $acsBindings
     * @param string[]        $ssoBindings
     * @param string[]|null   $use
     */
    public function __construct(
        $entityId,
        $acsUrl,
        $ssoUrl,
        $ownCertificate,
        array $acsBindings = array(SamlConstants::BINDING_SAML2_HTTP_POST),
        array $ssoBindings = array(SamlConstants::BINDING_SAML2_HTTP_POST, SamlConstants::BINDING_SAML2_HTTP_REDIRECT),
        $use = array(KeyDescriptor::USE_ENCRYPTION, KeyDescriptor::USE_SIGNING)
    ) {
        $certificate = $ownCertificate;

        if (!$ownCertificate instanceof X509Certificate) {
            $certificate = new X509Certificate();
            $certificate->loadFromFile($ownCertificate);
        }

        parent::__construct($entityId, $acsUrl, $ssoUrl, $certificate, $acsBindings, $use);
    }

    /**
     * @return SpSsoDescriptor|null
     */
    protected function getSpSsoDescriptor()
    {
        if (null === $this->acsUrl) {
            return null;
        }

        $spSso = new SpSsoDescriptor();

        foreach ($this->acsBindings as $index => $binding) {
            // On ajoute toutes les url autorisées pour le service
            if (is_array($this->acsUrl)) {
                foreach ($this->acsUrl as $acsUrl) {
                    $acs = new AssertionConsumerService();
                    $acs->setIndex($index)->setLocation($acsUrl)->setBinding($binding);
                    $spSso->addAssertionConsumerService($acs);
                }
            }
            else
            {
                $acs = new AssertionConsumerService();
                $acs->setIndex($index)->setLocation($this->acsUrl)->setBinding($binding);
                $spSso->addAssertionConsumerService($acs);
            }
        }

        $this->addKeyDescriptors($spSso);

        return $spSso;
    }

}