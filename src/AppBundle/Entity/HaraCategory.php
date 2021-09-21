<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * HaraCategory
 *
 * @ORM\Table(name="hara_category")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HaraCategoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class HaraCategory
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
     * @var string
     *
     * @ORM\Column(name="cate_name", type="string",length=30)
     * @Assert\NotBlank()
     */
    private $cateName;

    /**
     * @ORM\OneToMany(targetEntity="Contributions", mappedBy="category")
     */
    private $contributions;

    /**
     * @ORM\OneToMany(targetEntity="Questions", mappedBy="category")
     */
    private $questions;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->contributions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set cateName
     *
     * @param string $cateName
     *
     * @return HaraCategory
     */
    public function setCateName($cateName)
    {
        $this->cateName = $cateName;

        return $this;
    }

    /**
     * Get cateName
     *
     * @return string
     */
    public function getCateName()
    {
        return $this->cateName;
    }

    /**
     * Add contribution
     *
     * @param \AppBundle\Entity\Contributions $contribution
     *
     * @return HaraCategory
     */
    public function addContribution(\AppBundle\Entity\Contributions $contribution)
    {
        $this->contributions[] = $contribution;

        return $this;
    }

    /**
     * Remove contribution
     *
     * @param \AppBundle\Entity\Contributions $contribution
     */
    public function removeContribution(\AppBundle\Entity\Contributions $contribution)
    {
        $this->contributions->removeElement($contribution);
    }

    /**
     * Get contributions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContributions()
    {
        return $this->contributions;
    }

    /**
     * Add question
     *
     * @param \AppBundle\Entity\Questions $question
     *
     * @return HaraCategory
     */
    public function addQuestion(\AppBundle\Entity\Questions $question)
    {
        $this->questions[] = $question;

        return $this;
    }

    /**
     * Remove question
     *
     * @param \AppBundle\Entity\Questions $question
     */
    public function removeQuestion(\AppBundle\Entity\Questions $question)
    {
        $this->questions->removeElement($question);
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}
