<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Form\MatriculaType;

class AlteraSenhaDepartamentoLdapType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('senhaAntiga', PasswordType::class, array(
                'mapped' => false
            ))
            ->add('userPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'attr' => array('resultado' => true),
                'invalid_message' => 'As Senhas devem ser iguais.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Nova Senha:'),
                'second_options' => array('label' => 'Confirmação da nova senha:'),
            ));
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\DepartamentoLdap',
        ));

    }

}