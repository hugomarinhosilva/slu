<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class InsereEmailExternoType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $mail = isset($options['data']['email']) ? $options['data']['email'] : '';
        $builder
            ->add('uid', TextType::class, array('label' => 'Login:', 'attr' => ['readonly' => true], 'data' => $options['data']['uid']))
            ->add('mailExterno', EmailType::class, array('label' => 'E-mail de Recuperação da Conta:', 'required' => false, 'data' => $mail, 'attr' => array('resultado' => 'true')));

    }
}