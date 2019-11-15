<?php
/**
 * GROUPOFFICE authentication backend
 *
 * @license   GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author    WilmarVB <wilmar@intermesh.nl>
 * @author    Wesley Smits <wsmits@intermesh.nl>
 * @company    Intermesh B.V. (http://www.intermesh.nl)
 * @product    Group-office (http://www.group-office.com)
 *
 */

class auth_plugin_authgroupoffice extends DokuWiki_Auth_Plugin
{

    /**
     * Constructor sets this to true iff Group-Office authentication was correctly
     * loaded.
     * @var boolean
     */
    public $success;

    /**
     * List of all functions this backend provides.
     * @var array
     */
    public $cando;

    public function __construct()
    {
        // Check for suhosin patch to be disabled
        $suhosinEncrypt = ini_get('suhosin.session.encrypt');
        if (!empty($suhosinEncrypt) && strtolower($suhosinEncrypt) == 'on') {
            throw new Exception('You need to turn off "suhosin.session.encrypt" in the suhosin.ini file to let this module work. Otherwise this module cannot read the Group-Office session.');
        }

        $this->cando = array(
            'addUser' => false,
            'delUser' => false,
            'modLogin' => false,
            'modPass' => false,
            'modName' => false,
            'modMail' => false,
            'modGroups' => false,
            'getUsers' => true,
            'getUserCount' => true,
            'getGroups' => true,
            'external' => true,
            'retrieveUsers' => true,
            'retrieveGroups' => true,
            'logout' => true
        );

        $groupOfficeIncluded = $this->_includeGroupOffice();
        $goSessionCopied = $this->_copyGroupOfficeSession();
        $this->success = $groupOfficeIncluded && $goSessionCopied;

    }

    /**
     * @return bool
     * @throws Exception
     */
    private function _includeGroupOffice()
    {
        global $conf;
        //define("GO_NO_SESSION");
        if (!empty($conf['GO_php'])) {
            require_once($conf['GO_php']);
            error_reporting(E_ALL && ~E_NOTICE && ~E_DEPRECATED);
        } else {
            throw new Exception('NO VALID GO URL GIVEN IN THE DOKUWIKI CONFIGURATION');
        }
        return true;
    }

    /**
     * Unserialize the Group-Office session
     *
     * @param type $data
     * @return type
     */
    private function _unserializesession($data)
    {
        $vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/',
            $data, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0; isset($vars[$i]); $i++) {
            $result[$vars[$i++]] = unserialize($vars[$i]);
        }
        return $result;
    }

    /**
     * TODO - not sure if this will be available in future because GO uses JWT token
     *
     * @return bool
     * @throws Exception
     */
    private function _copyGroupOfficeSession()
    {
        if (isset($_COOKIE['groupoffice'])) {
            $GO_SID = $_COOKIE['groupoffice'];
        } else {
            return false;
        }

        if (!isset(\GO::session()->values['GO_SID']) || \GO::session()->values['GO_SID'] != $GO_SID) {
            //Group-Office session id changed. Someone else logged in.
            if (!$this->logOff()) {
                throw new Exception('The GO session is not closed properly.');
            }
        }

        if ($GO_SID) {
            if (empty(\GO::user()->id)) {

                $fname = session_save_path() . "/sess_" . $GO_SID;

                if (file_exists($fname)) {
                    $data = file_get_contents($fname);
                    $data = $this->_unserializesession($data);

                    \GO::session()->values = $data['GO_SESSION'];
                    \GO::session()->values['GO_SID'] = $GO_SID;

                } else {
                    return false;
                }

            }

            return true;
        }
        return false;
    }

    /**
     * Check for Group-Office login and auto login to Dokuwiki when inside Group-Office.
     *
     * @param string $user
     * @param string $pass
     * @param boolean $sticky
     * @return boolean Logged-in
     * @throws Exception
     * @global array $USERINFO
     */
    function trustExternal($user, $pass, $sticky = false)
    {
        global $USERINFO;
        global $AUTH_ACL;

        $sticky ? $sticky = true : $sticky = false; //sanity check

        $this->_includeGroupOffice();
        $this->_copyGroupOfficeSession();

        if (!empty(\GO::session()->values['user_id'])) {
            /** @var \go\core\model\User $userModel */
            $userModel = \go\core\model\User::findById(\GO::session()->values['user_id']);
            $USERINFO['name'] = $userModel->displayName;
            $USERINFO['mail'] = $userModel->email;

            $USERINFO['grps'] = $this->getGroups(\GO::session()->values['user_id']);
            $_SERVER['REMOTE_USER'] = $userModel->username;
            $_SESSION[DOKU_COOKIE]['auth']['user'] = $userModel->username;
            $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
            $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
            $this->checkRights();
            return true;
        }

        if (!empty($user)) {
            return $this->checkPass($user, $pass);
        }

        // to be sure
        auth_logoff();
        return false;
    }

    /**
     *
     * Check the given username and password against the Group-Office users.
     *
     * @param string $user
     * @param string $pass
     * @return bool
     * @throws \GO\Base\Exception\OtherLoginLocation
     * @throws \GO\Base\Exception\PasswordNeedsChange
     * @throws Exception
     * @global array $USERINFO
     */
    function checkPass($user, $pass)
    {
        global $USERINFO;

        define('GO_NO_SESSION', true);

        $this->_includeGroupOffice();

        $auth = new \go\core\auth\Password();
        $user = $auth->authenticate($user, $pass);

        if (!$user) {
            msg($lang['badlogin'], -1);
            auth_logoff();
            return false;
        }

        if (!\go\core\App::getModule('community', 'dokuwiki')) {
            return false;
        }

        $this->checkRights();

        $loggedUser = GO()->getAuthState()->getUser();

        $USERINFO['name'] = $loggedUser->displayName;
        $USERINFO['mail'] = $loggedUser->email;
        $USERINFO['grps'] = $this->getGroups($loggedUser->id);

        $_SERVER['REMOTE_USER'] = $loggedUser->username;

        $_SESSION[DOKU_COOKIE]['auth']['user'] = $loggedUser->username;
        $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
        $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;

        return true;
    }

    /**
     * Check which rights the user has.
     *
     */
    function checkRights()
    {
        $module = \go\core\App::getModule('community', 'dokuwiki');
        if ($module && $module->hasPermissionLevel(\go\core\model\Acl::LEVEL_MANAGE)) {
            $this->cando['UserMod'] = true;
            $this->cando['Profile'] = true;
        } else {
            $this->cando['Profile'] = true;
        }
    }

    /**
     * Get the user data to display in Dokuwiki
     *
     * @param string $user The username of the user
     * @param bool $requireGroups
     * @return array Array with user data
     * @throws Exception
     */
    function getUserData($user, $requireGroups = true)
    {
        $loggedUser = GO()->getAuthState()->getUser();

        $data = array(
            'name' => $loggedUser->displayName,
            'mail' => $loggedUser->email,
            'grps' => $this->getGroups($loggedUser->id)
        );

        return $data;
    }

    /**
     * Get an array of Groupnames from the current user
     *
     * @param int $userId
     * @return array list
     * @throws Exception
     */
    function getGroups($userId)
    {
        $groups = GO()->getDbConnection()
            ->selectSingleValue('groupId')
            ->from('core_user_group')
            ->where(['userId' => $userId])
            ->all();

        $list = array();

        foreach ($groups as $groupId) {
            /** @var \go\core\model\Group $group */
            $group = \go\core\model\Group::findById($groupId);
            $list[] = $group->name;
        }

        return $list;
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @param array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     * @throws Exception
     * @author Chris Smith <chris@jalakai.co.uk>
     */
    public function getUserCount($filter = array())
    {
        return \go\core\model\User::find()->execute()->rowCount();
    }

    /**
     * Bulk retrieval of user data [implement only where required/possible]
     *
     * Set getUsers capability when implemented
     *
     * @param int $start index of first user to be returned
     * @param int $limit max number of users to be returned, 0 for unlimited
     * @param array $filter array of field/pattern pairs, null for no filter
     * @return  array list of userinfo (refer getUserData for internal userinfo details)
     * @throws Exception
     * @author  Chris Smith <chris@jalakai.co.uk>
     */
    public function retrieveUsers($start = 0, $limit = 0, $filter = null)
    {
        /** @var \go\core\model\User[] $stmt */
        $stmt = \go\core\model\User::find()->all();

        $users = array();

        foreach ($stmt as $model) {

            $users[] = array(
                'id' => $model->id,
                'name' => $model->displayName,
                'mail' => $model->email,
                'grps' => $this->getGroups($model->id)
            );

        }

        return $users;
    }

    /**
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @param int $start
     * @param int $limit
     * @return  array
     * @throws Exception
     * @author  Chris Smith <chris@jalakai.co.uk>
     */
    function retrieveGroups($start = 0, $limit = 0)
    {
        $list = array();

        /** @var \go\core\model\Group[] $groups */
        $groups = \go\core\model\Group::find()->all();
        foreach ($groups as $group) {

            $list[] = $group->name;
        }

        return $list;
    }

    /**
     * Log out
     *
     * @return bool
     */
    function logOff()
    {
        unset($_SESSION['GO_SESSION']);
        return true;
    }
}
