<?php

namespace DSNEmpresas\OrdenPub\OrdenPubBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class OrdenPub
{
    /**
     * @var integer $id_ordenpub
     */
    protected $id_ordenpub;

    /**
     * @var integer $nro_ordenpub
     * 
     */
    private $nro_ordenpub;

    /**
     * @var date $fecha
     */
    private $fecha;

    /**
     * @var string $texto_publicidad
     */
    private $texto_publicidad;

    /**
     * @var string $observaciones
     */
    private $observaciones;

    /**
     * @var integer $id_agencia
     */
    private $id_agencia;
    
    /**
     * @var integer $estado
     */
    private $estado;
    
    /**
     * @var integer $total
     */
    private $total;

    /**
     * @var boolean $pagado
     */
    protected $pagado;

    /**
     * @var boolean $liquidado
     */
    protected $liquidado;
    
    /**
     * @var integer $idCtaCteC
     */
    private $idCliente;
    
    private $idTarifa;

    /**
    * @var \Doctrine\Common\Collections\Collection  $pautas
    */
    private $pautas;
    
    public function __construct() {
        $this->estado = 1;
        $this->pagado = false;
        $this->liquidado = false;
        $this->idTarifa = new ArrayCollection();
        $this->pautas = new ArrayCollection();
    }    

    /**
     * Get id_ordenpub
     *
     * @return integer 
     */
    public function getIdOrdenPub()
    {
        return $this->id_ordenpub;
    }

    /**
     * Set nro_ordenpub
     *
     * @param integer $nroOrdenpub
     * @return OrdenPub
     */
    public function setNroOrdenpub($nroOrdenpub)
    {
        $this->nro_ordenpub = $nroOrdenpub;
    
        return $this;
    }

    /**
     * Get nro_ordenpub
     *
     * @return integer 
     */
    public function getNroOrdenpub()
    {
        return $this->nro_ordenpub;
    }

    /**
     * Set texto_publicidad
     *
     * @param string $textoPublicidad
     * @return OrdenPub
     */
    public function setTextoPublicidad($textoPublicidad)
    {
        $this->texto_publicidad = $textoPublicidad;
    
        return $this;
    }

    /**
     * Get texto_publicidad
     *
     * @return string 
     */
    public function getTextoPublicidad()
    {
        return $this->texto_publicidad;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return OrdenPub
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set id_agencia
     *
     * @param integer $idAgencia
     * @return OrdenPub
     */
    public function setIdAgencia($idAgencia)
    {
        $this->id_agencia = $idAgencia;
    
        return $this;
    }

    /**
     * Get id_agencia
     *
     * @return integer 
     */
    public function getIdAgencia()
    {
        return $this->id_agencia;
    }

    /**
     * Set total
     *
     * @param integer $total
     * @return OrdenPub
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return integer 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     * @return OrdenPub
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return integer 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set pagado
     *
     * @param boolean $pagado
     * @return OrdenPub
     */
    public function setPagado($pagado)
    {
        $this->pagado = $pagado;
    
        return $this;
    }

    /**
     * Get pagado
     *
     * @return boolean 
     */
    public function getPagado()
    {
        return $this->pagado;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return OrdenPub
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set liquidado
     *
     * @param boolean $liquidado
     * @return OrdenPub
     */
    public function setLiquidado($liquidado)
    {
        $this->liquidado = $liquidado;
    
        return $this;
    }

    /**
     * Get liquidado
     *
     * @return boolean 
     */
    public function getLiquidado()
    {
        return $this->liquidado;
    }

    /**
     * Set idCliente
     *
     * @param \DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes $idCliente
     *
     * @return OrdenPub
     */
    public function setIdCliente(\DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes $idCliente = null)
    {
        $this->idCliente = $idCliente;

        return $this;
    }

    /**
     * Get idCliente
     *
     * @return \DSNEmpresas\Clientes\ClientesBundle\Entity\Clientes 
     */
    public function getIdCliente()
    {
        return $this->idCliente;
    }
    
    public function setIdTarifa($idTarifa) {
        $this->idTarifa = $idTarifa;
        
        return $this;
    }
    
    public function getIdTarifa() {
        return $this->idTarifa;
    }

    public function __toString() {
        return strval($this->id_ordenpub);
    }

    /**
     * Add pautas
     *
     * @param \DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas $pautas
     * @return OrdenPub
     */
    public function addPauta(\DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas $pautas)
    {
        $this->pautas[] = $pautas;

        return $this;
    }

    /**
     * Remove pautas
     *
     * @param \DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas $pautas
     */
    public function removePauta(\DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenTarifas $pautas)
    {
        $this->pautas->removeElement($pautas);
    }

    /**
     * Get pautas
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPautas()
    {
        return $this->pautas;
    }
}
