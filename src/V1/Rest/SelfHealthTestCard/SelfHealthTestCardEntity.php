<?php
namespace ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard;

use ApigilityCatworkFoundation\Base\ApigilityObjectStorageAwareEntity;
use ApigilityUser\DoctrineEntity\User;
use ApigilityUser\V1\Rest\User\UserEntity;

class SelfHealthTestCardEntity extends ApigilityObjectStorageAwareEntity
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * http://selfhealth.com.cn/ 北京燕鑫康达科技有限公司 测评卡服务的 测评卡ID，每个用户一个
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $self_health_card_id;

    protected $login_url;

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
        return $this->hydrator->extract(new UserEntity($this->user, $this->serviceManager));
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

    public function getLoginUrl()
    {
        return $this->serviceManager->get('ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService')->makeLoginUrl($this->getSelfHealthCardId());
    }
}
