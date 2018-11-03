<?php

namespace DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MovCtaCteClienteType extends AbstractType {
    var $id_agencia;
    var $id_cliente;
	var $mov;
    
    public function __construct($id_agencia, $id_cliente, $mov) {
        $this->id_agencia = $id_agencia;
        $this->id_cliente = $id_cliente;
        $this->mov = $mov;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $id_agencia = $this->id_agencia;
        $id_cliente = $this->id_cliente;
        $mov = $this->mov;
        
        $builder
            ->add('fecha', 'date', array(
                'label' => 'Fecha (*)',
                'required' => true,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'mapped' => false,
                'attr' => array('size' => 8, 'title' => 'Click sobre el campo para completarlo.', 'value' => date('d-m-Y')),
            ))
            ->add('idCliente', 'entity', array(
                'class' => 'ClientesBundle:Clientes',
                'property' => 'nomappraziva',
                'query_builder' => function(EntityRepository $er) use ($id_agencia) {
                    return $er->createQueryBuilder('c')
                            ->where('c.id_agencia = :id_agencia')
                            ->setParameter('id_agencia' , $id_agencia)
                            ->andWhere('c.isActive = :is_active')
                            ->setParameter('is_active', 1);
                },
                'choices' => array($id_cliente),
                'required' => true,
                'read_only' => true,
                'label' => 'Cliente (*)',
                'attr' => array('title' => 'Este campo no es editable.')
            ))
            ->add('idTipoDocumento', 'entity', array(
                'class' => 'MediterraneoFMBundle:TiposDocumentos',
                'property' => 'leyenda',
                'choices' => $mov,
                'mapped' => false,
                'label' => 'Tipo documento (*)',
            ))
            ;

            /* ->add('concepto', null, array(
                'label' => 'Concepto (*)',
                'attr' => array('value' => $mov->getDescripcion()),
            ));
		if($mov->getTipoMovimiento() == 'D'):
			$builder->add('debe', null, array(
                'label' => 'Debe (*)',
                'required' => false,
            ));
		else:
			$builder->add('haber', null, array(
                'label' => 'Haber (*)',
                'required' => false,
            ));
		endif; */

        $builder
        ->add('concepto', null, array(
            'required' => false,
            'mapped' => false,
            'attr' => array('placeholder' => 'Ej.: Pago Orden de Publicidad')
        ))
        ->add('haber', null, array(
                'label' => 'Haber (*)',
                'required' => false,
                'read_only' => true,
                'mapped' => false,
                'attr' => array('title' => 'Este campo no es editable.'),
        ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\CtasCtesClientes\CtasCtesClientesBundle\Entity\CtasCtesClientes',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention' => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_movctacteclientetype';
    }
}
