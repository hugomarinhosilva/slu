<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 23/08/17
 * Time: 10:13
 */

namespace UFT\UserBundle\Security\Service;

use LightSaml\Model\Protocol\Response;


/**
 * @Service(id="usuario_attribute_mapper")
 */
class UsuarioAttributeMapper
{
    public function getAttributes(Response $response)
    {
        return [
            'mail' => $response->getFirstAssertion()->getFirstAttributeStatement()
                ->getFirstAttributeByName('mail')->getFirstAttributeValue(),
        ];

    }
}