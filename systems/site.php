<?php

    /**
     * Site хранит информацию о сайте, доступную везде
     */

    class site{
        public static $sub_title = false;
        public static $is_admin = false;
        public static $is_login = false;
        public static $login = false;
        public static $language = 0;
        public static $language_uri = array('ru');

        // routing
        public static $module = '';
        public static $action = '';

        public static function getLang() {
            return self::$language_uri[self::$language];
        }

        public static function url($address){
            return str_replace('//','/',Config::get('url_base').$address);
        }
        public static function title(){
            return htmlspecialchars((trim(self::$sub_title) ? trim(self::$sub_title).' | ' : '').Config::get('title'));
        }
    }