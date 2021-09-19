<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Comments;
use AppBundle\Entity\Contributions;
use AppBundle\Entity\HaraCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\Tools\Pagination\Paginator;



/**
 * @Route("/contrib",)
 */
class ContributionController extends Controller
{
    /**
     * @Route("/",name="contrib_index")
     */
    public function indexAction(Request $request)
    {
        $_SESSION = [];
        $pageNum = $request->query->get('page');
        if (is_numeric($pageNum)) {
            $results = $this->pagenation(10, $pageNum);
        } else {
            $results = $this->pagenation(10, 1);
        }
        return $this->render('contributions/index.html.twig', ['contributions' => $results['result'], 'pagesCount' => $results['pagesCount']]);
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
        $contribution = $em->createQueryBuilder()
            ->select('a', 'b', 'c')
            ->from("AppBundle\Entity\Contributions", 'a')
            ->leftjoin('a.category', 'b')
            ->leftjoin('a.comments', 'c')
            ->andWhere('a.id = :id')
            ->setParameter('id', $pageId)
            ->addOrderBy('a.createdAt', 'desc')
            ->getQuery()
            ->getResult();
        $comments = $contribution[0]->getComments()->getValues();
        // $form = $this->createFormBuilder()
        //     ->setMethod('POST')
        //     ->add('comment', TextareaType::class, ['label' => 'コメントを記入'])
        //     ->add('submit', SubmitType::class, ['label' => '投稿する'])->getForm();
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
                $comment->setContribution($contribution[0]);
                $em->persist($comment);
                $em->flush();
                return $this->render('contributions/detail_complete.html.twig', ["id" => $contribution[0]->getId()]);
            }
        }
        if (isset($_SESSION['voted'])) {
            return $this->render('contributions/detail.html.twig', ["contribution" => $contribution[0], 'comments' => $comments, 'voted' => true]);
        } else {
            return $this->render('contributions/detail.html.twig', ["contribution" => $contribution[0], 'comments' => $comments, 'voted' => false]);
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
            $form = $request->request->get("form");
            $em = $this->getDoctrine()->getManager();
            $cateRepositry = $em->getRepository('AppBundle:HaraCategory');
            $category = $cateRepositry->findOneBy(['id' => (int) $_SESSION["whichHara"]]);
            $contribution = new Contributions();
            $contribution->setHaraRatio($_SESSION['resultPer']);
            $contribution->setContent($form["comment"]);
            $contribution->setCategory($category);
            $contribution->setQuestionsAnswers($_SESSION['select_vals']);
            $contribution->setSelectedQuestions($_SESSION['select_ids']);
            $contribution->setEmail($form["email"]);
            $em->persist($contribution);
            $em->flush();
            $_SESSION = [];
            // $message = \Swift_Message::newInstance()
            //     ->setSubject('投稿から一週間がたちました')
            //     ->setFrom(["jumpater.dev@gmail.com" => 'にたみ'])
            //     ->setTo($form["email"])
            //     ->setBody('Body');
            // $this->get('mailer')->send($message);
            return $this->render('contributions/complete.html.twig');
        } else {
            return $this->redirectToRoute('index_page');
        }
    }

    public function pagenation(int $dataPerPages, int $currentPage = 1, int $cateId = -1, string $dateOrder = "DESC")
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('a', 'b')
            ->from("AppBundle\Entity\Contributions", 'a')
            ->leftjoin('a.category', 'b');
        if ($cateId !== -1) {
            $query = $query->andWhere('b.id = :cateId')
                ->setParameter('cateId', $cateId);
        }
        if ($dateOrder !== "DESC") {
            $query = $query->andWhere('a.createdAt = :order')
                ->setParameter('order', 'ASC');
        }
        $query = $query->getQuery();
        $pagenator = new Paginator($query);
        $pagesCount = ceil(count($pagenator) / $dataPerPages);
        $result = $pagenator
            ->getQuery()
            ->setFirstResult($dataPerPages * ($currentPage - 1)) // set the offset
            ->setMaxResults($dataPerPages * $currentPage) // set the limit
            ->getResult();
        return ['result' => $result, 'pagesCount' => $pagesCount,];
    }
}
