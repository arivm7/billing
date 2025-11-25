<?php
/*
 *  Project : my.ri.net.ua
 *  File    : conciliation_intervals.php
 *  Path    : app/views/inc/conciliation_intervals.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 20 Sep 2025 20:22:31
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of conciliation_intervals.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */

use config\tables\Abon;
use config\tables\Module;
use config\Conciliation;
use billing\core\base\Lang;
Lang::load_inc(__FILE__);
/** @var array $item */
/** @var array $user */
/** @var array $abon */
if (empty($abon) && !empty($item)) {
    $abon = $item;
}
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
            <!-- Баннер предупреждение -->
            <tr>
                <td class="bg-warning-subtle">
                    <div class="small text-muted mt-1">
                        <p class="bukvitca"><?=__('The presence of a facsimile and a stamp in the output document does not mean that it is legally binding');?>.<br>
                        <?=__('To obtain a valid accounting document, you need to obtain signed paper documents, or use electronic digital signature systems (EDS), or a qualified electronic signature (CAP).');?>.</p>
                    </div>
                </td>
                <td class="text-end bg-warning-subtle">
                </td>
            </tr>

            <!-- Весь период -->
            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca"><?=__('From the activation date to the current month, Including');?>.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>"><?=__('Full');?></a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_SHTAMP; ?>=1" title="<?=__('Full with stamp');?>"><?=__('Beautiful');?></a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary"><?=__('Debugging');?></a>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Прошлый год -->
            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca"><?=__('Last year, from');?> <span class="text-info"><?= (year() - 1); ?>-01-01</span> <?=__('by');?> <span class="text-info"><?= (year() - 1); ?>-12-31</span> <?=__('including');?>.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31"><?=__('Last year');?></a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31&<?= Conciliation::F_SHTAMP; ?>=1" title="<?=__('With a stamp');?>"><?=__('Beautiful');?></a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= (year() - 1); ?>-01-01&<?= Conciliation::F_DATE2_STR; ?>=<?= (year() - 1); ?>-12-31&<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary"><?=__('Debugging');?></a>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Этот год -->
            <tr>
                <td>
                    <div class="small text-muted mt-1 bukvitca"><?=__('From the beginning of this year to the current month inclusive');?>.</div>
                    <div class="text-end">
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01"><?=__('This year');?></a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01&<?= Conciliation::F_SHTAMP; ?>=1" title="<?=__('Full with stamp');?>"><?=__('Beautiful');?></a>
                    </div>
                </td>
                <td class="text-end">
                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                    <a target="_blank" href="<?= $uri; ?>?<?= Conciliation::F_DATE1_STR; ?>=<?= year(); ?>-01-01&<?= Conciliation::F_DEBUG; ?>=1" class="link-secondary"><?=__('Debugging');?></a>
                    <?php endif; ?>
                </td>
            </tr>

            <!-- Форма: Этот год -->
            <tr>
                <td class="text-end">
                    <form action="<?= $uri; ?>" method="get" target="_blank">
                        <div class="row gy-2 gx-2 align-items-center justify-content-end">
                            <!-- <input type="hidden" name="abon_id" value="<?= $abon_id; ?>"> -->
                            <label class="col-auto col-form-label bukvitca"><?=__('This year');?>:</label>

                            <div class="col-auto">
                                <input name="<?= Conciliation::F_DATE1_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?=year(time());?>-01-01">
                            </div>

                            <div class="col-auto">
                                <input name="<?= Conciliation::F_DATE2_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", last_day_month()); ?>">
                            </div>

                            <div class="col-auto form-check">
                                <input name="<?= Conciliation::F_SHTAMP; ?>" class="form-check-input" type="checkbox" value="1" id="shtamp1">
                                <label class="form-check-label small" for="shtamp1"><?=__('stamp');?></label>
                            </div>

                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary"><?=__('Show');?></button>
                            </div>

                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                            <div class="col-auto form-check">
                                <input name="<?=Conciliation::F_DEBUG;?>" class="form-check-input" type="checkbox" value="1" id="debug1">
                                <label class="form-check-label small" for="debug1"><?=__('debugging');?></label>
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
                        <label class="col-auto col-form-label bukvitca"><?=__('The entire period +1 month.');?>:</label>

                        <div class="col-auto">
                            <input name="<?= Conciliation::F_DATE1_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", $abon[Abon::F_DATE_JOIN]); ?>">
                        </div>

                        <div class="col-auto">
                            <input name="<?= Conciliation::F_DATE2_STR; ?>" type="date" class="form-control form-control-sm text-center" value="<?= date("Y-m-d", next_month_last_day()); ?>">
                        </div>

                        <div class="col-auto form-check">
                            <input name="<?= Conciliation::F_SHTAMP; ?>" class="form-check-input" type="checkbox" value="1" id="shtamp2">
                            <label class="form-check-label small" for="shtamp2"><?=__('stamp');?></label>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><?=__('Show');?></button>
                        </div>

                    <?php if (can_use(Module::MOD_CONCILIATION)) : ?>
                        <div class="col-auto form-check">
                            <input name="<?=Conciliation::F_DEBUG;?>" class="form-check-input" type="checkbox" value="1" id="debug2">
                            <label class="form-check-label small" for="debug2"><?=__('debugging');?></label>
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