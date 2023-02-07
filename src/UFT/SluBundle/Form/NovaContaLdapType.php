<?php

namespace UFT\SluBundle\Form;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UFT\SluBundle\Entity\PessoaLdap;
use UFT\SluBundle\Form\MatriculaType;

class NovaContaLdapType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */

    private $em;
    private $emUtil;
    private $emLdap;
    private $convertor;

    public function __construct($entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->convertor  = $options['convertor'];
        $this->emUtil  = $options['emUtil'];
        $this->emLdap  = $options['emLdap'];

        $logins = $this->verificarLoginsDisponiveis($options['data']->getDisplayName());

        $builder
            ->add('displayName', TextType::class, array('label' => 'Nome Completo:', 'attr' => ['readonly' => true]))
            ->add('brPersonCPF', TextType::class, array('label' => 'CPF:', 'attr' => array('class' => "cpf", 'resultado' => "true", 'readonly' => true)))
            ->add('schacDateOfBirth', DateType::class, array(
                'label' => 'Data de Nascimento:',
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr' => array('readonly' => true)))
            ->add('schacGender', HiddenType::class)
            ->add('schacGender', ChoiceType::class,
                array(
                    'label' => 'Gênero:',
                    'choices' =>
                        array(
                            'Não conhecido' => 0,
                            'Masculino' => 1,
                            'Feminino' => 2,
                            'Não especificado' => 9,
                        ),
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'required' => true,
                    'attr' => ['disabled' => true],
                )
            )
            ->add('telephoneNumber', CollectionType::class, array(
                'entry_type' => TextType::class,
                'label' => 'Telefone:',
                'attr' => array('class' => "telefone"),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'delete_empty' => true,
            ))
            ->add('uids', ChoiceType::class,
                array(
                    'label' => 'Login:',
                    'choices' => $logins,
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                    'mapped' =>false,
                    'attr' => array('resultado'=>'true')
                )
            )
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
            ))
            ->add('userPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'attr' => array('resultado' => true),
                'invalid_message' => 'As Senhas devem ser iguais.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options' => array('label' => 'Senha:'),
                'second_options' => array('label' => 'Confirmação de senha:'),
            ))
            ->add('Aluno', ChoiceType::class,
                array(
                    'label' => 'Aluno:',
                    'placeholder' => 'Não',
                    'choices' =>
                        array(
                            'Sim' => 1
                        ),
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['disabled' => true],
                )
            )
            ->add('Funcionario', ChoiceType::class,
                array(
                    'label' => 'Técnico Administrativo:',
                    'placeholder' => 'Não',
                    'choices' =>
                        array(
                            'Sim' => 1, //obsoleto

                        ),
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['disabled' => true],
                )
            )
            ->add('Professor', ChoiceType::class,
                array(
                    'label' => 'Professor:',
                    'placeholder' => 'Não',
                    'choices' =>
                        array(
                            'Sim' => 1, //obsoleto
                        ),
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => false,
                    'required' => false,
                    'attr' => ['disabled' => true],
                )
            )
            ->add('Matricula', CollectionType::class, array(
                'entry_type' => TextType::class,
                'label' => 'Matricula(s):',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'delete_empty' => true,
                'attr' => ['readonly' => true],
            ))
            ->add('Campus', ChoiceType::class,
                array(
                    'label' => ' ',
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
                    'attr' => array('multiselect' => 'true', 'readonly' => true)
                )
            )
            ->add('IDPessoa', HiddenType::class)
            ->add('departmentNumber',   CollectionType::class, array(
                'entry_type' => HiddenType::class,))
            ->add('uid', HiddenType::class)
            ->add('IDDocente', HiddenType::class)
            ->add('Teste', HiddenType::class, array(
                'data' => 0
            ))->add('alteraSenha', HiddenType::class, array(
                'data' => 1
            ))
            ->add('givenName', HiddenType::class);


        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));


    }


    public function onPreSetData(Event $event){
        $form = $event->getForm();
        $data = $event->getData();
        if(!empty($data->getUid())){
            $logins = $this->verificarLoginsDisponiveis($data->getDisplayName());
            $logins = array_merge($logins,array($data->getUid()=>$data->getUid()));

            $form->add('uids', ChoiceType::class,
                array(
                    'label' => 'Login:',
                    'choices' => $logins,
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                    'mapped' =>false,
                    'attr' => array('resultado'=>'true')
                )
            );
        }

    }
    public function verificarLoginsDisponiveis($nomeCompleto)
    {
        $pessoaLdapRepository = $this->em->getRepository(PessoaLdap::class);
        $nomeCompleto = $this->convertor->tirarAcentos($nomeCompleto);
        $nomeCompleto = str_ireplace(array(
            " de ",
            " da ",
            " das ",
            " do ",
            " dos ",
            " na ",
            " no ",
            " em ",
            " a ",
            " o ",
            " e ",
            " as ",
            " os "
        ), " ", $nomeCompleto);
        $nomeCompleto = preg_replace('/[`^~\'"]/', null, iconv('UTF-8', 'ASCII//TRANSLIT', $nomeCompleto));
        $nomes = explode(" ", strtolower($nomeCompleto));

        $logins = array();
        for ($i = 0; $i < count($nomes); $i++) {
            for ($j = 0; $j < count($nomes); $j++) {
                if ($i != $j) $logins[] = $nomes[$i] . "." . $nomes[$j];
            }
        }
        $loginsDisponiveis = array();
        $loginsNaoDisponiveis = array();
        foreach ($logins as $login) {
            if ($this->verificaLogin($login)) {
                $loginsDisponiveis[$login] = $login;
            } else {
                $loginsNaoDisponiveis[] = $login;
            }
        }
        if (count($loginsDisponiveis) >= 5) {
            return array_slice($loginsDisponiveis, 0, 5);
        }
        $i = 1;
        while(count($loginsDisponiveis) < 5){
            foreach ($logins as $login) {
                if ($this->verificaLogin($login.$i)) {
                    $loginsDisponiveis[$login . $i] = $login . $i;
                }
                if (count($loginsDisponiveis) == 5) {
                    return $loginsDisponiveis;
                }
            }
            $i++;
        }
        return $loginsDisponiveis;
    }

    public function verificaLogin( $login)
    {
        $loginEntity = $login;
        $login = str_replace(".", "", $login);
        $pessoaLdapRepository = $this->emLdap;
        $emUtil = $this->emUtil;
        $ldapResult = $pessoaLdapRepository->getRepository(PessoaLdap::class)->findByUid($login, array(
            'searchDn' => 'dc=uft,dc=edu,dc=br'));

        if (strlen($login) > 5) {
            if(!$ldapResult){
                $string = implode('*',str_split($login));
                $possibilidades = $emUtil->find('all', array('conditions' => "uid=$string"),'ou=People,dc=uft,dc=edu,dc=br');
                if($possibilidades!=false){
                    return false;
                }
                return true;
            }else if ($loginEntity == $ldapResult[0]->getUid()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UFT\SluBundle\Entity\PessoaLdap',
            'convertor'=> 'UFT\SluBundle\Util\Convetores',
            'emUtil'=> 'UFT\SluBundle\Util\LdapManeger',
            'emLdap'=> 'UFT\LdapOrmBundle\Ldap\LdapEntityManager'
        ));

    }

}