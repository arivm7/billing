<!--/app/views/Payments/indexView.php-->
<?php
use config\Conciliation;
use config\tables\Abon;
use config\tables\Module;
use config\tables\User;
require_once DIR_LIBS . '/form_functions.php';

$A = $user[Abon::TABLE][0];
?>
<div class="container my-3">
    <?php if (can_use([Module::MOD_MY_PAYMENTS, Module::MOD_PAYMENTS])) : ?>
        <h2 class="h4 mb-3"><?=__('Выберите Абонентское подключение для просмотра платежей')?></h2>
        <h3 class="fs-6 mb-3"><span class="text text-secondary"><?=num_len($user[User::F_ID], 6);?> |</span> <?=$user[User::F_NAME_FULL];?>:</h3>
        <?php if ($user[Abon::TABLE]) : ?>
            <?=get_html_accordion(
                    table: $user[Abon::TABLE],
                    file_view: DIR_INC . '/....php',
                    func_get_title: function (array $abon) {
                        return get_html_content_left_right(
                        left: "" . num_len($abon[Abon::F_ID], 6) . " :: " . $abon[Abon::F_ADDRESS] . "",
                        right: ($abon['is_payer']
                                    ? "<span class='badge bg-success'>" . __('Абонент') . "</span>"
                                    : "<span class='badge bg-secondary'>" . __('Отключён') . "</span>"
                        ) . '&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;',
                        add_class: 'w-100');
                    }
            );?>
        <?php else : ?>
            <div class='alert alert-info' role='alert'><?=__('Абоненских подключений нет')?></div>
        <?php endif; ?>
    <?php else : ?>
        <div class='alert alert-info' role='alert'><?=__('Нет информации для отображения')?></div>
    <?php endif; ?>
</div>
