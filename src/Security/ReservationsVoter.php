<?php

namespace App\Security;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReservationsVoter extends Voter
{
    const VIEW_ALL = 'view_reservations';
    const CREATE = 'create_reservation';
    const BOOK_ROOM = 'book_room';
    const APPROVE = 'approve_reservation';
    const REJECT = 'reject_reservation';
    const DELETE = 'delete_reservation';
    const EDIT = 'edit_reservation';

    protected function supports(string $attribute, $subject): bool
    {
        if (!in_array($attribute, [self::VIEW_ALL, self::CREATE, self::BOOK_ROOM,self::APPROVE, self::REJECT, self::DELETE, self::EDIT])) {
            return false;
        }
        if (!($subject instanceof Room || $subject instanceof Reservation || !$subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // check if the user is logged at all
        if (!$user) {
            return false;
        }

        switch($attribute) {
            case self::VIEW_ALL:
                return $this->canViewAll();
            case self::CREATE:
                return $this->canCreate($user);
            case self::BOOK_ROOM:
                return $this->canBookRoom($user, $subject);
            case self::APPROVE:
                return $this->canApprove($user, $subject);
            case self::REJECT:
                return $this->canReject($user, $subject);
            case self::DELETE:
                return $this->canDelete($user, $subject);
            case self::EDIT:
                return $this->canEdit($user, $subject);
            default:
                return false;
        }
    }

    private function canViewAll(): bool
    {
        return true;
    }

    private function canCreate(User $account): bool
    {
        return $account->isAdmin() || $account->isRoomAdmin() || $account->isGroupAdmin();
    }

    private function canBookRoom(User $account, Room $room): bool
    {
        if ($account->isAdmin()) {
            return true;
        }
        if (in_array($room, $account->getRooms()->getValues())
            || ($account->isGroupAdmin() && $room->getGroup()->isSubGroupOfParentGroups($account->getAllManagedGroups()))
            || ($account->isRoomAdmin() && $room->getRoomManager() === $account)
            || ($account->getGroup() === $room->getGroup())) {
            return true;
        }
        return false;
    }

    private function canReject(User $account, Reservation $reservation): bool
    {
        $room = $reservation->getRoom();
        if ($account->isAdmin()) {
            return true;
        }

        if (($account->isGroupAdmin() && $room->getGroup()->isSubGroupOfParentGroups($account->getAllManagedGroups()))
            || ($account->isRoomAdmin() && $room->getRoomManager() === $account)) {
            return true;
        }
        return false;
    }

    private function canApprove(User $account, Reservation $reservation): bool
    {
        return $this->canReject($account, $reservation);
    }

    private function canDelete(User $account, Reservation $reservation): bool
    {
        return $this->canReject($account, $reservation) && !$reservation->isPending();
    }

    private function canEdit(User $account, Reservation $reservation): bool
    {
        return $this->canBookRoom($account, $reservation->getRoom()) && $reservation->isPending();
    }
}