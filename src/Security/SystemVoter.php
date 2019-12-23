<?php
namespace App\Security;

use App\Entity\User;
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
    const VIEW = 'SYSTEM_VIEW';
    const ENCODE = 'SYSTEM_ENCODE';
    const VALIDATE = 'SYSTEM_VALIDATE';
    const EXPORT = 'SYSTEM_EXPORT';
    const DELETE = 'SYSTEM_DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return 
            in_array($attribute, [
                self::VIEW, self::ENCODE, self::VALIDATE, self::EXPORT, self::DELETE]) &&
            (($subject instanceof System) ||
                ($subject instanceof Station) ||
                ($subject instanceof SystemParameter) ||
                ($subject instanceof Reading) ||
                ($subject instanceof SystemReading) ||
                ($subject === null));
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /* L'utilisateur doit être connecté */
        $user = $token->getUser();
        if ($user instanceof User)
        {
            /*if ($this->security->isGranted('ROLE_ADMIN'))
                return true;*/

            if (null === $subject) {
                return true;
            } else {
                if ($subject instanceof System) {
                    $system = $subject;
                } else if ($subject instanceof Station) {
                    $system = $subject->getBasin()->getStation();
                } else if ($subject instanceof SystemParameter) {
                    $system = $subject->getSystem();
                } else if ($subject instanceof SystemReading) {
                    $system = $subject->getSystem();
                } else if ($subject instanceof Reading) {
                    $system = $subject->getStation()->getBasin()->getSystem();
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
                    if (((null === $systemRole->getAuthor()) ||($systemRole->getAuthor() === $user)) &&
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
        switch ($attribute) {
            case self::VIEW:
                return $systemRole->getCanView();
            case self::EXPORT:
                return $systemRole->getCanExport();
            case self::ENCODE:
                return $systemRole->getCanEncode();
            case self::VALIDATE:
                return $systemRole->getCanValidate();
            case self::DELETE:
                return $systemRole->getCanDelete();
            default:
                throw new \LogicException('Attribut inconnu');
        }
    }
}
