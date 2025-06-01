<?php

namespace WindBox\Infrastructure\Http;

class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly array $queryParams;
    public readonly array $bodyParams;
    public readonly array $pathParams; 

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = strtok($_SERVER['REQUEST_URI'], '?');
        $this->queryParams = $_GET;
        $this->bodyParams = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->pathParams = []; 
    }

    public function input(string $key, $default = null)
    {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $this->pathParams[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->queryParams, $this->bodyParams, $this->pathParams);
    }

    public function setPathParams(array $params): void
    {
        $this->pathParams = $params;
    }

    public function validate(array $rules): void
    {
       
        foreach ($rules as $field => $ruleString) {
            $value = $this->input($field);
            $rulesArray = explode('|', $ruleString);

            foreach ($rulesArray as $rule) {
                if ($rule === 'required' && (empty($value) && $value !== 0 && $value !== '0')) {
                    throw new \InvalidArgumentException("Validation failed: {$field} is required.");
                }
                if ($rule === 'string' && !is_string($value)) {
                    throw new \InvalidArgumentException("Validation failed: {$field} must be a string.");
                }
                if ($rule === 'numeric' && !is_numeric($value)) {
                    throw new \InvalidArgumentException("Validation failed: {$field} must be numeric.");
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (float) substr($rule, 4);
                    if (is_numeric($value) && (float)$value < $min) {
                        throw new \InvalidArgumentException("Validation failed: {$field} must be at least {$min}.");
                    }
                }
                if (str_starts_with($rule, 'in:')) {
                    $allowedValues = explode(',', substr($rule, 3));
                    if (!in_array($value, $allowedValues)) {
                        throw new \InvalidArgumentException("Validation failed: {$field} must be one of " . implode(', ', $allowedValues) . ".");
                    }
                }
                if ($rule === 'date' && (!is_string($value) || !strtotime($value))) {
                    throw new \InvalidArgumentException("Validation failed: {$field} must be a valid date.");
                }
                if ($rule === 'nullable' && ($value === null || $value === '')) {
                    continue 2;
                }
            }
        }
    }
}