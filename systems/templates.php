<?php

    /**
     * Templates сдужит для формирования шаблонов
     *
     * @author Oleg Shevelev
     */
    function template($src_template, $arg = array()){

        list($this_path, $template) = template_path($src_template);

        if (is_file($this_path.$template)){
            ob_start();
                if ($arr_params = globals_params('', true)) extract($arr_params, EXTR_SKIP);
                include($this_path.$template);
                $content = ob_get_contents();
            ob_end_clean();
            if (!empty($parent)){
                list($parent_path, $parent_template) = template_path($parent);

                if (is_file($parent_path.$parent_template)){
                    $arg = array();
                    $arg['content'] = $content;
                    return template($parent, $arg);
                }
                return eEcho('error: no_parent_template: '.$parent).$content;
            }
            return $content;
        }
        return eEcho('error: no_template: '.$template);
    }

    /**
     * @param string адрес шаблона, вида "module/template", "template", может содержать точки которые заменяются на "/"
     * @return array адрес каталога где хранятся шаблоны данного типа и адрес шаблона относительно этого каталога
     */
    function template_path($src_template){
        $tmp_template = explode('/', $src_template);
        $tmp_count = count($tmp_template);

        if ($tmp_count == 0) return false;

        $template = str_replace('.', '/', trim($tmp_template[$tmp_count-1])).".tmpl";

        if ($tmp_count > 1) return array("./modules/".str_replace('.', '/', trim($tmp_template[0])).'/templates/', $template);
        return array("./templates/", $template);
    }

    /**
     * Проверяет наличие файла шаблона, но не выполняет его
     * Only for action_template()
     */
    function is_template($template){
        list($path, $address) = template_path($template);

        if (is_file($path.$address)) return true;
        return false;
    }
