<?php

    import('systems.templates');
    import('systems.load');
    import('systems.site');

    /**
     * Активно-пассивный роутинг
     *
     * @author Oleg Shevelev
     */

    /**
     * Redirect на уровне сервера
     */
    function redirect($url = '/'){
        header('Location: '.$url); exit;
    }

    /**
     * Выбор активного шаблона
     * default/view
     *
     * @param integer количество отсекаемых параметров в URL слева, по умолчанию 0, рассматриваюстя все параметры
     *
     *           /edit -> /default/edit
     *           /module/edit/
     *           /module/ -> /module/view
     */
    function action_template($url_trim = 0, $module_default = 'default', $action_default = 'view'){
        $language = array('ru' => 0, 'en' => 1, 'fr' => 2, 'de' => 3);

        if (!isset($_SERVER['DOCUMENT_URI'])) redirect('/');

        $URL = $_SERVER['DOCUMENT_URI'];
        $URI = explode('/',$URL);

        $i = $url_trim; $uri_count = count($URI);

        if (isset($language[$URI[$i+1]])){
            site::$language = $language[$URI[$i+1]];
            $i++;
        } else {
            site::$language = $language['ru'];
        }
        site::$language_uri = array('ru', 'en', 'fr', 'de');

        $module = ''; $action = ''; $param = array();

        if ($uri_count == $i+2) $action = $URI[1+$i];
        if ($uri_count>$i+2) {
            $module = $URI[1+$i];
            $action = $URI[$uri_count-1];
        }

        if (($uri_count-1)>($i+2)){
            $d = $i+2;
            while($d<$uri_count-1){
                $param[] = $URI[$d]; $d++;
            }
        }


        if ($module == '') $module = $module_default;
        if ($action == '') $action = $action_default;

        site::$module = $module;
        site::$action = $action;

        if (is_template("action.$module")){
            $arg = array();
            $arg['param'] = $param;
            $arg['action'] = $action;
            return template("action.$module", $arg);
        } else{
            if(is_template("404")) return template("404");
            return eEcho("error: Нет активного шаблона (action.$module), нет так же шаблона 404");
        }
    }