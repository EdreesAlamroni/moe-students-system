<?php

namespace App\Services\School\ClassroomDistribution;

use App\Enums\ClassroomDistributionMethod;
use App\Services\School\ClassroomDistribution\Contracts\DistributionMethodContract;
use App\Services\School\ClassroomDistribution\Contracts\DistributionValidationRuleContract;
use App\Services\School\ClassroomDistribution\Methods\ManualDistributionMethod;
use App\Services\School\ClassroomDistribution\Methods\RandomDistributionMethod;
use App\Services\School\ClassroomDistribution\ValidationRules\ManualDistributionValidationRule;
use App\Services\School\ClassroomDistribution\ValidationRules\RandomDistributionValidationRule;
use InvalidArgumentException;

class ClassroomDistributionMethodRegistry
{
    /**
     * Defines the mapping for each classroom distribution method.
     *
     * @return array{
     *     method: class-string<DistributionMethodContract>,
     *     validation_rules: class-string<DistributionValidationRuleContract>,
     *     view: string,
     * }
     */
    private static function map(ClassroomDistributionMethod $method): array
    {
        $map = [
            ClassroomDistributionMethod::RANDOM->value => [
                'method' => RandomDistributionMethod::class,
                'validation_rules' => RandomDistributionValidationRule::class,
                'view' => 'school/classroom-distribution/methods/random',
            ],
            ClassroomDistributionMethod::MANUAL->value => [
                'method' => ManualDistributionMethod::class,
                'validation_rules' => ManualDistributionValidationRule::class,
                'view' => 'school/classroom-distribution/methods/manual',
            ],
        ];

        if (! array_key_exists($method->value, $map)) {
            throw new InvalidArgumentException(
                sprintf('Invalid classroom distribution method: %s', $method->value)
            );
        }

        return $map[$method->value];
    }

    /**
     * Resolves the method for a given classroom distribution method.
     *
     * @throws InvalidArgumentException
     */
    public static function getMethod(ClassroomDistributionMethod $method): DistributionMethodContract
    {
        $class = self::map($method)['method'];

        return app($class);
    }

    /**
     * Resolves the validation rules class for a given classroom distribution method.
     *
     * @throws InvalidArgumentException
     */
    public static function getValidationRules(ClassroomDistributionMethod $method): DistributionValidationRuleContract
    {
        $class = self::map($method)['validation_rules'];

        return app($class);
    }

    /**
     * Resolves the view path for a given classroom distribution method.
     *
     * @throws InvalidArgumentException
     */
    public static function getView(ClassroomDistributionMethod $method): string
    {
        return self::map($method)['view'];
    }
}
