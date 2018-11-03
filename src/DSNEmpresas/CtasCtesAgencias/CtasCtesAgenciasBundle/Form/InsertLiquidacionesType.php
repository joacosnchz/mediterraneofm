<?php

namespace DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\FechasType;

class InsertLiquidacionesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('fecha', new FechasType(), array(
            'label' => false,
        ));

        $builder
        ->add('idAgencia', 'entity', array(
            'class' => 'AgenciasBundle:Agencias',
            'property' => 'razonSocial',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('a')
                        ->where('a.isActive = :is_active')
                        ->setParameter('is_active', 1);
            }, 
            'required' => true,
            'empty_value' => 'Agencia',
            'label' => 'Agencia (*)',
            'attr' => array('title' => 'Solo agencias activas'),
        ))
        ->add('param', 'choice', array(
            'choices' => array('LI' => 'Solo ordenes NO liquidadas', 'CO' => 'Solo ordenes cobradas'),
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => 'Parámetros',
            'data' => array('LI', 'CO'),
        ))
        ->add('docs', 'choice', array(
            'choices' => array('OP' => 'Ordenes de publicidad'),
            'multiple' => true,
            'expanded' => true,
            'required' => false,
            'label' => 'Docs. a incluir',
            'data' => array('OP'),
        ))
        ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Entity\Liquidaciones',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_insertliquidacionestype';
    }
}
