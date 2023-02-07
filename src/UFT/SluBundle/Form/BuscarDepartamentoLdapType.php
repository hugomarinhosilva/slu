<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use UFT\SluBundle\Form\MatriculaType;

class BuscarDepartamentoLdapType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uid', TextType::class, array(
                'label' => 'Login de Departamento:',
                'attr' => array(
                    'style' => 'text-transform:lowercase',
                    'autocomplete' => "off"),
                'required' => false))
            ->add('manager', EmailType::class, array(
                'label' => 'Login do Gerente:',
                'required' => false))
            ->add('campus', ChoiceType::class,
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
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => array('multiselect' => 'false')
                )
            );
    }
}