<?php

namespace UFT\SluBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UFT\SluBundle\Entity\PessoaLdap;

class UftLdapAtualizarDepartamentNumberCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('uft:ldap:atualizar_departament_number')
            ->setDescription('...')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $argument = $input->getArgument('argument');

        //LDAPCON
        $port = "389";
        $password = $this->getContainer()->getParameter('ldap_password');
        $host = $this->getContainer()->getParameter('ldap_host');
        $managerdn = $this->getContainer()->getParameter('ldap_username');
        $ldap = ldap_connect($host, $port);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = ldap_bind($ldap, $managerdn, $password);


        $em = $this->getContainer()->get('ldap_entity_manager');
        $em2 = $this->getContainer()->get('doctrine')->getManager('db2');
        $filtro = array();
        $filtro['&'] = array(
            'institucional' => '0',
            '!' => Array('title' => '*')
        );
        $countTitle0 = 0;
        $countTitle1 = 0;
        $countTitle2 = 0;
        $convertor = $this->getContainer()->get('uft.convertores');
        $persons = $em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        while (count($persons) > 0){
            foreach ($persons as $person){
                $matriculas = $person->getMatricula();
                $cpf = $person->getCPF();
                if (!is_null($matriculas)){
                    $index = array_search('0',$matriculas);
                    while ($index !== false){
                        unset($matriculas[$index]);
                        $index = array_search('0',$matriculas);
                    }
                    $matriculas = array_values($matriculas);
                }else{
                    $matriculas = array();
                }
                $countMatriculas = count($matriculas);
                $query1 = $em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s');
                $query2 = $em2->getRepository('SluBundle:SieAluno')->createQueryBuilder('s');
                if (is_null($cpf) || $cpf == '0'){
                    if ($countMatriculas > 0){
                        $query1->Where('trim(s.matricula) = :matricula')
                            ->setParameter('matricula', trim($matriculas[0]));
                        $query2->Where('trim(s.matricula) = :matricula')
                            ->setParameter('matricula', trim($matriculas[0]));
                        unset($matriculas[0]);
                    }
                }else{
                    $query1->Where('trim(s.cpf) = :cpf')
                        ->setParameter('cpf',  $convertor->formataCPF(trim($cpf)));
                    $query2->Where('trim(s.cpf) = :cpf')
                        ->setParameter('cpf',  $convertor->formataCPF(trim($cpf)));
                }

                foreach ($matriculas as $matricula){
                    $matricula = preg_replace('/\D/', '', $matricula);
                    $query1->orWhere('trim(s.matricula) = :matricula' . trim($matricula))
                        ->setParameter('matricula'. trim($matricula), trim($matricula));
                    $query2->orWhere('trim(s.matricula) = :matricula' . trim($matricula))
                        ->setParameter('matricula'. trim($matricula), trim($matricula));
                }
                $usuarios = array();
                $usuarios2 = array();
                $idPessoa = '0';
                if (!is_null($person->getIdPessoa())){
                    $idPessoa = trim($person->getIdPessoa());
                }

                if ((!is_null($cpf) && $cpf != '0') || $countMatriculas > 0 ){
                    if ( $idPessoa != '0'){
                        $query1->orWhere('trim(s.idPessoa) = :idPessoa')
                            ->setParameter('idPessoa', $idPessoa);
                        $query2->orWhere('trim(s.idPessoa) = :idPessoa')
                            ->setParameter('idPessoa', $idPessoa);
                    }
                    $usuarios = $query1->getQuery()->getResult();
                    $usuarios2 = $query2->getQuery()->getResult();
                }elseif ( $idPessoa != '0'){
                    $query1->Where('trim(s.idPessoa) = :idPessoa')
                        ->setParameter('idPessoa', $idPessoa);
                    $query2->Where('trim(s.idPessoa) = :idPessoa')
                        ->setParameter('idPessoa', $idPessoa);
                    $usuarios = $query1->getQuery()->getResult();
                    $usuarios2 = $query2->getQuery()->getResult();
                }
                //Verifica se há mais de uma pessoa
                $countUsuarios = 0;
                $idPessoa = 0;
                if(count($usuarios) > 0) {
                    $idPessoa = $usuarios[0]->getIdPessoa();
                    $countUsuarios++;
                }else if (count($usuarios2) > 0){
                    $idPessoa = $usuarios2[0]->getIdPessoa();
                    $countUsuarios++;
                }
                foreach ($usuarios as $usuario){
                    if ($idPessoa != $usuario->getIdPessoa()){
                        $countUsuarios++;
                    }
                }
                foreach ($usuarios2 as $usuario2){
                    if ($idPessoa != $usuario2->getIdPessoa()){
                        $countUsuarios++;
                    }
                }
                if ($countUsuarios == 1){
                    $departamentNumbers = Array();
                    $campus = Array();
                    foreach ($usuarios as $usuario){
                        if (array_search(trim($usuario->getCodEstruturadoExercicio()),$departamentNumbers) === false){
                            array_push($departamentNumbers, trim($usuario->getCodEstruturadoExercicio()));
                        }
                        if (array_search(trim($usuario->getCampus()),$campus) === false){
                            array_push($campus, trim($usuario->getCampus()));
                        }
                    }
                    foreach ($usuarios2 as $usuario2){
                        if (array_search(trim($usuario2->getCodEstruturadoExercicio()),$departamentNumbers) === false){
                            array_push($departamentNumbers, trim($usuario2->getCodEstruturadoExercicio()));
                        }
                        if (array_search(trim($usuario2->getNomeCampus()),$campus) === false){
                            array_push($campus, trim($usuario2->getNomeCampus()));
                        }
                    }

                    foreach ($campus as $c){
                        $index = array_search('',$campus);
                        if ( $index !== false){
                            unset($campus[$index]);
                        }
                    }
                    $campus = array_values($campus);

                    if (is_null($person->getDepartmentNumber())){
                        if (!@ldap_mod_add($ldap, $person->getDn(), array('departmentNumber' => $departamentNumbers))){
                            $output->writeln("Error ao adicionar departmentNumber do dn: " . $person->getDn() . PHP_EOL);
                        }
                    }else if (!$this->valores_iguais($departamentNumbers, $person->getDepartmentNumber())){
                        if (!@ldap_mod_replace($ldap, $person->getDn(), array('departmentNumber' => $departamentNumbers))){
                            $output->writeln("Error ao modificar departmentNumber do dn: " . $person->getDn() . PHP_EOL);
                        }
                    }
                    if (is_null($person->getCampus())){
                        if (!@ldap_mod_add($ldap, $person->getDn(), array('Campus' => $campus))){
                            $output->writeln("Error ao adicionar Campus do dn: " . $person->getDn() . PHP_EOL);
                        }
                    }else if (!$this->valores_iguais($campus, $person->getCampus())){

                        if (!@ldap_mod_replace($ldap, $person->getDn(), array('Campus' => $campus))){
                            $output->writeln("Error ao modificar Campus do dn: " . $person->getDn() . PHP_EOL);
                            dump($this->valores_iguais($campus, $person->getCampus()), $campus, $person->getCampus());
                        }
                    }
                    // Title 0 - Conta Atualizada com sucesso
                    @ldap_mod_add($ldap, $person->getDn(), array('title' => '0'));
                    $countTitle0++;
                }else if($countUsuarios == 0){
                    // Title 1 - Conta Não atualizada - Usuário não encontrado no sie
                    @ldap_mod_add($ldap, $person->getDn(), array('title' => '1'));
                    $countTitle1++;
                }else {
                    // Title 2 - Conta Não atualizada - Encontrado mais de uma ocorrência no Sie
                    @ldap_mod_add($ldap, $person->getDn(), array('title' => '2'));
                    $countTitle2++;

                }
            }
            $persons = $em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        }
        ldap_close($ldap);
        $output->writeln("Contas Atualizadas" . PHP_EOL  . PHP_EOL);
        $output->writeln("Número de Contas atualizadas com sucesso: " . $countTitle0 . PHP_EOL );
        $output->writeln("Número de Contas não atualizadas - Usuário não encontrado no sie: " . $countTitle1 . PHP_EOL );
        $output->writeln("Número de Contas não atualizadas - Encontrado mais de uma ocorrência no Sie : " . $countTitle2 . PHP_EOL );
    }

    function valores_iguais( $arrayA , $arrayB ) {
        sort( $arrayA );
        sort( $arrayB );
        return $arrayA == $arrayB;
    }

}
