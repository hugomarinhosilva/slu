<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Form\MatriculaType;

class AlteraDadosUsuarioType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('telephoneNumber', CollectionType::class, array(
                'entry_type' => TextType::class,
                'label' => 'Telefones:',
                'attr' => array('class' => "telefone"),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'delete_empty' => true,


            ))
            ->add('postalAddress', EmailType::class, array(
                'label' => 'E-mail Secundário: (Sem ser @uft.edu.br)',
                'required' => true,
            ))
            ->add('verificarSenha', PasswordType::class, array(
                'label' => 'Digite a senha para confirmar:',
                'label_attr'=>array('class'=>'control-label'),
                'invalid_message' => 'As Senhas inválida',
                'attr' => array('class' => 'password-field col-md-8','autocomplete'=>'off'),
                'required' => true,
                'mapped'=>false
            ))
           ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\PessoaLdap',
        ));

    }

}