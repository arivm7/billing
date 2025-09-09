<?php

namespace app\controllers;


class PppController extends AppBaseController{






    //#[\Override]
    static function getMenuTitle(): array {
        return [
            FIELD_TITLE => "ППП",
            FIELD_DESCR => "Пункты приёма платежей, счета, карты",
        ];
    }



    //#[\Override]
    static function getMenuItems(): array {
        $items = [
            [
                FIELD_TITLE => 'Список шаблонов по ППП',
                FIELD_DESCR => 'Список шаблонов соответсвий [фрагент]=[абонент]',
                FIELD_HREF => '/ppp/templates',
                FIELD_ICON => '',
                FIELD_TARGET => '_blank'
            ],
            [
                FIELD_TITLE => 'ППП Список',
                FIELD_DESCR => 'Список пунктов/способов приёма платежей',
                FIELD_HREF => '/ppp/list',
                FIELD_ICON => '',
                FIELD_TARGET => '_blank'
            ],
            [/*линия*/],
        ];
        return $items;
    }
}
