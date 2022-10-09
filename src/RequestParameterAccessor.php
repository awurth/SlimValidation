<?php

/*
 * This file is part of the Awurth Validator package.
 *
 * (c) Alexis Wurth <awurth.dev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awurth\Validator;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteContext;

/**
 * Allows accessing a Request's parameters.
 *
 * @author Alexis Wurth <awurth.dev@gmail.com>
 */
final class RequestParameterAccessor
{
    /**
     * Fetches a request parameter's value from the body or query string (in that order).
     */
    public static function getValue(Request $request, string $key, mixed $default = null): mixed
    {
        $postParams = $request->getParsedBody();
        $getParams = $request->getQueryParams();
        $route = $request->getAttribute('route');
        $routeParams = [];

        if (\class_exists(RouteContext::class)) {
            $routeContext = RouteContext::fromRequest($request);
            $route = $routeContext->getRoute();
        }

        if ($route instanceof RouteInterface) {
            $routeParams = $route->getArguments();
        }

        $result = $default;
        if (\is_array($postParams) && isset($postParams[$key])) {
            $result = $postParams[$key];
        } elseif (\is_object($postParams) && \property_exists($postParams, $key)) {
            $result = $postParams->$key;
        } elseif (isset($getParams[$key])) {
            $result = $getParams[$key];
        } elseif (isset($routeParams[$key])) {
            $result = $routeParams[$key];
        } elseif (isset($_FILES[$key])) {
            $result = $_FILES[$key];
        }

        return $result;
    }
}
