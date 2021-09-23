<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/diagnoze")
 */
class DiagnozeController extends Controller
{
    public $buttonStats = ['当てはまる' => 4, 'やや当てはまる' => 3, '分からない' => 2, 'あまり当てはまらない' => 1, '当てはまらない' => 0];
    public $assessVals = [4 => 1, 3 => 0.8, 2 => 0.4, 1 => 0.2, 0 => 0];

    /**
     * @Route("/select_cate/", name="diagnoze_cate")
     */
    public function selectCateAction()
    {
        $_SESSION = [];
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl("diagnoze_questions"))
            ->setMethod('POST')
            ->add('whichHara', HiddenType::class, ['data' => 1])
            ->add('submit', SubmitType::class, ['label' => '質問へ'])->getForm();
        $em = $this->getDoctrine()->getManager();
        $HaraCategoryRepository = $em->getRepository('AppBundle:HaraCategory');
        $categories = $HaraCategoryRepository->findAll();
        return $this->render('diagnoze/selectCate.html.twig', ['categories' => $categories, 'form' => $form->createView()]);
    }

    /**
     * @Route("/questions", name="diagnoze_questions")
     */
    public function questionPageAction(Request $request)
    {
        if ($request->isMethod('post')) {
            $em = $this->getDoctrine()->getManager();
            $forms = [];
            if (isset($_SESSION['questions']) && count($_SESSION['questions']) === 1) {
                foreach ($this->buttonStats as $key => $stat) {
                    $form = $this->createFormBuilder()
                        ->setAction($this->generateUrl("diagnoze_result"))
                        ->setMethod('POST')
                        ->add('whichStats', HiddenType::class, ['data' => $stat])
                        ->add('submit', SubmitType::class, ['label' => $key])->getForm()->createView();
                    $forms[] = $form;
                }
            } else {
                foreach ($this->buttonStats as $key => $stat) {
                    $form = $this->createFormBuilder()
                        ->setMethod('POST')
                        ->add('whichStats', HiddenType::class, ['data' => $stat])
                        ->add('submit', SubmitType::class, ['label' => $key])->getForm()->createView();
                    $forms[] = $form;
                }
            }
            //一回目
            if (isset($request->request->get('form')['whichHara'])) {
                $_SESSION['whichHara'] = $request->request->get('form')['whichHara'];
                $sql = 'SELECT id, cate_id,content, ratio_of_weight FROM harassment_app.questions WHERE cate_id = ' . $_SESSION['whichHara'] . ' ORDER BY RAND() LIMIT 10;';
                $questions = $em->getConnection()->query($sql)->fetchAll();
                $_SESSION['questions'] = $questions;
                $_SESSION['select_ids'] = [];
                $_SESSION['select_vals'] = [];
                $_SESSION['question_ratios'] = [];
            }
            //二回目以降
            if (isset($request->request->get('form')['whichStats'])) {
                $_SESSION['select_vals'][] = (int) $request->request->get('form')['whichStats'];
            }
            $question = array_shift($_SESSION['questions']);
            $_SESSION['select_ids'][] = (int) $question['id'];
            $_SESSION['question_ratios'][] = (int) $question['ratio_of_weight'];
            $questionNum = count($_SESSION['select_ids']);
            return $this->render('diagnoze/question.html.twig', ['question' => $question, 'forms' => $forms, 'questionNum' => $questionNum]);
        } else {
            return $this->redirectToRoute('index_page');
        }
    }

    /**
     * @Route("/result", name="diagnoze_result")
     */
    public function resultPageAction(Request $request)
    {
        if ($request->isMethod('post')) {
            if (count($_SESSION['select_vals']) === 9) {
                $_SESSION['select_vals'][] = (int) $request->request->get('form')['whichStats'];
            }
            $select_vals = $_SESSION['select_vals'];
            $select_ids = $_SESSION['select_ids'];
            $qestion_ratios = $_SESSION['question_ratios'];
            //パーセント計算
            $resultPer = 0;
            for ($i = 0; $i < 10; $i++) {
                $resultPer += $this->assessVals[$select_vals[$i]] * $qestion_ratios[$i];
            }
            if (0 > $resultPer) {
                $resultPer = 0;
            } elseif ($resultPer > 100) {
                $resultPer = 100;
            }
            $_SESSION['resultPer'] = $resultPer;
            $_SESSION = [];
            return $this->render('diagnoze/result.html.twig', ["resultPer" => $resultPer]);
        } else {
            return $this->redirectToRoute('index_page');
        }
    }
}
