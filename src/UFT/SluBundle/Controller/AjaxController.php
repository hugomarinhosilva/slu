<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\GrupoLdap;
use UFT\SluBundle\Entity\NewGrupoLdap;
use UFT\SluBundle\Entity\PessoaLdap;

/**
 * Ajax controller.
 *
 * @Route("/ajax")
 */
class AjaxController extends Controller
{
    /**
     * Ajax para retornar pessoas do ldap atraves de um filtro no uid e cn, utilizado em:
     * -Lista de pessoas
     *
     * @Route("/busca_pessoas", name="ajax_busca_pessoas")
     * @Method("POST")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function buscaPessoasAction(Request $request)
    {
        $text = $request->request->get('text');
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findByComplex(array(
            '|' => array(
                'uid' => $text . "*",
                'cn' => $text . "*",
            )
        ));
        return new \Symfony\Component\HttpFoundation\JsonResponse($person);
    }

    /**
     * Ajax para retornar uma pessoa do ldap atraves do uid, utilizado em:
     * -editar grupos
     *
     * @Route("/busca_pessoa_uid", name="ajax_busca_pessoa_uid")
     * @Method("POST")
     * @Security("has_role('ROLE_GRUPO_USUARIO_ADICIONAR')")
     */
    public function buscaPessoaUidAction(Request $request)
    {
        $uid = $request->request->get('uid');
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findOneByUid(
            $uid
        );
        return new \Symfony\Component\HttpFoundation\JsonResponse($person);
    }

    /**
     * Ajax para retornar pessoas do ldap atraves de um filtro no uid e cn, utilizado em:
     * -Lista de pessoas
     *
     * @Route("/busca_membros", name="ajax_busca_membros")
     * @Method("GET")
     * @Security("has_role('ROLE_SLU_USUARIO_EDITAR_BASICO')")
     */
    public function buscaMembrosAction(Request $request)
    {

        $text = $request->query->get('q');
        $em = $this->get('ldap_entity_manager');
        $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('&' => array(
            '|' => array(
                'uid' => "*" . $text . "*",
                'cn' => $text . "*",
                'gecos' => $text . "*",
            ),
            '!' => array('Institucional'=>1)
        )));
        $retorno = array();
        foreach ($person as $pessoa) {
            $text = $pessoa->getGecos();
            if ($text == '') {
                if (is_array($pessoa->getCN())) {
                    if (count($pessoa->getCN()) > 1) {
                        $text = strlen($pessoa->getCN()[0]) > strlen($pessoa->getCN()[1]) ? $pessoa->getCN()[0] : $pessoa->getCN()[1];
                    } else {
                        $text = $pessoa->getCN()[0];
                    }
                } else {
                    $text = $pessoa->getCN();
                }
            }
            $retorno[] = array(
                "id" => $pessoa->getUid(),
                "text" => trim($text) . ' - ' . $pessoa->getUid(),
            );
        }
//        $retorno = array('items'=>$retorno,"total_count"=>30);
        return new \Symfony\Component\HttpFoundation\JsonResponse($retorno);
    }


    /**
     * Ajax para retornar grupos do ldap atraves de um filtro no cn, utilizado em:
     * - CRIAR E EDITAR PESSOA (ADICIONAR GRUPOS)
     *
     * @Route("/busca_grupo", name="ajax_busca_grupo")
     * @Method("GET")
     * @Security("has_role('ROLE_SLU_GRUPO_MOSTRAR')")
     */
    public function buscaGrupoAction(Request $request)
    {
        $text = $request->query->get('q');
        $em = $this->get('ldap_entity_manager');
        $grupos = $em->getRepository(NewGrupoLdap::class)->findByComplex(array(
            '|' => array(
                'cn' => $text . "*",
            )
        ));
        $retorno = array();
        foreach ($grupos as $grupo) {
            if (is_array($grupo->getCN())) {
                if (count($grupo->getCN()) > 1) {
                    $text = strlen($grupo->getCN()[0]) > strlen($grupo->getCN()[1]) ? $grupo->getCN()[0] : $grupo->getCN()[1];
                } else {
                    $text = $grupo->getCN()[0];
                }
            } else {
                $text = $grupo->getCN();
            }
            $retorno[] = array(
                "id" => $grupo->getCn()[0],
                "text" => trim($text),
            );
        }
        return new \Symfony\Component\HttpFoundation\JsonResponse($retorno);
    }


    /**
     * Ajax para retornar filtros que serão adicionados nos grupos:
     * - CRIAR E EDITAR USERGRUPO (ADICIONAR FILTROS)
     *
     * @Route("/busca_filtro_grupo", name="ajax_busca_filtro_grupo")
     * @Method("GET")
     * @Security("has_role('ROLE_SLU_GRUPO_MOSTRAR') or has_role('ROLE_RELATORIO')")
     */
    public function buscaFiltroGrupoAction(Request $request)
    {
        $text = $request->query->get('q');
//        echo $text;

        $em = $this->getDoctrine()->getManager();
        $grupos = $em->getRepository('UserBundle:FiltroUnidade')->createQueryBuilder('f')
            ->select('f')
            ->where('f.nomeUnidade LIKE :nome')->orderBy('f.codEstruturado', 'ASC')
            ->setParameter('nome', '%'.trim($text).'%')
            ->getQuery()->getArrayResult();

//        echo $grupos;
//        die();
//
        $retorno = array();

        foreach ($grupos as $grupo) {
            $retorno[] = array(
                "id" => $grupo['codEstruturado'],
                "text" => trim($grupo['nomeUnidade']),
            );
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($retorno);
    }


//    /**
//     * @Route("/email_proprietario", name="ajax_email_proprietario")
//     */
//    public function ajaxEmailProprietarioAction(Request $request)
//    {
//        $idProprietario = $request->request->get('idProprietario');
//
//
//        $em = $this->getDoctrine()->getManager();
//        $proprietario = $em->getRepository('SluBundle:SluProprietario')->createQueryBuilder('p')
//            ->select('p')
//            ->where('p.id = :id')
//            ->setParameter('id', $idProprietario)
//            ->getQuery()->getArrayResult();
//
//
//        return new \Symfony\Component\HttpFoundation\JsonResponse($proprietario);
//    }


//    Conjunto de metodos auxiliares ajax para controlador do GRUPO
    /**
     * @Route("/verifica_nome_grupo", name="ajax_verifica_nome_grupo")
     * @Security("has_role('ROLE_SLU_GRUPO_CRIAR')")
     */
    public function verificaNomeGrupo(Request $request)
    {
        $ldapManager = $this->get('ldap_entity_manager');
        $nomeGrupo = $request->request->get('nomeGrupo');
        $cn = $request->request->get('cn');
        $ldapResult = $ldapManager->getRepository(GrupoLdap::class)->findByCn($nomeGrupo);
        if (strlen($nomeGrupo) > 4) {
            if (!$ldapResult || ($cn == $ldapResult[0]->getCn()[0])) {
                $resultado = 'Este nome está disponivel!';
            } else {
                $resultado = 'Este nome já está sendo utilizado em outro grupo!';
            }
        } else {
            $resultado = 'O tamanho mínimo permitido para este campo é de 5 caracteres!';
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($resultado);
    }


    //    Conjunto de metodos auxiliares ajax para controlador da PESSOA
    /**
     * @Route("/verifica_cpf", name="verifica_cpf")
     */
    public function verificaCPF(Request $request)
    {
        $em = $this->get('ldap_entity_manager');
        $filtro = array();
        $filtro['brPersonCPF'] = preg_replace("/[^0-9]/", "", $request->request->get('cpf'));
        $filtro['cpf'] = $filtro['brPersonCPF'];

        $person = $em->getRepository(PessoaLdap::class)->findByComplex(array('|' => $filtro), array('searchDn' => $this->getParameter('ldap_basedn')));

        if (count($person) > 1 || count($person) == 1) {
            $resultado = 'Este CPF já está em uso!';

        } else {
            $resultado = '';
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($resultado);
    }

    /**
     * @Route("/verifica_login", name="ajax_verifica_login")
     *
     */
    public function verificaLogin(Request $request)
    {
        $ldapManager = $this->get('ldap_entity_manager');
        $emUtil = $this->get('uft.ldap.manager');
        $login = $request->request->get('login');
        $login = str_replace(".", "", $login);
        $resultado = '';
        if ($request->request->get('uid') != '') {
            $entityLogin = $request->request->get('uid');
        } else {
            $entityLogin = "";
        }
        $ldapResult = $ldapManager->getRepository(PessoaLdap::class)->findByUid($login, array(
            'searchDn' => $this->getParameter('ldap_basedn')));
        if (strlen($login) > 5) {
            if(!$ldapResult){
                $string = implode('*',str_split($login));
                $possibilidades = $emUtil->find('all', array('conditions' => "uid=$string"),'ou=People,dc=uft,dc=edu,dc=br');

                if($possibilidades!=false){
                    foreach ($possibilidades as $row){
                        $uidExistente = str_replace(".", "", $row['People']['uid']);
                        if ($uidExistente==$login){
                            $resultado = 'Este login já está sendo utilizado!';
                            return new \Symfony\Component\HttpFoundation\JsonResponse($resultado);
                        }
                    }

                }
                $resultado = 'Login disponivel!';

            }else if ($entityLogin == $ldapResult[0]->getUid()) {
                $resultado = 'Login disponivel!';
            } else {
                $resultado = 'Este login já está sendo utilizado!';
            }
        } else {
            $resultado = 'O tamanho mínimo permitido para este campo é de 6 caracteres!';
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($resultado);
    }

    /**
     * @Route("/verifica_login_departamento", name="ajax_verifica_login_departamento")
     *
     */
    public function verificaLoginDepartamento(Request $request)
    {
        $ldapManager = $this->get('ldap_entity_manager');

        $login = $request->request->get('login');

        if ($request->request->get('uid') != '') {
            $entityLogin = $request->request->get('uid');
        } else {
            $entityLogin = "";
        }


        $ldapResult = $ldapManager->getRepository(PessoaLdap::class)->findByUid($login, array(
            'searchDn' => $this->getParameter('ldap_basedn')));
        if (strlen($login) > 2) {
            if (!$ldapResult || $entityLogin == $ldapResult[0]->getUid()) {
                $resultado = 'Login disponivel!';
            } else {
                $resultado = 'Este login já está sendo utilizado!';
            }
        } else {
            $resultado = 'O tamanho mínimo permitido para este campo é de 3 caracteres!';
        }

        return new \Symfony\Component\HttpFoundation\JsonResponse($resultado);
    }

    /**
     * @Route("/ajax_usuario_autocomplete", name="ajax_usuario_autocomplete")
     */
    public function autocompleteUsuarioAction(Request $request)
    {
        $names = array();
        $term =  $request->query->get('q');


        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('UserBundle:Usuario')->createQueryBuilder('c')
            ->where('c.username LIKE :name')
            ->setParameter('name', '%'.$term.'%')
            ->getQuery()
            ->setMaxResults(20)
            ->getResult();

        foreach ($entities as $entity)
        {
            $names[] = array(
                "id" => $entity->getId(),
                "text" => trim($entity->getUsername()),
            );
        }

        $response = new JsonResponse();
        $response->setData($names);

        return $response;
    }
}
