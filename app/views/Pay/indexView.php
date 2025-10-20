<?php
/**
 *  Project : s1.ri.net.ua
 *  File    : indexView.php
 *  Path    : app/views/Pay/indexView.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 18 Oct 2025 13:06:32
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Карточка оплаты по этапам. Основной файл представления.
 * В зависимости от фазы оплаты подключаются соответствующие части.
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */


/**
 * @var string $title
 * @var int $phase
 * @var array $abon_list
 */

?>
<div class="container-fluid min-vh-100 d-flex justify-content-center align-items-start">
    <div class="col-12 col-sm-12 col-md-10 col-lg-8 col-xl-6">
        <div class="card shadow-sm">
            <h1 class="card-header"><?=__('Payment for services');?></h1>
            <div class="card-body">

                <div class="container my-4">
                    <!-- <h1>Оплата по этапам</h1> -->
                    <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                        <?php for ($i = 1; $i <= 3; $i++): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $phase === $i ? 'active' : 'disabled' ?>"
                                        id="tab-<?= $i ?>-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#tab-<?= $i ?>"
                                        type="button"
                                        role="tab"
                                        aria-controls="tab-<?= $i ?>"
                                        aria-selected="<?= $phase === $i ? 'true' : 'false' ?>"
                                        <?= $phase === $i ? '' : 'disabled' ?>>
                                    <?=__('Step')?> <?= $i ?>
                                </button>
                            </li>
                        <?php endfor; ?>
                    </ul>

                    <div class="tab-content border p-3" id="paymentTabsContent">
                        <div class="tab-pane fade <?= $phase === 1 ? 'show active' : '' ?>" id="tab-1" role="tabpanel">
                            <?php if ($phase == 1) : ?>
                                <!-- <p class="mt-3 text-secondary"><strong>1: </strong>Укажите номер договора по которому Вы хотите пополнить лицевой счёт.</p> -->
                                <?php require DIR_INC . '/pay_phase_01.php'; ?>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade <?= $phase === 2 ? 'show active' : '' ?>" id="tab-2" role="tabpanel">
                            <?php if ($phase == 2) : ?>
                                <!-- <p class="mt-3 text-secondary"><strong>2: </strong>Подтвердите оплачиваемые услуги</p> -->
                                <?php require DIR_INC . '/pay_phase_02.php'; ?>
                            <?php endif; ?>
                        </div>
                        <div class="tab-pane fade <?= $phase === 3 ? 'show active' : '' ?>" id="tab-3" role="tabpanel">
                            <?php if ($phase == 3) : ?>
                                <!-- <p class="mt-3 text-secondary"><strong>3: </strong>Выберите способ оплаты.</p> -->
                                <?php require DIR_INC . '/pay_phase_03.php'; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
