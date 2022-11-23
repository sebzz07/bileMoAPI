<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "api_customerDetails",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getCustomers", excludeIf="expr(not is_granted('ROLE_USER'))"),
 * )
 * @Hateoas\Relation(
 *     "delete",
 *     href = @Hateoas\Route(
 *         "api_deleteCustomer",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getCustomers", excludeIf="expr(not is_granted('ROLE_USER'))"),
 * )
 * @Hateoas\Relation(
 *     "update",
 *     href = @Hateoas\Route(
 *         "api_updateCustomer",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getCustomers", excludeIf="expr(not is_granted('ROLE_USER'))"),
 * )
 */
#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCustomers', 'getCustomerDetails'])]
    #[OA\Property(description: 'The unique identifier of the customer.')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomers', 'getCustomerDetails', 'createCustomer'])]
    #[Assert\Length(min: 3, max: 255, minMessage: 'The first name must be at least {{ limit }} characters', maxMessage: 'The first name must be no more than {{ limit }} characters')]
    #[OA\Property(type: 'string', maxLength: 255, minLength: 3)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomers', 'getCustomerDetails', 'createCustomer'])]
    #[Assert\Length(min: 3, max: 255, minMessage: 'The last name must be at least {{ limit }} characters', maxMessage: 'The last name must be no more than {{ limit }} characters')]
    #[OA\Property(type: 'string', maxLength: 255, minLength: 3)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getCustomers', 'getCustomerDetails', 'createCustomer'])]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[OA\Property(type: 'string', example: 'email@provider.com')]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['getCustomerDetails'])]
    #[OA\Property(description: 'The date of creation the customer.')]
    private ?\DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
