<?php


namespace app\controllers;

use app\models\AbonModel;
use billing\core\App;
use config\tables\Abon;
use config\tables\Contacts;
use config\tables\Firm;
use config\tables\Module;
use config\tables\Notify;
use config\tables\User;
use config\tables\PA;
use billing\core\base\View;


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
                $my[Abon::TABLE] = $this->model->get_rows_by_field(
                        table: Abon::TABLE,
                        field_name: Abon::F_USER_ID,
                        field_value:  $my[User::F_ID]
                );

                if (can_view(Module::MOD_MY_PA)) {
                    foreach ($my[Abon::TABLE] as &$abon) {
                        $abon[PA::TABLE] = $this->model->get_pa_by_abon_id($abon[Abon::F_ID]);
                        $abon[Notify::TABLE] = $this->model->get_notify_by_abon_id($abon[Abon::F_ID], App::$app->get_config('notify_list_limit'));
                    }
                }
            }

            $this->setVariables([
                'user' => $my,
            ]);
        } else {
            redirect('/auth/login');
        }

        View::setMeta(
            title: __('Rilan') . " :: " . __('Abonent personal account'),
         // descr: __('Административный сайт сети %s. Данные абонента, парамеры услуги.', __('Rilan')),
        );

    }



}
