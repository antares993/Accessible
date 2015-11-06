<?php

namespace Accessible;

use \Accessible\Reader\AutoConstructReader;
use \Accessible\Reader\ConstraintsReader;

trait AutoConstructTrait
{
    /**
     * Initializes the object according to its class specification and given arguments.
     */
    public function __construct()
    {
        $neededArguments = AutoConstructReader::getConstructArguments($this);
        $givenArguments = func_get_args();
        $constraintsValidationEnabled = ConstraintsReader::isConstraintsValidationEnabled($this);

        $numberOfNeededArguments = count($neededArguments);
        $numberOfGivenArguments = count($givenArguments);

        if ($numberOfGivenArguments !== $numberOfNeededArguments) {
            throw new \BadMethodCallException("Wrong number of arguments given to the constructor.");
        }

        // Initialize the properties that were defined using the Initialize / InitializeObject annotations
        $initialValues = AutoConstructReader::getPropertiesToInitialize($this);
        foreach ($initialValues as $propertyName => $value) {
            $this->$propertyName = $value;
        }

        // Initialize the propeties using given arguments
        for ($i = 0; $i < $numberOfNeededArguments; $i++) {
            $property = $neededArguments[$i];
            $argument = $givenArguments[$i];

            if ($constraintsValidationEnabled) {
                $constraintsViolations = ConstraintsReader::validatePropertyValue($this, $property, $argument);
                if ($constraintsViolations->count()) {
                    $errorMessage = "Object Initialization failed; argument given for the property $property is invalid; \
                    its constraints validation failed with the following messages: \"";
                    $errorMessageList = array();
                    foreach ($constraintsViolations as $violation) {
                        $errorMessageList[] = $violation->getMessage();
                    }
                    $errorMessage .= implode("\", \"", $errorMessageList)."\".";

                    throw new \InvalidArgumentException($errorMessage);
                }
            }

            $this->$property = $argument;
        }
    }
}
