<?php
namespace common;

class ERROR
{
    const DEF_MSG = '系统错误';
    //系统级错误码
    const PARAM_ERROR = '1_参数错误';
    const CONNECTION_TIMEOUT = '2_连接超时';
    const MUST_LONG_SERVER = '3_必需是在长服务模式下';
    const HTTP_METHOD_NO_SUPPORT = '4_只支持GET,POST方式';
    const REMOVE_WHERE_EMPTY = '5_删除必需有条件';

    const SERVICE_NAME_ERROR = '101_服务名错误';
}