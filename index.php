<?php
    error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

//    xhprof_enable(XHPROF_FLAGS_MEMORY);

    include_once('./systems/core.php');

    // импортируем глобальные переменные
    if ($arr_params = globals_params()) extract($arr_params, EXTR_SKIP);

    import('systems.db');
    import('systems.config');
    import('systems.routing');

    echo action_template(0, 'news', 'view');
