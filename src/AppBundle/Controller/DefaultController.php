<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index_page")
     */
    public function indexAction()
    {
        $_SESSION = [];
        $em = $this->getDoctrine()->getManager();
        $contributions = $em->createQueryBuilder()
            ->select('c', 'r')
            ->from("AppBundle\Entity\Contributions", 'c')
            ->leftjoin('c.category', 'r')
            ->addOrderBy('c.createdAt', 'desc')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
        return $this->render('default/index.html.twig', ['contributions' => $contributions]);
    }
}
