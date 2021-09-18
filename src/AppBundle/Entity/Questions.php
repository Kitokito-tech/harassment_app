<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Questions
 *
 * @ORM\Table(name="questions")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuestionsRepository")
 */
class Questions
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
     * @var Hara_category
     *
     * @ORM\ManyToOne(targetEntity="Hara_category", inversedBy="questions")
     * @ORM\JoinColumn(name="cate_id", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=300)
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(name="ratio_of_weight", type="integer")
     */
    private $ratioOfWeight = 0;

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
     * Set content
     *
     * @param string $content
     *
     * @return Questions
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
     * Set ratioOfWeight
     *
     * @param integer $ratioOfWeight
     *
     * @return Questions
     */
    public function setRatioOfWeight($ratioOfWeight)
    {
        $this->ratioOfWeight = $ratioOfWeight;

        return $this;
    }

    /**
     * Get ratioOfWeight
     *
     * @return integer
     */
    public function getRatioOfWeight()
    {
        return $this->ratioOfWeight;
    }

    /**
     * Set category
     *
     * @param \AppBundle\Entity\Hara_category $category
     *
     * @return Questions
     */
    public function setCategory(\AppBundle\Entity\Hara_category $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \AppBundle\Entity\Hara_category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
