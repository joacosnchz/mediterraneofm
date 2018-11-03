<?php

namespace DSNEmpresas\Responsables\ResponsablesBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * DSNEmpresas\Usuarios\UsuariosBundle\Entity\Usuarios
 *
 */
class Usuarios implements UserInterface, \Serializable
{    
    /**
     * @var integer $id
     */
    protected $id;
    
    /**
     * @var string $username
     */
    protected $username;
    
    /**
     * @var string $password
     */
    protected $password;
    
    /**
     * @var string $salt
     */
    protected $salt;
    
    /**
     * @var boolean $is_active
     */
    protected $isActive;
    
    /**
     * @var string $roles
     */
    protected $roles;
    
    public function __construct() {
        $this->roles = 'ROLE_USER';
        $this->salt = md5(uniqid(null, true));
    }
    
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
     * Set username
     *
     * @param string $username
     * @return Usuarios
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Usuarios
     */
    public function setPassword($password)
    {
        $this->password = $password;
    
        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Usuarios
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    
        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return Usuarios
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set roles
     *
     * @param string $roles
     * @return Usuarios
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    
        return $this;
    }
    
    /**
     * Get roles
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRoles()
    {
        return array($this->roles);
    }
    
    /**
     * @inheritDoc
     */
    public function eraseCredentials() {
    }

    /**
     * @inheritDoc
     */
    public function equals(UserInterface $user) {
        return array($this->username => $user->getUsername(), $this->password => $user->getPassword());
    }
    
    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
        ) = unserialize($serialized);
    }
    
    public function __toString() {
    	return strval($this->id);
    }
}
