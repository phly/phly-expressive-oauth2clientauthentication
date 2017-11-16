<?php

/**
 * @license http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 * @copyright Copyright (c) Matthew Weier O'Phinney
 */

namespace Phly\Expressive\OAuth2ClientAuthentication\Debug;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DebugProviderMiddleware implements MiddlewareInterface
{
    const DEFAULT_PATH_TEMPLATE = '/auth/debug/oauth2callback?code=%s&state=%s';

    /**
     * @var string
     */
    private $pathTemplate;

    /**
     * @var callable
     */
    private $redirectResponseFactory;

    public function __construct(callable $redirectResponseFactory, string $pathTemplate = self::DEFAULT_PATH_TEMPLATE)
    {
        $this->redirectResponseFactory = $redirectResponseFactory;
        $this->pathTemplate = $pathTemplate;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $uri = sprintf($this->pathTemplate, DebugProvider::CODE, DebugProvider::STATE);
        return ($this->redirectResponseFactory)($uri);
    }
}
