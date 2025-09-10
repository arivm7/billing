<?php



namespace app\controllers;



use app\models\AbonModel;
use billing\core\App;
use billing\core\base\View;
use config\tables\Abon;
use config\tables\Pay;
use config\tables\User;

class PaymentsController extends AppBaseController {

    function indexAction() {

        if (!App::$auth->isAuth) { redirect(); }
        $model = new AbonModel();

        $user = $_SESSION[User::SESSION_USER_REC];
        $user[Abon::TABLE] = $model->get_rows_by_field(table: Abon::TABLE, field_name: Abon::F_USER_ID, field_value: $user[User::F_ID]);

        foreach ($user[Abon::TABLE] as &$abon) {
            $abon[Pay::TABLE] = $model->get_payments(abon_id: $abon[Abon::F_ID], pay_type: Pay::TYPE_MONEY);
        }

        $this->setVariables([
            'user' => $user,
        ]);

        View::setMeta(title: __('Payment history | История платежей | Історія платежів'));
    }



}
