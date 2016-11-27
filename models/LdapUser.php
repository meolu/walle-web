<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;
use app\models\User;

class LdapUser extends User
{

	private $id;

	public static function tableName() {
		return 'user';
	}

	public function getId() {
		return $this->id;//isset(\Yii::$app->params['ldap']) == true && is_array(\Yii::$app->params['ldap']) == true && isset(\Yii::$app->params['ldap']['identity']) == true ? \Yii::$app->params['ldap']['identity'] : 'uid';
	}

	public function setId($id){
		$this->id = $id;
	}

	/**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password) {
    	$encryptionType = strstr(substr($this->_password, 1), '}', true);
        return self::generate_password($password, $encryptionType) == $this->_password;
    }

    private static function generate_password($password, $encryptionType) {
    	switch(strtolower($encryptionType)) {
        	case 'blowfish':
            	if (defined('CRYPT_BLOWFISH') == false || CRYPT_BLOWFISH == 0) {
    				throw new \Exception(yii::t('yii', 'Your system crypt library does not support blowfish encryption'));
                }

            	# Hardcoded to second blowfish version and set number of rounds
            	return sprintf('{CRYPT}%s', crypt($password, '$2a$12$' . self::random_salt(13)));
            	break;
        	case 'crypt':
                //$new_value = sprintf('{CRYPT}%s',crypt($password_clear,substr($password_clear,0,2)));
                return sprintf('{CRYPT}%s', crypt($password, self::random_salt(2)));
            	break;
            case 'ext_des':
            	# Extended des crypt. see OpenBSD crypt man page.
            	if (defined('CRYPT_EXT_DES') == false || CRYPT_EXT_DES == 0) {
    				throw new \Exception(yii::t('yii', 'Your system crypt library does not support extended DES encryption'));
            	}

            	return sprintf('{CRYPT}%s', crypt($password, '_' . self::random_salt(8)));
            	break;
        	case 'k5key':
            	return sprintf('{K5KEY}%s', $password);
            	break;
        	case 'md5':
            	return sprintf('{MD5}%s', base64_encode(pack('H*', md5($password))));
            	break;
        	case 'md5crypt':
            	if (defined('CRYPT_MD5') == false || CRYPT_MD5 == 0) {
            		throw new \Exception(yii::t('yii', 'Your system crypt library does not support md5crypt encryption'));
            	}

            	return sprintf('{CRYPT}%s', crypt($password, '$1$' . self::random_salt(9)));
            	break;
         	case 'sha':
            	# Use php 4.3.0+ sha1 function, if it is available.
            	if (function_exists('sha1') == true) {
            		return sprintf('{SHA}%s', base64_encode(pack('H*', sha1($password))));
            	} elseif(function_exists('mhash')) {
            		return sprintf('{SHA}%s', base64_encode(mhash(MHASH_SHA1, $password)));
            	} else {
            		throw new \Exception(yii::t('yii', 'Your PHP install does not have the mhash() function. Cannot do SHA hashes'));
            	}
            	break;
        	case 'ssha':
            	if (function_exists('mhash') == true && function_exists('mhash_keygen_s2k') == true) {
                	mt_srand((double) microtime() * 1000000);
                	$salt = mhash_keygen_s2k(MHASH_SHA1, $password, substr(pack('h*', md5(mt_rand())), 0, 8) ,4);
                	return sprintf('{SSHA}%s', base64_encode(mhash(MHASH_SHA1, $password . $salt) . $salt));
            	} else {
            		throw new \Exception(yii::t('yii', 'Your PHP install does not have the mhash() or mhash_keygen_s2k() function. Cannot do S2K hashes'));
            	}
            	break;
            case 'smd5':
            	if(function_exists('mhash') == true && function_exists('mhash_keygen_s2k') == true) {
                	mt_srand((double) microtime() * 1000000);
                	$salt = mhash_keygen_s2k(MHASH_MD5, $password, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
                	return sprintf('{SMD5}%s', base64_encode(mhash(MHASH_MD5, $password . $salt) . $salt));
            	} else {
                	throw new \Exception(yii::t('yii', 'Your PHP install does not have the mhash() or mhash_keygen_s2k() function. Cannot do S2K hashes'));
            	}
            	break;
        	case 'sha512':
            	if(function_exists('openssl_digest') == true && function_exists('base64_encode') == true) {
                	return sprintf('{SHA512}%s', base64_encode(openssl_digest($password, 'sha512', true)));
            	} else {
                	throw new \Exception(yii::t('yii', 'Your PHP install doest not have the openssl_digest() or base64_encode() function. Cannot do SHA512 hashes'));
            	}
            	break;
        	case 'clear':
        	default:
            	return $password;
    	}
	}

	private static function random_salt($length) {
    	$possible = '0123456789'.
        	'abcdefghijklmnopqrstuvwxyz'.
        	'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        	'./';

    	$str = '';
    	$possibleLength = strlen($possible);
    	mt_srand((double)microtime() * 1000000);

    	for ($i = 0; $i < $length; $i++) {
        	$str .= substr($possible, (rand() % $possibleLength), 1);
        }

    	return $str;
	}

}