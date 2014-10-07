<?php

namespace Entities;

use Doctrine\ORM\Mapping as ORM;
use Ilmatar\BaseEntity;
use Ilmatar\Exception\TranslatedException;

/**
 * UserSetting
 */
class UserSetting extends BaseEntity
{
    //List of possible types is taken from \Entities\Parameter

    //List of possible codes
    const LANDING_PAGE = 'LANDING_PAGE';
    const LOCALE       = 'LOCALE';
    const THEME        = 'THEME';
    //Add new setting here

    const DEFAULT_LOCALE = "fr";
    const DEFAULT_THEME  = 17;//start
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $code = null;

    /**
     * @var string
     */
    private $value;

    /**
     * @var \Entities\User
     */
    private $user;

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
     * Set code
     *
     * @param string $code
     * @return UserSetting
     */
    public function setCode($code)
    {
        $this->code = $code;

        $config = self::getDefaultSettings(true);
        if (!isset($config[$code])) {
                throw \Exception(sprintf("Invalid data code %s into %s", $code, __FUNCTION__));
        }
        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return UserSetting
     */
    public function setValue($value)
    {
        if (is_null($this->code)) {
            throw \Exception(sprintf("setCode() must be called before setting value into ", __FUNCTION__));
        }

        $this->value = Parameter::convertToStringViaType(
            $value,
            self::getDefaultSettings(true)[$this->code]['type']
        );

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        if (is_null($this->code)) {
            throw \Exception(sprintf("setCode() must be called before setting value into ", __FUNCTION__));
        }
        //format is based on provided code
        return Parameter::convertToStringViaType(
            $this->value,
            self::getDefaultSettings(true)[$this->code]['type']
        );
    }

    /**
     * Set user
     *
     * @param \Entities\User $user
     * @return UserSetting
     */
    public function setUser(\Entities\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Entities\User
     */
    public function getUser()
    {
        return $this->user;
    }

    public static function getDefaultSettings($isWithType = false)
    {
        $defaultsettings = [
            self::LANDING_PAGE => array(
                'type'        => Parameter::TYPE_STRING,
                'value'       => \Project\Controller\DashboardController::PRIVATE_DEFAULT_HOMEPAGE,
                'label'       => 'Landing page after connection',
                'is_editable' => true
            ),
            self::LOCALE => array(
                'type'        => Parameter::TYPE_STRING,
                'value'       => self::DEFAULT_LOCALE,
                'label'       => 'User interface language',
                'is_editable' => true
            ),
            self::THEME => array(
                'type'        => Parameter::TYPE_ENUM,
                'value'       => self::DEFAULT_THEME,
                'label'       => 'User interface theme',
                'is_editable' => true
            )
            //Add default setting here
            //Manually set the right format for 'value' here (string, boolean, integer, float, enum)
        ];
        if ($isWithType) {
            return $defaultsettings;
        }
        $out = [];
        foreach ($defaultsettings as $key => $value) {
            $out[$key] = $value['value'];
        }
        return $out;
    }

    /**
     * @ORM\PrePersist
     */
    public function assertValidUserSetting()
    {
        if (!in_array($this->code, array_keys(self::getDefaultSettings(true)), true)) {
            throw new TranslatedException('The field "%s" must be valid.', array('trans:Code'));
        }
    }

    public static function getAllThemes()
    {
        return [
            'black-tie',
            'blitzer',
            'cupertino',
            'dark-hive',
            'dot-luv',
            'eggplant',
            'excite-bike',
            'flick',
            'hot-sneaks',
            'humanity',
            'le-frog',
            'mint-choc',
            'overcast',
            'pepper-grinder',
            'redmond',
            'smoothness',
            'south-street',
            'start',
            'sunny',
            'swanky-purse',
            'trontastic',
            'ui-darkness',
            'ui-lightness',
            'vader'
        ];
    }
}
