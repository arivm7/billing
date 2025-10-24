<?php
/**
 *  Project : my.ri.net.ua
 *  File    : AdminBaseController.php
 *  Path    : app/controllers/admin/AdminBaseController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 13 Oct 2025 00:39:33
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

/**
 * Description of AdminBaseController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */



namespace app\controllers\admin;


class AdminBaseController extends \app\controllers\AppBaseController {

    /**
     * Имя файла админского шаблона
     * @var string
     */
    public $layout = 'default';  // admin


}