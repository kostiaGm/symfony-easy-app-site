<?php

namespace App\Security\Voter;

use App\Entity\Interfaces\PermissionInterface;
use App\Entity\Page;
use App\Repository\Interfaces\UserPermissionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class PageVoter extends Voter
{
    private LoggerInterface $logger;

    protected const INDEX = 'index';
    protected const VIEW = 'show';
    protected const EDIT = 'edit';
    protected const DETAIL = 'detail';
    protected const DELETE = 'delete';
    protected const NEW = 'new';

    protected const ATTRIBUTES = [
        self::INDEX,
        self::VIEW,
        self::EDIT,
        self::DETAIL,
        self::DELETE,
        self::NEW,
    ];

    protected const VIEWS = [
        self::INDEX,
        self::VIEW,
        self::DETAIL
    ];

    private const WITHOUT_ENTITY = [
        self::INDEX
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, self::ATTRIBUTES)) {
            $this->logger->error("page.voter: attribute [$attribute] not found");
            return false;
        }

        if (!in_array($attribute, self::WITHOUT_ENTITY) && !$subject instanceof Page) {
            $this->logger->error("page.voter: subject isn't type [" . get_class(Page::class) . "] not found");
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        if (empty($token)) {
            $this->logger->error("page.voter: user token is empty");
            return false;
        }

        if (($user = $token->getUser()) === null) {
            $this->logger->error("page.voter: user not found");
            return false;
        }

        if (!in_array($attribute, self::WITHOUT_ENTITY) && !$subject instanceof Page) {
            $this->logger->error("page.voter: subject isn't type [" . get_class(Page::class) . "] not found");
            return false;
        }

        $authorRoles = [];
        $isAuthorTheSameAsActiveUser = false;

        $debugIndo = [
            "action" => $attribute,
            "user" => $user->getId()
        ];

        if (!in_array($attribute, self::WITHOUT_ENTITY) && $subject->getAuthor()) {
            $authorRoles = $subject->getAuthor()->getRoles();
            $isAuthorTheSameAsActiveUser = $subject->getAuthor()->getId() == $user->getId();
            $debugIndo["page"] = $subject->getId();
            $debugIndo["author"] = $subject->getAuthor()->getId();
        }

        $intersectGroups = array_intersect(
            $user->getRoles(),
            $authorRoles
        );

        $permissionMode = $subject !== null ? $subject->getPermissionMode() : null;

        switch ($permissionMode) {

            case PermissionInterface::AUTHOR_ONLY_READING_ALLOWED:

                if (!$isAuthorTheSameAsActiveUser) {
                    $this->logger->error("page.voter", $debugIndo);
                }
                return $isAuthorTheSameAsActiveUser;

            case  PermissionInterface::EVERY_ONE_READING_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser || in_array($attribute, self::VIEWS);
                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::VIEWS);
                    $this->logger->error("page.voter", $debugIndo);
                }
                return $result;

            case PermissionInterface::GROUPS_READING_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser ||
                    (in_array($attribute, self::VIEWS) && !empty($intersectGroups));

                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::VIEWS);
                    $debugIndo["found common groups"] = implode(',', $intersectGroups);
                    $this->logger->error("page.voter", $debugIndo);
                }

                return $result;

            case PermissionInterface::GROUPS_EDIT_ALLOWED:

                $result = $isAuthorTheSameAsActiveUser ||
                    (in_array($attribute, self::ATTRIBUTES) &&
                        !empty(array_intersect($user->getRoles(), $subject->getAuthor()->getRoles())));

                if (!$result) {
                    $debugIndo["allowed actions"] = implode(',', self::ATTRIBUTES);
                    $debugIndo["found common groups"] = implode(',', $intersectGroups);
                    $this->logger->error("page.voter", $debugIndo);
                }
                return $result;

            default: {

                return in_array($attribute, [
                        self::INDEX,
                        self::NEW
                    ]
                );
            }
        }
        return false;
    }
}

