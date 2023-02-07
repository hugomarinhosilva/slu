<?php

namespace UFT\SluBundle\Util\GSuit;

use Google_Auth_AssertionCredentials;
use Google_Service_Books;
use Google_Service_Directory;
use Google_Service_Directory_User;
use Google_Service_Directory_UserName;
use Google_Service_Exception;
use GuzzleHttp\Client;

/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 13/04/16
 * Time: 17:23
 */
class EmailManager
{
    protected $dir;
    protected $client;
    protected $emailManager;

    /**
     * EmailManager constructor.
     * @param $emailManager
     * @param GoogleClient $googleClient
     */
    public function __construct($emailManager, GoogleClient $googleClient)
    {
        $this->emailManager = $emailManager;
        $this->client= $googleClient->getGoogleClient();
        $this->dir = $googleClient->directory();
    }

    public function criarEmail($entity)
    {
        if ($this->emailManager === true) {
            //        verifica se já existe uma conta para este e-mail
            try {
                $r = $this->dir->users->get($entity->getUid() . "@mail.uft.edu.br");
                if ($r) {
                    $return = true;
                } else {
                    $return = false;
                }
            } catch (Google_Service_Exception $e) {
                $return = false;
            }
            if ($return) {
                return false;
            }

            try {
                // SET UP THE USER/USERNAME OBJECTS
                $user = new Google_Service_Directory_User();
                $name = new Google_Service_Directory_UserName();
                // SET THE ATTRIBUTES
                $giveName = is_array($entity->getCn())?array_values($entity->getCn())[0]:$entity->getCn();
                $name->setGivenName($giveName);
                $name->setFamilyName(is_array($entity->getSn())?array_values($entity->getSn())[0]:$entity->getSn());
                $user->setName($name);
                $user->setHashFunction("SHA-1");
                $user->setPrimaryEmail($entity->getUid() . "@mail.uft.edu.br");
                $user->setPassword(hash("sha1", md5($entity->getUid() . "@mail.uft.edu.br")));
                $result = $this->dir->users->insert($user,['quotaUser'=>$entity->getUid()]);
                return true;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function editarEmail($uidNova, $uidAntiga)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($uidAntiga . "@mail.uft.edu.br");
                $user->setPrimaryEmail($uidNova . "@mail.uft.edu.br");
                $result = $this->dir->users->update($user->getId(), $user);
                return true;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return true;
        }
//
    }


    public function editarNome($uid, $nome, $sobrenome)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($uid . "@mail.uft.edu.br");
                $name = new Google_Service_Directory_UserName();
                // SET THE ATTRIBUTES
                $name->setGivenName($nome);
                $name->setFamilyName($sobrenome);
                $user->setName($name);
                $result = $this->dir->users->update($user->getId(), $user);
                return true;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return true;
        }
//
    }

    public function deletarEmail($entity)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($entity->getUid() . "@mail.uft.edu.br");
                //verifica se há alias, e se a conta a ser exdcluída é diferente da principal
                if ($user->getAliases() != null && strcmp($entity->getUid() . "@mail.uft.edu.br", $user->getPrimaryEmail()) != 0) {
                    return $user->getPrimaryEmail();
                }
                $result = $this->dir->users->delete($entity->getUid() . "@mail.uft.edu.br");
                return true;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return true;
        }
    }

    public function reativarEmail($uid,$novaSenha = null)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($uid . "@mail.uft.edu.br");
                $user->setSuspended(false);
                $novaSenha = ($novaSenha==null)?uniqid($uid):$novaSenha;
                $user->setPassword(hash("sha1", md5($novaSenha . "@mail.uft.edu.br")));
                $result = $this->dir->users->update($user->getId(), $user);
                return true;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return true;
        }
//
    }

    public function isCreated($uid)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($uid . "@mail.uft.edu.br");
                if($user){
                    return true;
                }
                return false;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return false;
        }
//
    }

    public function isSuspenso($uid)
    {
        if ($this->emailManager === true) {
            try {
                $user = $this->dir->users->get($uid . "@mail.uft.edu.br");
                if($user->getSuspended()){
                    return true;
                }
                return false;
            } catch (Google_Service_Exception $e) {
                return false;
            }
        } else {
            return false;
        }
//
    }
}