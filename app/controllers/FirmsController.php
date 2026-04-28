<?php
/*
 *  Project : my.ri.net.ua
 *  File    : FirmsController.php
 *  Path    : app/controllers/FirmsController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */



namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use config\tables\Employees;
use config\tables\Firm;
use config\tables\Module;

/**
 * Description of FirmsController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class FirmsController extends AppBaseController {


    public function indexAction(): void {

        if (!can_use(Module::MOD_FIRM)) { self::log_no_rights(); redirect(); }

        $model = new AbonModel();

        $firms = $model->get_rows_by_sql(
            'SELECT * FROM `'.Firm::TABLE.'` '
                .'WHERE `'.Firm::F_ID.'` IN '
                    .'(SELECT `'.Employees::F_FIRM_ID.'` FROM `'.Employees::TABLE.'` WHERE `'.Employees::F_USER_ID.'`=?)',
            [ App::get_user_id() ], 
            Firm::F_ID
        );

        $firms = $model->get_firms(); 
        debug($firms);


        View::setMeta(__('Список предприятий'));
        $this->setVariables([
            'firms' => $firms
        ]);
    }



    public function employeesAction(): void {

        if (!can_use(Module::MOD_FIRM)) { self::log_no_rights(); redirect(); }
        


        View::setMeta(__('Сотрудники предприятия'));
        $this->setVariables([
            //
        ]);
    }
    


//    //#[\Override]
//    static function getMenuTitle(): array {
//        return [
//            FIELD_TITLE => "Предприятия",
//            FIELD_DESCR => "Редактирование данных о предприятиях",
//        ];
//    }



//    //#[\Override]
//    static function getMenuItems(): array {
//        $items = [
//            [
//                FIELD_TITLE => 'Список Предприятий',
//                FIELD_DESCR => 'Список Предприятий',
//                FIELD_HREF => '/firm/list',
//                FIELD_ICON => '',
//                FIELD_TARGET => '_blank'
//            ],
//            [/*линия*/],
//            [
//                FIELD_TITLE => 'Неприкреплённые',
//                FIELD_DESCR => 'Список предприятий не прикрепленных к пользователю.',
//                FIELD_HREF => '/firm/unlinked',
//                FIELD_ICON => '',
//                FIELD_TARGET => '_blank'
//            ],
//        ];
//        return $items;
//    }

}