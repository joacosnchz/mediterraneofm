<?php

namespace DSNEmpresas\Responsables\ResponsablesBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InsertResponsablesType extends AbstractType {
    var $rol;
    var $idAgencia;
    
    public function __construct($rol, $idAgencia) {
        $this->rol = $rol;
        $this->idAgencia = $idAgencia;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $idAgencia = $this->idAgencia;
        
        $builder
            ->add('nombre', null, array(
			'label' => 'Nombre (*)',
			'required' => true,		
		))
            ->add('apellido', null, array(
			'label' => 'Apellido (*)',
			'required' => true,		
		))
            ->add('direccion', null, array(
                'required' => false,
            ));

            if(in_array('ROLE_SUPER_ADMIN', $this->rol)):
                $builder->add('id_agencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                        'property' => 'razonSocial',
                        'query_builder' => function(EntityRepository $er) {
                            return $er->createQueryBuilder('c')
                                    ->where('c.isActive = :is_active')
                                    ->setParameter('is_active', 1);
                        },
                        'required' => true,
                        'label' => 'Agencia (*)',
                        'attr' => array('title' => 'Solo agencias activas.'),
                    ));
            else:
                $builder->add('id_agencia', 'entity', array(
                    'class' => 'AgenciasBundle:Agencias',
                        'property' => 'razonSocial',
                        'query_builder' => function(EntityRepository $er) use($idAgencia) {
                            return $er->createQueryBuilder('c')
                                    ->where('c.id_agencia = :idAgencia')
                                    ->setParameter('idAgencia', $idAgencia)
                                    ->andWhere('c.isActive = :is_active')
                                    ->setParameter('is_active', 1);
                        },
                        'required' => true,
                        'label' => 'Agencia (*)',
                        'attr' => array('title' => 'Solo agencias activas.'),
                    ));
            endif;
            
                    
            $builder->add('telefono_particular', null, array(
			'label' => 'Teléfono particular',
                        'required' => false,
		))
            ->add('telefono_comercial', null, array(
			'label' => 'Teléfono comercial (*)',
			'required' => true,		
		))
            ->add('celular', null, array(
                'required' => false,
            ))
            ->add('email', null, array(
                'required' => false,
            ))
            ->add('is_active', 'checkbox', array(
                'label' => 'Activo',
                'required' => false,
            ))
            ->add('username', null, array('label' => 'Usuario (*)', 'required' => true))
            ->add('password', 'password', array(
                'label' => 'Contraseña (*)', 
                'required' => true,
                'attr' => array('maxlength' => 15),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DSNEmpresas\Responsables\ResponsablesBundle\Entity\Responsables',
            'csrf_protection' => false,
            'csrf_field_name' => '_token',
            // a unique key to help generate the secret token
            'intention'       => 'task_item',
        ));
    }

    public function getName()
    {
        return 'mediterraneofm_mediterraneofmbundle_insertresponsablestype';
    }
}
