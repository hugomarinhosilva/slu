<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PreRegistroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('vinculo', ChoiceType::class,
                array('choices' => array(
                    'Aluno' =>'1',
                    'Servidor'=>'2',
                ),
                    'choices_as_values'=>true,
                    'multiple'=> false,
                    'expanded' => true,
                    'required' => true,
                    'data'=> $options["data"]->getAluno()?1:0,
                    'mapped'=>false))
            ->add('displayName',TextType::class,array('label'=>'Primeiro nome','required' => true,'attr' => array('autocomplete'=>"off")))
            ->add('BrPersonCPF',TextType::class,array('label'=>'CPF','attr' => array(
                'class' => 'cpf','autocomplete'=>"off"),'required' => true))
            ->add('matricula',TextType::class,array('attr' => array('autocomplete'=>"off",'maxlength'=>10, 'title' => 'somente numeros', 'pattern'=>'[0-9]*')))
            ->add('nomeMae',TextType::class,array('label'=>'Primeiro nome da Mãe','required' => true,'attr' => array('autocomplete'=>"off"),'mapped'=>false))
            ->add('schacDateOfBirth', DateType::class,array(
                'label'=>'Data de nascimento',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'invalid_message' => 'Este valor não é válido.',
                'error_bubbling' => true,
                'html5'  => false,
                'required' => true,
                'input' => 'datetime', # return a Datetime object (*),
                'attr' => array(
                    'class' => 'datepicker'
                )
            ));



    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }


}
