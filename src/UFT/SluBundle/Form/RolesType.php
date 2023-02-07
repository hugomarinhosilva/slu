<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use UFT\SluBundle\Util\RolesHelper;

class RolesType extends AbstractType
{
    /**
     * @var RolesHelper
     */
    private $roles;

    /**
     * @param string $class The User class name
     * @param RolesHelper $roles Array or roles.
     */
    public function __construct( RolesHelper $roles)
    {

        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        parent::buildForm($builder, $options);

        $builder->add('username')
            ->add('roles', ChoiceType::class, array(
                'choices' => $this->roles->getRoles(),
                'required' => false,
                'multiple'=>true,
                'choice_attr' => function ($val, $key, $index) {
                    $var = 'PRINCIPAIS';
                    $key1 = 'data-section';
                    if ($val->getNivel() !== 0) {
                        $key1 = 'data-section';
                        $var = $val->getParent()->getRole();
                    }
                    return [$key1 => $var];
                },
                'attr'=>array('selectTree'=>true),
                'choices_as_values' => true
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user_registration';
    }
}
