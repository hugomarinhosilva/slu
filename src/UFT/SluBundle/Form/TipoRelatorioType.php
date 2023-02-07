<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TipoRelatorioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            ->add('tipo', ChoiceType::class,
                array('choices' => array(
                    'Aluno' =>'1',
                    'Professor'=>'2',
//                    'Funcionário'=>'3',
//                    'Todos'=>'4',
                ),
                    'choices_as_values'=>true,
                    'multiple'=> false,
                    'expanded' => true,
                    'required' => true,
                    'mapped'=>false))
//            ->add('curso',ChoiceType::class,
//                array(
//                    'label' => 'Curso:',
//                    'choices' =>
//                        array(
//                        ),
//                    'choices_as_values' => true,
//                    'expanded' => false,
//                ))
//            ->add('campus',ChoiceType::class,
//                array(
//                    'label' => 'Campus:',
//                    'choices' =>
//                        array(
//                        ),
//                    'choices_as_values' => true,
//                    'expanded' => false,
//                ))
            ->add('departmentNumber',ChoiceType::class,
                array(
                    'label' => 'Filtro:',
                    'choices' =>
                        array(
                        ),
                    'choices_as_values' => true,
                    'expanded' => false,
                ))
            ;



    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }


}
