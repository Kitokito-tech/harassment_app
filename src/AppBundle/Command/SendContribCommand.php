<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Controller\ContributionController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Twig\Environment;

class SendContribCommand extends Command
{
    use ControllerTrait;
    private $entityManager;
    private $twig;
    private $mailer;
    protected static $defaultName = 'app:send-contrib';
    public function __construct(EntityManagerInterface $entityManager, Environment $twig, \Swift_Mailer $mailer)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
        parent::__construct();
    }
    protected function configure()
    {
        $this->setDescription('send a mail to user who created a contribution.')
            ->setHelp('This command allows you to send a mail to user who created a contribution a week ago.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from("AppBundle\Entity\Contributions", 'a')
            ->andWhere('a.createdAt BETWEEN :dateA AND :dateB')
            ->setParameter(':dateA', (new \DateTime())->modify('-7 day'))
            ->setParameter(':dateB', (new \DateTime())->modify('-6 day'))
            ->getQuery()->getResult();
        if ($results) {
            foreach ($results as $result) {
                if ($result->getEmail()) {
                    $message = \Swift_Message::newInstance()
                        ->setFrom('system_test@glic.co.jp', '〇Xハラスメント')
                        ->setSubject('〇Xハラスメント【ご相談内容の結果についてのご報告】')
                        ->setBody($this->twig->render('Email/cont_email.html.twig', ['pageId' => $result->getId()]), 'text/html')
                        ->setReplyTo('system_test@glic.co.jp')
                        ->setTo($result->getEmail());
                    $this->mailer->send($message);
                }
            }
            $output->write('success!!');
        } else {
            $output->write('no data is fetched');
        }
    }
}
