<?php
use config\tables\Abon;
use config\tables\Module;
use config\Conciliation;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
/** @var array $user */
/** @var array $abon */
$abon = $item;
$abon_id = $abon[Abon::F_ID];
$id = (!empty($abon[Abon::F_ID_HASH]) ? $abon[Abon::F_ID_HASH] : $abon_id);
$uri = Conciliation::URI_PRINT . '/' . $id;
/** @var int $abon_id */
?>
<style>
    .bukvitca::first-letter {
            font-size: 2em;
            font-weight: bold;
    }
</style>
<div class="table-responsive">

    <table class="table table-bordered table-striped table-hover align-middle">
        <tbody>
            <tr>
                <td>
                    <div class="small text-muted mt-1">
                        <p class="bukvitca">Наличие факсимиле и штампа в выходном документе не означает его юридической силы.<br>
                        Для получения действующего бухгалтерского документа нужно получить подписаные бумажные документы или воспользоваться системами электронной подписи&nbsp;ЭЦП&nbsp;(КЭП).</p>
                    </div>
                </td>
                <td class="text-end">
                </td>
            </tr>

            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca">С даты подключения по текущий месяц, включительно.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>">Полный</a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_SHTAMP; ?>=1" title="Полный со штампом">Красивый</a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary">Отладочный</a>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca">С начала этого года по текущий месяц включительно.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01">Этот год</a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01&<?= Conciliation::F_SHTAMP; ?>=1" title="Полный со штампом">Красивый</a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01&<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary">Отладочный</a>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca">Прошлый год, с <span class="text-info"><?= (year() - 1); ?>-01-01</span> по <span class="text-info"><?= (year() - 1); ?>-12-31</span> включительно.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31">Прошлый год</a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31&<?= Conciliation::F_SHTAMP; ?>=1" title="Со штампом">Красивый</a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31&<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary">Отладочный</a>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Форма: Этот год -->
            <tr>
                <td class="text-end">
                    <form action="<?= $uri; ?>" method="get" target="_blank">
                        <div class="row gy-2 gx-2 align-items-center justify-content-end">
                            <!-- <input type="hidden" name="abon_id" value="<?= $abon_id; ?>"> -->
                            <label class="col-auto col-form-label bukvitca">Этот год:</label>

                            <div class="col-auto">
                                <input name="<?= Conciliation::F_DATE1_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?=year(time());?>-01-01">
                            </div>

                            <div class="col-auto">
                                <input name="<?= Conciliation::F_DATE2_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", last_day_month()); ?>">
                            </div>

                            <div class="col-auto form-check">
                                <input name="<?= Conciliation::F_SHTAMP; ?>" class="form-check-input" type="checkbox" value="1" id="shtamp1">
                                <label class="form-check-label small" for="shtamp1">штамп</label>
                            </div>

                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">Показать</button>
                            </div>

                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                            <div class="col-auto form-check">
                                <input name="<?=Conciliation::F_DEBUG;?>" class="form-check-input" type="checkbox" value="1" id="debug1">
                                <label class="form-check-label small" for="debug1">отладка</label>
                            </div>
                    <?php endif; ?>

                        </div>
                    </form>
                </td>
                <td>
                </td>
            </tr>

            <!-- Форма: Весь период +1 мес. -->
            <tr>
                <td class="text-end">
                    <form action="<?= $uri; ?>" method="get" target="_blank" class="row gy-2 gx-2 align-items-center justify-content-end">
                        <!-- <input type="hidden" name="abon_id" value="<?= $abon_id; ?>"> -->
                        <label class="col-auto col-form-label bukvitca">Весь период +1 мес.:</label>

                        <div class="col-auto">
                            <input name="<?= Conciliation::F_DATE1_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", $abon[Abon::F_DATE_JOIN]); ?>">
                        </div>

                        <div class="col-auto">
                            <input name="<?= Conciliation::F_DATE2_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", next_month_last_day()); ?>">
                        </div>

                        <div class="col-auto form-check">
                            <input name="<?= Conciliation::F_SHTAMP; ?>" class="form-check-input" type="checkbox" value="1" id="shtamp2">
                            <label class="form-check-label small" for="shtamp2">штамп</label>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary">Показать</button>
                        </div>

                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                        <div class="col-auto form-check">
                            <input name="<?=Conciliation::F_DEBUG;?>" class="form-check-input" type="checkbox" value="1" id="debug2">
                            <label class="form-check-label small" for="debug2">отладка</label>
                        </div>
                    <?php endif; ?>

                    </form>
                </td>
                <td>
                </td>
            </tr>

        </tbody>
    </table>

</div>
