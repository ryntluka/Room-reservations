<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\ManyToOne(targetEntity=GroupManager::class, inversedBy="groups")
     */
    private ?GroupManager $groupManager;

    /**
     * @ORM\ManyToMany(targetEntity=GroupMember::class, mappedBy="groups")
     */
    private Collection $members;

    /**
     * @ORM\OneToMany(targetEntity=Room::class, mappedBy="roomGroup")
     */
    private $rooms;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->rooms = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGroupManager(): ?GroupManager
    {
        return $this->groupManager;
    }

    public function setGroupManager(?GroupManager $groupManager): self
    {
        $this->groupManager = $groupManager;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(GroupMember $groupMember): self
    {
        if (!$this->members->contains($groupMember)) {
            $this->members[] = $groupMember;
        }

        return $this;
    }

    public function removeMember(GroupMember $groupMember): self
    {
        $this->members->removeElement($groupMember);

        return $this;
    }

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
            $room->setRoomGroup($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->removeElement($room)) {
            // set the owning side to null (unless already changed)
            if ($room->getRoomGroup() === $this) {
                $room->setRoomGroup(null);
            }
        }

        return $this;
    }


}
