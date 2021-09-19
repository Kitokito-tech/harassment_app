<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Contributions
 *
 * @ORM\Table(name="contributions")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContributionsRepository")
 */
class Contributions
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var HaraCategory
     *
     * @ORM\ManyToOne(targetEntity="HaraCategory", inversedBy="contributions")
     * @ORM\JoinColumn(name="cate_id", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @var int
     * 
     * @ORM\Column(name="hara_ratio", type="integer")
     * @Assert\NotBlank()
     */
    private $haraRatio;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=300)
     * @Assert\NotBlank()
     * @Assert\Length(max=300)
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="consult_num", type="integer")
     */
    private $consultNum = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="not_consult_num", type="integer")
     */
    private $notConsultNum = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     * @Assert\Length(max=100)
     */
    private $email;

    /**
     * @var array
     *
     * @ORM\Column(name="selected_questions", type="array")
     * @Assert\NotBlank()
     */
    private $selectedQuestions;

    /**
     * @var array
     *
     * @ORM\Column(name="questions_answers", type="array")
     * @Assert\NotBlank()
     */
    private $questionsAnswers;

    /**
     * @var date
     * @ORM\Column(name="created_at", type="date")
     * @Assert\NotBlank()
     */
    private $createdAt;

    /**
     * @var Comments
     *
     * @ORM\OneToMany(targetEntity="Comments", mappedBy="contribution")
     */
    private $comments;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set haraRatio
     *
     * @param integer $haraRatio
     *
     * @return Contributions
     */
    public function setHaraRatio($haraRatio)
    {
        $this->haraRatio = $haraRatio;

        return $this;
    }

    /**
     * Get haraRatio
     *
     * @return integer
     */
    public function getHaraRatio()
    {
        return $this->haraRatio;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Contributions
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set consultNum
     *
     * @param integer $consultNum
     *
     * @return Contributions
     */
    public function setConsultNum($consultNum)
    {
        $this->consultNum = $consultNum;

        return $this;
    }

    /**
     * Get consultNum
     *
     * @return integer
     */
    public function getConsultNum()
    {
        return $this->consultNum;
    }

    /**
     * Set notConsultNum
     *
     * @param integer $notConsultNum
     *
     * @return Contributions
     */
    public function setNotConsultNum($notConsultNum)
    {
        $this->notConsultNum = $notConsultNum;

        return $this;
    }

    /**
     * Get notConsultNum
     *
     * @return integer
     */
    public function getNotConsultNum()
    {
        return $this->notConsultNum;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contributions
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set selectedQuestions
     *
     * @param array $selectedQuestions
     *
     * @return Contributions
     */
    public function setSelectedQuestions($selectedQuestions)
    {
        $this->selectedQuestions = $selectedQuestions;

        return $this;
    }

    /**
     * Get selectedQuestions
     *
     * @return array
     */
    public function getSelectedQuestions()
    {
        return $this->selectedQuestions;
    }

    /**
     * Set questionsAnswers
     *
     * @param array $questionsAnswers
     *
     * @return Contributions
     */
    public function setQuestionsAnswers($questionsAnswers)
    {
        $this->questionsAnswers = $questionsAnswers;

        return $this;
    }

    /**
     * Get questionsAnswers
     *
     * @return array
     */
    public function getQuestionsAnswers()
    {
        return $this->questionsAnswers;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Contributions
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\HaraCategory $category
     *
     * @return Contributions
     */
    public function setCategory(\AppBundle\Entity\HaraCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\HaraCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add comment
     *
     * @param \AppBundle\Entity\Comments $comment
     *
     * @return Contributions
     */
    public function addComment(\AppBundle\Entity\Comments $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\Comments $comment
     */
    public function removeComment(\AppBundle\Entity\Comments $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }
}
