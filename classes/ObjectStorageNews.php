<?php

    import('systems.ObjectFactory');
    import('classes.ObjectStorage');

    class ObjectStorageNews_factory extends ObjectFactory {

        public static function factory($conf_group = false, $conf_name = false, $file = 'properties') {
            self::$factory_type = 'singleton';
            self::$factory_name = 'classe.ObjectStorageNews';
            return parent::factory($conf_group, $conf_name, $file);
        }
    }

    interface ObjectStorageNews {
        public function get_news();
    }

    class ObjectStorageNews_file extends ObjectStorage_file implements ObjectStorageNews {
        protected $db_table = 'news';

        public function get_news($ids = false) {
            if ($ids) {
                $ids = (array)$ids;
            } else {
                // all files, all news
                $ids = scandir($this->address, SCANDIR_SORT_DESCENDING);
            }

            foreach ($ids as $id) {
                if (is_file($this->address.$id)) {
                    if ($obj = $this->get($id)) {
                        $arr[] = $obj;
                    }
                }
            }
            return $arr;
        }
    }

    class ObjectStorageNews_sql extends ObjectStorage_sql implements ObjectStorageNews {
        protected $db_table = 'news';

        public function get_news($ids = false) {
            if (is_array($ids)) {
                if (count($ids)) {
                    $ids = array_map("mysql_escape_string", $ids);
                    $WHERE[] = "`id` IN ('".implode("', '", $ids)."')";
                }
            } elseif ($ids) {
                $WHERE[] = "`id` = '".mysql_escape_string($ids)."'";
            }
            $query = "
                SELECT
                    id,
                    create_time as create_time,
                    update_time as update_time,
                    UNIX_TIMESTAMP(create_time) as create_time_stamp,
                    UNIX_TIMESTAMP(update_time) as update_time_stamp,
                    value
                FROM `".$this->db_table."`
                ".($WHERE ? 'WHERE '.implode(' AND ', $WHERE) : '')."
                ORDER BY `create_time` DESC
            ";
            if ($res = $this->db->q($query)) {
                while ($row = $res->fetch()) {
                    $row['value'] = (array)json_decode($row['value']);
                    $row['value']['create_time'] = $row['create_time_stamp'];
                    $row['value']['update_time'] = $row['update_time_stamp'];

                    $row['value']['create_time_view'] = $row['create_time'];
                    $row['value']['update_time_view'] = $row['update_time'];

                    $arr[$row['id']] = $row;
                }
            }
            return $arr;
        }
    }

