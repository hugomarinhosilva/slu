<?php

namespace UFT\SluBundle\Controller;

use Doctrine\ORM\ORMException;
use FOS\RestBundle\Controller\Annotations as FOSRest;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\PessoaLdap;

/**
 * @FOSRest\NamePrefix("api_")
 */

class RestController extends FOSRestController
{

    /**
     * @FOSRest\Get("v2/dados/ldap/{key}/{value}/{chave}")
     */
    public function getDadosLdapV2Action($key,$value, $chave)
    {
        if($chave!='@*!@'){
            return array('show' => 'false');
        }

        $em = $this->get('ldap_entity_manager');
        $metodo = 'findOneBy'.ucfirst($key);

        /** @var PessoaLdap $person*/
        $person = $em->getRepository(PessoaLdap::class)->$metodo($value);
        $antiga = $person->getObjectClass();
        $nova = $person->getNovasObjectClass();
        $dados = [];
        $isPadraoAntigo = array_diff($nova, $antiga);
        $isPadraoAntigo2 = array_diff($antiga, $nova);
        if (!empty($person)  && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) ) {
            $dados['recadastrado'] = false;
        }else{
            $dados['recadastrado'] = true;
        }
        $dados['nome'] = $person->getGecos();
        $dados['login'] = $person->getUid();
        $dados['campus'] = (is_array($person->getCampus())?$person->getCampus()[0]:$person->getCampus());
        $dados['professor'] = ($person->getProfessor())?$person->getProfessor():'0';
        $dados['aluno'] = ($person->getAluno())?$person->getAluno():'0';
        $dados['tecnico'] = ($person->getFuncionario())?$person->getFuncionario():'0';
        $dados['idpessoa'] = $person->getIdPessoa();
        $dados['matricula'] =  (is_array($person->getMatricula())?implode($person->getMatricula(),'|'):$person->getMatricula());;
        $dados['cpf'] = $person->getBrPersonCPF();
        $dados['telefones'] = $person->getTelephoneNumber();
        $dados['emails'] = $person->getMail();

        return array('show' => 'true', 'dados' => $dados);
    }

    /**
     * @FOSRest\Get("v2/dados/ldap/all/{key}/{value}/{chave}")
     */
    public function getAllDadosLdapV2Action($key,$value, $chave)
    {
        if($chave!='@*!@'){
            return array('show' => 'false');
        }

        $em = $this->get('ldap_entity_manager');
        $metodo = 'findBy'.ucfirst($key);

        /** @var PessoaLdap $person*/
        $persons = $em->getRepository(PessoaLdap::class)->$metodo($value);
        $dados = [];

        foreach ($persons as $person) {

            $data = [];
            $antiga = $person->getObjectClass();
            $nova = $person->getNovasObjectClass();
            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            if (!empty($person)  && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) ) {
                $data['recadastrado'] = false;
            }else{
                $data['recadastrado'] = true;
            }
            $data['nome'] = $person->getGecos();
            $data['login'] = $person->getUid();
            $data['campus'] = (is_array($person->getCampus())?$person->getCampus()[0]:$person->getCampus());
            $data['professor'] = ($person->getProfessor())?$person->getProfessor():'0';
            $data['aluno'] = ($person->getAluno())?$person->getAluno():'0';
            $data['tecnico'] = ($person->getFuncionario())?$person->getFuncionario():'0';
            $data['idpessoa'] = $person->getIdPessoa();
            $data['matricula'] =  (is_array($person->getMatricula())?implode($person->getMatricula(),'|'):$person->getMatricula());;
//            $data['cpf'] = $person->getBrPersonCPF();
//            $data['telefones'] = $person->getTelephoneNumber();
//            $data['emails'] = $person->getMail();
            $dados[] = $data;
        }


        return array('show' => 'true', 'dados' => $dados);
    }


    /**
     * @FOSRest\Get("dados/ldap/{login}/{chave}")
     */
    public function getDadosLdapAction($login, $chave)
    {
        return $this->getDadosLdapV2Action("uid",$login, $chave);
    }


}
