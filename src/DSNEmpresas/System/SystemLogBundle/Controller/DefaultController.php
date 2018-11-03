<?php

namespace DSNEmpresas\System\SystemLogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SystemLogBundle:Default:index.html.twig', array('name' => $name));
    }
}
