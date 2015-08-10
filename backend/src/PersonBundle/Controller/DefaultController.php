<?php

namespace PersonBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('PersonBundle:Default:index.html.twig');
    }
}
