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

class RecadastrarType extends AbstractType
{

    private $em;
    private $emUtil;
    private $convertor;



    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $this->em  = $options['em'];
        $this->emUtil  = $options['emUtil'];
        $this->convertor  = $options['convertor'];
        $logins = $this->verificarLoginsDisponiveis($options['data']->getDisplayName());


        $builder
            ->add('displayName', TextType::class, array('label' => 'Nome Completo:'))
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
            ->add('telephoneNumber', CollectionType::class, array(
                'entry_type' => TextType::class,
                'label' => 'Telefones:',
                'attr' => array('class' => "telefone"),
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'delete_empty' => true,

            ))
//            ->add('uid', TextType::class, array('label' => 'Login:', 'attr' => array('style' => 'text-transform:lowercase', 'autocomplete' => "off", 'resultado' => "true")))
            ->add('uid', HiddenType::class)
            ->add('uids', ChoiceType::class,
                array(
                    'label' => 'Login:',
                    'choices' => $logins,
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                    'mapped' => false,
                    'attr' => array('resultado' => 'true')
                )
            )
            ->add('alteraSenha', HiddenType::class, array(
                'data' => 1,
            ))
            ->add('idOrigem', HiddenType::class)
            ->add('tipoOrigemItem', HiddenType::class)
            ->add('departmentNumber', CollectionType::class, array(
                'entry_type' => HiddenType::class,
                'required' => false,
                'delete_empty' => true,
            ))
            ->add('mail', CollectionType::class, array(
                'entry_type' => EmailType::class,
                'label' => 'E-mail(s):',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
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
                'options' => array('attr' => array('class' => 'password-field','resultado' => 'true')),
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
//            ->add('Aluno', ChoiceType::class,
//                array(
//                    'label' => 'Aluno:',
//                    'placeholder' => 'Nenhum',
//                    'choices' =>
//                        array(
//                            'Aluno (Graduação)' => 1,
//                            'Ex-aluno' => 2,
//                        ),
//                    'choices_as_values' => true,
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => false,
//                )
//            )
//            ->add('Funcionario', ChoiceType::class,
//                array(
//                    'label' => 'Técnico Administrativo:',
//                    'placeholder' => 'Nenhum',
//                    'choices' =>
//                        array(
//                            'Concursado' => 1, //obsoleto
//                            'Prof/Técnicos Extra' => 2,
//                            'Terceirizados' => 3,
//                            'Estagiários/Bolsistas' => 4,
//                            'Cedidos de outros orgãos' => 5,
//                        ),
//                    'choices_as_values' => true,
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => false,
//                )
//            )
//            ->add('Professor', ChoiceType::class,
//                array(
//                    'label' => 'Professor:',
//                    'placeholder' => 'Nenhum',
//                    'choices' =>
//                        array(
//                            'Concursado' => 1, //obsoleto
//                            'Prof/Técnicos Extra' => 2,
//                            'MEC (convênio)/Voluntários' => 6,
//                        ),
//                    'choices_as_values' => true,
//                    'multiple' => false,
//                    'expanded' => true,
//                    'required' => false,
//                )
//            )
            ->add('Matricula', CollectionType::class, array(
                'entry_type' => TextType::class,
                'label' => 'Matricula(s):',
                'allow_add' => true,
                'allow_delete' => true,
                'required' => false,
                'delete_empty' => true,
                'attr' => array('readonly' => 'true')

            ))
            ->add('Campus', TextType::class,
                array(
                    'label' => 'Câmpus:',
                    'required' => true,
                    'attr' => array( 'readonly' => 'true')
                )
            );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));

    }

    public function onPreSetData(Event $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (!empty($data->getUid())) {
            $logins = $this->verificarLoginsDisponiveis($data->getDisplayName());
            $logins = array_merge($logins, array($data->getUid() => $data->getUid()));

            $form->add('uids', ChoiceType::class,
                array(
                    'label' => 'Login:',
                    'choices' => $logins,
                    'data' => $data->getUid(),
                    'choices_as_values' => true,
                    'multiple' => false,
                    'expanded' => true,
                    'required' => true,
                    'mapped' => false,
                    'attr' => array('resultado' => 'true')
                )
            );
        }

    }

    public function verificarLoginsDisponiveis($nomeCompleto)
    {

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
        foreach ($loginsNaoDisponiveis as $loginNaoDisponivel) {
            $i = 1;
            while ($this->verificaLogin($loginNaoDisponivel.$i)) {
                $loginsDisponiveis[$loginNaoDisponivel . $i] = $loginNaoDisponivel . $i;
                if (count($loginsDisponiveis) == 5) {
                    return $loginsDisponiveis;
                }
                $i++;
            }

        }

        return $loginsDisponiveis;
    }

    public function verificaLogin( $login)
    {
        $loginEntity = $login;
        $login = str_replace(".", "", $login);
        $pessoaLdapRepository = $this->em;
        $emUtil = $this->emUtil;

        $ldapResult = $pessoaLdapRepository->getRepository(PessoaLdap::class)->findByUid($login, array(
            'searchDn' => 'dc=uft,dc=edu,dc=br'));
        if (strlen($login) > 5) {

            if(!$ldapResult){
                $string = implode('*',str_split($login));
                $possibilidades = $emUtil->find('all', array('conditions' => "uid=$string"),'ou=People,dc=uft,dc=edu,dc=br');

                if($possibilidades!=false){
                    foreach ($possibilidades as $row){
                        $uidExistente = str_replace(".", "", $row['People']['uid']);
                        if ($uidExistente==$login){
                            $resultado = 'Este login já está sendo utilizado!';
                            return false;
                        }
                    }
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
            'em'=> 'UFT\LdapOrmBundle\Ldap\LdapEntityManager',
            'convertor'=> 'UFT\SluBundle\Util\Convetores',
            'emUtil'=> 'UFT\SluBundle\Util\LdapManeger'
        ));

    }

}