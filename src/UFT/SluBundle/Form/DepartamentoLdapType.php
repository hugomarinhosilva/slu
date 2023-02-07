<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Entity\DepartamentoLdap;

class DepartamentoLdapType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('displayName', TextType::class,array('label' => 'Nome do Departamento:' , 'attr' => array('maxlength' => '60')));
        if($options['recadastrar']){
            $builder
                ->add('uid', TextType::class, array('label' => 'Login:', 'attr' => array('style' => 'text-transform:lowercase', 'autocomplete' => "off", 'disabled' => "true")));

        }else{
            $builder
                ->add('uid', TextType::class, array('label' => 'Login:', 'attr' => array('style' => 'text-transform:lowercase', 'autocomplete' => "off", 'resultado' => "true")));

        }
        $builder
            ->add('userPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'As Senhas devem ser iguais.',
                'attr' =>  array('resultado' => "true"),
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Senha:'),
                'second_options' => array('label' => 'Confirmação de senha:'),
                'empty_data' => null
            ));
        if(!$options['recadastrar']){
            $builder
                ->add('Campus', ChoiceType::class,
                    array(
                        'label' => 'Câmpus:',
                        'choices' =>
                            array(
                                'Reitoria' => 'Reitoria',
                                'Araguaína' => 'Araguaína',
                                'Arraias' => 'Arraias',
                                'Miracema' => 'Miracema',
                                'Palmas' => 'Palmas',
                                'Porto Nacional' => 'Porto Nacional',
                                'Gurupi' => 'Gurupi',
                                'Tocantinópolis' => 'Tocantinópolis',
                            ),
                        'choices_as_values' => true,
                        'multiple' => true,
                        'expanded' => false,
                        'required' => true,
                        'attr' => array('multiselect' => 'true','readOnly'=>'true')
                    )
                );
        }
        $builder
            ->add('alteraSenha', HiddenType::class, array(
                'label' => 'Alterar Senha:',
                'data' => 1,
            ))
            ->add('manager', ChoiceType::class, array(
                'label' => 'Responsável:',
                'attr' => array('class'=>'autocomplete','resultado'=>'true'),
                'required' => true,
                'multiple' => true,
                'choices_as_values' => true
            )) ->add('mail', CollectionType::class, array(
                'entry_type' => EmailType::class,
                'label' => 'E-mail Institucional:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'attr' => ['readonly' => true],
                'delete_empty' => true,
            ))
            ->add('postalAddress', EmailType::class, array(
                'label' => 'E-mail Secundário: (Sem ser @uft.edu.br)',
                'required' => true,
                'attr' => ['readonly' => true],
            ));
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->get('manager')->resetViewTransformers();
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }
    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $membros = array();
        foreach ($data['manager'] as $member){
            if($member instanceof DepartamentoLdap){
                $membros[$member->getuid()] = $member->getuid();
            }else{
                $membros[$member] = $member;

            }
        }
        $data['manager'] = $membros;
        $form->remove('manager');
        $form->add('manager', ChoiceType::class, array(
            'label' => 'Responsável:',
            'attr' => array('class'=>'autocomplete','resultado'=>'true'),
            'required' => true,
            'choices' => $membros,
            'multiple' => true,
            'choices_as_values' => true
        ));

    }

    function onPreSetData(FormEvent $event)
    {
        $dados = $event->getData();
        $form = $event->getForm();
        $responsaveis = array();
        $dadosResponsaveis = array();

        if(!empty($dados->getManager())){
            foreach ($dados->getManager() as $member){
                $nome = (strlen(($member->getGecos()?trim($member->getGecos()):''))>strlen($member->getCn()[0])?trim($member->getGecos()):$member->getCn()[0]).' - '.$member->getUid();
                $responsaveis[$nome] = $member->getUid();
                $dadosResponsaveis[$member->getUid()] = $member->getUid();
            }
            $dados->setManager($dadosResponsaveis);
            $form->remove('manager');

            $form->add('manager', ChoiceType::class, array(
                'label' => 'Responsável:',
                'attr' => array('class'=>'autocomplete','resultado'=>'true'),
                'required' => true,
                'choices' => $responsaveis,
                'multiple' => true,
                'choices_as_values' => true
            ));

        }

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\DepartamentoLdap',
            'recadastrar' => false
        ));

    }
}
