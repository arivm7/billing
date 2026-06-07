<?php
/**
 *  Project : my.ri.net.ua
 *  File    : TestController.php
 *  Path    : app/controllers/TestController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 26 Oct 2025 20:30:19
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of TestController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


namespace app\controllers;

use app\controllers\AppBaseController;
use billing\core\App;
use config\tables\Module;
use billing\core\MikrotikDevice;
use RouterOS\Query;
use config\Mik;



require_once DIR_LIBS . '/functions.php';
require DIR_LIBS ."/tests.php";



class TestController extends AppBaseController {


  
    public function indexAction() {

        if  (
                App::isAuth() &&
                can_use(Module::MOD_WEB_DEBUG)
            )
        {
            
            echo "<a href=/ >на главную</a><hr>";
            /**
             * ЗАПУСК ТЕСТОВ
             */

            
            
            // 44. 2922-VOK
            // 68. RI_MAIN_CCR 
            // 97. Буча. ДОМ 
            $dev = new MikrotikDevice(97);
            echo implode('|', $dev->get_description()) . '<hr>';
            
//$t = microtime(true);
//            $stat=$dev->get_address_lists_stat();
//            echo get_html_table(t: $stat, show_key: 1);
//echo 'TIMER: $query: ' . round(microtime(true) - $t, 3) . ' sec<hr>';

//            $t = microtime(true);
//            $query = (new Query('/ip/firewall/address-list/print'))
//                ->where(Mik::F_LIST_LIST, Mik::L_ABON);
//            $response = $dev->client
//                ->query($query)
//                ->read();
//            echo 'TIMER: $query: ' . round(microtime(true) - $t, 3) . ' sec<hr>';
//            echo get_html_table(t: $response, show_key: 1, pre_align: 1);


            $addr_list = $dev->get_address_list_items(list: Mik::L_ABON);
//            debug($addr_list, '$addr_list1');
            if ($dev->add_address_list_item(Mik::L_ABON, '0.0.0.0/3', 1, 'test1', true)) { echo '-OK-'; } else { echo '-ERROR-'; }
            debug(MikrotikDevice::get_messages(), 'get_messages');
            $addr_list = $dev->get_address_list_items(list: Mik::L_ABON);
            debug($addr_list, '$addr_list2');
            
            $res = $dev->get_address_list_items(list: Mik::L_ABON, descr: 'test1');
            debug($res, 'search1');
            echo '<hr>';
            
            $res = $dev->update_address_list_items(
                    [   
                        MikrotikDevice::F_SEARCH_LIST => Mik::L_ABON,
                        MikrotikDevice::F_SEARCH_DESCR => 'test1'
                    ], 
                    [
                        MikrotikDevice::F_UPDATE_DESCR => 'test2'
                    ]);
            
            debug($res, '$res3');
            debug(MikrotikDevice::get_messages(), 'get_messages');
            
            
            
            
            die();
            
            echo "TEST highlight_like_groups: <br>";
            echo h(highlight_like_groups("Отключились потому что через этот интернет не работала касса... хз...", "что%то")) . "<br>";
            echo h(highlight_like_groups("Платит нерегулярно и не верит,что что-то должен", "что%то")) . "<br>";


            translit_uk_test(0);
            echo "<hr>";
            translit_ru_test(0);
            echo "<hr>";
            detect_language_test(0);
            echo "<hr>";
            translit_test(0);
        }

        die();
    }





}