<?php

namespace Admin\Backend\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;


class ComplaintType extends AbstractType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('name')
            ->add('address')
            ->add('locality')
            ->add('phone', 'number', array(
                'required' => false
            ))
            ->add('email', 'email', array(
                'required' => false
            ))
            ->add('type', 'choice', array(
                'choices'  => array(
                    'queixa' => 'Queixa',
                    'denuncia' => 'Denuncia'
                ),
            ))
            ->add('sector', 'choice', array(
                'choices'  => array(
                    'farm' => 'Farmacêutico',
                    'alim' => 'Alimentar'
                ),
            ))
            ->add('complaintCategory')
            ->add('opName')
            ->add('opAddress', 'text', array(
                'required' => false
            ))
            ->add('opLocality', 'text', array(
                'required' => false
            ))
            ->add('opPhone', 'number', array(
                'required' => false
            ))
            ->add('opEmail', 'email', array(
                'required' => false
            ))
            ->add('factDate')
            ->add('factLocality')
            ->add('factDetail', 'textarea', array(
                'attr' => array('rows' => 6)
            ))
            ->add('sectorDescr', 'textarea', array(
                'attr' => array('rows' => 3, 'cols'=>3),
                'required'=>false
            ))
            ->add('hasProduct', 'choice', array(
                'choices' => array(
                    0 => 'Sim',
                    1 => 'Não'
                )
            ))
            // ->add('annexType', 'entity', array(
            //     ''
            //     'class' => 'BackendBundle:Document',
            //     'query_builder' => function (EntityRepository $er) {
            //         return $er->createQueryBuilder('u')
            //           ->orderBy('u.name', 'ASC');
            //     },
            //     'choice_label' => 'name'
            // ))
            ->add('createdAt')
            ->add('submit', 'submit', array(
                'label' => 'Enviar formulário',
                'attr' => array(
                    'class' => 'btn btn-success',
                )
            ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\Backend\Entity\Complaint'
        ));
    }

    /**
     * @return string
     */
    public function getName() {
        return 'admin_backend_complaint';
    }
}
