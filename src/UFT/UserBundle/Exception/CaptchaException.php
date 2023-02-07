<?php


namespace UFT\UserBundle\Exception;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 06/02/17
 * Time: 10:55
 */
class CaptchaException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return "O reCAPTCHA não foi preechido corretamente.";
    }
}
