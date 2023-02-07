<?php

namespace UFT\SluBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Util\GSuit\GrupoManager;

class UftGsuitAtualizarListaEmailCommand extends ContainerAwareCommand
{
    private $ldap;
    /* @var $em EntityManager */
    private $em;
    /* @var $grupoEmailManager GrupoManager */
    private $grupoEmailManager;
    protected function configure()
    {
        $this
            ->setName('uft:gsuit:atualizar-lista-email')
            ->setDescription('Comando para atualizar lista de e-mails no GSuit com base nos usuários do LDAP')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->getConnections();

//        $person = $this->em->getRepository(PessoaLdap::class)->findByUid('jeany')[0];
//        $this->grupoEmailManager->atualizarGruposDeUsuarioGSuit($person);
//        dump("DONE!");die();
        /*
         * Adicionar title as entradas que não possuem
         * */
        $filtro1['&'] = array(
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
            '!' => Array('title' => '*')
        );
        /*
         * Alterar para 0 o title as entradas com valor diferente de 0
         * */
        $filtro2['&'] = array(
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
            '!' => Array('title' => '0')
        );
//        $filtro2['&'] = array(
//            '|' => array(
//                'institucional' => '0',
//                '!' => array('institucional' => '*')
//            ),
//            'title' => '0'
//        );
//        $this->limparVariavelDeControleDeBusca($filtro2,'replace');
//        die();
        $contador1 = $this->limparVariavelDeControleDeBusca($filtro1, 'add');
        $contador1 += $this->limparVariavelDeControleDeBusca($filtro2,'replace');

        $output->writeln('Variáveis de controle limpas.');

        $filtro['&'] = array(
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
            'CPF' => '*',
            'title' => '0',
            'Campus' => '*',
            '!' => array('Campus' => '0')
        );
        $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        $contador2 = 0;
        while (count($persons) > 0) {
            foreach ($persons as $person) {
                $this->grupoEmailManager->atualizarGruposDeUsuarioGSuit($person);
                @ldap_mod_replace($this->ldap, $person->getDn(), array('title' => '1'));
                $this->progressBar($contador2,$contador1);
                $contador2 ++;
            }
            $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        }
        ldap_close($this->ldap);
        $output->writeln('Listas de Grupos Atualizada.');
    }

    private function getConnections(){
        //LDAPCON
        $port = "389";
        $password = $this->getContainer()->getParameter('ldap_password');
        $host = $this->getContainer()->getParameter('ldap_host');
        $managerdn = $this->getContainer()->getParameter('ldap_username');
        $this->ldap = ldap_connect($host, $port);
        ldap_set_option($this->ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        $bind = ldap_bind($this->ldap, $managerdn, $password);
        $this->em = $this->getContainer()->get('ldap_entity_manager');
        $this->grupoEmailManager = $this->getContainer()->get('uft.grupo_email.manager');
    }

    private function limparVariavelDeControleDeBusca($filtro, $mod){
        $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        $contador = count($persons);
        while (count($persons) > 0){
            foreach ($persons as $person) {
                if (strcmp($mod,'add')===0){
                    @ldap_mod_add($this->ldap, $person->getDn(), array('title' => '0'));
                }else{
                    @ldap_mod_replace($this->ldap, $person->getDn(), array('title' => '0'));
                }
            }
            $contador += count($persons);
            $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
        }
        return $contador;
    }

    private function progressBar($done, $total) {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }
}