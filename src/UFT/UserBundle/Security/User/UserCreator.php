<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 21/08/17
 * Time: 15:42
 */

namespace UFT\UserBundle\Security\User;


use AppBundle\Util\ConfigAcl;
use Doctrine\Common\Persistence\ObjectManager;
use LightSaml\Model\Protocol\Response;
use LightSaml\SpBundle\Security\User\UserCreatorInterface;
use LightSaml\SpBundle\Security\User\UsernameMapperInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UFT\UserBundle\Entity\Usuario;
use UFT\UserBundle\Security\Service\UsuarioAttributeMapper;

class UserCreator implements UserCreatorInterface
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var UsernameMapperInterface */
    private $usernameMapper;

    /** @var UsuarioAttributeMapper */
    private $attributeMapper;


    /**
     * @param ObjectManager           $objectManager
     * @param UsernameMapperInterface $usernameMapper
     * @param UsuarioAttributeMapper $attributeMapper
     */
    public function __construct($objectManager, $usernameMapper, $attributeMapper)
    {
        $this->objectManager = $objectManager;
        $this->usernameMapper = $usernameMapper;
        $this->attributeMapper = $attributeMapper;
    }

    /**
     * @param Response $response
     *
     * @return UserInterface|null
     */
    public function createUser(Response $response)
    {
        $username = $this->usernameMapper->getUsername($response);
        $attributes = $this->attributeMapper->getAttributes($response);


        $user = new Usuario();
        $user
            ->setUsername($username)
            ->setEmail($attributes['mail'])
            ->setEmailCanonical($attributes['mail'])
            ->setEnabled(true)
            ->setPassword('')
        ;

        $this->objectManager->persist($user);
        $this->objectManager->flush();


        return $user;
    }
}