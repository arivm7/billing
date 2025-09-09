<?php

namespace app\controllers;



class TpController extends AppBaseController {



    function listAction() {
        debug_msg('in listAction (Список ТП)', 'GRAY');
    }



    function addAction() {
        debug_msg('in addAction (Добавление ТП)', 'GRAY');
    }



    function backupAction() {
        debug_msg('in backupAction (Скрипт бакапа ТП)', 'GRAY');
    }



    //#[\Override]
    static function getMenuTitle(): array {
        return [
            FIELD_TITLE => "TP",
            FIELD_DESCR => "Технические площадки",
        ];
    }



    //#[\Override]
    static function getMenuItems(): array {
        $items = [
            [
                FIELD_TITLE => 'Список ТП',
                FIELD_DESCR => 'Полный список всех ТП',
                FIELD_HREF => '/tp/list',
                FIELD_ICON => 'icon_tp_080.png',
                FIELD_TARGET => '_blank'
            ],
            [
                FIELD_TITLE => 'Скрипт бакапа ТП',
                FIELD_DESCR => 'Скрипт бакапа ТП',
                FIELD_HREF => '/tp/backup',
                FIELD_ICON => 'icon_tp_080.png',
                FIELD_TARGET => '_blank'
            ],
            [/*линия*/],
            [
                FIELD_TITLE => '+ТП',
                FIELD_DESCR => 'Добавление ТП',
                FIELD_HREF => '/tp/add',
                FIELD_ICON => 'icon_tp_080.png',
                FIELD_TARGET => '_blank'
            ],
        ];
        return $items;
    }



}
