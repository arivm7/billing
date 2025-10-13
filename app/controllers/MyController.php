<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : MyController.php
 *  Path    : app/controllers/MyController.php
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
use config\Auth;
use config\tables\Abon;
use config\tables\Contacts;
use config\tables\Firm;
use config\tables\Module;
use config\tables\Notify;
use config\tables\User;
use config\tables\PA;
use billing\core\base\View;

/**
 * Description of MyController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class MyController extends AppBaseController  {


    public ?AbonModel $model = null;



    public function __construct(array $route) {
        parent::__construct($route);
        $this->model = new AbonModel();
    }



    function indexAction() {

        if (App::$auth->isAuth) {
            $my = $_SESSION[User::SESSION_USER_REC];

            if (can_use([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS])) {
                $my[Contacts::TABLE] = (can_del([Module::MOD_MY_CONTACTS, Module::MOD_CONTACTS])
                        ? $this->model->get_contacts(user_id: $my[User::F_ID], has_deleted: null)
                        : $this->model->get_contacts(user_id: $my[User::F_ID], has_deleted: 0));
            }

            if (can_use([Module::MOD_MY_FIRM, Module::MOD_FIRM])) {
                $my[Firm::TABLE] = $this->model->get_firms($my[User::F_ID]);
            }

            if (can_view(Module::MOD_MY_ABON)) {
                $my[Abon::TABLE] = $this->model->get_rows_by_where(
                        table: Abon::TABLE,
                        where: '('.Abon::F_USER_ID.'='.$my[User::F_ID].')'
                                . (can_view(Module::MOD_ABON) ? '' : ' AND ('.Abon::F_IS_PAYER.')'),
                        order_by: Abon::F_DATE_JOIN . ' DESC',

                );

                if (can_view(Module::MOD_MY_PA)) {
                    foreach ($my[Abon::TABLE] as &$abon) {

                        $abon[PA::TABLE] = $this->model->get_pa_by_abon_id($abon[Abon::F_ID]);

                        /** Для передачи USER_ID в PA */
                        foreach ($abon[PA::TABLE] as &$pa_one) {
                            $pa_one[PA::F_USER_ID] = $my[User::F_ID];
                        }

                        /** Общее количество записей в базе */
                        $abon[Notify::F_COUNT] = $this->model->get_count_by_sql($this->model->get_sql_notify_by_abon_id($abon[Abon::F_ID]));
                        /** Отображаемые записи */
                        $abon[Notify::TABLE] = $this->model->get_notify_by_abon_id($abon[Abon::F_ID], App::$app->get_config('notify_list_limit'));

                        /** Для передачи USER_ID в Notify */
                        foreach ($abon[Notify::TABLE] as &$notify_one) {
                            $notify_one[Notify::F_USER_ID] = $my[User::F_ID];
                        }
                    }
                }
            }

            $this->setVariables([
                'user' => $my,
            ]);
        } else {
            redirect(Auth::URI_LOGIN);
        }

        View::setMeta(
            title: __('Rilan') . " :: " . __('Abonent personal account'),
         // descr: __('Административный сайт сети %s. Данные абонента, парамеры услуги.', __('Rilan')),
        );

    }



}