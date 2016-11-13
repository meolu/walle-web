<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;
use app\models\User;
use app\models\LdapUser;
use app\models\forms\LoginForm;

/**
 * LdapLoginForm is the model behind the login form by ldap.
 */

class LdapLoginForm extends LoginForm
{

	private static $_configs = null;

	private static $_conn = null;

    private $_user = false;

	public function __construct(array $configs) {
		if(extension_loaded('ldap') == false) {
			throw new \Exception(yii::t('yii', 'The extendsion \'ldap\' not loaded'));
		}

		parent::__construct();

		self::$_configs = $configs;
	}

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login() {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        } else {
            return false;
        }
    }

	public function getUser() {
        if ($this->_user === false) {
        	self::getConn();
        	self::bind();

        	$filter = str_replace('${username}', $this->username, self::$_configs['accountPattern']);
	    	$sr = ldap_search(self::$_conn, self::$_configs['accountBase'], $filter);
	    	unset($filter);

	    	if(ldap_count_entries(self::$_conn, $sr) == 0) {
	    		ldap_close(self::$_conn);
	    		return array();
	    	}

	    	$entry = ldap_first_entry(self::$_conn, $sr);
	    	$attributes = ldap_get_attributes(self::$_conn, $entry);

	    	$this->_user = new LdapUser();

	    	foreach ($attributes as $key => $value) {
	    		if($key == 'userPassword') {
	    			$this->_user->setPassword($value[0]);
	    		}

	    		if(isset(self::$_configs['attributesMap']) == true && is_array(self::$_configs['attributesMap']) == true) {
	    			if (isset(self::$_configs['attributesMap'][$key]) == true && is_string(self::$_configs['attributesMap'][$key]) == true) {
	    				$field = self::$_configs['attributesMap'][$key];
	    				$this->_user->setAttribute($field, $value[0]);
	    				unset($field);
	    			}
	    		} else {
	    			if (is_string($key) == true) {
	    				$this->_user->$key = $value[0];
	    			}
	    		}
	    	}
	    	ldap_close(self::$_conn);

	    	$user = User::findByUsername(array(
	    			'username' => $this->username
	    		));

	    	if ($user == null) {
	    		$attributes['username'] = $this->username;
	    		$user = $this->register($attributes);
	    	}

	    	$this->_user->setId($user->getId());
	    	unset($attributes, $user);
        }

        return $this->_user;
    }

    private static function getConn(){
    	if(self::$_conn !== null) {
    		return self::$_conn;
    	}

    	$host = isset(self::$_configs['host']) == false ? 'localhost' : self::$_configs['host'];
    	$port = 389;

    	if (isset(self::$_configs['port']) == true) {
    		if(is_numeric(self::$_configs['port']) == false) {
    			throw new \Exception(yii::t('walle', 'illegal service port', array(
    					'service'=>'Ldap',
    					'port'=>self::$_configs['port']
    				)));
    		}

    		$port = (int) self::$_configs['port'];
    	}

    	if (isset(self::$_configs['ssl']) == true && self::$_configs['ssl'] == true) {
    		$host = 'ldaps://' . $host;
    	}

    	if (isset(self::$_configs['accountBase']) == false || empty(self::$_configs['accountBase']) == true) {
    		throw new \Exception(yii::t('walle', 'account base dn could not defined'));
    	}

    	if (isset(self::$_configs['accountPattern']) == false || empty(self::$_configs['accountPattern']) == true) {
    		throw new \Exception(yii::t('walle', 'account pattern could not defined'));
    	}

    	self::$_conn = ldap_connect($host, $port);
    	unset($host);

    	ldap_set_option(self::$_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option(self::$_conn, LDAP_OPT_REFERRALS, 0);

		return self::$_conn;
    }

    private static function bind() {
    	$ldapbind = null;
		if(isset(self::$_configs['username']) == true && isset(self::$_configs['password']) == true) {
    		$ldapbind = ldap_bind(self::$_conn, self::$_configs['username'], self::$_configs['password']);
    	} elseif(isset(self::$_configs['username']) == true) {
    		$ldapbind = ldap_bind(self::$_conn, self::$_configs['username']);
    	}
    }

    private function register(array $data) {
    	$user = new User(['scenario' => 'signup']);

	    foreach ($data as $key => $value) {
		    if($key == 'userPassword') {
		    	$user->setPassword(preg_replace('/\{[a-zA-Z\d]+\}/', '', $value[0]));
		    }

		    if(isset(self::$_configs['attributesMap']) == true && is_array(self::$_configs['attributesMap']) == true) {
		    	if (isset(self::$_configs['attributesMap'][$key]) == true && is_string(self::$_configs['attributesMap'][$key]) == true) {
		    		$field = self::$_configs['attributesMap'][$key];
		    		$user->$field = $value[0];
		    		unset($field);
		    	}
		    } else {
		    	if (is_string($key) == true) {
		    		$user->$key = $value[0];
		    	}
		    }
		    unset($value);
		}

		$user->role = User::ROLE_DEV;
		$user->status = User::STATUS_ACTIVE;

		if ($user->save()) {
			Yii::$app->mail->compose('confirmEmail', ['user' => $user])
		        ->setFrom(Yii::$app->mail->messageConfig['from'])
		        ->setTo($user->email)
		        ->setSubject('瓦力平台 - ' . $user->realname)
		        ->send();

		    unset($user);
		    return User::findByUsername(array(
		    	'username' => $data['username']
		    ));
		} else {
			unset($user);
	    	throw new \Exception(yii::t('user', 'user auto register failure'));
		}
    }

}