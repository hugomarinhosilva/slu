<?php
/**
 * Created by PhpStorm.
 * User: rafaelmansilha
 * Date: 25/10/17
 * Time: 13:41
 */

namespace UFT\SluBundle\Services;
use Doctrine\ORM\EntityManager;
use Mmoreram\GearmanBundle\Driver\Gearman;
use UFT\LdapOrmBundle\Entity\RevisaoBase;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Util\GSuit\GrupoManager;

/**
 * @Gearman\Work(
 *     service="gSuitWorkerService"
 * )
 */
class GSuitWorkerService
{
    /* @var $grupoManager GrupoManager */
    private $grupoManager;
    /* @var $ldapEm EntityManager*/
    private $ldapEm;
    /* @var $em EntityManager*/
    private $em;
    public function __construct($grupoManager, $ldapEm, $em)
    {
        $this->grupoManager = $grupoManager;
        $this->ldapEm = $ldapEm;
        $this->em = $em;
    }
    /**
     * Test method to run as a job
     *
     * @param \GearmanJob $job Object with job parameters
     *
     * @Gearman\Job(
     *     iterations = 0,
     * )
     */
    public function atualizarGruposDeUsuarioGSuit(\GearmanJob $job)
    {

        try{
            $workload = json_decode($job->workload());
            $pessoaLdap = $this->ldapEm->getRepository(PessoaLdap::class)->findByUid($workload->uid);
            if (!empty($pessoaLdap)){
                $this->grupoManager->atualizarGruposDeUsuarioGSuit($pessoaLdap[0]);
            }
        }catch (\Exception $e){
            $workload = json_decode($job->workload());
            $roleRevisao = new RevisaoBase();
            $roleRevisao->setEntidade('UFT\SluBundle\Services\GSuitWorkerService');
            $roleRevisao->setTipoRevisao('FALHA');
            $roleRevisao->setIndentificador($workload->uid);
            $roleRevisao->setUsername('Worker');
            $this->em->persist($roleRevisao);
            $this->em->flush();
        }
    }
}