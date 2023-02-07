<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use UFT\SluBundle\Form\MatriculaType;

class PessoaLdapType extends AbstractType
{
    private $authorization;

    /**
     * PessoaLdapType constructor.
     */

    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorization = $authorizationChecker;
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('displayName', TextType::class, array('label' => 'Nome Completo:', 'attr' => ['readonly' => true, 'maxlength' => '60']))
            ->add('brPersonCPF', TextType::class, array('label' => 'CPF:', 'data' => $options['data']->getCpf(), 'mapped' => false, 'attr' => array('class' => "cpf", 'resultado' => "true", 'readonly' => true)))
            ->add('telephoneNumber', CollectionType::class, array('label' => 'Telefone:', 'attr' => ['class' => "telefone"],
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'delete_empty' => true,))
            //Campo adicionado de forma oculta para manter a ordem do formulário
            ->add('uid', HiddenType::class, array('label' => 'Login:', 'attr' => array('style' => 'text-transform:lowercase', 'autocomplete' => "off", 'resultado' => "true")))
            ->add('mail', CollectionType::class, array(
                'entry_type' => EmailType::class,
                'label' => 'E-mail Institucional:',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'delete_empty' => true,
            ))
            ->add('postalAddress', EmailType::class, array(
                'label' => 'E-mail Secundário: (Sem ser @uft.edu.br)',
                'required' => true,
            ));
        //verifica se é a criação de uma nova conta ou se é super ADM
        if (($this->authorization->isGranted('ROLE_ADMINISTRADOR_SLU') && $options['data']->getUid() == null) || $this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')) {
            $builder
                ->add('displayName', TextType::class, array('label' => 'Nome Completo:', 'attr' => ['maxlength' => '60']))
                ->add('brPersonCPF', TextType::class, array('label' => 'CPF:', 'attr' => array('class' => "cpf", 'resultado' => "true")))
                ->add('schacDateOfBirth', DateType::class, array(
                    'label' => 'Data de Nascimento:',
                    'widget' => 'single_text',
                    'format' => 'dd/MM/yyyy',
                    'attr' => array('class' => 'datepicker')))
                ->add('schacGender', ChoiceType::class,
                    array(
                        'label' => 'Género:',
                        'choices' =>
                            array(
                                'Não conhecido' => 0,
                                'Masculino' => 1,
                                'Feminino' => 2,
                                'Não especificado' => 9,
                            ),
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => true,
                        'required' => true,
                    )
                )
                ->add('uid', TextType::class, array('label' => 'Login:', 'attr' => array('style' => 'text-transform:lowercase', 'autocomplete' => "off", 'resultado' => "true")))
                //Campo adicionado de forma oculta para manter a ordem do formulário para ROLE_SUPER_ADMINISTRADOR_SLU
                ->add('alteraSenha', HiddenType::class, array(
                    'label' => 'Alterar Senha:',
                    'data' => 0,
                ))
                ->add('userPassword', HiddenType::class, array(
                    'label' => 'Senha:',
                    'data' => 0,
                ))
                ->add('Aluno', ChoiceType::class,
                    array(
                        'label' => 'Aluno:',
                        'placeholder' => 'Nenhum',
                        'choices' =>
                            array(
                                'Aluno (Graduação)' => 1,
                                'Ex-aluno' => 2,
                            ),
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => true,
                        'required' => false,
                    )
                )
                ->add('Funcionario', ChoiceType::class,
                    array(
                        'label' => 'Técnico Administrativo:',
                        'placeholder' => 'Nenhum',
                        'choices' =>
                            array(
                                'Concursado' => 1, //obsoleto
                                'Prof/Técnicos Extra' => 2,
                                'Terceirizados' => 3,
                                'Estagiários/Bolsistas' => 4,
                                'Cedidos de outros orgãos' => 5,
                            ),
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => true,
                        'required' => false,
                    )
                )
                ->add('Professor', ChoiceType::class,
                    array(
                        'label' => 'Professor:',
                        'placeholder' => 'Nenhum',
                        'choices' =>
                            array(
                                'Concursado' => 1, //obsoleto
                                'Prof/Técnicos Extra' => 2,
                                'MEC (convênio)/Voluntários' => 6,
                            ),
                        'choices_as_values' => true,
                        'multiple' => false,
                        'expanded' => true,
                        'required' => false,
                    )
                )
                ->add('Matricula', CollectionType::class, array(
                    'entry_type' => TextType::class,
                    'label' => 'Matricula(s):',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false,
                    'delete_empty' => true,

                ))
                ->add('Campus', ChoiceType::class,
                    array(
                        'label' => 'Campus',
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
                        'attr' => array('multiselect' => 'true', 'readonly' => $options['data']->getUid() != null)
                    )
                )
//                ->add('Campus', TextType::class,
//                    array(
//                        'label' => 'Câmpus:',
//                        'required' => true,
//                        'attr' => array('readonly' => 'true')
//                    )
//                )
            ;

            if ($this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')) {
                $builder->add('IDPessoa', TextType::class, array('label' => 'ID Pessoa:'))
                    ->add('IDDocente', TextType::class, array('label' => 'ID Docente:'));

            }
            $builder->add('givenName', HiddenType::class)
                ->add('departmentNumber', CollectionType::class, array(
                    'entry_type' => HiddenType::class,));
        }
        //Adicona do campo de adicionar Grupo caso seja ROLE_SUPER_ADMINISTRADOR_SLU
//        if ($this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')){
//            $builder->add('grupo', ChoiceType::class, array(
//                'label' => 'Grupo(s):',
//                'attr' => array('class' => 'autocomplete', 'resultado' => 'true'),
//                'required' => false,
//                'multiple' => true,
//                'choices_as_values' => true,
//                'mapped' => false
//            ));
//        }
//
        //adiciona a opção de alterar ou não a senha caso seja edição
        if ($this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU') && $options['data']->getUid() != null) {
            $builder
                ->add('alteraSenha', ChoiceType::class, array(
                    'label' => 'Alterar Senha:',
                    'choices' => array('Não' => 0, 'Sim' => 1),
                    'data' => 0,
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'choices_as_values' => true,
                ))
                ->add('userPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'invalid_message' => 'As Senhas devem ser iguais.',
                    'attr' => array('resultado' => "true"),
                    'options' => array('attr' => array('class' => 'password-field')),
                    'required' => true,
                    'first_options' => array('label' => 'Senha:'),
                    'second_options' => array('label' => 'Confirmação de senha:'),
                    'empty_data' => null
                ));
        }
        if ($this->authorization->isGranted('ROLE_SLU_USUARIO_CRIAR_TESTE')) {
            $builder->add('teste', ChoiceType::class, array(
                'label' => 'Conta de Teste:',
                'choices' => array('Não' => 0, 'Sim' => 1),
                'data' => 0,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'choices_as_values' => true,
            ));
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        if ($this->authorization->isGranted('ROLE_ADMINISTRADOR_SLU') && $builder->has('grupo')) {
            $builder->get('grupo')->resetViewTransformers();
        }
        $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
        if (($this->authorization->isGranted('ROLE_ADMINISTRADOR_SLU') && $options['data']->getUid() == null) || $this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')) {

            $builder->get("Campus")->addModelTransformer(new CallbackTransformer(
                function ($tagsAsArray) {
                    return is_array($tagsAsArray) ? $tagsAsArray[0] : $tagsAsArray;
                },
                function ($tagsAsString) {
                    // transform the string back to an array
                    return is_array($tagsAsString) ? $tagsAsString[0] : $tagsAsString;
                }
            ));
        }

    }


    function onPreSetData(FormEvent $event)
    {

        $dados = $event->getData();
        $form = $event->getForm();

        if ($this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')) {
//            $auxiliar = array();
//            if (!empty($dados->getMemberOf())) {
//                foreach ($dados->getMemberOf() as $grupo) {
//                    $auxiliar[$grupo->getCn()[0]] = $grupo->getCn()[0];
////                $auxiliar[]= array('id'=>$grupo->getCn()[0],'text'=>$grupo->getCn()[0] );
//                }
//                $form->remove('grupo');
//
//                $form->add('grupo', ChoiceType::class, array(
//                    'label' => 'Grupo(s):',
//                    'choices' => $auxiliar,
//                    'data' => $auxiliar,
//                    'attr' => array('class' => 'autocomplete', 'resultado' => 'true'),
//                    'required' => false,
//                    'multiple' => true,
//                    'choices_as_values' => true,
//                    'mapped' => false
//                ));
//            }
        } else if ($this->authorization->isGranted('ROLE_SLU_USUARIO_CRIAR_TESTE')) {
            if ($dados->getTeste()) {
                $form->add('teste', ChoiceType::class, array(
                    'label' => 'Conta de Teste:',
                    'choices' => array('Não' => 0, 'Sim' => 1),
                    'data' => $dados->getTeste(),
                    'required' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'choices_as_values' => true,
                ));
            }
        }


    }


    function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

//        if ($this->authorization->isGranted('ROLE_SUPER_ADMINISTRADOR_SLU')) {
//            $grupos = (isset($data['grupo']) ? $data['grupo'] : array());
//            $form->remove('grupo');
//            $form->add('grupo', ChoiceType::class, array(
//                'label' => 'Grupo(s):',
//                'choices' => $grupos,
//                'attr' => array('class' => 'autocomplete', 'resultado' => 'true'),
//                'required' => false,
//                'multiple' => true,
//                'choices_as_values' => true,
//                'mapped' => false
//            ));
//        }


    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\PessoaLdap',
        ));

    }

}