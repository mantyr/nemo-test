<?php

    /**
     * Load вытягивание кода, посути запускает контроллер из любой точки пространства и времени
     *
     * @param string название модуля
     * @param string название действия
     * @param array  перечень параметров для передачи контроллеру
     * @param mixed  название префикса или false, может принимать значения: ajax
     */
    function load($module, $action, $param = array(), $prefix = false){
        $modulesPath = "modules";

        $module_name = $module;
        $action_name = ucfirst($action);
        if ($prefix) $module_name = $prefix.".".$prefix."_".$module_name;

        $address = "$modulesPath.$module.controllers.".$module_name.$action_name.'Command';

        $class = import($address);
        if ($class){
            if (!class_exists($class)) return eEcho("error: no_action in controller: $address,  is action:  ".$module_name.$action_name.'Command');

            $command = new $class();
            if (!method_exists($command,"view")) return eEcho("error: no_view in controller: ".$address);
            if ($prefix == 'ajax'){
                echo json_encode($command->view($param));
            } else {
                echo $command->view($param);
            }
            return;
        }
        return eEcho("error: no_controller: ".$address);
    }

    /**
     * Устарело, смотри прямой роутинг
     */
    function ajax($module, $action, $param = false){ return load($module, $action, $param, 'ajax'); }
