<?php

declare(strict_types=1);

namespace Phly\Mezzio\OAuth2ClientAuthentication\Debug;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class DebugProviderMiddleware implements MiddlewareInterface
{
    public const DEFAULT_PATH_TEMPLATE = '/auth/debug/oauth2callback?code=%s&state=%s';

    /** @var string */
    private $pathTemplate;

    /** @var callable */
    private $redirectResponseFactory;

    public function __construct(callable $redirectResponseFactory, string $pathTemplate = self::DEFAULT_PATH_TEMPLATE)
    {
        $this->redirectResponseFactory = $redirectResponseFactory;
        $this->pathTemplate            = $pathTemplate;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = sprintf($this->pathTemplate, DebugProvider::CODE, DebugProvider::STATE);
        return ($this->redirectResponseFactory)($uri);
    }
}
