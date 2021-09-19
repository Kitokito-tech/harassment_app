<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comments;
use AppBundle\Entity\Contributions;
use AppBundle\Entity\Hara_category;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Session;



/**
 * @Route("/contrib",)
 */
class ContributionController extends Controller
{
    /**
     * @Route("/",name="contrib_index")
     */
    public function indexAction()
    {
        $_SESSION = [];
        $em = $this->getDoctrine()->getManager();
        $sql = 'SELECT A.id, hara_ratio,content,consult_num,not_consult_num,cate_name FROM harassment_app.contributions AS A LEFT JOIN harassment_app.hara_category AS B ON A.cate_id = B.id;';
        $contributions = $em->getConnection()->query($sql)->fetchAll();
        return $this->render('contributions/index.html.twig', ['contributions' => $contributions]);
    }

    /**
     * @Route("/detail",name="contrib_detail")
     */
    public function detailAction(Request $request)
    {
        if (!isset($_SESSION)) {
            $session = new Session();
            $session->start();
        }
        $pageId = $request->query->get('id');
        if (!$pageId) {
            return $this->redirectToRoute('contrib_index');
        }
        $em = $this->getDoctrine()->getManager();
        $contribution = $em->getRepository('AppBundle:Contributions')->findOneBy(['id' => $pageId]);
        if (!$contribution) {
            return $this->redirectToRoute('contrib_index');
        }
        $sql = "SELECT * FROM harassment_app.comments AS A WHERE A.contribution_id = {$pageId};";
        $comments = $em->getConnection()->query($sql)->fetchAll();
        if ($request->isMethod('POST')) {
            if ($request->request->get('consult') && !isset($_SESSION['voted'])) {
                if ($request->request->get('consult') === 'yes') {
                    $num = $contribution->getConsultNum() + 1;
                    $contribution->setConsultNum($num);
                } else {
                    $num = $contribution->getNotConsultNum() + 1;
                    $contribution->setNotConsultNum($num);
                }
                $_SESSION['voted'] = true;
                $em->flush();
            }
            if ($request->request->get('comment')) {
                $comment = new Comments();
                $comment->setContent($request->request->get('comment'));
                $comment->setContribution($contribution);
                $em->persist($comment);
                $em->flush();
                return $this->render('contributions/detail_complete.html.twig', ["id" => $contribution->getId()]);
            }
        }
        if (isset($_SESSION['voted'])) {
            return $this->render('contributions/detail.html.twig', ["contribution" => $contribution, 'comments' => $comments, 'voted' => true]);
        } else {
            return $this->render('contributions/detail.html.twig', ["contribution" => $contribution, 'comments' => $comments, 'voted' => false]);
        }
    }

    /**
     * @Route("/make",name="contrib_make")
     */
    public function makeContribAction()
    {
        if (isset($_SESSION['resultPer'])) {
            $form = $this->createFormBuilder()
                ->setAction($this->generateUrl("contrib_complete"))
                ->setMethod('POST')
                ->add('email', EmailType::class)
                ->add('comment', TextareaType::class)
                ->add('submit', SubmitType::class, ['label' => '完了'])->getForm()->createView();
            return $this->render('contributions/make_contrib.html.twig', ['form' => $form]);
        } else {
            return $this->redirectToRoute('index_page');
        }
    }

    /**
     * @Route("/make_complete",name="contrib_complete")
     */
    public function completeContrib(Request $request)
    {
        if ($request->isMethod('post')) {
            $em = $this->getDoctrine()->getManager();
            $cateRepositry = $em->getRepository('AppBundle:Hara_category');
            $category = $cateRepositry->findOneBy(['id' => (int) $_SESSION["whichHara"]]);
            $requests = $request->request->get('form');
            $contribution = new Contributions();
            $contribution->setHaraRatio($_SESSION['resultPer']);
            $contribution->setContent($requests["comment"]);
            $contribution->setCategory($category);
            $contribution->setQuestionsAnswers($_SESSION['select_vals']);
            $contribution->setSelectedQuestions($_SESSION['select_ids']);
            $contribution->setEmail($requests["email"]);
            $em->persist($contribution);
            $em->flush();
            $_SESSION = [];
            $message = \Swift_Message::newInstance()
                ->setSubject('投稿から一週間がたちました')
                ->setFrom(["jumpater.dev@gmail.com" => 'にたみ'])
                ->setTo($requests["email"])
                ->setBody('Body');
            $this->get('mailer')->send($message);
            return $this->render('contributions/complete.html.twig');
        } else {
            return $this->redirectToRoute('index_page');
        }
    }
}
