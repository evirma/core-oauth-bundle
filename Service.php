<?php
namespace Evirma\Bundle\CoreOauthBundle;

use Evirma\Bundle\CoreOauthBundle\OAuth\ResourceOwnerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Service
{
    protected $id;
    protected string $redirectUriRoute;
    protected UrlGeneratorInterface $urlGenerator;
    protected ResourceOwnerInterface $resourceOwner;
    protected $title;

    public function __construct($id, $title, ResourceOwnerInterface $resourceOwner)
    {
        $this->id = $id;
        $this->title = $title;
        $this->resourceOwner = $resourceOwner;
    }

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function setRedirectUriRoute($route)
    {
        $this->redirectUriRoute = $route;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getResourceOwner(): ResourceOwnerInterface
    {
        return $this->resourceOwner;
    }

    public function getRedirectUri(): string
    {
        return $this->urlGenerator->generate($this->redirectUriRoute, [ 'service' => $this->id ], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    public function getAuthorizationUrl(array $extraParameters = array()): string
    {
        return $this->resourceOwner->getAuthorizationUrl($this->getRedirectUri(), $extraParameters);
    }

    /**
     * @param Request $request
     * @param array   $extraParameters
     * @return array
     * @throws OAuth\Exception\HttpTransportException
     */
    public function getAccessToken(Request $request, array $extraParameters = array()): array
    {
        return $this->resourceOwner->getAccessToken($request, $this->getRedirectUri(), $extraParameters);
    }

    /**
     * @param array $accessToken
     * @param array $extraParameters
     * @return OAuth\Response\UserResponseInterface
     * @throws OAuth\Exception\HttpTransportException
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array()): OAuth\Response\UserResponseInterface
    {
        return $this->resourceOwner->getUserInformation($accessToken, $extraParameters);
    }
}
