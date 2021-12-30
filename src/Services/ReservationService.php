<?php


namespace App\Services;


use App\Entity\Request;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\States;
use App\Entity\User;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints\Date;

class ReservationService
{
    private ReservationRepository $reservationRepository;
    private EntityManagerInterface $entityManager;

    /**
     * ReservationService constructor.
     * @param ReservationRepository $reservationRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ReservationRepository $reservationRepository, EntityManagerInterface $entityManager)
    {
        $this->reservationRepository = $reservationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $id
     * @return Reservation|null
     */
    public function find(int $id): ?Reservation
    {
        return $this->reservationRepository->find($id);
    }

    /**
     * @return Reservation[]|array
     */
    public function findAll(): array
    {
        return $this->reservationRepository->findAll();
    }

    public function save(Reservation $reservation): void
    {
        $this->entityManager->persist($reservation);
        $this->entityManager->flush();
    }

    public function filterAll(array $queryParams): array
    {
        return $this->reservationRepository->filterAll(
            ParamsParser::getFilters($queryParams, 'filter_by'),
            ParamsParser::getFilters($queryParams, 'order_by'),
            ParamsParser::getFilters($queryParams, 'paginate')
        );
    }

    /**
     * @param User $user
     * @param array $queryParams
     * @return array
     */
    public function filterAllForUser(User $user, array $queryParams): array
    {
        return $this->reservationRepository->filterAllForUser(
            $user,
            ParamsParser::getFilters($queryParams, 'filter_by'),
            ParamsParser::getFilters($queryParams, 'order_by'),
            ParamsParser::getFilters($queryParams, 'paginate')
        );
    }

    public function newWithRequesterAndRoom(User $user, Room $room) : Reservation
    {
        $request = new Reservation();
        $request->setUser($user);
        $request->setState(new States("PENDING"));
        $request->setRoom($room);
        return $request;
    }

    public function filterCurrentReservation(Room $room) : \Doctrine\ORM\LazyCriteriaCollection
    {
        $today = new \DateTime();
        $criteria = $this->getCriteriaDateRoomState($room, $today->format("Y-m-d"));
        $criteria
            ->andWhere(Criteria::expr()->lte('timeFrom', new \DateTime($today->format("H:i:s"))))
            ->andWhere(Criteria::expr()->gte('timeTo', new \DateTime($today->format("H:i:s"))));
        return $this->reservationRepository->matching($criteria);
    }

    public function checkTimeOfReservation(Reservation $reservation): Bool
    {
        $criteria = Criteria::create()
            ->where( Criteria::expr()->andX(
                Criteria::expr()->gte('timeFrom', new \DateTime($reservation->getTimeFrom())),
                Criteria::expr()->lt('timeFrom', new \DateTime($reservation->getTimeTo()))))
            ->orWhere(Criteria::expr()->andX(
                Criteria::expr()->gt('timeTo', new \DateTime($reservation->getTimeFrom())),
                Criteria::expr()->lte('timeTo', new \DateTime($reservation->getTimeTo()))))
            ->orWhere(Criteria::expr()->andX(
                Criteria::expr()->lte('timeFrom', new \DateTime($reservation->getTimeFrom())),
                Criteria::expr()->gte('timeTo', new \DateTime($reservation->getTimeTo()))));
        $criteria = $this->getCriteriaDateRoomState($reservation->getRoom(), $reservation->getDate(), $criteria);

        $reservations = $this->reservationRepository->matching($criteria);
        return $reservations->isEmpty();
    }

    private function getCriteriaDateRoomState(Room $room, string $date, Criteria $criteria = null): Criteria
    {
        $criteria = $criteria ?? Criteria::create();
        return $criteria
            ->andWhere(Criteria::expr()->eq('date', new \DateTime($date)))
            ->andWhere(Criteria::expr()->eq('room', $room))
            ->andWhere(Criteria::expr()->eq('state', States::APPROVED));
    }
}