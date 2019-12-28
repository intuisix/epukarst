<?php
namespace App\Security;

use App\Entity\User;
use App\Entity\Alarm;
use App\Entity\System;
use App\Entity\Reading;
use App\Entity\Station;
use App\Entity\SystemRole;
use App\Entity\SystemReading;
use App\Entity\SystemParameter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemVoter extends Voter
{
    const OBSERVER = 'SYSTEM_OBSERVER';
    const CONTRIBUTOR = 'SYSTEM_CONTRIBUTOR';
    const MANAGER = 'SYSTEM_MANAGER';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return 
            (($attribute === self::OBSERVER) ||
                ($attribute === self::CONTRIBUTOR) || 
                ($attribute === self::MANAGER)) &&
            (($subject instanceof System) ||
                ($subject instanceof Station) ||
                ($subject instanceof SystemParameter) ||
                ($subject instanceof Reading) ||
                ($subject instanceof SystemReading) ||
                ($subject instanceof Alarm) ||
                ($subject === null));
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /* L'utilisateur doit être connecté */
        $user = $token->getUser();
        if ($user instanceof User)
        {
            if (null === $subject) {
                return true;
            } else {
                if ($subject instanceof System) {
                    $system = $subject;
                } else if ($subject instanceof Station) {
                    $system = $subject->getBasin()->getSystem();
                } else if ($subject instanceof SystemParameter) {
                    $system = $subject->getSystem();
                } else if ($subject instanceof SystemReading) {
                    $system = $subject->getSystem();
                } else if ($subject instanceof Reading) {
                    $system = $subject->getStation()->getBasin()->getSystem();
                } else if ($subject instanceof Alarm) {
                    $system = $subject->getSystem();
                } else {
                    throw new \LogicException('Classe inconnue');
                }

                /* Parcourir les autorisations de l'utilisateur, afin de déterminer si l'autorisation est donnée explicitement sur le système recherché ou implicitement pour tous les systèmes (système null) */
                foreach ($user->getSystemRoles() as $systemRole) {
                    if (((null === $systemRole->getSystem()) ||($systemRole->getSystem() === $system)) &&
                        ($this->isGranted($systemRole, $attribute))) {
                        return true;
                    }
                }

                /* Parcourir les autorisations du système, afin de déterminer si l'autorisation est donnée explicitement pour l'utilisateur recherché ou implicitement pour tous les systèles (utilisateur null) */
                foreach ($system->getSystemRoles() as $systemRole) {
                    if (((null === $systemRole->getUserAccount()) ||($systemRole->getUserAccount() === $user)) &&
                        ($this->isGranted($systemRole, $attribute))) {
                        return true;
                    }
                }

                return false;
            }
        }
    }

    private function isGranted(SystemRole $systemRole, $attribute)
    {
        $role = $systemRole->getRole();

        switch ($attribute) {
            case self::OBSERVER:
                return
                    ($role === self::OBSERVER) ||
                    ($role === self::CONTRIBUTOR) ||
                    ($role === self::MANAGER);
            case self::CONTRIBUTOR:
                return
                    ($role == self::MANAGER) ||
                    ($role === self::CONTRIBUTOR);
            case self::MANAGER:
                return $role === self::MANAGER;
            default:
                throw new \LogicException('Attribut inconnu');
        }
    }
}
