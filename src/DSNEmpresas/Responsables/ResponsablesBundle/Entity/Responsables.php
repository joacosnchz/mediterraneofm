<?php

namespace DSNEmpresas\Responsables\ResponsablesBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use DSNEmpresas\Responsables\ResponsablesBundle\Entity\Usuarios;

/**
 * DSNEmpresas\Responsables\ResponsablesBundle\Entity\Responsables
 *
 */
class Responsables extends Usuarios
{
    /* No se porqué, pero al haber tenido anteriormente este atributo
    si borro la variable de acá, el sistema no funciona y da error */
    var $id_ciudad;

    /**
     * @var string $nombre
     */
    private $nombre;

    /**
     * @var string $apellido
     */
    private $apellido;

    /**
     * @var string $direccion
     */
    private $direccion;

    /**
     * @var integer $telefono_particular
     */
    private $telefono_particular;

    /**
     * @var integer $telefono_comercial
     */
    private $telefono_comercial;

    /**
     * @var string $celular
     */
    private $celular;

    /**
     * @var string $email
     */
    private $email;
    
    /**
     * @var integer $id_agencia
     */
    private $id_agencia;

    /**
     * @var boolean $isLogged
     */
    private $isLogged;

    /**
     * @var time $loggedTime
     */
    private $loggedTime;
    
    public function __construct() {
	parent::__construct();
        $this->isLogged = false;
    }
    
    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Responsables
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set apellido
     *
     * @param string $apellido
     * @return Responsables
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;
    
        return $this;
    }

    /**
     * Get apellido
     *
     * @return string 
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return Responsables
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telefono_particular
     *
     * @param integer $telefonoParticular
     * @return Responsables
     */
    public function setTelefonoParticular($telefonoParticular)
    {
        $this->telefono_particular = $telefonoParticular;
    
        return $this;
    }

    /**
     * Get telefono_particular
     *
     * @return integer 
     */
    public function getTelefonoParticular()
    {
        return $this->telefono_particular;
    }

    /**
     * Set telefono_comercial
     *
     * @param integer $telefonoComercial
     * @return Responsables
     */
    public function setTelefonoComercial($telefonoComercial)
    {
        $this->telefono_comercial = $telefonoComercial;
    
        return $this;
    }

    /**
     * Get telefono_comercial
     *
     * @return integer 
     */
    public function getTelefonoComercial()
    {
        return $this->telefono_comercial;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return Responsables
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Responsables
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Set id_agencia
     *
     * @param integer $idAgencia
     * @return Responsables
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
     * Set isLogged
     *
     * @param boolean $isLogged
     * @return Responsables
     */
    public function setIsLogged($isLogged)
    {
        $this->isLogged = $isLogged;
    
        return $this;
    }

    /**
     * Get isLogged
     *
     * @return boolean 
     */
    public function getIsLogged()
    {
        return $this->isLogged;
    }

    /**
     * Set loggedTime
     *
     * @param \DateTime $loggedTime
     * @return Responsables
     */
    public function setLoggedTime($loggedTime)
    {
        $this->loggedTime = $loggedTime;

        return $this;
    }

    /**
     * Get loggedTime
     *
     * @return \DateTime 
     */
    public function getLoggedTime()
    {
        return $this->loggedTime;
    }
    
    public function __toString() {
    	return strval($this->id);
    }
}
