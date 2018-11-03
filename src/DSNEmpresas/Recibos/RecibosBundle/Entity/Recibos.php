<?php

namespace DSNEmpresas\Recibos\RecibosBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DSNEmpresas\Recibos\RecibosBundle\Entity
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Recibos
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $nro_recibo
     */
    private $nro_recibo;

    /**
     * @var \DateTime $fecha
     */
    private $fecha;

    /**
     * @var integer $importe
     */
    private $importe;

    /**
     * @var string $concepto
     */
    private $concepto;

    /**
     * @var float $total
     */
    private $total;
    
    /**
     * @var integer $idOrdenPub
     */
    private $idOrdenPub;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nro_recibo
     *
     * @param integer $nroRecibo
     * @return Recibos
     */
    public function setNroRecibo($nroRecibo)
    {
        $this->nro_recibo = $nroRecibo;
    
        return $this;
    }

    /**
     * Get nro_recibo
     *
     * @return integer 
     */
    public function getNroRecibo()
    {
        return $this->nro_recibo;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Recibos
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
     * Set importe
     *
     * @param string $importe
     * @return Recibos
     */
    public function setImporte($importe)
    {
        $this->importe = $importe;
    
        return $this;
    }

    /**
     * Get importe
     *
     * @return string 
     */
    public function getImporte()
    {
        return $this->importe;
    }

    /**
     * Set concepto
     *
     * @param string $concepto
     * @return Recibos
     */
    public function setConcepto($concepto)
    {
        $this->concepto = $concepto;
    
        return $this;
    }

    /**
     * Get concepto
     *
     * @return string 
     */
    public function getConcepto()
    {
        return $this->concepto;
    }

    /**
     * Set total
     *
     * @param float $total
     * @return Recibos
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }
    
    /**
     * Set idOrdenPub
     *
     * @param \DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub $idOrdenPub
     *
     * @return Recibos
     */
    public function setIdOrdenPub(\DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub $idOrdenPub = null)
    {
        $this->idOrdenPub = $idOrdenPub;

        return $this;
    }

    /**
     * Get idOrdenPub
     *
     * @return \DSNEmpresas\OrdenPub\OrdenPubBundle\Entity\OrdenPub 
     */
    public function getIdOrdenPub()
    {
        return $this->idOrdenPub;
    }

    public function __toString() {
        return strval($this->id);
    }
}
