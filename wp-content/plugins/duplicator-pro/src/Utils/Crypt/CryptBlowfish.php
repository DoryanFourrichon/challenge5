<?php

/**
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Utils\Crypt;

use DUP_PRO_Log;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\WpConfig\WPConfigTransformer;
use Error;
use Exception;
use pcrypt;

class CryptBlowfish implements CryptInterface
{
    /** @var string */
    protected static $tempDefinedKey = null;

    /**
     * Create wp-config dup secure key
     *
     * @param bool $overwrite  if it is false and the key already exists it is not modified
     * @param bool $fromLegacy if true save legacy key
     *
     * @return bool
     */
    public static function createWpConfigSecureKey($overwrite = false, $fromLegacy = false)
    {
        $result = false;

        try {
            if (($wpConfig = SnapWP::getWPConfigPath()) == false) {
                return false;
            }

            if ($fromLegacy) {
                $key = self::getLegacyKey();
            } else {
                $key = SnapUtil::generatePassword(64, true, true);
            }

            $transformer = new WPConfigTransformer($wpConfig);

            if ($transformer->exists('constant', 'DUP_SECURE_KEY')) {
                if ($overwrite) {
                    if (!is_writeable($wpConfig)) {
                        throw new Exception('wp-config isn\'t writeable');
                    }
                    $result = $transformer->update('constant', 'DUP_SECURE_KEY', $key);
                }
            } else {
                if (!is_writeable($wpConfig)) {
                    throw new Exception('wp-config isn\'t writeable');
                }
                $result = $transformer->add('constant', 'DUP_SECURE_KEY', $key);
            }

            if ($result) {
                self::$tempDefinedKey = $key;
            }
        } catch (Exception $e) {
            DUP_PRO_Log::trace('Can create wp-config secure key, error: ' . $e->getMessage());
        } catch (Error $e) {
            DUP_PRO_Log::trace('Can create wp-config secure key, error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Remove secure key in wp config is exists
     *
     * @return bool
     */
    public static function removeWpConfigSecureKey()
    {
        $result = false;

        try {
            if (($wpConfig = SnapWP::getWPConfigPath()) == false) {
                return false;
            }

            $transformer = new WPConfigTransformer($wpConfig);

            if ($transformer->exists('constant', 'DUP_SECURE_KEY')) {
                if (!is_writeable($wpConfig)) {
                    throw new Exception('wp-config isn\'t writeable');
                }

                $result = $transformer->remove('constant', 'DUP_SECURE_KEY');
            }

            if (!is_writeable($wpConfig)) {
                throw new Exception('wp-config isn\'t writeable');
            }
        } catch (Exception $e) {
            DUP_PRO_Log::trace('Can remove wp-config secure key, error: ' . $e->getMessage());
        } catch (Error $e) {
            DUP_PRO_Log::trace('Can remove wp-config secure key, error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Get default key encryption
     *
     * @return string
     */
    protected static function getDefaultKey()
    {
        if (self::$tempDefinedKey !== null) {
            return self::$tempDefinedKey;
        } elseif (strlen(DUP_SECURE_KEY) == 0) {
            return self::getLegacyKey();
        } else {
            return DUP_SECURE_KEY;
        }
    }


    /**
     * Get legacy key encryption
     *
     * @return string
     */
    protected static function getLegacyKey()
    {
        $auth_key  = defined('AUTH_KEY') ? AUTH_KEY : 'atk';
        $auth_key .= defined('DB_HOST') ? DB_HOST : 'dbh';
        $auth_key .= defined('DB_NAME') ? DB_NAME : 'dbn';
        $auth_key .= defined('DB_USER') ? DB_USER : 'dbu';
        return hash('md5', $auth_key);
    }


    /**
     * Return encrypt string
     *
     * @param string $string string to encrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function encrypt($string, $key = null)
    {
        if ($key == null) {
            $key = self::getDefaultKey();
        }

        $crypt           = new pcrypt(MODE_ECB, "BLOWFISH", $key);
        $encrypted_value = $crypt->encrypt($string);
        $encrypted_value = base64_encode($encrypted_value);
        return $encrypted_value;
    }

    /**
     * Encrypt a generic value (scalar o array o object)
     *
     * @param mixed  $value value to encrypt
     * @param string $key   hash key
     *
     * @return string
     */
    public static function encryptValue($value, $key = null)
    {
        return self::encrypt(SnapJson::jsonEncode($value), $key);
    }

    /**
     * Return decrypt string
     *
     * @param string $string string to decrypt
     * @param string $key    hash key
     *
     * @return string
     */
    public static function decrypt($string, $key = null)
    {
        if (empty($string)) {
            return '';
        }

        if ($key == null) {
            $key = self::getDefaultKey();
        }

        $crypt  = new pcrypt(MODE_ECB, "BLOWFISH", $key);
        $orig   = $string;
        $string = base64_decode($string);
        if (empty($string)) {
            DUP_PRO_Log::traceObject("Bad encrypted string for $orig", debug_backtrace());
        }

        $decrypted_value = $crypt->decrypt($string);
        return $decrypted_value;
    }

    /**
     * Return decrypt value
     *
     * @param string $string string to decrypt
     * @param string $key    hash key
     *
     * @return mixed
     */
    public static function decryptValue($string, $key)
    {
        $json = self::decrypt($string, $key);
        return json_decode($json);
    }
}
