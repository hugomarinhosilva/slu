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
use UFT\UserBundle\Entity\Role;

/**
 * Description of LoadUserBundleData
 *
 * @author flaviomelo
 */
class LoadRoleData extends LoadUserBundleData implements OrderedFixtureInterface {

    public function load(ObjectManager $manager) {

        $yaml = $this->getModelFixtures();

//        foreach ($yaml['Role'] as $reference => $columns) {
//            $colum = new Role();
//            $colum->setId($columns['id']);
//            $colum->setRole($columns['role']);
//            $colum->setRoleIdentifier($columns['roleIdentifier']);
//            $colum->setPrincipal($columns['principal']);
//            $colum->setNivel($columns['nivel']);
//            $manager->persist($colum);
//            $manager->flush();
//        }
    }

    public function getOrder() {
        return 1;
    }

    public function getModelFile() {
        return 'role';
    }

}