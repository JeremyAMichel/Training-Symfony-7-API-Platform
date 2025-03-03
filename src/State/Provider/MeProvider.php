<?php

// namespace App\State\Provider;

// use ApiPlatform\Metadata\Operation;
// use ApiPlatform\State\ProviderInterface;
// use Symfony\Bundle\SecurityBundle\Security;
// use Symfony\Component\HttpFoundation\RequestStack;

// class MeProvider implements ProviderInterface
// {
//     public function __construct(
//         private Security $security,
//         private RequestStack $requestStack
//     ) {
//     }

//     public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
//     {
//         $request = $this->requestStack->getCurrentRequest();
//         $authHeader = $request->headers->get('Authorization');
        
//         // Extraction du token (retire le "Bearer " du début)
//         $token = str_replace('Bearer ', '', $authHeader);
        
//         // Affiche le token et l'utilisateur courant
//         dd([
//             'token' => $token,
//             'user' => $this->security->getUser()
//         ]);

//         return $this->security->getUser();
//     }
// }


namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

class MeProvider implements ProviderInterface
{
    public function __construct(
        private Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        
        return $this->security->getUser();
    }
}