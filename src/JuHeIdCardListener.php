<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2017/1/11
 * Time: 19:57:30
 */
namespace ApigilityVendorIntegrate;

use ApigilityAddress\DoctrineEntity\Address;
use ApigilityAddress\Service\AddressService;
use ApigilityUser\DoctrineEntity\User;
use ApigilityUser\Service\PersonalCertificationService;
use ApigilityUser\Service\UserService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventInterface;
use Zend\ServiceManager\ServiceManager;
use ApigilityAddress\Service\RegionService;

class JuHeIdCardListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    private $services;

    /**
     * @var \ApigilityVendorIntegrate\Service\JuHe\IdCardService
     */
    protected $idCardService;

    /**
     * @var UserService
     */
    protected $userService;

    /**
     * @var RegionService
     */
    protected $regionService;

    /**
     * @var AddressService
     */
    protected $addressService;

    public function __construct(ServiceManager $services)
    {
        $this->services = $services;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(PersonalCertificationService::EVENT_STATUS_SWITCH_TO_OK, [$this, 'getIdCardInfo'], $priority);
    }

    public function getIdCardInfo(EventInterface $e)
    {
        $params = $e->getParams();
        $personal_certification = $params['personal_certification'];

        // 查询身份证信息填写到用户资料
        try {
            $this->idCardService = $this->services->get('ApigilityVendorIntegrate\Service\JuHe\IdCardService');
            $id_card_info = $this->idCardService->getIdCardInfo($personal_certification->getIdentityCardNumber());

            $id_card_info = json_decode($id_card_info);
            if ((int)$id_card_info->resultcode === 200) {

                $user_data = [];

                switch ($id_card_info->result->sex) {
                    case '男':
                        $user_data['sex'] = User::SEX_MALE;
                        break;

                    case '女':
                        $user_data['sex'] = User::SEX_FEMALE;
                        break;
                }

                // 计算年龄
                $born_year = (int)substr($id_card_info->result->birthday, 0, 4);
                $user_data['age'] = (int)((new \DateTime())->format('Y')) - $born_year;

                // 分析省市区
                /* 不再使用此方法分析省市区，已采用根据身份证号码进行识别的方案，详情请参阅ApigilityAddress和ApigilityUser模块
                $pattern = '/^([^省市]+)[省市]{1,1}([^市县区]+)[市县区]{1,1}(([^市县区]+)[市县区]{1,1})?$/Uu';
                $matches = null;
                preg_match($pattern, $id_card_info->result->area, $matches);

                if (count($matches) >= 3) {
                    $province_name = null;
                    $city_name = null;
                    $district_name = null;

                    if (count($matches) === 5) {
                        $province_name = $matches[1];
                        $city_name = $matches[2];
                        $district_name = $matches[4];
                    } elseif (count($matches) === 3) {
                        $province_name = $matches[1];
                        $city_name = $matches[1];
                        $district_name = $matches[2];
                    }

                    $province = $this->getRegionService()->getRegionByName($province_name, 'province');
                    $city = $this->getRegionService()->getRegionByName($city_name, 'city');
                    $district = $this->getRegionService()->getRegionByName($district_name, 'district');

                    $address_data = [
                        'province'=>$province->getId(),
                        'city'=>$city->getId(),
                        'district'=>$district->getId()
                    ];

                    // 如果没有户口地址，创建一个
                    $address = $personal_certification->getUser()->getCensusRegisterAddress();
                    if ($address instanceof Address) {
                        $address = $this->getAddressService()->updateAddress($address->getId(), (object)$address_data);
                    } else {
                        $address = $this->getAddressService()->createAddress((object)$address_data);
                        $user_data['census_register_address'] = $address->getId();
                    }
                }
                */

                // 保存到用户资料
                $this->userService = $this->services->get('ApigilityUser\Service\UserService');
                $this->userService->updateUser($personal_certification->getUser()->getId(), (object)$user_data);
            }
        } catch (\Exception $exception) {
            throw $exception;
        }

    }

    /**
     * @return RegionService
     */
    private function getRegionService()
    {
        if (!($this->regionService instanceof RegionService)) $this->regionService = $this->services->get('ApigilityAddress\Service\RegionService');

        return $this->regionService;
    }

    /**
     * @return AddressService
     */
    private function getAddressService()
    {
        if (!($this->addressService instanceof AddressService)) $this->addressService = $this->services->get('ApigilityAddress\Service\AddressService');

        return $this->addressService;
    }
}