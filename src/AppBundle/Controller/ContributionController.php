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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
        $pageNum = $request->query->get('page') ?? 1;
        $cateIds = $request->query->get('form')['categories'] ?? [];
        $dateOrder = $request->query->get('form')['dateOrder'] ?? 'DESC';
        $search = $request->query->get('form')['search'] ?? '';
        $queries = ['cateIds' => $cateIds, 'dateOrder' => $dateOrder, 'search' => $search];
        $results = $this->pagenation(10, $pageNum, $cateIds = $cateIds, $search = $search, $dateOrder = $dateOrder);
        if (!$results['pagesCount']) {
            $results['pagesCount'] = 1;
        }
        $searchForm = $this->createSearchForm($cateIds,  $dateOrder, $search);
        return $this->render(
            'contributions/index.html.twig',
            [
                'contributions' => $results['result'],
                'pagesCount' => $results['pagesCount'],
                'searchForm' => $searchForm,
                'queries' => $queries,
                'currentPage' => $pageNum,
            ]
        );
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
        $contribution = $contribution[0];
        $comments = $contribution->getComments()->getValues();
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
                ->add('email', EmailType::class, ['required' => false])
                ->add('comment', TextareaType::class)
                ->add('submit', SubmitType::class, ['label' => '??????'])->getForm()->createView();
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
        if ($request->isMethod('post') && isset($_SESSION["whichHara"])) {
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
            return $this->render('contributions/complete.html.twig');
        } else {
            return $this->redirectToRoute('index_page');
        }
    }

    public function pagenation(int $dataPerPages, int $currentPage = 0, array $cateIds = [], string $search = '', string $dateOrder = "DESC")
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQueryBuilder()
            ->select('a', 'b')
            ->from("AppBundle\Entity\Contributions", 'a')
            ->leftjoin('a.category', 'b');
        if ($cateIds) {
            $query = $query->andWhere('b.id in( :cateIds)')
                ->setParameter('cateIds', $cateIds);
        }
        if ($search !== '') {
            $query = $query->andWhere('a.content like :content')
                ->setParameter('content', '%' . $search . '%');
        }
        if ($dateOrder !== "DESC") {
            $query = $query->addOrderBy('a.createdAt', 'ASC');
        } else {
            $query = $query->addOrderBy('a.createdAt', 'DESC');
        }
        $query = $query->getQuery();
        $pagenator = new Paginator($query);
        $pagesCount = ceil(count($pagenator) / $dataPerPages);
        $result = $pagenator
            ->getQuery()
            ->setFirstResult($dataPerPages * ($currentPage - 1))
            ->setMaxResults($dataPerPages)
            ->getResult();
        return ['result' => $result, 'pagesCount' => $pagesCount,];
    }
    public function createSearchForm($cateIds, $dateOrder, $search)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryObjs = $em->getRepository('AppBundle:HaraCategory')->findAll();
        $categorys = [];
        $attrAry = [];
        foreach ($categoryObjs as $categoryObj) {
            $categorys[$categoryObj->getCateName()] = $categoryObj->getId();
            if (in_array($categoryObj->getId(), $cateIds, false)) {
                $attrAry[$categoryObj->getCateName()] = ['checked' => 'checked'];
            }
        }
        if ($dateOrder === "DESC") {
            $dateOrderArray = ['????????????' => 'DESC', '?????????' => 'ASC'];
        } else {
            $dateOrderArray = ['?????????' => 'ASC', '????????????' => 'DESC'];
        }
        $form = $this->createFormBuilder()
            ->setMethod('GET');
        $form = $form
            ->add('search', TextType::class, [
                'required' => false,
                'label' => '?????????????????????',
                'data' => $search,
                'attr' => array(
                    'maxlength' => 100,
                    'placeholder' => "search",
                    'class' => 'form-control'
                ),
                'data' => $search
            ])
            ->add('categories', ChoiceType::class, [
                'choices'  => $categorys,
                'label' => "????????????",
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => $attrAry,
            ])
            ->add('submit', SubmitType::class, ['label' => ' '])->getForm();
        return $form->createView();
    }
}
