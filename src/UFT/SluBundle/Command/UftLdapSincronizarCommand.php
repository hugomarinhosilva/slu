<?php

namespace UFT\SluBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UFT\SluBundle\Entity\PessoaLdap;
use Doctrine\ORM\EntityManager;
use UFT\SluBundle\Util\Sincronizacao;

class UftLdapSincronizarCommand extends ContainerAwareCommand{
    private $ldap;
    /* @var $em EntityManager */
    private $em;
    /** @var  Sincronizacao $sincronizador*/
    private $sincronizador;
    private $gearman;

    protected function configure(){
        $this
            ->setName('uft:ldap:sincronizar')
            ->setDescription('Comando para sincronizar todas as contas de usuário no LDAP com o SIE')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $this->getConnections();
        $contador1 = $this->limparVariaveisDeControleDeBusca($output);
        $filtro['&'] = array(
            'objectClass' => 'brPerson',
            '&' => array(
                'objectClass' => 'inetOrgPerson',
                '&' => array(
                    'objectClass' => 'organizationalPerson',
                    '&' => array(
                        'objectClass' => 'person',
                        '&' => array(
                            'objectClass' => 'schacPersonalCharacteristics',
                            '&' => array(
                                'objectClass' => 'top',
                                '&' => array(
                                    'objectClass' => 'uftOrgUnit',
                                    '&' => array(
                                        'objectClass' => 'posixAccount',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            'title' => '0',
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
        );
        $contador2 = 0;
        $contadorCredenciaisCriadas = 0;
        $contadorCredenciaisAtualizada = 0;
        do{
            $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtro, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
            foreach ($persons as $person) {
                try{
                    /** @var PessoaLdap $person */
                    $this->sincronizador->sincronizar($person,true);
                    if (!($person->getTeste())) {
                        $this->gearman->doBackgroundJob('UFTSluBundleServicesGSuitWorkerService~atualizarGruposDeUsuarioGSuit', json_encode(array(
                            'uid' => $person->getUid(),
                        )));
                    }
                    $retorno = $this->sincronizador->verificarUsuarioSie($person,true);
                    if ($retorno === 1){
                        $contadorCredenciaisCriadas++;
                    }elseif ($retorno === 2){
                        $contadorCredenciaisAtualizada++;
                    }
                    @ldap_mod_replace($this->ldap, $person->getDn(), array('title' => '1'));
                    $this->progressBar($contador2,$contador1);
                    $contador2 ++;
                }catch (\Exception $exception){
                    echo $exception;
                }
            }
        }while (count($persons) > 0);
        ldap_close($this->ldap);
        $output->writeln('');
        $output->writeln($contadorCredenciaisCriadas . ' contas não tinham credenciais.');
        $output->writeln($contadorCredenciaisAtualizada . ' contas tiveram suas credenciais atualizadas.');
        $output->writeln($contador2 . ' contas sincronizadas com sucesso!');
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
        $this->gearman = $this->getContainer()->get('gearman');
        $this->sincronizador = $this->getContainer()->get('uft.sincronizacao.manager');
    }

    private function limparVariaveisDeControleDeBusca(OutputInterface $output){
        $output->writeln('Limpando variáveis de controle...');
        /*
         * Adicionar title as entradas que não possuem
         * */
        $filtroAdd['&'] = array(
            'objectClass' => 'brPerson',
            '&' => array(
                'objectClass' => 'inetOrgPerson',
                '&' => array(
                    'objectClass' => 'organizationalPerson',
                    '&' => array(
                        'objectClass' => 'person',
                        '&' => array(
                            'objectClass' => 'schacPersonalCharacteristics',
                            '&' => array(
                                'objectClass' => 'top',
                                '&' => array(
                                    'objectClass' => 'uftOrgUnit',
                                    '&' => array(
                                        'objectClass' => 'posixAccount',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
            '!' => Array('title' => '*')
        );
        /*
         * Alterar para 0 o title as entradas com valor diferente de 0
         * */
        $filtroReplace['&'] = array(
            'objectClass' => 'brPerson',
            '&' => array(
                'objectClass' => 'inetOrgPerson',
                '&' => array(
                    'objectClass' => 'organizationalPerson',
                    '&' => array(
                        'objectClass' => 'person',
                        '&' => array(
                            'objectClass' => 'schacPersonalCharacteristics',
                            '&' => array(
                                'objectClass' => 'top',
                                '&' => array(
                                    'objectClass' => 'uftOrgUnit',
                                    '&' => array(
                                        'objectClass' => 'posixAccount',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            '|' => array(
                'institucional' => '0',
                '!' => array('institucional' => '*')
            ),
            '!' => array('title' => '0')
        );

        $contador = 0;
        do{
            $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtroAdd, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
            $contador += count($persons);
            $this->infoBar($contador);
            foreach ($persons as $person) {
                @ldap_mod_add($this->ldap, $person->getDn(), array('title' => '0'));
            }
        }while(count($persons) > 0);
        do{

            $persons = $this->em->getRepository(PessoaLdap::class)->findByComplex($filtroReplace, array('searchDn' => $this->getContainer()->getParameter('people_basedn')));
            $contador += count($persons);
            $this->infoBar($contador);
            foreach ($persons as $person) {
                @ldap_mod_replace($this->ldap, $person->getDn(), array('title' => '0'));
            }
        }while(count($persons) > 0);
        $output->writeln('');
        $output->writeln('Variáveis de controle limpas!');
        return $contador;
    }

    private function progressBar($done, $total) {
        $perc = floor(($done / $total) * 100);
        $left = 100 - $perc;
        $write = sprintf("\033[0G\033[2K[%'={$perc}s>%-{$left}s] - $perc%% - $done/$total", "", "");
        fwrite(STDERR, $write);
    }

    private function infoBar($total){
        $write = sprintf("\033[0G\033[2K Total de contas: $total","","");
        fwrite(STDERR, $write);
    }
}
