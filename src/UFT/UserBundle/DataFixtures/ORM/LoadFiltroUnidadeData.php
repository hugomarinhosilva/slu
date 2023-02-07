<?php
/**
 * Created by PhpStorm.
 * User: flavio
 * Date: 06/07/16
 * Time: 09:14
 */

namespace UFT\UserBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UFT\UserBundle\Entity\FiltroUnidade;

/**
 * Description of LoadFiltroUnidadeData
 *
 * @author flaviomelo
 */
class LoadFiltroUnidadeData extends LoadUserBundleData implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {

        $yaml = $this->getModelFixtures();

        foreach ($yaml['FiltroUnidade'] as $reference => $columns) {
            $colum = new FiltroUnidade();
            $colum->setCodEstruturado($columns['codEstruturado']);
            $colum->setNomeUnidade($columns['nomeUnidade']);
            $manager->persist($colum);
            $manager->flush();
        }
    }

    public function getOrder() {
        return 2;
    }

    public function getModelFile() {
        return 'filtrounidade';
    }

}