<?php
/**
 * @name        MemberAccessRight
 * @package		BiberLtd\Bundle\CoreBundle\AccessManagementBundle
 *
 * @author		Murat Ünal
 * @version     1.0.2
 * @date        09.10.2013
 *
 * @copyright   Biber Ltd. (http://www.biberltd.com)
 * @license     GPL v3.0
 *
 * @description Model / Entity class.
 *
 */
namespace BiberLtd\Bundle\AccessManagementBundle\Entity;

use BiberLtd\Bundle\CoreBundle\CoreEntity;
use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Table(
 *     name="member_access_right",
 *     options={"charset":"utf8","collate":"utf8_turkish_ci","engine":"innodb"},
 *     indexes={@ORM\Index(name="idx_n_member_access_right_date_assigned", columns={"date_assigned"})},
 *     uniqueConstraints={@ORM\UniqueConstraint(name="idx_u_member_access_right", columns={"member","action"})}
 * )
 * @ORM\Entity
 */
class MemberAccessRight extends CoreEntity
{
    /**
     * @ORM\Column(type="string", length=1, nullable=false)
     */
    private $right;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_assigned;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\LogBundle\Entity\Action")
     * @ORM\JoinColumn(name="action", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $action;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="BiberLtd\Bundle\MemberManagementBundle\Entity\Member")
     * @ORM\JoinColumn(name="member", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $member;
    /******************************************************************
     * PUBLIC SET AND GET FUNCTIONS                                   *
     ******************************************************************/

    /**
     * @name            setRight()
     *  				Sets $right property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           string          $right
     *
     * @return          object          $this
     */
    public function setRight($right){
        if(!$this->setModified('right', $right)->isModified()){
            return $this;
        }
        $this->right = $right;

        return $this;
    }
    /**
     * @name            getRight(()
     *  				Gets $right property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          string          $this->right
     */
    public function getRight(){
        return $this->right;
    }
    /**
     * @name            setDateAssigned()
     *  				Sets $date_assigned property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           \DateTime       $date_assigned
     *
     * @return          object          $this
     */
    public function setDateAssigned($date_assigned){
        if(!$this->setModified('date_assigned', $date_assigned)->isModified()){
            return $this;
        }
        $this->date_assigned = $date_assigned;

        return $this;
    }
    /**
     * @name            getDateAssigned()
     *  				Gets $date_assigned property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          datetime          $this->date_assigned
     */
    public function getDateAssigned(){
        return $this->date_assigned;
    }
    /**
     * @name            setAction()
     *  				Sets $action property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           BiberLtd\Bundle\LogBundle\Entity\Action          $action
     *
     * @return          object          $this
     */
    public function setAction($action){
        if(!$this->setModified('action', $action)->isModified()){
            return $this;
        }
        $this->action = $action;

        return $this;
    }
    /**
     * @name            getAction()
     *  				Gets $action property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\LogBundle\Entity\Action          $this->action
     */
    public function getAction(){
        return $this->action;
    }
    /**
     * @name            setMember()
     *  				Sets $member property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @param           BiberLtd\Bundle\MemberManagementBundle\Entity\Member          $member
     *
     * @return          object          $this
     */
    public function setMember($member){
        if(!$this->setModified('member', $member)->isModified()){
            return $this;
        }
        $this->member = $member;

        return $this;
    }
    /**
     * @name            getMember()
     *  				Gets $member property.
     * .
     * @author          Murat Ünal
     * @since			1.0.0
     * @version         1.0.0
     *
     * @return          BiberLtd\Bundle\MemberManagementBundle\Entity\Member          $this->member
     */
    public function getMember(){
        return $this->member;
    }

}
/**
 * Change Log:
 * **************************************
 * v1.0.1                      Murat Ünal
 * 16.07.2013
 * **************************************
 * A getAction()
 * A getDateAssigned()
 * A getMember()
 * A getRight(()
 *
 * A setAction()
 * A setDateAssigned()
 * A setMember()
 * A setRight()
 *
 */