<?php
declare (strict_types=1);

namespace app\shop\middleware;

class ShopCheck
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $requestUrl = $request->domain();
        $shopUrl = $requestUrl . '/shop';

        //当前uri;
        $currentUri = $request->pathinfo();

        if ($currentUri == '') {
            return redirect($shopUrl);
        }

        return $next($request);
    }
}
