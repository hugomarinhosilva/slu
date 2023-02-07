<?php


namespace UFT\UserBundle\Form;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Util\LegacyFormHelper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Util\RolesHelper;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use UFT\UserBundle\Entity\Grupo;
use UFT\UserBundle\Entity\Usuario;

class GrupoType extends AbstractType
{
    protected $user = null;
    protected $nivel = 99;
    protected $rolesGrupo = [];
    protected $filtroSql = '';
    protected $filtroSql2 = '';
    protected $filtroSql3 = '';
    /**
     * @var RolesHelper
     */

    /**
     * @param string $class The User class name
     * @param RolesHelper $roles Array or roles.
     */
    public function __construct()
    {
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        if ($this->user != null) {
            foreach ($this->user->getRoles() as $role) {
                if ($role->getId() != null && $role->getNivel() < $this->nivel) {
                    $this->nivel = $role->getNivel();
                }
            }
        }



        if ($options['data'] instanceof Grupo ) {

            foreach ($options['data']->getRoles() as $role) {
                $this->rolesGrupo[] = $role->getId();
            }
        }

        if (count($options['data']->getUsers()->toArray()) > 0) {

            $this->filtroSql .= "c.id in (";
            foreach ($options['data']->getUsers()->toArray() as $membros) {
                $this->filtroSql .= " " . $membros->getId() . ",";
            }
            foreach ($options['data']->getChefes()->toArray() as $membros) {
                $this->filtroSql .= " " . $membros->getId() . ",";
            }
            $this->filtroSql = substr($this->filtroSql, 0, -1) . ')';
        }
        $builder
            ->add('name', TextType::class, array('label' => 'Nome do Grupo:', 'attr' => array('resultado' => "true")))
            ->add('roles', EntityType::class, array(
                'label' => 'Tipo de permissões:',
                'class' => 'UFT\UserBundle\Entity\Role',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('r')->where('(r.principal = 0 AND r.nivel >=:nivel) or r.id in (:ids)')
                        ->setParameter('nivel', $this->nivel)->setParameter('ids', $this->rolesGrupo)->orderBy('r.id');
                },
                'required' => true,
                'multiple' => true,
//                'choice_attr' => function ($val, $key, $index) {
//                    $var = 'PRINCIPAIS';
//                    $key1 = 'data-section';
//                    if ($val->getNivel() !== 0) {
//                        $key1 = 'data-section';
//                        $var = $val->getParent()->getRole();
//                    }
//                    return [$key1 => $var];
//                },
                'attr' => array('multiselect' => 'true'),
                'choices_as_values' => true,
                'choice_label' => 'roleIdentifier',
            ))->add('filtros', EntityType::class,
                array(
                    'class' => 'UFT\UserBundle\Entity\FiltroUnidade',
                    'label' => 'Filtros:',
                    'choice_label' => 'nomeUnidade',
                    'choices_as_values' => true,
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,
                    'attr' => array('multiselect' => 'true')
                )
            )
            ->add('chefes', EntityType::class, array(
                'label' => 'Chefe(s):',
                'class' => 'UFT\UserBundle\Entity\Usuario',
                'attr' => array('resultado'=>'true', 'class' => 'autocomplete col-md-2'),
                'required' => true,
                'multiple' => true,
                'choices_as_values' => true,
                'query_builder' => function (EntityRepository $er) {
                    $sql = $er->createQueryBuilder('c');
                    if($this->filtroSql!= '')$sql->where($this->filtroSql);
                    return $sql->orderBy('c.id')->setMaxResults(20);
                },
            ))
            ->add('users', EntityType::class, array(
                'label' => 'Membro(s):',
                'class' => 'UFT\UserBundle\Entity\Usuario',
                'attr' => array('resultado'=>'true', 'class' => 'autocomplete col-md-2'),
                'required' => false,
                'multiple' => true,
                'choices_as_values' => true,
                'query_builder' => function (EntityRepository $er) {
                    $sql = $er->createQueryBuilder('c');
                    if($this->filtroSql!= '')$sql->where($this->filtroSql);
                    return $sql->orderBy('c.id')->setMaxResults(20);
                },

            ));


        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }


    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $form->remove('users');
        $this->filtroSql2 .= "c.id in (";
        foreach ($data['users'] as $member) {
            $this->filtroSql2 .= " " . $member . ",";
        }

        $this->filtroSql2 = substr($this->filtroSql2, 0, -1) . ')';
        $form->add('users', EntityType::class, array(
            'label' => 'Membro(s):',
            'class' => 'UFT\UserBundle\Entity\Usuario',
            'attr' => array('resultado'=>'true', 'class' => 'autocomplete col-md-2'),
            'required' => false,
            'multiple' => true,
            'choices_as_values' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->where($this->filtroSql2)->setMaxResults(20)->orderBy('c.id');
            },
        ));


        $form->remove('chefes');

        $this->filtroSql3 .= "c.id in (";
        foreach ($data['chefes'] as $member) {
            $this->filtroSql3 .= " " . $member . ",";
        }

        $this->filtroSql3 = substr($this->filtroSql3, 0, -1) . ')';
        $form->add('chefes', EntityType::class, array(
            'label' => 'Chefe(s):',
            'class' => 'UFT\UserBundle\Entity\Usuario',
            'attr' => array('resultado'=>'true', 'class' => 'autocomplete col-md-2'),
            'required' => true,
            'multiple' => true,
            'choices_as_values' => true,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('c')->where($this->filtroSql3)->setMaxResults(20)->orderBy('c.id');
            },
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'user' => null
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'slu_rest_form';
    }

}
