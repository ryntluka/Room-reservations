<?php

namespace App\Entity;

use App\Repository\RoomRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $capacity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="integer")
     */
    private int $floor;

    /**
     * @ORM\Column(type="time")
     */
    private \DateTime $openedFrom;

    /**
     * @ORM\Column(type="time")
     */
    private \DateTime $openedTo;

    /**
     * @ORM\ManyToOne(targetEntity=Building::class, inversedBy="rooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private Building $building;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="rooms")
     * @ORM\JoinTable(name="members_rooms")
     */
    private Collection $users;

    /**
     * @ORM\OneToMany(targetEntity=Request::class, mappedBy="room")
     */
    private Collection $requests;

    /**
     * @ORM\ManyToOne(targetEntity=RoomManager::class, inversedBy="managedRooms")
     */
    private ?RoomManager $roomManager;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->requests = new ArrayCollection();
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

    public function getOpened_from():string
    {
        return $this->openedFrom->format("H:i");
    }

    public function getOpenedFrom(): \DateTime
    {
        return $this->openedFrom;
    }

    public function setOpenedFrom(\DateTime $openedFrom): self
    {
        $this->openedFrom = $openedFrom;
        return $this;
    }

    public function getOpened_to(): string
    {
        return $this->openedTo->format("H:i");
    }

    public function getOpenedTo(): \DateTime
    {
        return $this->openedTo;
    }

    public function setOpenedTo(\DateTime $openedTo): self
    {
        $this->openedTo = $openedTo;

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

    /**
     * @return Collection|Request[]
     */
    public function getRequests(): Collection
    {
        return $this->requests;
    }

    public function addRequest(Request $request): self
    {
        if (!$this->requests->contains($request)) {
            $this->requests[] = $request;
            $request->setRoom($this);
        }

        return $this;
    }

    public function removeRequest(Request $request): self
    {
        if ($this->requests->removeElement($request)) {
            // set the owning side to null (unless already changed)
            if ($request->getRoom() === $this) {
                $request->setRoom(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getRoomManager(): ?RoomManager
    {
        return $this->roomManager;
    }

    public function setRoomManager(?RoomManager $roomManager): self
    {
        $this->roomManager = $roomManager;

        return $this;
    }

    public function addRegisteredUser(User $user): self
    {
        if (!$this->registeredUsers->contains($user)) {
            $this->registeredUsers[] = $user;
        }

        return $this;
    }

    public function removeRegisteredUser(User $user): self
    {
        $this->registeredUsers->removeElement($user);

        return $this;
    }
}
