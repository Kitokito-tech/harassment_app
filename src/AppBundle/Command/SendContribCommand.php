<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Controller\ContributionController;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;

class SendContribCommand extends Command
{
    use ControllerTrait;
    protected static $defaultName = 'app:send-contrib';
    protected function __construct()
    {
        parent::__construct();
    }
    protected function configure()
    {
        $this->setDescription('send a mail to user who created a contribution.')
            ->setHelp('This command allows you to send a mail to user who created a contribution a week ago.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = new EntityManagerInterface();
        $results = $em->createQueryBuilder()
            ->select('a')
            ->from("AppBundle\Entity\Contributions", 'a')
            ->andWhere('createdAt = :date')
            ->setParameter(':date', new \DateTime("-7 days"))
            ->getQuery()->getResult();
        if ($results) {
            foreach ($results as $result) {
                if ($result->getEmail()) {
                    $controller = new ContributionController;
                    $message = \Swift_Message::newInstance()
                        ->setFrom('system_test@glic.co.jp', '〇Xハラスメント')
                        ->setSubject('〇Xハラスメント【ご相談内容の結果についてのご報告】')
                        ->setBody($this->renderView('Email/cont_email.html.twig', ['pageId' => $result->getId()]), 'text/html')
                        ->setReplyTo('system_test@glic.co.jp')
                        ->setTo($result->getEmail());
                    (new \Swift_Mailer())->send($message);
                }
            }
        }
    }
}
