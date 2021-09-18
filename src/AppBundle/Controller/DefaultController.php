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
        $sql = 'SELECT A.id, hara_ratio,content,consult_num,not_consult_num,cate_name FROM harassment_app.contributions AS A LEFT JOIN harassment_app.hara_category AS B ON A.cate_id = B.id LIMIT 5;';
        $contributions = $em->getConnection()->query($sql)->fetchAll();
        // $contributionsRepository = $em->getRepository('AppBundle:Contributions');
        // $contributions = $contributionsRepository->findBy([], ['id' => 'DESC'], 5);
        return $this->render('default/index.html.twig', ['contributions' => $contributions]);
    }
}
