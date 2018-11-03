<?php

namespace DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\liquidacionesMovimientosType;
use DSNEmpresas\CtasCtesAgencias\CtasCtesAgenciasBundle\Form\FechasType;

class AgenciasFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    var $liqudMov;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
        ->add('fecha', new FechasType(), array(
        ))
        ->add('idAgencia', 'entity', array(
            'class' => 'AgenciasBundle:Agencias',
            'property' => 'razonSocial',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('a')
                        ->where('a.isActive = :is_active')
                        ->setParameter('is_active', 1);
            }, 
            'required' => true,
            // el atributo disabled es realizado por jquery en la vista
            'empty_value' => 'Agencia',
            'label' => 'Agencia (*)',
            'attr' => array('title' => 'Este campo no es modificable'),
        ))
        ->add('param', new liquidacionesMovimientosType(), array(
            'required' => true,
            'label' => false,
        ))
        ->add('total', null, array(
            'required' => true,
            'label' => 'Total (*)',
            'read_only' => true,
            'attr' => array('size' => 8),
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
        return 'mediterraneofm_mediterraneofmbundle_agenciasfiltertype';
    }
}
