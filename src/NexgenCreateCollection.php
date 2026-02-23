<?php

namespace Reliva\Nexgen;

class NexgenCreateCollection
{
    public String $name;
    public String $description;
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