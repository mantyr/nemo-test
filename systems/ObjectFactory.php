<?php

    class ObjectFactory {
        private static $instances;
        public static $factory_type; // singleton/new
        public static $factory_name;

        private function __construct() {

        }

        public function instance() {

        }

        public static function factory($conf_group = 'storage_type', $conf_name = 'default', $file = 'properties') {
            $storage_types = Config::get($conf_group, $file);
            $type = $storage_types[$conf_name];

            import(self::$factory_name.'_'.$type);

            $object_name = substr(strrchr(self::$factory_name, '.'), 1);
            $object_name = $object_name ? $object_name : self::$factory_name;
            $object_name .= '_'.$type;

            if (self::$factory_type == 'singleton') {
                if (!self::$instances[$object_name]) self::$instances[$object_name] = new $object_name();
                return self::$instances[$object_name];
            }
            return new $object_name();
        }
    }