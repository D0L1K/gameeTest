<?php
namespace Logic;

class Dto
{
    /**
     * @param array $data
     * @return Dto
     * @throws \ReflectionException
     */
    public static function fromDto(array $data): self
    {
        $obj = new static();
        $reflection = new \ReflectionClass($obj);

        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if (!array_key_exists($propertyName, $data)) {
                continue;
            }

            $obj->$propertyName = $data[$propertyName];
        }

        return $obj;
    }

    /**
     * @return array
     */
    public function toDto(): array
    {
        return (array)$this;
    }
}