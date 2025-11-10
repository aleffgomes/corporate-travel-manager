<?php

namespace App\Domain;

use App\Models\UserModel as User;

class UserDomain
{
    private int $id;
    private string $name;
    private string $email;
    private string $createdAt;
    private string $updatedAt;

    private function __construct(
        int $id,
        string $name,
        string $email,
        string $createdAt,
        string $updatedAt
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromModel(User $user): self
    {
        return new self(
            $user->id,
            $user->name,
            $user->email,
            $user->created_at->toIso8601String(),
            $user->updated_at->toIso8601String()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}


