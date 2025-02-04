<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 * @ExclusionPolicy("all")
 */
class Room
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Expose
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     * @Expose
     * @Assert\Positive
     */
    private int $capacity;

    /**
     * @ORM\Column(type="string", length=255)
     * @Expose
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     * @Expose
     * @Assert\Positive
     */
    private int $floor;

    /**
     * @ORM\Column(type="boolean")
     * @Expose
     */
    private bool $private = true;

    /**
     * @ORM\Column(type="time")
     * @Expose
     */
    private \DateTime $openedFrom;

    /**
     * @ORM\Column(type="time")
     * @Expose
     * @Assert\Expression(
     *     "this.getOpenedTo() >= this.getOpenedFrom()",
     *     message="The start of opening hours must be before its end!",
     * )
     */
    private \DateTime $openedTo;

    /**
     * @ORM\ManyToOne(targetEntity=Building::class, inversedBy="rooms", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     * @Expose
     */
    private Building $building;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="rooms")
     * @ORM\JoinTable(name="members_rooms")
     * @Expose
     */
    private Collection $users;

    /**
     * @ORM\OneToMany(targetEntity=Reservation::class, mappedBy="room")
     */
    private Collection $reservations;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="managedRooms", fetch="EAGER")
     * @Expose
     */
    private ?User $roomManager = null;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="rooms")
     */
    private ?Group $group = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $lastAccess = null;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $locked = false;

    /**
     * @ORM\Column(type="integer")
     */
    private int $accessCounter = 0;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): self
    {
        $this->floor = $floor;

        return $this;
    }

    public function getOpenedFrom(): string
    {
        return $this->openedFrom->format("H:i:s");
    }

    public function setOpenedFrom(string $openedFrom): self
    {
        try {
            $this->openedFrom = new DateTime($openedFrom);
        } catch (\Exception $e) {
            print($e);
        }
        return $this;
    }

    public function getOpenedTo(): string
    {
        return $this->openedTo->format("H:i:s");
    }

    public function setOpenedTo(string $openedTo): self
    {
        try {
            $this->openedTo = new DateTime($openedTo);
        } catch (\Exception $e) {
            print($e);
        }
        return $this;
    }

    public function getBuilding(): Building
    {
        return $this->building;
    }

    public function setBuilding(Building $building): self
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Collection
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations[] = $reservation;
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }

    public function getRoomManager(): ?User
    {
        return $this->roomManager;
    }

    public function setRoomManager(?User $roomManager): self
    {
        $this->roomManager = $roomManager;

        return $this;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function setGroup(?Group $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getLastAccess(): ?int
    {
        return $this->lastAccess;
    }

    public function setLastAccess(int $lastAccess): self
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    public function getLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getAccessCounter(): int
    {
        return $this->accessCounter;
    }

    public function setAccessCounter(int $accessCounter): self
    {
        $this->accessCounter = $accessCounter;
        return $this;
    }
}
