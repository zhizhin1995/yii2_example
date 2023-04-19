<?php

namespace app\models;

use webvimark\helpers\LittleBigHelper;
use webvimark\helpers\Singleton;
use webvimark\modules\UserManagement\components\AuthHelper;
use webvimark\modules\UserManagement\components\UserIdentity;
use webvimark\modules\UserManagement\models\rbacDB\Role;
use webvimark\modules\UserManagement\models\rbacDB\Route;
use webvimark\modules\UserManagement\UserManagementModule;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property integer $email_confirmed
 * @property string $auth_key
 * @property string $access_token
 * @property string $password_hash
 * @property string $confirmation_token
 * @property string $bind_to_ip
 * @property string $registration_ip
 * @property integer $status
 * @property integer $superadmin
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \webvimark\modules\UserManagement\models\User implements IdentityInterface
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_BANNED = -1;

    /**
     * @var string
     */
    public $gridRoleSearch;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $repeat_password;

    /**
     * Store result in singleton to prevent multiple db requests with multiple calls
     *
     * @param bool $fromSingleton
     *
     * @return static
     */
    public static function getCurrentUser($fromSingleton = true)
    {
        if ( !$fromSingleton )
        {
            return static::findOne(Yii::$app->user->id);
        }

        $user = Singleton::getData('__currentUser');

        if ( !$user )
        {
            $user = static::findOne(Yii::$app->user->id);

            Singleton::setData('__currentUser', $user);
        }

        return $user;
    }

    /**
     * Assign role to user
     *
     * @param int  $userId
     * @param string $roleName
     *
     * @return bool
     */
    public static function assignRole($userId, $roleName)
    {
        try
        {
            Yii::$app->db->createCommand()
                ->insert(Yii::$app->getModule('user-management')->auth_assignment_table, [
                    'user_id' => $userId,
                    'item_name' => $roleName,
                    'created_at' => time(),
                ])->execute();

            AuthHelper::invalidatePermissions();

            return true;
        }
        catch (\Exception $e)
        {
            return false;
        }
    }

    /**
     * Revoke role from user
     *
     * @param int    $userId
     * @param string $roleName
     *
     * @return bool
     */
    public static function revokeRole($userId, $roleName)
    {
        $result = Yii::$app->db->createCommand()
            ->delete(Yii::$app->getModule('user-management')->auth_assignment_table, ['user_id' => $userId, 'item_name' => $roleName])
            ->execute() > 0;

        if ( $result )
        {
            AuthHelper::invalidatePermissions();
        }

        return $result;
    }

    /**
     * @param string|array $roles
     * @param bool         $superAdminAllowed
     *
     * @return bool
     */
    public static function hasRole($roles, $superAdminAllowed = true)
    {
        if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
        {
            return true;
        }
        $roles = (array)$roles;

        AuthHelper::ensurePermissionsUpToDate();

        return array_intersect($roles, Yii::$app->session->get(AuthHelper::SESSION_PREFIX_ROLES,[])) !== [];
    }

    /**
     * @param string $permission
     * @param bool   $superAdminAllowed
     *
     * @return bool
     */
    public static function hasPermission($permission, $superAdminAllowed = true)
    {
        if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
        {
            return true;
        }

        AuthHelper::ensurePermissionsUpToDate();

        return in_array($permission, Yii::$app->session->get(AuthHelper::SESSION_PREFIX_PERMISSIONS,[]));
    }

    /**
     * Useful for Menu widget
     *
     * <example>
     *  ...
     *      [ 'label'=>'Some label', 'url'=>['/site/index'], 'visible'=>User::canRoute(['/site/index']) ]
     *  ...
     * </example>
     *
     * @param string|array $route
     * @param bool         $superAdminAllowed
     *
     * @return bool
     */
    public static function canRoute($route, $superAdminAllowed = true)
    {
        if ( $superAdminAllowed AND Yii::$app->user->isSuperadmin )
        {
            return true;
        }

        $baseRoute = AuthHelper::unifyRoute($route);

        if ( Route::isFreeAccess($baseRoute) )
        {
            return true;
        }

        AuthHelper::ensurePermissionsUpToDate();

        return Route::isRouteAllowed($baseRoute, Yii::$app->session->get(AuthHelper::SESSION_PREFIX_ROUTES,[]));
    }

    /**
     * getStatusList
     * @return array
     */
    public static function getStatusList()
    {
        return array(
            self::STATUS_ACTIVE   => UserManagementModule::t('back', 'Active'),
            self::STATUS_INACTIVE => UserManagementModule::t('back', 'Inactive'),
            self::STATUS_BANNED   => UserManagementModule::t('back', 'Banned'),
        );
    }

    /**
     * getStatusValue
     *
     * @param string $val
     *
     * @return string
     */
    public static function getStatusValue($val)
    {
        $ar = self::getStatusList();

        return isset( $ar[$val] ) ? $ar[$val] : $val;
    }

    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return Yii::$app->getModule('user-management')->user_table;
    }

    /**
    * @inheritdoc
    */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }


    public function getPolyclinic()
    {
        return $this->hasOne(Polyclinics::className(), ['id' => 'polyclinic_id']);
    }



    /**
    * @inheritdoc
    */
    public function rules()
    {
        return [
            ['username', 'required'],
            ['username', 'unique'],
            ['username', 'trim'],

            [['status', 'email_confirmed', 'polyclinic_id'], 'integer'],

            ['email', 'email'],
            ['email', 'validateEmailConfirmedUnique'],

            ['superadmin', 'boolean'],

            ['bind_to_ip', 'validateBindToIp'],
            ['bind_to_ip', 'trim'],
            ['bind_to_ip', 'string', 'max' => 255],

            ['password', 'required', 'on'=>['newUser', 'changePassword']],
            ['password', 'string', 'max' => 255, 'on'=>['newUser', 'changePassword']],
            ['password', 'trim', 'on'=>['newUser', 'changePassword']],
            ['password', 'match', 'pattern' => Yii::$app->getModule('user-management')->passwordRegexp],

            ['repeat_password', 'required', 'on'=>['newUser', 'changePassword']],
            ['repeat_password', 'compare', 'compareAttribute'=>'password'],
        ];
    }

    /**
     * Check that there is no such confirmed E-mail in the system
     */
    public function validateEmailConfirmedUnique()
    {
        if ( $this->email )
        {
            $exists = User::findOne([
                'email'           => $this->email,
                'email_confirmed' => 1,
            ]);

            if ( $exists AND $exists->id != $this->id )
            {
                $this->addError('email', UserManagementModule::t('front', 'This E-mail already exists'));
            }
        }
    }

    /**
     * Validate bind_to_ip attr to be in correct format
     */
    public function validateBindToIp()
    {
        if ( $this->bind_to_ip )
        {
            $ips = explode(',', $this->bind_to_ip);

            foreach ($ips as $ip)
            {
                if ( !filter_var(trim($ip), FILTER_VALIDATE_IP) )
                {
                    $this->addError('bind_to_ip', UserManagementModule::t('back', "Wrong format. Enter valid IPs separated by comma"));
                }
            }
        }
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'username'           => UserManagementModule::t('back', 'Логин'),
            'superadmin'         => 'Администратор',
            'confirmation_token' => UserManagementModule::t('back', 'Confirmation Token'),
            'registration_ip'    => UserManagementModule::t('back', 'Registration IP'),
            'bind_to_ip'         => UserManagementModule::t('back', 'Bind to IP'),
            'status'             => UserManagementModule::t('back', 'Статус'),
            'gridRoleSearch'     => UserManagementModule::t('back', 'Роли'),
            'created_at'         => UserManagementModule::t('back', 'Создан'),
            'updated_at'         => UserManagementModule::t('back', 'Изменен'),
            'password'           => UserManagementModule::t('back', 'Пароль'),
            'repeat_password'    => UserManagementModule::t('back', 'Повторить пароль'),
            'email_confirmed'    => UserManagementModule::t('back', 'E-mail confirmed'),
            'email'              => UserManagementModule::t('back', 'E-mail'),
            'polyclinic_id'      => 'Поликлиника',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Role::className(), ['name' => 'item_name'])
            ->viaTable(Yii::$app->getModule('user-management')->auth_assignment_table, ['user_id'=>'id']);
    }


    /**
     * Make sure user will not deactivate himself and superadmin could not demote himself
     * Also don't let non-superadmin edit superadmin
     *
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ( $insert )
        {
            if ( php_sapi_name() != 'cli' )
            {
                $this->registration_ip = LittleBigHelper::getRealIp();
            }
            $this->generateAuthKey();
        }
        else
        {
            // Console doesn't have Yii::$app->user, so we skip it for console
            if ( php_sapi_name() != 'cli' )
            {
                if ( Yii::$app->user->id == $this->id )
                {
                    // Make sure user will not deactivate himself
                    $this->status = static::STATUS_ACTIVE;

                    // Superadmin could not demote himself
                    if ( Yii::$app->user->isSuperadmin AND $this->superadmin != 1 )
                    {
                        $this->superadmin = 1;
                    }
                }

                // Don't let non-superadmin edit superadmin
                if ( isset($this->oldAttributes['superadmin']) && !Yii::$app->user->isSuperadmin && $this->oldAttributes['superadmin'] == 1 )
                {
                    return false;
                }
            }
        }

        // If password has been set, than create password hash
        if ( $this->password )
        {
            $this->setPassword($this->password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Don't let delete yourself and don't let non-superadmin delete superadmin
     *
     * @inheritdoc
     */
    public function beforeDelete()
    {
        // Console doesn't have Yii::$app->user, so we skip it for console
        if ( php_sapi_name() != 'cli' )
        {
            // Don't let delete yourself
            if ( Yii::$app->user->id == $this->id )
            {
                return false;
            }

            // Don't let non-superadmin delete superadmin
            if ( !Yii::$app->user->isSuperadmin AND $this->superadmin == 1 )
            {
                return false;
            }
        }

        return parent::beforeDelete();
    }

    /**
     * @return string|bool
     * @throws Exception
     */
    public static function authAPI(LoginForm $form)
    {
        $user = self::findByUsername($form->username);

        if ($user && $user->validatePassword($form->password)) {
            $db = Yii::$app->db;

            $token = Yii::$app->security->generateRandomString();
            $userID = $user->getId();

            if (!$userID) {
                return false;
            }

            $db->createCommand("UPDATE {{%user}} SET access_token=:token WHERE id=:userID")
                ->bindParam('token', $token)
                ->bindParam('userID', $userID)
                ->execute();

            return $token;
        }

        return false;
    }

    /**
     * @param $token
     * @param $type
     * @return User|UserIdentity
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
}
