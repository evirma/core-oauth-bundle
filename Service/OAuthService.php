<?php

namespace Evirma\Bundle\CoreOauthBundle\Service;

use Evirma\Bundle\CoreOauthBundle\Exception\ServiceNotFoundException;
use Evirma\Bundle\CoreOauthBundle\Service;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;

class OAuthService
{
    use ContainerAwareTrait;

    /**
     * @param $id
     * @return Service|object
     * @throws ServiceNotFoundException
     */
    public function getService($id)
    {
        if (!$this->container->has('core_oauth.oauth.service.' . $id)) {
            throw new ServiceNotFoundException();
        }
        $service = $this->container->get('core_oauth.oauth.service.' . $id);
        return $service;
    }

    /**
     * @param Request $request
     * @return Service|object
     * @throws ServiceNotFoundException
     */
    public function getServiceByRequest(Request $request)
    {
        $service = $this->getService($request->get('service'));
        if (!$service->getResourceOwner()->handles($request)) {
            throw new ServiceNotFoundException();
        }
        return $service;
    }
}