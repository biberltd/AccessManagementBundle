<?php
/**
 * @author		Can Berkol
 * @author		Murat Ünal
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com) (C) 2015
 * @license     GPLv3
 *
 * @date        10.12.2015
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Entity;

use BiberLtd\Bundle\CoreBundle\CoreEntity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(
 *     name="member_access_right",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idxNMemberAccessRightDateAssigned", columns={"date_assigned"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idxUMemberAccessRight", columns={"member","action"})}
 * )
 * @ORM\Entity
 */
class MemberAccessRight extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=1, nullable=false, options={"default":"g"})
     * @var \string
     */
    private $right;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @var \DateTime
     */
    private $date_assigned;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LogBundle\Entity\Action")
     * @ORM\JoinColumn(name="action", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\LogBundle\Entity\Action
     */
    private $action;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @var \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
     */
    private $member;

    /**
     * @param string $right
     *
     * @return $this
     */
    public function setRight(\string $right){
        if(!$this->setModified('right', $right)->isModified()){
            return $this;
        }
        $this->right = $right;

        return $this;
    }

    /**
     * @return string
     */
    public function getRight(){
        return $this->right;
    }

    /**
     * @param \DateTime $date_assigned
     *
     * @return $this
     */
    public function setDateAssigned(\DateTime $date_assigned){
        if(!$this->setModified('date_assigned', $date_assigned)->isModified()){
            return $this;
        }
        $this->date_assigned = $date_assigned;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAssigned(){
        return $this->date_assigned;
    }

    /**
     * @param \BiberLtd\Bundle\LogBundle\Entity\Action $action
     *
     * @return $this
     */
    public function setAction(\BiberLtd\Bundle\LogBundle\Entity\Action $action){
        if(!$this->setModified('action', $action)->isModified()){
            return $this;
        }
        $this->action = $action;

        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\LogBundle\Entity\Action
     */
    public function getAction(){
        return $this->action;
    }

    /**
     * @param \BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member
     *
     * @return $this
     */
    public function setMember(\BiberLtd\Bundle\MemberManagementBundle\Entity\Member $member){
        if(!$this->setModified('member', $member)->isModified()){
            return $this;
        }
        $this->member = $member;

        return $this;
    }

    /**
     * @return \BiberLtd\Bundle\MemberManagementBundle\Entity\Member
     */
    public function getMember(){
        return $this->member;
    }

}