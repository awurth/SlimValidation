<?php

declare(strict_types=1);

namespace Awurth\Validator\ValueReader;

use Psr\Http\Message\ServerRequestInterface;

final class PsrServerRequestValueReader implements ValueReaderInterface
{
    /**
     * @param ServerRequestInterface $subject
     */
    public function getValue(mixed $subject, string $path, mixed $default = null): mixed
    {
        $postParams = $subject->getParsedBody();
        $getParams = $subject->getQueryParams();
        $route = $subject->getAttribute('route');
        $routeParams = [];

        if (\class_exists(RouteContext::class)) {
            $routeContext = RouteContext::fromRequest($subject);
            $route = $routeContext->getRoute();
        }

        if ($route instanceof RouteInterface) {
            $routeParams = $route->getArguments();
        }

        $result = $default;
        if (\is_array($postParams) && \array_key_exists($path, $postParams)) {
            $result = $postParams[$path];
        } elseif (\is_object($postParams) && \property_exists($postParams, $path)) {
            $result = $postParams->$path;
        } elseif (\array_key_exists($path, $getParams)) {
            $result = $getParams[$path];
        } elseif (\array_key_exists($path, $routeParams)) {
            $result = $routeParams[$path];
        } elseif (\array_key_exists($path, $_FILES)) {
            $result = $_FILES[$path];
        }

        return $result;
    }

    public function supports(mixed $subject): bool
    {
        return $subject instanceof ServerRequestInterface;
    }
}
