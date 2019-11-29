<?php

session_start();

class User {
    private static $passwords;

    public static function initialize() {
        self::$passwords = json_decode(file_get_contents(__DIR__ . '/../.htusers.json'), true);
    }

    /**
     * ユーザー認証
     */
    public static function auth() {
        if ($_SESSION['user'] ?? null) {
            return $_SESSION['user'];
        } else {
            // ログイン画面へ
            header('Location: ./login.php');
            exit;
        }
    }

    /**
     * ログイン
     */
    public static function login($email, $password, $remember) {
        if (isset(self::$passwords[$email]) && self::$passwords[$email] == $password) {
            // ログイン成功
            $_SESSION['user'] = [
                'email' => $email
            ];

            // ログインを記憶
            if ($remember) {
                setcookie('u', md5($email . $password), time() + 60 * 60 * 24 * 365);
            }

            return true;
        } else {
            //ログイン失敗
            return false;
        }
    }

    /**
     * Cookieログイン
     */
    public static function cookie_login() {
        if ($_COOKIE['u'] ?? null) {
            foreach (self::$passwords as $k => $v) {
                if ($_COOKIE['u'] == md5($k . $v)) {
                    // Cookieからログイン
                    $_SESSION['user'] = [
                        'email' => $k
                    ];

                    return true;
                }
            }
        } else {
            //ログイン失敗
            return false;
        }
    }
    
    /**
     * ログアウト
     */
    public static function logout() {
        if ($_SESSION['user'] ?? null) {
            unset($_SESSION['user']);
        }
        if ($_COOKIE['u'] ?? null) {
            setcookie('u', '', 0);
        }
    }

}
User::initialize();

?>