<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Entity\PessoaLdap;

class GrupoLdapType extends AbstractType
{


    /**
     * PessoaLdapType constructor.
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
        $builder
            ->add('nomeGrupo', TextType::class,array('label' => 'Nome do Grupo:' ,'mapped'=>false, 'attr' => array('resultado'=>"true")))
            ->add('description', TextareaType::class,array('label' => 'Descrição:', 'attr' => array('maxlength' => 1024) ))
            ->add('member', ChoiceType::class, array(
            'label' => 'Membro(s):',
            'attr' => array('class'=>'autocomplete','resultado'=>'true'),
            'required' => true,
            'multiple' => true,
            'choices_as_values' => true
        ));


        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));

        $builder->get('member')->resetViewTransformers();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));

    }
    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $membros = array();
        foreach ($data['member'] as $member){
            if($member instanceof PessoaLdap){
                $membros[$member->getuid()] = $member->getuid();
            }else{
                $membros[$member] = $member;

            }
        }
        $data['member'] = $membros;

        $form->remove('member');

        $form->add('member', ChoiceType::class, array(
            'label' => 'Membro(s):',
            'choices' => $membros,
            'attr' => array('class'=>'autocomplete','resultado'=>'true'),
            'required' => true,
            'multiple' => true,
            'choices_as_values' => true,
        ));

    }

    function onPreSetData(FormEvent $event)
    {
        $dados = $event->getData();
        $form = $event->getForm();
        $membros = array();
        $nomeGrupo = $dados->getCn()[0];
        $form->add('nomeGrupo', TextType::class,array('label' => 'Nome do Grupo:' ,'mapped'=>false, 'data' => $nomeGrupo,'attr' => array('resultado'=>"true")));
        if(!empty($dados->getMember())){
            foreach ($dados->getMember() as $member){
                $nome = (strlen(($member->getGecos()?trim($member->getGecos()):''))>strlen($member->getCn()[0])?trim($member->getGecos()):$member->getCn()[0]).' - '.$member->getUid();
                $membros[$nome] = $member->getUid();
            }
            
            $dados->setMember($membros);
            $form->remove('member');

            $form->add('member', ChoiceType::class, array(
                'label' => 'Membro(s):',
                'choices' => $membros,
                'attr' => array('class'=>'autocomplete','resultado'=>'true'),
                'required' => true,
                'multiple' => true,
                'choices_as_values' => true,
            ));

        }

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\GrupoLdap',
        ));

    }

}