<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use UFT\SluBundle\Form\MatriculaType;

class BuscarPessoaLdapType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayName', TextType::class, array(
                'label' => 'Nome:',
                'required' => false))
            ->add('brPersonCPF', TextType::class, array(
                'label' => 'CPF:',
                'attr' => array(
                    'class' => "cpf"),
                'required' => false))
            ->add('mail', EmailType::class, array(
                'label' => 'E-mail:',
                'required' => false))
            ->add('uid', TextType::class, array(
                'label' => 'Login:',
                'attr' => array(
                    'style' => 'text-transform:lowercase',
                    'autocomplete' => "off"),
                'required' => false))
            ->add('Matricula', TextType::class, array(
                'label' => 'Matricula:',
                'required' => false,
                'attr' => array(
                    'resultado'=>"true")));
    }
}