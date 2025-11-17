<?php

namespace Reliva\Nexgen;

use Reliva\Nexgen\Enum\NexgenCollectionStatus;

class NexgenCreateCollection
{
    public String $name;
    public String $description;
    public NexgenCollectionStatus $status;
    public function __construct(String $name, String $description,)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    public function getName(): String
    {
        return $this->name;
    }

    public function getDescription(): String
    {
        return $this->description;
    }

  

  
}