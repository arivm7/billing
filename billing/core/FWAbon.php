<?php


namespace billing\core;

/**
 * Description of FWAbon
 *
 * @author ar
 */
class FWAbon {

    public const LAN_INTERFACE_LIST = 'LAN';
    public const WAN_INTERFACE_LIST = 'WAN';

    public const ABON_LIST = 'ABON';

    public const PROXY_PORT = 8088;

    public const FILTER_CHAIN = 'forward';
    public const NAT_CHAIN    = 'dstnat';

    public const COMMENT_ACCEPT = 'ABON ACCEPT';
    public const COMMENT_DROP   = 'ABON DROP';

    public const COMMENT_LOG_LAN = 'ABON LOG _NO_ABON_LAN';
    public const COMMENT_LOG_WAN = 'ABON LOG _NO_ABON_WAN';
    public const COMMENT_LOG_ALL = 'ABON LOG _NO_ABON_ALL';

    public const COMMENT_PROXY = 'ABON REDIRECT';

    public const TMP_PREFIX = '_';    
    
    
}
