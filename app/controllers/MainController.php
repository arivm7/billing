<?php
/*
 *  Project : s1.ri.net.ua
 *  File    : MainController.php
 *  Path    : app/controllers/MainController.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 16 Sep 2025 12:49:54
 *  License : GPL v3
 *
 *  Copyright (C) 2025 Ariv <ariv@meta.ua> | https://github.com/arivm7 | RI-Network, Kiev, UK
 */

namespace app\controllers;

use app\models\MainModel;
use billing\core\App;
use billing\core\base\View;
use billing\core\Pagination;
use billing\core\base\Lang;


/**
 * Description of MainController.php
 *
 * @author Ariv <ariv@meta.ua> | https://github.com/arivm7
 */
class MainController extends AppBaseController {



    public function indexAction() {
        redirect('/my');
//        $title = __('page_title');
//        View::setMeta(
//                title: __('meta_title'),
//                descr: __('meta_descr'),
//                keywords: __('meta_keywords'));
//
//        $model = new MainModel;
//        $perPage = 5;
//        $total = $model->get_count(table: $model->table);
//        $pageCurrent = isset($_GET['page']) ? (int)$_GET['page'] : 1;
//        $cache_key = "index_{$total}_{$perPage}_{$pageCurrent}";
//        $pager = new Pagination($pageCurrent, $perPage, $total);
//        $posts = App::$app->cache->get($cache_key);
//        if ($posts === false) {
//            $limitStart = $pager->get_sql_limit_start();
//            $sql = "SELECT * FROM `{$model->table}` WHERE 1 LIMIT {$limitStart},{$perPage}";
//            $posts = $model->get_rows_by_sql($sql);
//            App::$app->cache->set($cache_key, $posts);
//        }
//
//        $this->setVariables([
//            'title' => $title,
//            'posts' => $posts,
//            'pager' => $pager,
//        ]);

    }
}