<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\GetLoggedUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 *
 * @UniqueEntity(fields={"email"})
 *
 * @ApiResource(
 *     attributes={
 *         "normalization_context"={"groups"={"read"}},
 *         "denormalization_context"={"groups"={"write"}}
 *     },
 *     collectionOperations={
 *         "get_logged"={
 *              "method"="GET",
 *              "path"="/users/me",
 *              "controller"=GetLoggedUser::class,
 *              "access_control"="is_granted('ROLE_USER')",
 *          },
 *          "get"={
 *              "access_control"="is_granted('ROLE_ADMIN')",
 *          },
 *         "post"={
 *              "denormalization_context"={"groups"={"write", "password"}},
 *              "validation_groups"={"Default", "password"},
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object == user)",
 *          },
 *          "put"={
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object == user)",
 *          },
 *          "put_password"={
 *              "denormalization_context"={"groups"={"password"}},
 *              "validation_groups"={"password"},
 *              "method"="PUT",
 *              "path"="/users/{id}/password",
 *              "access_control"="is_granted('ROLE_ADMIN') or (is_granted('ROLE_USER') and object == user)",
 *          },
 *     }
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @Groups({"read"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @Assert\Email()
     * @Assert\NotBlank()
     *
     * @Groups({"read", "write"})
     */
    private $email;

    /**
     * @ORM\Column(type="array")
     *
     * @Assert\Choice(choices={"ROLE_USER", "ROLE_ADMIN"}, multiple=true)
     * @Assert\NotBlank()
     *
     * @Groups({"read"})
     */
    private $roles = ["ROLE_USER"];

    /**
     * @var string The hashed password
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(groups={"password"})
     * @Assert\Length(min="4", max="40", groups={"password"})
     *
     * @Groups({"password"})
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Project", mappedBy="user", orphanRemoval=true)
     *
     * @Groups({"read"})
     */
    private $projects;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = array_unique($roles);

        return $this;
    }

    /**
     * @param $role
     *
     * @return User
     */
    public function addRole($role): self
    {
        if (!is_array($this->roles)) {
            $this->roles = [];
        }

        if (!in_array($role, $this->roles)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Project[]
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): self
    {
        if (!$this->projects->contains($project)) {
            $this->projects[] = $project;
            $project->setUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): self
    {
        if ($this->projects->contains($project)) {
            $this->projects->removeElement($project);
            // set the owning side to null (unless already changed)
            if ($project->getUser() === $this) {
                $project->setUser(null);
            }
        }

        return $this;
    }
}
