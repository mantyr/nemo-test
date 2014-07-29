<?php

    import('systems.ObjectFactory');

    class ObjectStorage_factory extends ObjectFactory {

        public static function factory($conf_group = false, $conf_name = false, $file = 'properties') {
            self::$factory_type = 'singleton';
            self::$factory_name = 'classe.ObjectStorage';
            return parent::factory($conf_group, $conf_name, $file);
        }
    }

    interface ObjectStorage {
        public function save();
        public function get($id);
        public function delete();
    }

    class ObjectStorage_file implements ObjectStorage {
        protected $db_table = 'objects';

        public function __construct() {
            $this->object_address = Config::get('objects_address_file');
            $this->address = ROOT_DIR.$this->object_address.$this->db_table.'/id/';

            //mkdir($this->address, 0600, true); // включить только если сервер правильно выставляет владельца для каталогов
        }

        public function save($arr = false) {
            $arr['value']['create_time'] = $arr['value']['create_time'] ? $arr['value']['create_time'] : time();
            $arr['value']['update_time'] = time();

            $arr['id'] = $arr['id'] ? $arr['id'] : $this->lock_file('id');

            if (!$arr['id']) {
                return false;
            }
            $address = $this->address.$arr['id'];
            $file = fopen($address, 'w');
            flock($file, 2);
            fwrite($file, json_encode($arr['value']));
            flock($file, 3);
            //fclose($file);
            return $arr['id'];
        }

        public function get($id) {
            $address = $this->address.$id;
            if (is_file($address)) {
                $arr = [
                    'id' => $id,
                    'value' => (array)json_decode(file_get_contents($address)),
                ];
                $arr['value']['create_time_view'] = date('Y-m-d H:i:s', $arr['value']['create_time']);
                $arr['value']['update_time_view'] = date('Y-m-d H:i:s', $arr['value']['update_time']);
                return $arr;
            }
        }

        public function delete() {
            $arr = func_get_args();
            foreach ($arr as $id) {
                if (is_array($id)) {
                    $this->delete($id);
                    continue;
                }
                if (is_file($this->address.$id)) unlink($this->address.$id);
            }
        }

        protected function lock_file($type) {
            $address = $this->object_address.'/'.$this->db_table.'/'.$type.'/';

            for ($i = 1; $i < 1000; $i++) {
                $time = microtime(1);
                $file = @fopen($address.$time, 'x');
                fclose($file);

                if ($file === false) {
                    return $time;
                }
            }
            return false;
        }
    }

    class ObjectStorage_sql implements ObjectStorage {
        protected $db_table = 'objects';

        public function __construct() {
            $this->db = db('objects');
        }

        public function save($arr = false) {
            if ($arr['id']) {
                $query = "
                    UPDATE `".$this->db_table."`
                    SET
                        `value` = '".mysql_escape_string(json_encode($arr['value']))."',
                        `update_time` = NOW()
                    WHERE `id` = '".mysql_escape_string($arr['id'])."'
                ";
            } else {
                $query = "
                    INSERT INTO `".$this->db_table."` (`create_time`, `update_time`, `value`) VALUE (
                        NOW(),
                        NOW(),
                        '".mysql_escape_string(json_encode($arr['value']))."'
                    )
                ";
            }

            $this->db->q($query);
            return $arr['id'] ? (int)$arr['id'] : (int)$this->db->lastInsertId();
        }

        public function get($id = false) {
            if (!$id) return false;
            $query = "
                SELECT
                    id,
                    create_time as create_time,
                    update_time as update_time,
                    UNIX_TIMESTAMP(create_time) as create_time_stamp,
                    UNIX_TIMESTAMP(update_time) as update_time_stamp,
                    value
                FROM `".$this->db_table."`
                WHERE `id` = '".mysql_escape_string($id)."'
            ";
            if ($res = $this->db->q($query)) {
                $row = $res->fetch();

                $arr = [
                    'id' => $id,
                    'value' => (array)json_decode($row['value']),
                ];
                $arr['value']['create_time'] = $row['create_time_stamp'];
                $arr['value']['update_time'] = $row['update_time_stamp'];
                $arr['value']['create_time_view'] = $row['create_time'];
                $arr['value']['update_time_view'] = $row['update_time'];
                return $arr;
            }
        }

        public function delete() {
            $arr = func_get_args();
            foreach ($arr as $id) {
                if (is_array($id)) {
                    $this->delete($id);
                    continue;
                }
                $ids[$id] = mysql_escape_string($id);
            }
            if (count($ids)) {
                $query = "
                    DELETE FROM `".$this->db_table."` WHERE `id` IN ('".implode("', '", $ids)."')
                ";
                $this->db->q($query);
            }
        }
    }

