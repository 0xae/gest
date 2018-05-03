<?php

namespace Admin\Backend\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use Admin\Backend\Model\Settings;

class UserType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name')
            ->add('email', 'email')
            ->add('username')
            ->add('context')            
            ->add('plainPassword', 'password')
            ->add('passwordConf', 'password')
            ->add('isActive')
            ->add('isActive', 'choice', array(
                'choices'  => array(
                    true => 'Ativo',
                    false => 'Inativo'
                ),
            ))
            // ->add('password_confir', 'password')
            ->add('entity', 'entity', array(
                'class' => 'BackendBundle:AppEntity',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                      ->where("u.context = '".Settings::SGRS_CTX."'")
                      ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name'                
            ))
            ->add('profile', 'entity', array(
                'class' => 'BackendBundle:Profile',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                      ->where("u.context = '".Settings::SGRS_CTX."'")
                      ->orderBy('u.name', 'ASC');
                },
                'choice_label' => 'name'                
            ))
            ->add('phone')
            ->add('address')
            ->add('photoDir')
            ->add('submit', 'submit', array(
                'label' => 'Enviar formulário',
                'attr' => array(
                    'class' => 'btn btn-success'
                )
            ))
            ->add('submit', 'submit', array(
                'label' => 'Enviar formulário',
                'attr' => array(
                    'class' => 'btn btn-success'
                )
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\Backend\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'admin_backend_user';
    }
}
