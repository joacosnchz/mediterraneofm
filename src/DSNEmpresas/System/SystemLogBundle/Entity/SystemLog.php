<?php

namespace DSNEmpresas\System\SystemLogBundle\Entity;

/**
 * SystemLog
 */
class SystemLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $idResponsable;

    /**
     * @var \DateTime
     */
    private $fechaHora;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var integer
     */
    private $idType;


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
     * Set idResponsable
     *
     * @param integer $idResponsable
     *
     * @return SystemLog
     */
    public function setIdResponsable($idResponsable)
    {
        $this->idResponsable = $idResponsable;

        return $this;
    }

    /**
     * Get idResponsable
     *
     * @return integer 
     */
    public function getIdResponsable()
    {
        return $this->idResponsable;
    }

    /**
     * Set fechaHora
     *
     * @param \DateTime $fechaHora
     *
     * @return SystemLog
     */
    public function setFechaHora($fechaHora)
    {
        $this->fechaHora = $fechaHora;

        return $this;
    }

    /**
     * Get fechaHora
     *
     * @return \DateTime 
     */
    public function getFechaHora()
    {
        return $this->fechaHora;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return SystemLog
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set idType
     *
     * @param integer $idType
     *
     * @return SystemLog
     */
    public function setIdType($idType)
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * Get idType
     *
     * @return integer 
     */
    public function getIdType()
    {
        return $this->idType;
    }
}

