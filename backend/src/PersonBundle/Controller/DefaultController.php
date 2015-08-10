<?php

namespace PersonBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="category_index")
     * @return mixed
     */
    public function indexAction()
    {
        return $this->render('PersonBundle:Default:index.html.twig');
    }
}
