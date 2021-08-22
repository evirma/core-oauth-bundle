<?php

/*
 * This file is part of the HWIOAuthBundle package.
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Evirma\Bundle\CoreOauthBundle\OAuth\RequestDataStorage;

use Evirma\Bundle\CoreOauthBundle\OAuth\RequestDataStorageInterface;
use Evirma\Bundle\CoreOauthBundle\OAuth\ResourceOwnerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Request token storage implementation using the Symfony session.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Francisco Facioni <fran6co@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class SessionStorage implements RequestDataStorageInterface
{
    private RequestStack $requestStack;

    /**
     * @param RequestStack     $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(ResourceOwnerInterface $resourceOwner, $key, $type = 'token')
    {
        $key = $this->generateKey($resourceOwner, (string)$key, (string)$type);
        if (null === $token = $this->getSession()->get($key)) {
            throw new InvalidArgumentException('No data available in storage.');
        }

        // request tokens are one time use only
        $this->getSession()->remove($key);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResourceOwnerInterface $resourceOwner, $value, $type = 'token')
    {
        if ('token' === $type) {
            if (!is_array($value) || !isset($value['oauth_token'])) {
                throw new InvalidArgumentException('Invalid request token.');
            }

            $key = $this->generateKey($resourceOwner, (string)$value['oauth_token'], 'token');
        } else {
            $key = $this->generateKey($resourceOwner, (string)(is_array($value) ? reset($value) : $value), (string)$type);
        }

        $this->getSession()->set($key, $value);
    }

    /**
     * Key to for fetching or saving a token.
     *
     * @param ResourceOwnerInterface $resourceOwner
     * @param string                 $key
     * @param string                 $type
     *
     * @return string
     */
    protected function generateKey(ResourceOwnerInterface $resourceOwner, string $key, string $type): string
    {
        return sprintf('_core_oauth.%s.%s.%s.%s', $resourceOwner->getName(), $resourceOwner->getOption('client_id'), $type, $key);
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }
}
