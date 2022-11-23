<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use OpenApi\Attributes as OA;

/**
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "api_phoneDetails",
 *         parameters = { "id" = "expr(object.getId())" }
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getPhones", excludeIf="expr(not is_granted('ROLE_USER'))"),
 * )
 */
#[ORM\Entity(repositoryClass: PhoneRepository::class)]
class Phone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getPhones'])]
    #[OA\Property(description: 'The unique identifier of the phone.')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPhones'])]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPhones'])]
    private ?string $model = null;

    #[ORM\Column(length: 255)]
    #[Groups(['getPhones'])]
    #[OA\Property(description: 'The price of the phone in cents.')]
    #[OA\Property(type: 'int', maxLength: 255)]
    private ?int $price = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[OA\Property(type: 'string', maxLength: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 128, nullable: true)]
    #[OA\Property(type: 'string', maxLength: 128)]
    private ?string $color = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[OA\Property(type: 'float', maxLength: 16)]
    #[OA\Property(description: 'The height of the phone in centimeter.')]
    private ?float $height = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[OA\Property(type: 'float', maxLength: 16)]
    #[OA\Property(description: 'The lenght of the phone in centimeter.')]
    private ?float $lenght = null;

    #[ORM\Column(length: 16, nullable: true)]
    #[OA\Property(type: 'float', maxLength: 16)]
    #[OA\Property(description: 'The thickness of the phone in centimeter.')]
    private ?float $thickness = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getLenght(): ?float
    {
        return $this->lenght;
    }

    public function setLenght(?float $lenght): self
    {
        $this->lenght = $lenght;

        return $this;
    }

    public function getThickness(): ?float
    {
        return $this->thickness;
    }

    public function setThickness(?float $thickness): self
    {
        $this->thickness = $thickness;

        return $this;
    }
}
