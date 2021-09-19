<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comments
 *
 * @ORM\Table(name="comments")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentsRepository")
 */
class Comments
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
     * @var Contributions
     *
     * @ORM\ManyToOne(targetEntity="Contributions", inversedBy="comment")
     * @ORM\JoinColumn(name="contribution_id", referencedColumnName="id", nullable=false)
     */
    private $contribution;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=300)
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @var date
     * @ORM\Column(name="created_at", type="date")
     * @Assert\NotBlank()
     */
    private $createdAt;

    public function __construct()
    {
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
     * Set content
     *
     * @param string $content
     *
     * @return Comments
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
     * Set contribution
     *
     * @param \AppBundle\Entity\Contributions $contribution
     *
     * @return Comments
     */
    public function setContribution(\AppBundle\Entity\Contributions $contribution)
    {
        $this->contribution = $contribution;

        return $this;
    }

    /**
     * Get contribution
     *
     * @return \AppBundle\Entity\Contributions
     */
    public function getContribution()
    {
        return $this->contribution;
    }

    /**
     * @return date
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param date $createdAt
     * @return Comments
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
