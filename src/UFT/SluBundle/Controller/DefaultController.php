<?php

namespace UFT\SluBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use UFT\SluBundle\Entity\DepartamentoLdap;
use UFT\SluBundle\Entity\PessoaLdap;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
//        $service = $this->get('uft.sincronizacao.manager');
//
//        $em = $this->get('ldap_entity_manager');
//        $filtro['&'] = array(
//            'objectClass' => 'brPerson',
//            '&' => array(
//                'objectClass' => 'inetOrgPerson',
//                '&' => array(
//                    'objectClass' => 'organizationalPerson',
//                    '&' => array(
//                        'objectClass' => 'person',
//                        '&' => array(
//                            'objectClass' => 'schacPersonalCharacteristics',
//                            '&' => array(
//                                'objectClass' => 'top',
//                                '&' => array(
//                                    'objectClass' => 'uftOrgUnit',
//                                    '&' => array(
//                                        'objectClass' => 'posixAccount',
//                                    ),
//                                ),
//                            ),
//                        ),
//                    ),
//                ),
//            ),
//            '!' => array('Title' => '9'),
//            '|' => array(
//                'institucional' => '0',
//                '!' => array('institucional' => '*')
//            ),
//
//        );
//        $persons = $em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => 'ou=People,dc=uft,dc=edu,dc=br'));
//        foreach ($persons as $person){
//            if($person->getUid() == 'rodrigogouvea') {
//                dump($person->getMail());
//                dump($person->getPostalAddress());
//                $service->sincronizar($person,true);
//                die;
//            }
//
//
//            if(count($person->getMail())>0){
//                $person->ordenaMail();
//                $person->setTitle(9);
//                dump($person->getMail());
//                dump($person->getPostalAddress());
//            }
//        }
//        dump("aaaa");
//        die;

//        $person = $em->getRepository(PessoaLdap::class)->findOneByUid("meloflavio");
////
//        $service->criarSei($person);
//
////        dump($person);die;
//        $person = new PessoaLdap();
//        $person->setUid('testeSlu2019');
//        $person->setCn(['testeslu�','teste1']);
//        $person->setSn('$personteste�');
//        $response = $service->deletarEmail($person);
//        dump($response);die;
//        $emDB2 = $this->getDoctrine()->getManager('db2');
//        $repoAlunos = $emDB2->getRepository('SluBundle:SieAluno')->createQueryBuilder('t');
//        $repoEnderecos = $emDB2->getRepository('SluBundle:SieEndereco')->createQueryBuilder('e');
//
//        $alunos = $repoAlunos->andWhere('t.cpf = :cpf')
//            ->setParameter('cpf', '021.649.971-23')
//            ->getQuery()
//            ->getResult();
//
//        $enderecos= $repoEnderecos
//            ->andWhere('e.idOrigem = :id')
//            ->andWhere('e.tipoOrigemItem = 11')
//            ->setParameter('id', $alunos[0]->getIdAluno() )
//            ->getQuery()
//            ->getResult();
//
//
//        dump($alunos,$enderecos);die();
//        $em = $this->get('ldap_entity_manager');
//        $person = $em->getRepository(PessoaLdap::class)->findOneByUid("rodolfo");
//        dump($person);die();

//        foreach ($person as $pessoa) {
//            if (strlen($pessoa->getCpf()) > 10 && $pessoa->getDepartmentNumber() == null) {
//                $em2 = $this->getDoctrine()->getManager('db2');
//                $convertor = $this->get('uft.convertores');
//                $sie = $em2->getRepository(SieServidor::class)->findOneByCpf($convertor->formataCPF(trim($pessoa->getBrPersonCPF())));
//                if($sie!=null){
//
//                    $pessoa->setDepartmentNumber($sie->getCodEstruturadoExercicio());
//                    $em->persist($pessoa);
//                }
//            }
//
//        }

//        $em = $this->getDoctrine()->getManager();
//
//        $grupo=$em->getRepository(Role::class)->createQueryBuilder('c')->getQuery()->getArrayResult();
//        $yaml = Yaml::dump($grupo);
//        dump($grupo);
//        file_put_contents('roles.yml', $yaml);die();
//
//
//        foreach ($grupo->getChildren()as $user){
//            dump($user);
//        }
//        $em->remove($grupo);
//        $em->flush();
//        dump($grupo);
//        die();
//
//
//        $roles = array();
//        $rol = $this->getParameter('security.role_hierarchy.roles');
//        unset($rol['ROLE_SUPER_ADMIN']);
//        foreach ($rol as $key => $value) {
//            $roles[$key] = $key;
//
//            foreach ($value as $value2) {
//                $roles[] = $value2;
//            }
//        }
//        $roles = array_unique($roles);


//        dump($rol,$roles);die();
//        $em = $this->get('ldap_entity_manager');
//        $person = $em->getRepository(PessoaLdap::class)->findOneByUid('flavio22w');
//        $g = $em->getRepository(GrupoLdap::class)->findByMember($person);

//        $g = new GrupoLdap();
//$g->setCn('Administrators');
//$g->setId(12);
//$g->addMember($person[0]->getUid());
//$em->persist($g);
//$em->flush();
        $this->get('session')->remove('busca_completa');
        $this->get('session')->remove('busca');
        $syncService = $this->get('uft.sincronizacao.manager');
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $recadastrar = false;
            $suspenso = false;
            $emailCriado = false;
            $emailManager = $this->get('uft.email.manager');
            $logger = $this->get('logger');

            $em = $this->get('ldap_entity_manager');
            $person = $em->getRepository(PessoaLdap::class)->findOneByUid($this->getUser()->getUsername());
            $msupdate = false;
            if (count($person->getMail()) > 1) {
                $person->ordenaMail();
                $em->persist($person);
                $em->flush();
                $msupdate = true;
            }

            if(!$emailManager->isCreated($person->getUid())){
                try{
                    $emailManager->criarEmail($person);
                    $this->addFlash(
                        'info',
                        "O seu e-mail foi ativado."
                    );
                }catch (\Exception $exception){
                    $logger->error('Email Error: UID'.$person->getUid().' : '.$exception->getMessage());
                }
            }
            $departamento = $em->getRepository(DepartamentoLdap::class)->findOneByUid($this->getUser()->getUsername());
            if(!empty($person) && $person->getProfessor()==NULL && $person->getAluno()==NULL && $person->getFuncionario()==NULL && !empty($departamento) && $departamento->getInstitucional() == 1){
                $antiga = $departamento->getObjectClass();
                $nova = $departamento->getNovasObjectClass();
                $emailCriado = $emailManager->isCreated($person->getUid());
                $suspenso = $emailManager->isSuspenso($person->getUid());
//                if($suspenso){
//                    $path = $this->get('router')->generate('alterar_senha_departamento',array(),true);
//                    $this->addFlash(
//                        'warning',
//                        "O seu e-mail foi suspenso automaticamente e pode estar em risco.
//                        Por favor utilize a opção <a href='$path'>
//                                                <br/>\"Reativar E-mail\"</a> abaixo, para alterar sua senha e reativar seu e-mail."
//                    );
//                }
            }elseif(!empty($person)){
                $antiga = $person->getObjectClass();
                $nova = $person->getNovasObjectClass();
                $emailCriado = $emailManager->isCreated($person->getUid());
                $suspenso = $emailManager->isSuspenso($person->getUid());

//                if($suspenso){
//                    $path = $this->get('router')->generate('alterar_senha_usuario',array(),true);
//                    $this->addFlash(
//                        'warning',
//                        "O seu e-mail foi suspenso automaticamente e pode estar em risco.
//                        Por favor utilize a opção <a href='$path'>
//                                                <br/>\"Reativar E-mail\"</a> abaixo, para alterar sua senha e reativar seu e-mail."
//                    );
//                }
            }else{
                $this->addFlash(
                    'warning',
                    'Usuário sem conta no LDAP.'
                );
                return $this->forward('SluBundle:Registrar:verificacao');
            }


            $isPadraoAntigo = array_diff($nova, $antiga);
            $isPadraoAntigo2 = array_diff($antiga, $nova);
            if ((!empty($person) || !empty($departamento)) && (!empty($isPadraoAntigo) || !empty($isPadraoAntigo2)) ) {
                $recadastrar = true;
                $this->addFlash(
                    'warning',
                    'Conta Antiga. Necessário fazer o recadastramento primeiro.'
                );
            }
//            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMINISTRADOR_SLU')) {
//                return $this->redirectToRoute('lista_pessoas', array(), 301);
//            }
            $temContaSip = false;
            $uid = null;
            if(!empty($person)){
                $temContaSip = $syncService->temUsuarioSei($person);
                $uid = $person->getUid();
            }
            if($person->getPostalAddress() == null){
                $this->addFlash(
                    'warning',
                    'Conta sem email secundário. Necessário adicionar o e-mail secundário.'
                );
                return $this->forward('SluBundle:Usuario:editaDados', array('uid' => $person->getUid()));
            } else if ($msupdate){
                $this->addFlash(
                    'info',
                    'Sua conta foi atualizada com sucesso.'
                );
            }
            return $this->render('SluBundle:Default:index.html.twig', array(
                'recadastrar' => $recadastrar,
                'suspenso'=>$suspenso,
                'emailCriado'=>$emailCriado,
                'temContaSip'=>$temContaSip,
                'uid'=>$uid,
            ));
        } else {
            return $this->redirectToRoute('fos_user_security_login');

        }


//        $person->setAluno(1);
//        $person->setBrPersonCPF('02164997123');
//        $person->setMail(array('meloflavio@gmail.com'));
//        $person->setCn(array('Flavio','Flávio Fernandes de Melo'));
//        $person->setGecos('Flavio Fernandes de Melo');
//        $person->setSn('Fernandes de Melo');
//        $person->setTelephoneNumber('+556399999999');
//        $person->setUid('meloteste28');
//        $person->setHomeDirectory('home/'.$person->getUid());
//        $em->persist($person);
//        dump($person);die();
//        return $this->render('SluBundle:Default:index.html.twig');
    }
    //teste commit

    /**
     * @Route("/login", name="login")
     */
    public function redirectLoginAction(Request $request)
    {
        return $this->redirectToRoute('lightsaml_sp.login');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function redirectLogoutAction(Request $request)
    {
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/default", name="default")
     */
    public function redirectDefaultAction(Request $request)
    {
        return $this->redirectToRoute('homepage');
    }

}
