<?php
namespace Repositories;

use Ilmatar\DbUserProvider;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Carbon\Carbon;
use Ilmatar\JqGrid;
use Ilmatar\HelperFactory;

/**
 * User
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class User extends DbUserProvider
{
    protected $listExcludeForDisplay = array('deleted_at', 'deleted_by', 'password');

    public function getJqGridColNames(array $options = [])
    {
        $columns = parent::getJqGridColNames($options);
        //Appends users
        array_splice($columns, 4, 0, 'Role');

        return $columns;
    }

    public function getJqGridColModels(Translator $translator, UrlGenerator $urlGenerator = null, array $options = [])
    {
        $columns = parent::getJqGridColModels($translator, $urlGenerator, $options);
        //Inserts a new column
        $roleModel = array(
            array(
                "name"       => 'role',
                "index"      => 'role',
                "search"     => true,
                "editable"   => false,
                "sortable"   => false,
                "stype"       => 'select',
                "searchoptions" => array(
                    "sopt"  => array(self::JQGRID_OPERATOR_EQUAL),
                    "value" => self::buildSortValue(
                        HelperFactory::build('ArrayHelper')->buildAssociativeArray(
                            $this->getEntityManager()->getRepository('\\Entities\\Role')->findAll(),
                            'getId',
                            'getCode'
                        ),
                        $translator
                    )
                )
            )
        );
        array_splice($columns, 4, 0, $roleModel);
        //Change gender filter
        foreach ($columns as $idx => $column) {
            if ('gender' == $column['name']) {
                $columns[$idx]['searchoptions'] = array(
                    "sopt"  => array(self::JQGRID_OPERATOR_EQUAL),
                    "value" => self::buildSortValue(\Entities\User::getAllGenders(), $translator)
                );
                $columns[$idx]['stype'] = "select";
                unset($columns[$idx]['align']);
                unset($columns[$idx]['formatter']);
                break;
            }
        }
        return $columns;
    }

    public function formatJqGridRow($entity, Translator $translator, array $options = [])
    {
        $columns = parent::formatJqGridRow($entity, $translator, $options);
        //Get roles
        $role = $entity->getRole();
        if (!is_null($role)) {
            if (isset($options['has_role_access']) && $options['has_role_access']) {
                $columns['role'] = sprintf(
                    '<a title="%s" href="%s">%s</a>',
                    $translator->trans("Click to display this role"),
                    $options['url_generator']->generate('role-edit', array('roleId' => $entity->getId())),
                    $role->getCode()
                );
            } else {
                $columns['role'] = $role->getCode();
            }
        } else {
            $columns['role'] = '';
        }
        //Gender
        $columns['gender'] = $translator->trans(\Entities\User::getAllGenders()[$columns['gender']]);
        
        //Country
        if (!empty($entity->getCountry())) {
            $columns['country'] = HelperFactory::build("IntlHelper", ["locale" => $options['_locale']])->getCountryName($entity->getCountry());
        }

        return $columns;
    }

    public function processJqGridSpecialFields($entity, &$values, $oper, Translator $translator, array $options = [])
    {
        //Manages user settings
        $settingConfig      = \Entities\UserSetting::getDefaultSettings(true);
        $em                 = $this->getEntityManager();
        $repo               = $em->getRepository('\\Entities\\UserSetting');
        foreach ($settingConfig as $code => $config) {
            if ($oper == JqGrid::JQGRID_ACTION_DELETE) {
                $userSetting = $repo->findOneBy(
                    array(
                        'code' => $code,
                        'user' => $options['user']
                    )
                );
                if ($userSetting instanceof \Entities\UserSetting) {
                    $em->remove($userSetting);
                }
            } elseif (isset($values['user']['settings'][$code])) {
                $this->setUserSettings(
                    $options['user'],
                    array($code => $values['user']['settings'][$code])
                );
                unset($values['user']['settings'][$code]);
            }
        }
    }
    
    public function getActiveUsers($sessionLifetime, $idleLifetime)
    {
        $now = Carbon::now();

        $results = $this
            ->createQueryBuilder('f')
            ->select('f.username, f.firstname, f.lastname, f.login_at, f.active_at, f.logout_at')
            ->where('f.is_active = 1')
            ->andwhere('f.login_at is not NULL')
            ->andwhere('f.logout_at is NULL OR f.logout_at < f.login_at')
            ->andwhere('TIMESTAMPDIFF(SECOND, f.active_at, :date) < :idleLifetime')
            ->andwhere('TIMESTAMPDIFF(SECOND, f.login_at, :date) < :sessionLifetime')
            ->orderby('f.active_at', 'DESC')
            ->setParameter('date', Carbon::now())
            ->setParameter('sessionLifetime', $sessionLifetime)
            ->setParameter('idleLifetime', $idleLifetime)
            ->getQuery()
            ->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
        foreach ($results as $idx => $result) {
            foreach (['login_at', 'active_at', 'logout_at'] as $key) {
                if (!is_null($result[$key])) {
                    $results[$idx][$key] = sprintf(
                        "%s (%s)",
                        $result[$key]->format(self::DATETIME_STORAGE_FORMAT),
                        Carbon::instance($result[$key])->diffForHumans($now)
                    );
                }
            }
        }
        
        return $results;
    }
    
    public function setUserSettings(\Entities\User $user, Array $settings, $isFlush = false)
    {

        $em                 = $this->getEntityManager();
        $settingConfig      = \Entities\UserSetting::getDefaultSettings(true);
        $storedUserSettings = $user->getSettings();
        $repo               = $em->getRepository('\\Entities\\UserSetting');

        foreach ($settings as $code => $value) {
            if (!array_key_exists($code, $settingConfig)) {
                continue;
            }
            $newValue     =  \Entities\Parameter::convertToTypeFromString($value, $settingConfig[$code]['type']);
            $storedValue  =  $storedUserSettings[$code];
            $defaultValue =  $settingConfig[$code]['value'];
            if (($newValue == $defaultValue) && ($storedValue != $defaultValue)) {
                $userSetting = $repo->findOneBy(
                    array(
                        'code' => $code,
                        'user' => $user
                    )
                );
                $em->remove($userSetting);
            }
            if (($newValue != $defaultValue) && ($newValue != $storedValue)) {
                if ($storedValue == $defaultValue) {
                    $userSetting = new \Entities\UserSetting();
                    $userSetting->setCode($code);
                    $userSetting->setuser($user);
                } else {
                    $userSetting = $repo->findOneBy(
                        array(
                            'code' => $code,
                            'user' => $user
                        )
                    );
                }
                $userSetting->setValue($newValue);
                $em->persist($userSetting);
            }
        }
        if ($isFlush) {
            $em->flush();
        }
    }
    
    public function getLocaleFromMail($email)
    {
        $user = $this->findOneBy(['username' => $email]);
        if ($user instanceof \Entities\User) {
            return $user->getSettings()[\Entities\UserSetting::LOCALE];
        }
        return null;
    }
}
