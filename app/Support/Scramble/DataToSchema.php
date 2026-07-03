<?php

namespace App\Support\Scramble;

use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use Dedoc\Scramble\Support\Generator\ClassBasedReference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApi;
use Dedoc\Scramble\Support\Generator\Types\Type as OpenApiType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class DataToSchema extends TypeToSchemaExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType && is_subclass_of($type->name, Data::class);
    }

    public function toSchema(Type $type): OpenApiType
    {
        $schema = new OpenApi\ObjectType();
        $required = [];

        $constructor = (new ReflectionClass($type->name))->getConstructor();

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $schema->addProperty(
                $parameter->getName(),
                $this->resolveParameter($parameter),
            );

            if (! $parameter->getType()?->allowsNull()) {
                $required[] = $parameter->getName();
            }
        }

        return $schema->setRequired($required);
    }

    public function toResponse(Type $type): Response
    {
        return Response::make(200)
            ->description('')
            ->setContent('application/json', Schema::fromType($this->openApiTransformer->transform($type)));
    }

    public function reference(ObjectType $type): \Dedoc\Scramble\Support\Generator\Reference
    {
        return ClassBasedReference::create('schemas', $type->name, $this->components);
    }

    private function resolveParameter(ReflectionParameter $parameter): OpenApiType
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType) {
            return new OpenApi\StringType();
        }

        return $this->resolveNamedType($type, $parameter)->nullable($type->allowsNull());
    }

    private function resolveNamedType(ReflectionNamedType $type, ReflectionParameter $parameter): OpenApiType
    {
        return match ($type->getName()) {
            'string' => new OpenApi\StringType(),
            'int' => new OpenApi\IntegerType(),
            'float' => new OpenApi\NumberType(),
            'bool' => new OpenApi\BooleanType(),
            'array' => $this->resolveArray($parameter),
            default => $this->resolveObject($type->getName()),
        };
    }

    private function resolveArray(ReflectionParameter $parameter): OpenApiType
    {
        $array = new OpenApi\ArrayType();

        foreach ($parameter->getAttributes(DataCollectionOf::class) as $attribute) {
            $itemClass = $attribute->getArguments()[0] ?? null;

            if (is_string($itemClass) && is_subclass_of($itemClass, Data::class)) {
                $array->setItems($this->componentReference($itemClass));
            }
        }

        return $array;
    }

    private function resolveObject(string $class): OpenApiType
    {
        if (is_subclass_of($class, Data::class)) {
            return $this->componentReference($class);
        }

        return new OpenApi\StringType();
    }

    private function componentReference(string $class): OpenApiType
    {
        return $this->openApiTransformer->transform(new ObjectType($class));
    }
}
