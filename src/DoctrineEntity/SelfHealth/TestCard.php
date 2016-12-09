<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 16:11
 */
namespace ApigilityVendorIntegrate\DoctrineEntity\SelfHealth;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * Class TestCard
 * @package ApigilityUser\DoctrineEntity
 * @Entity @Table(name="apigilityvi_selfhealth_test_card")
 */
class TestCard
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="ApigilityUser\DoctrineEntity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * http://selfhealth.com.cn/ 北京燕鑫康达科技有限公司 测评卡服务的 测评卡ID，每个用户一个
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $self_health_card_id;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setSelfHealthCardId($self_health_card_id)
    {
        $this->self_health_card_id = $self_health_card_id;
        return $this;
    }

    public function getSelfHealthCardId()
    {
        return $this->self_health_card_id;
    }
}