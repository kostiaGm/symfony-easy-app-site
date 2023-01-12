<?php

namespace App\Security\Voter;

use App\Entity\Interfaces\PermissionInterface;
use App\Entity\Page;
use App\Repository\Interfaces\UserPermissionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class PageVoter extends Voter
{
    private LoggerInterface $logger;

    protected const VIEW = 'view';
    protected const EDIT = 'edit';
    protected const DETAIL = 'detail';
    protected const DELETE = 'delete';
    protected const CREATE = 'create';

    protected const ATTRIBUTES = [
        self::VIEW,
        self::EDIT,
        self::DETAIL,
        self::DELETE,
        self::CREATE,
    ];

    protected const VIEWS = [
        self::VIEW,
        self::DETAIL
    ];

    protected const EDITS = [
        self::EDIT,
        self::DELETE,
        self::CREATE,
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, self::ATTRIBUTES)) {
            $this->logger->debug("page.voter: attribute [$attribute] not found");
            return false;
        }

        if (!$subject instanceof Page) {
            $this->logger->debug("page.voter: subject isn't type [".get_class(Page::class)."] not found");
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if (empty($token)) {
            $this->logger->debug("page.voter: user token is empty");
            return false;
        }
        if (($user = $token->getUser()) === null) {
            $this->logger->debug("page.voter: user not found");
            return false;
        }

        if (!$subject instanceof Page) {
            $this->logger->debug("page.voter: subject isn't type [".get_class(Page::class)."] not found");
            return false;
        }

        $intersectGroups = array_intersect(
            $user->getRoles(),
            $subject->getAuthor()->getRoles()
        );

        $isAuthorTheSameAsActiveUser = $subject->getAuthor()->getId() == $user->getId();
        $debugIndo =  [
            "page" => $subject->getId(),
            "action" => $attribute,
            "author" =>  $subject->getAuthor()->getId(),
            "user" =>  $user->getId()
        ];

        switch ($subject->getPermissionMode()) {

            case PermissionInterface::AUTHOR_ONLY_READING_ALLOWED:

                if (!$isAuthorTheSameAsActiveUser) {
                    $this->logger->debug("page.voter", $debugIndo);
                }
                return $isAuthorTheSameAsActiveUser;

            case  PermissionInterface::EVERY_ONE_READING_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser || in_array($attribute, self::VIEWS);
                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::VIEWS);
                    $this->logger->debug("page.voter", $debugIndo);
                }
                return $result;

            case PermissionInterface::GROUPS_READING_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser ||
                    (in_array($attribute, self::VIEWS) && !empty($intersectGroups));

                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::VIEWS);
                    $debugIndo["found common groups"] = implode(',', $intersectGroups);
                    $this->logger->debug("page.voter", $debugIndo);
                }

                return $result;

            case PermissionInterface::GROUPS_EDIT_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser ||
                    (in_array($attribute, self::ATTRIBUTES) &&
                        !empty(array_intersect($user->getRoles(), $subject->getAuthor()->getRoles())));

                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::VIEWS);
                    $debugIndo["found common groups"] = implode(',', $intersectGroups);
                    $this->logger->debug("page.voter", $debugIndo);
                }

                return $result;
        }
        return false;
    }
}

