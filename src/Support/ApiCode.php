<?php

namespace FeloZ\LaravelHelper\Support;

/**
 * 通用 API code 常量（可直接使用，也可在业务项目中二次扩展）。
 */
class ApiCode
{
    /**
     * 业务成功
     */
    public const BIZ_OK = 0;

    /**
     * 通用业务失败
     */
    public const BIZ_FAILED = 1000;

    /**
     * 参数校验失败
     */
    public const BIZ_VALIDATION_ERROR = 1001;

    /**
     * 未认证 / 登录失效
     */
    public const BIZ_UNAUTHORIZED = 1002;

    /**
     * 无权限
     */
    public const BIZ_FORBIDDEN = 1003;

    /**
     * 资源不存在
     */
    public const BIZ_NOT_FOUND = 1004;

    /**
     * 数据冲突（重复、状态冲突等）
     */
    public const BIZ_CONFLICT = 1005;

    /**
     * 请求频率过高
     */
    public const BIZ_TOO_MANY_REQUESTS = 1006;

    /**
     * 系统异常
     */
    public const BIZ_SYSTEM_ERROR = 1999;

    /**
     * 兼容常见 HTTP code 映射（便于统一引用）
     */
    public const HTTP_OK = 200;

    public const HTTP_CREATED = 201;

    public const HTTP_ACCEPTED = 202;

    public const HTTP_NO_CONTENT = 204;

    public const HTTP_BAD_REQUEST = 400;

    public const HTTP_UNAUTHORIZED = 401;

    public const HTTP_FORBIDDEN = 403;

    public const HTTP_NOT_FOUND = 404;

    public const HTTP_CONFLICT = 409;

    public const HTTP_UNPROCESSABLE_ENTITY = 422;

    public const HTTP_TOO_MANY_REQUESTS = 429;

    public const HTTP_INTERNAL_SERVER_ERROR = 500;
}
