# Structured Output

Enforce the Agent output based on the provided schema.

## Overview

There are many use cases where we need Agents to understand natural language but output in a structured format. One
common use-case is extracting data from text to insert into a database or use with some downstream system.

Neuron allows you to enforce structured outputs from agents using PHP type hints and validation attributes.

## Basic Usage

Define a class with strictly typed properties:

```php
use NeuronAI\StructuredOutput\Attributes\SchemaProperty;
use NeuronAI\StructuredOutput\Validation\NotBlank;

class Person
{
    public function __construct(
        #[SchemaProperty(description: 'The person full name', required: true)]
        #[NotBlank]
        public string $name,
        
        #[SchemaProperty(description: 'The person age', required: true)]
        public int $age,
    ) {}
}
```text

Neuron generates the corresponding JSON schema from the PHP object to instruct the underlying model about your required
data format.

### Using with Agent

```php
use NeuronAI\Chat\Messages\UserMessage;

$person = MyAgent::make()->structured(
    Person::class,
    new UserMessage("Extract: John Doe is 30 years old")
);

echo $person->name; // "John Doe"
echo $person->age;  // 30
```

### Encapsulating in Agent

You can encapsulate the output format into the Agent implementation:

```php
class PersonExtractorAgent extends Agent
{
    public function extract(string $text): Person
    {
        return $this->structured(
            Person::class,
            new UserMessage($text)
        );
    }
}

$person = PersonExtractorAgent::make()->extract(
    "John Doe is 30 years old"
);
```text

## Two-Layer Validation System

Neuron requires two layers of rules for structured output:

### 1. SchemaProperty Attribute

Controls the JSON schema sent to the LLM to understand the required data format:

```php
#[SchemaProperty(
    description: 'The person full name',
    required: true
)]
public string $name;
```

**Always define at least:**

- `description`: Helps the LLM understand the property purpose
- `required`: Whether the property must be present

### 2. Validation Attributes

Ensures data gathered from the LLM response is consistent with your requirements:

```php
use NeuronAI\StructuredOutput\Validation\NotBlank;
use NeuronAI\StructuredOutput\Validation\StringLength;

#[SchemaProperty(description: 'The person full name', required: true)]
#[NotBlank]
#[StringLength(min: 2, max: 100)]
public string $name;
```text

## Complex Structures

### Nested Objects

Construct complex output structures using other PHP objects:

```php
class Address
{
    public function __construct(
        #[SchemaProperty(description: 'Street address', required: true)]
        #[NotBlank]
        public string $street,
        
        #[SchemaProperty(description: 'ZIP code', required: true)]
        #[NotBlank]
        public string $zipCode,
        
        #[SchemaProperty(description: 'City name', required: false)]
        public ?string $city = null,
    ) {}
}

class Person
{
    public function __construct(
        #[SchemaProperty(description: 'Person name', required: true)]
        #[NotBlank]
        public string $name,
        
        #[SchemaProperty(description: 'Person address', required: true)]
        public Address $address,
    ) {}
}
```

### Arrays of Strings

By default, array properties are assumed to be lists of strings:

```php
class Person
{
    public function __construct(
        #[SchemaProperty(description: 'Person name', required: true)]
        public string $name,
        
        #[SchemaProperty(description: 'List of tags', required: false)]
        public array $tags = [],
    ) {}
}
```text

### Arrays of Objects

To populate arrays with structured data types, use the `ArrayOf` attribute:

```php
use NeuronAI\StructuredOutput\Validation\ArrayOf;

class Person
{
    public function __construct(
        #[SchemaProperty(description: 'Person name', required: true)]
        public string $name,
        
        /**
         * @var Tag[]
         */
        #[SchemaProperty(description: 'List of tags', required: false)]
        #[ArrayOf(Tag::class)]
        public array $tags = [],
    ) {}
}

class Tag
{
    public function __construct(
        #[SchemaProperty(description: 'Tag name', required: true)]
        #[NotBlank]
        public string $name,
        
        #[SchemaProperty(description: 'Tag color', required: false)]
        public ?string $color = null,
    ) {}
}
```

### Multiple Object Types in Arrays

Use square brackets or array syntax:

```php
/**
 * @var Tag[]|Category[]
 */
#[ArrayOf([Tag::class, Category::class])]
public array $items = [];

// Or using array syntax:
/**
 * @var array<Tag|Category>
 */
#[ArrayOf([Tag::class, Category::class])]
public array $items = [];
```text

## Retry Mechanism

Since LLMs are not perfectly deterministic, Neuron includes a retry mechanism for validation failures.

By default, Neuron extracts and validates data from the LLM response. If validation errors occur, it automatically
retries once, informing the LLM about what went wrong.

### Customize Retry Count

```php
// Retry up to 3 times
$person = MyAgent::make()->structured(
    Person::class,
    new UserMessage("Extract person data"),
    maxRetry: 3
);

// Disable retry (one-shot attempt)
$person = MyAgent::make()->structured(
    Person::class,
    new UserMessage("Extract person data"),
    maxRetry: 0
);
```

**Tip:** For less capable LLMs, balance retry count with token consumption.

## Available Validation Rules

### NotBlank

Property cannot be blank. Accepts `allowNull` flag:

```php
#[NotBlank(allowNull: false)]
public string $name;
```text

### StringLength

Validate string length:

```php
#[StringLength(min: 2, max: 100)]
public string $name;
```

### WordsCount

Validate number of words in a string:

```php
#[WordsCount(min: 10, max: 500)]
public string $description;
```text

### ArraySize

Validate array size:

```php
#[ArraySize(min: 1, max: 10)]
public array $tags;
```

### Comparison Rules

**EqualTo / NotEqualTo:**

```php
#[EqualTo(5)]
public int $rating;

#[NotEqualTo(0)]
public int $quantity;
```text

**GreaterThan / GreaterThanEqual:**

```php
#[GreaterThan(0)]
public float $price;

#[GreaterThanEqual(18)]
public int $age;
```

**LowerThan / LowerThanEqual:**

```php
#[LowerThan(100)]
public int $percentage;

#[LowerThanEqual(5)]
public int $rating;
```text

### InRange

Validate number is within range:

```php
#[InRange(min: 0, max: 100)]
public int $percentage;
```

### Boolean Rules

```php
#[IsTrue]
public bool $accepted;

#[IsFalse]
public bool $rejected;
```text

### Nullable

```php
#[Nullable(true)]
public ?string $middleName;
```

### Format Validation

**JSON:**

```php
#[IsJson]
public string $metadata;
```text

**URL:**

```php
#[IsUrl]
public string $website;
```

**Email:**

```php
#[IsEmail]
public string $email;
```text

**IP Address:**

```php
#[IsIp]
public string $ipAddress;
```

### ArrayOf

Validate array contains specific object types:

```php
/**
 * @var Tag[]|Category[]
 */
#[ArrayOf([Tag::class, Category::class])]
public array $items;
```text

## Monitoring

Connect your Agent to the [Inspector monitoring dashboard](https://inspector.dev) to see structured output execution
flow in real-time, including validation attempts and retries.

```env
INSPECTOR_INGESTION_KEY=your_key_here
```

Each segment brings its own debug information to follow the agent execution in real time.

---

**Source:** <https://docs.neuron-ai.dev/getting-started/structured-output>
