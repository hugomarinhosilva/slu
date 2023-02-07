<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ResetaContaType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array('label' => 'Login:', 'attr' => ['readonly' => true]))
            ->add('cpf', TextType::class, array('label' => 'CPF:', 'required' => true, 'attr'=> array('class' => "cpf")));
            if($options['data']['mail']==null){
                $builder->add('mail', EmailType::class, array('label' => 'E-mail de Recuperação da Conta:', 'required' => true, 'attr' => array('resultado' => 'true')));
            }else{
                $builder->add('mail', HiddenType::class, array('label' => 'E-mail de Recuperação da Conta:', 'required' => true, 'attr' => array('resultado' => 'true')));

            }
    }

}