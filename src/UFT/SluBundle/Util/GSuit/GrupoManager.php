<?php
/**
 * Created by PhpStorm.
 * User: rafael
 * Date: 12/05/16
 * Time: 17:13
 */

namespace UFT\SluBundle\Util\GSuit;

use Doctrine\ORM\EntityManager;
use Google_Client;
use Google_Service_Books;
use Google_Service_Directory;
use Google_Service_Exception;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Util\Convertores;

class GrupoManager
{
    protected $dir;
    protected $client;
    protected $conversor;
    protected $emailManager;
    protected $em2;
    /**
     * GrupoManager constructor.
     * @param Convertores $conversor
     * @param EmailManager $emailManager
     * @param EntityManager $entityManager2
     * @param GoogleClient $googleClient
     */
    public function __construct($conversor, $emailManager,$entityManager2, GoogleClient $googleClient)
    {
        $this->emailManager = $emailManager;
        $this->client= $googleClient->getGoogleClient();
        $this->dir = $googleClient->directory();
        $this->conversor = $conversor;
        $this->em2 = $entityManager2;
    }

    /**
     * @param PessoaLdap $pessoaLdap
     */
    public function atualizarGruposDeUsuarioGSuit($pessoaLdap){

        if($this->emailManager === true AND !$pessoaLdap->getTeste()) {
            $gruposBusca = $this->dir->groups->listGroups(array('domain' => 'mail.uft.edu.br', 'userKey' => $pessoaLdap->getUid() . '@mail.uft.edu.br'))['modelData'];
            (empty($gruposBusca)) ? $gruposBusca = array() :  $gruposBusca = $gruposBusca['groups'];
            $gruposAtuais = array();
            if (!empty($gruposBusca)){
                foreach ($gruposBusca as $grupo) {
                    $gruposAtuais[] = $grupo['email'];
                }
            }
            $gruposAdicionar = array();
            $gruposRemover = array();

            $cpf = null;
            if (is_null($pessoaLdap->getBrPersonCPF())){
                if (strcmp($pessoaLdap->getCPF(), "0") !== 0){
                    $cpf = $pessoaLdap->getCPF();
                }
            }else{
                $cpf = $pessoaLdap->getBrPersonCPF();
            }
            if (is_null($cpf)){
                $campusLists = array();
                foreach ($pessoaLdap->getCampus() as $campus){
                    $campusLists[] =  $this->conversor->tirarAcentos(strtolower(str_replace(" ", "", $campus))) . '-l@mail.uft.edu.br';
                }
                if ($pessoaLdap->getAluno() == 1){
                    if (count($campusLists) > 1){
                        if (in_array('reitoria-l@mail.uft.edu.br', $campusLists)){
                            foreach ($campusLists as $campus){
                                if (strcmp($campus,'reitoria-l@mail.uft.edu.br') !==0){
                                    $gruposAdicionar[] = 'alunos_' . $campus;
                                }
                            }
                        }else{
                            foreach ($campusLists as $campus){
                                $gruposAdicionar[] = 'alunos_' . $campus;
                            }
                        }
                    } else {
                        $gruposAdicionar[] = 'alunos_' . $campusLists[0];
                    }
                }
                if ($pessoaLdap->getFuncionario() == 1){
                    if (count($campusLists) > 1){
                        if (in_array('reitoria-l@mail.uft.edu.br', $campusLists)){
                            foreach ($campusLists as $campus){
                                if (strcmp($campus,'reitoria-l@mail.uft.edu.br') ==0){
                                    $gruposAdicionar[] = 'tecnicos_' . $campus;
                                }
                            }
                        }else{
                            foreach ($campusLists as $campus){
                                $gruposAdicionar[] = 'tecnicos_' . $campus;
                            }
                        }
                    } else {
                        $gruposAdicionar[] = 'alunos_' . $campusLists[0];
                    }
                }
                if ($pessoaLdap->getProfessor() == 1){
                    foreach ($campusLists as $campus){
                        $gruposAdicionar[] = 'professores_' . $campus;
                    }
                }
            }else{
                $alunos = $this->em2->getRepository('SluBundle:SieAluno')->createQueryBuilder('a')
                    ->where('a.cpf = :cpf')
                    ->andWhere('a.idSituacao = :idSituacao')
                    ->setParameter('cpf', $this->conversor->formataCPF(trim($cpf)))
                    ->setParameter('idSituacao', 1)
                    ->orderBy('a.dataIngresso', 'DESC')
                    ->getQuery()->getResult();
                if (!is_null($alunos)){
                    foreach ($alunos as $aluno){
                        $grupoAluno = 'alunos_' . $this->conversor->tirarAcentos(strtolower(str_replace(" ", "", $aluno->getNomeCampus()))) . "-l@mail.uft.edu.br";
                        if (!in_array($grupoAluno, $gruposAdicionar)){
                            $gruposAdicionar[] = $grupoAluno;
                        }
                    }
                }
                $professores = $this->em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                    ->where('s.cpf = :cpf')
                    ->andWhere('s.idCargo = :cargo1 OR s.idCargo = :cargo2 OR s.idCargo = :cargo3')
                    ->andWhere('s.idSituacao <> :idSituacao')
                    ->setParameter('cpf', $this->conversor->formataCPF(trim($cpf)))
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->orderBy('s.dataPosse', 'DESC')
                    ->getQuery()->getResult();
                if (!is_null($professores)){
                    foreach ($professores as $professor){
                        $grupoProfessor = 'professores_' . $this->conversor->tirarAcentos(strtolower(str_replace(" ", "", $professor->getCampus()))) . "-l@mail.uft.edu.br";
                        if (!in_array($grupoProfessor, $gruposAdicionar)){
                            $gruposAdicionar[] = $grupoProfessor;
                        }
                    }
                }
                $servidores = $this->em2->getRepository('SluBundle:SieServidor')->createQueryBuilder('s')
                    ->where('s.cpf = :cpf')
                    ->andWhere('s.idCargo <> :cargo1')
                    ->andWhere('s.idCargo <> :cargo2')
                    ->andWhere('s.idCargo <> :cargo3')
                    ->andWhere('s.idSituacao <> :idSituacao')
                    ->setParameter('cpf', $this->conversor->formataCPF(trim($cpf)))
                    ->setParameter('cargo1', 61)
                    ->setParameter('cargo2', 62)
                    ->setParameter('cargo3', 733)
                    ->setParameter('idSituacao', 5)
                    ->orderBy('s.dataPosse', 'DESC')
                    ->getQuery()->getResult();
                if (!is_null($servidores)){
                    foreach ($servidores as $servidor){
                        $grupoServidor = 'tecnicos_' . $this->conversor->tirarAcentos(strtolower(str_replace(" ", "", $servidor->getCampus()))) . "-l@mail.uft.edu.br";
                        if (!in_array($grupoServidor, $gruposAdicionar)){
                            $gruposAdicionar[] = $grupoServidor;
                        }
                    }
                }
            }
            $gruposPadroes = array();
            foreach ($gruposAtuais as $grupo){
                if (strstr($grupo,'alunos_') OR strstr($grupo,'professores_') OR strstr($grupo,'tecnicos_')){
                    $gruposPadroes[] = $grupo;
                }
            }
            /*
             * Lista de grupos padrões a remover o usuário
             */
            foreach ($gruposPadroes as $grupo){
                if (!in_array($grupo, $gruposAdicionar)){
                    $gruposRemover[] = $grupo;
                }
            }
            /*
             * Remover da lista de grupos adicionar os grupos ao qual o usuário já está adicionado
             */
            $total = count($gruposAdicionar);
            for ($i=0; $i<$total;$i++){
                if (in_array($gruposAdicionar[$i],$gruposAtuais)){
                    unset($gruposAdicionar[$i]);
                }
            }
            (count($gruposAdicionar) > 0) ? $this->adicionarUsuarioGrupos($pessoaLdap, $gruposAdicionar): false;
            (count($gruposRemover) > 0) ? $this->removerUsuarioGrupos($pessoaLdap, $gruposRemover):false;
        }
    }

    /**
     * @param PessoaLdap $pessoaLdap
     * @param Array() $gruposAdicionar
     */
    public function adicionarUsuarioGrupos($pessoaLdap, $gruposAdicionar){
        $membro = new \Google_Service_Directory_Member();
        $membro->setEmail($pessoaLdap->getUid() . '@mail.uft.edu.br');
        $membro->setRole("MEMBER");
        $membro->setType("USER");
        foreach ( $gruposAdicionar as $grupoAdicionar){
            try{
                $this->dir->members->insert($grupoAdicionar,$membro);
            }catch(Google_Service_Exception $e){

            };
        }
    }

    /**
     * @param PessoaLdap $pessoaLdap
     * @param Array() $gruposRemover
     */
    public function removerUsuarioGrupos($pessoaLdap, $gruposRemover){
        foreach ($gruposRemover as $grupoRemover){
            try{
                $this->dir->members->delete($grupoRemover, $pessoaLdap->getUid() . '@mail.uft.edu.br');
            }catch(Google_Service_Exception $e){

            };
        }
    }
}