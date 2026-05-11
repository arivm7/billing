<?php
/*
 *  Project : my.ri.net.ua
 *  File    : DevAclTable.php
 *  Path    : config/tables/DevAclTable.php
 *  Author  : Ariv <ariv@meta.ua> | https://github.com/arivm7
 *  Org     : RI-Network, Kiev, UK
 *  Created : 10 May 2026
 *  License : GPL v3
 */

namespace config\tables;

class DevAclTable {

    public const TABLE = 'dev_acl_tables';

    public const F_ID = 'id';
    public const F_NAME = 'name';
    public const F_DESCRIPTION = 'description';
    public const F_CREATION_UID = 'creation_uid';
    public const F_CREATION_DATE = 'creation_date';
    public const F_MODIFIED_UID = 'modified_uid';
    public const F_MODIFIED_DATE = 'modified_date';
}
