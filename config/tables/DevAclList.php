<?php
/*
 *  Project : my.ri.net.ua
 *  File    : DevAclList.php
 *  Path    : config/tables/DevAclList.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 May 2026
 *  License : GPL v3
 */

namespace config\tables;

class DevAclList {

    public const URI_INDEX = '/acl';
    public const URI_ADD = '/acl/add';
    public const URI_EDIT = '/acl/edit';
    public const URI_SAVE = '/acl/save';
    public const URI_DELETE = '/acl/delete';

    public const POST_REC = 'acl';

    public const TABLE = 'dev_acl_list';

    public const F_ID = 'id';
    public const F_ACL_TABLE_ID = 'acl_table_id';
    public const F_TP_ID = 'tp_id';
    public const F_ADDRESS = 'address';
    public const F_COMMENT = 'comment';
    public const F_ENABLED = 'is_enabled';
    public const F_CREATION_UID = 'creation_uid';
    public const F_CREATION_DATE = 'creation_date';
    public const F_MODIFIED_UID = 'modified_uid';
    public const F_MODIFIED_DATE = 'modified_date';
}
