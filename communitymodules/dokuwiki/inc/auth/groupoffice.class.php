<?php
/**
 * GROUPOFFICE authentication backend
 *
 * @license   GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author    Wesley Smits <wsmits@intermesh.nl>
 * @company    Intermesh B.V. (http://www.intermesh.nl)
 * @product    Group-office (http://www.group-office.com)
 * 
 */

class auth_groupoffice extends auth_basic {

  /**
   * Constructor of this authentication class
   */
  function auth_groupoffice()
  {
		// Check for suhosin patch to be disabled
		$suhosinEncrypt = ini_get('suhosin.session.encrypt');
		if(!empty($suhosinEncrypt) && strtolower($suhosinEncrypt) == 'on')
			throw new Exception('You need to turn off "suhosin.session.encrypt" in the suhosin.ini file to let this module work. Otherwise this module cannot read the Group-Office session.');
		
		
    $this->cando['external'] = true;
    $this->cando['logoff'] = false;
    $this->success = true;
  }

  /**
   * Set the options that a user can do when logged in to Dokuwiki
   * 
   * @param type $cap
   * @return type 
   */
  function canDo($cap) {
    switch($cap)
    {
      case 'Profile':
        // can at least one of the user's properties be changed?
        return ( $this->cando['modPass']  ||
                 $this->cando['modName']  ||
                 $this->cando['modMail'] );
        break;
      case 'UserMod':
        // can at least anything be changed?
        return ( $this->cando['getGroups'] || 
                 $this->cando['modPass']   ||
                 $this->cando['modName']   ||
                 $this->cando['modMail']   ||
                 $this->cando['modLogin']  ||
                 $this->cando['modGroups'] ||
                 $this->cando['modMail'] );
        break;
      default:
        // print a helping message for developers
        if(!isset($this->cando[$cap])){
          msg("Check for unknown capability '$cap' - Do you use an outdated Plugin?",-1);
        }
        return $this->cando[$cap];
    }
  }

  /**
   * Get the user data to display in Dokuwiki
   * 
   * @param StringHelper $user The username of the user
   * @return array Array with user data 
   */
  function getUserData($user)
  {		
		//$this->_includeGroupOffice();

		$data = array(
			'name'=>\GO::user()->name,
			'mail'=>\GO::user()->email,
			'grps'=>$this->getGroups(\GO::user()->id)
		);
		
		return $data;
  }
  
  /**
   * Check for Group-Office login and auto login to Dokuwiki when inside Group-Office.
   * 
   * @global array $USERINFO
   * @param StringHelper $user
   * @param StringHelper $pass
   * @param boolean $sticky
   * @return boolean Logged-in 
   */
  function trustExternal($user,$pass,$sticky=false)
  {
    global $USERINFO;
					
    $sticky ? $sticky = true : $sticky = false; //sanity check
		
		
		$this->_includeGroupOffice();
		$this->_copyGroupOfficeSession();
		   
   if(!empty(\GO::session()->values['user_id']))
    {           
      $USERINFO['name'] = \GO::session()->values['name'];
      $USERINFO['mail'] = \GO::session()->values['email'];
      
      $USERINFO['grps'] = $this->getGroups(\GO::session()->values['user_id']);
            
      $_SERVER['REMOTE_USER'] = \GO::session()->values['username'];
      $_SESSION[DOKU_COOKIE]['auth']['user'] = \GO::session()->values['username'];
      $_SESSION[DOKU_COOKIE]['auth']['pass'] = $pass;
      $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
      return true;
    }
    else if(!empty($user))
    {
      return $this->checkPass($user, $pass);
    }
    else
    {
      // to be sure
      auth_logoff();
      return false;
    }
  }
  
  /**
   * Retrieve groups [implement only where required/possible]
   *
   * Set getGroups capability when implemented
   *
   * @author  Chris Smith <chris@jalakai.co.uk>
   * @return  array
   */
  function retrieveGroups($start=0,$limit=0) {
    return $this->getGroups(\GO::user()->id);
  }

  /**
   * Log out
   * 
   * @return type 
   */
  function logOff() 
  {
    unset($_SESSION[DOKU_COOKIE]);
		unset($_SESSION['GO_SESSION']);
		return true;
  }
  
	/**
   *
   * Check the given username and password against the Group-Office users.
   * 
   * @global type $USERINFO
   * @param type $user
   * @param type $pass
   * @return type 
   */
  function checkPass($user, $pass)
  {
		global $USERINFO;
		define('GO_NO_SESSION', true);
		
		$this->_includeGroupOffice();
		
		$user = \GO::session()->login($user, $pass);
		if(!$user)
			return false;
		
		if(!\GO::modules()->dokuwiki)
			return false;
		
		$this->checkRights();
		
		$USERINFO['name'] = \GO::user()->name;
		$USERINFO['mail'] = \GO::user()->email;
		$USERINFO['grps'] = $this->getGroups(\GO::user()->id);
		
		$_SERVER['REMOTE_USER'] = \GO::user()->username;
		
    $_SESSION[DOKU_COOKIE]['auth']['user'] = \GO::user()->username;
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
    if(\GO::modules()->dokuwiki->checkPermissionLevel(\GO\Base\Model\Acl::MANAGE_PERMISSION))
      $this->canDo('UserMod');
    else
      $this->canDo('Profile');
  }
  
  /**
   * Get an array of Groupnames from the current user
	 * 
   * @param int $userId
   * @return array list  
   */
  function getGroups($userId)
  {
		$groups = \GO::user()->getGroupIds($userId);
		
		$list = array();
		
		foreach($groups as $groupId){
			$group = \GO\Base\Model\Group::model()->findByPk($groupId, array(), true);
			$list[] = $group->name;
		}
		
		return $list;
  }
  	
	private function _includeGroupOffice(){
		global $conf;
		//define("GO_NO_SESSION");
		if(!empty($conf['GO\php'])){
			require_once($conf['GO\php']);
			error_reporting(E_ALL ^ E_NOTICE);
		} else {	
			throw new Exception('NO VALID GO URL GIVEN IN THE DOKUWIKI CONFIGURATION');
		}
	}
	
	private function _copyGroupOfficeSession(){
		
		if(isset($_COOKIE['groupoffice']))
    {
      $GO_SID=$_COOKIE['groupoffice'];
    }else
    {
      $GO_SID=false;
    }
		
		if(!isset(\GO::session()->values['GO_SID']) || \GO::session()->values['GO_SID']!=$GO_SID){
      //Group-Office session id changed. Someone else logged in.      
      if(!$this->logOff())
        throw new Exception('The GO session is not closed properly.');
    }
		
		if($GO_SID && empty(\GO::user()->id)) 
    {
      $fname = session_save_path() . "/sess_" . $GO_SID;
      if (file_exists($fname)) 
      {
        $data = file_get_contents($fname);
        $data = $this->_unserializesession($data);
        
        $_SESSION['GO_SESSION'] = $data['GO_SESSION'];
				\GO::session()->values = $data['GO_SESSION'];
        \GO::session()->values['GO_SID']=$GO_SID;
      }
    }
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
    for ($i = 0; isset($vars[$i]); $i++)
      $result[$vars[$i++]] = unserialize($vars[$i]);
    return $result;
  }
	
}